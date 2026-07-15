<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\AnalyticsProduk;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $errorsList = [];

    public function collection(Collection $rows)
    {
        // Filter out empty rows (rows where nama_produk is empty)
        $filteredRows = $rows->filter(function ($row) {
            $namaProduk = isset($row['nama_produk']) ? trim($row['nama_produk']) : '';
            return !empty($namaProduk);
        });

        if ($filteredRows->isEmpty()) {
            $this->errorsList[] = "File Excel kosong atau tidak memiliki baris data.";
            throw new Exception("Empty excel");
        }

        // Convert and sanitize rows
        $rowsArray = $filteredRows->map(function ($row) {
            // Cast string fields to string to avoid validator issues with numeric types parsed by Excel
            $stringFields = ['kode_barang', 'nama_produk', 'nama_display', 'kategori', 'warna', 'size', 'satuan', 'lokasi_default'];
            foreach ($stringFields as $field) {
                if (isset($row[$field]) && $row[$field] !== null && $row[$field] !== '') {
                    $row[$field] = (string)$row[$field];
                }
            }
            return $row;
        })->toArray();

        // Check if non-empty SKU has duplicates inside Excel
        $skus = array_filter(array_map('trim', array_column($rowsArray, 'kode_barang')));
        if (count($skus) !== count(array_unique($skus))) {
            $this->errorsList[] = "Terdapat Kode Barang duplikat di dalam file Excel.";
            throw new Exception("Duplicate SKU in excel");
        }

        // 1. Validate rows first
        $rules = [
            '*.kode_barang' => 'nullable|string|max:100|unique:mst_produk,kode_barang',
            '*.nama_produk' => 'required|string|max:200',
            '*.nama_display' => 'required|string|max:200',
            '*.kategori' => 'required|string|max:100',
            '*.warna' => 'required|string|max:100',
            '*.size' => 'nullable|string|max:50',
            '*.satuan' => 'required|string|max:20',
            '*.lokasi_default' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $val = strtoupper(trim($value));
                    if (!in_array($val, ['GUDANG', 'TOKO']) && !\App\Models\Lokasi::where(['kode_lokasi' => $val])->exists()) {
                        $fail("Lokasi Default '{$value}' tidak terdaftar di sistem. Gunakan kode lokasi yang valid (seperti GDG-A01, TKO-R01) atau GUDANG / TOKO.");
                    }
                }
            ],
            '*.hpp_per_pcs' => 'required|numeric|min:0',
            '*.harga_jual' => 'required|numeric|min:0',
            '*.stok_awal' => 'nullable|integer|min:0',
        ];

        // Format custom attributes for validation errors
        $customAttributes = [
            '*.kode_barang' => 'Kode Barang',
            '*.nama_produk' => 'Nama Produk',
            '*.nama_display' => 'Nama Display',
            '*.kategori' => 'Kategori',
            '*.warna' => 'Warna',
            '*.size' => 'Size',
            '*.satuan' => 'Satuan',
            '*.lokasi_default' => 'Lokasi Default',
            '*.hpp_per_pcs' => 'HPP per Pcs',
            '*.harga_jual' => 'Harga Jual',
            '*.stok_awal' => 'Stok Awal',
        ];

        $validator = Validator::make($rowsArray, $rules, [], $customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                // Clean the index for a nicer error message
                $cleanedError = preg_replace('/The \d+\./i', 'Kolom ', $error);
                $this->errorsList[] = $cleanedError;
            }
            throw new Exception("Validation failed");
        }

        DB::transaction(function () use ($rowsArray) {
            $itemsWithStock = [];

            foreach ($rowsArray as $row) {
                $lokasi = strtoupper(trim($row['lokasi_default']));
                if ($lokasi === 'GUDANG') {
                    $lokasi = 'GDG-A01';
                } elseif ($lokasi === 'TOKO') {
                    $lokasi = 'TKO-R01';
                }

                $qty = isset($row['stok_awal']) ? (int)$row['stok_awal'] : 0;
                $hpp = (float)$row['hpp_per_pcs'];

                $warna = isset($row['warna']) ? trim($row['warna']) : '';
                $size = isset($row['size']) ? trim($row['size']) : '';

                if ($warna !== '' && $size !== '') {
                    $variasi = "{$warna} / {$size}";
                } elseif ($warna !== '') {
                    $variasi = $warna;
                } else {
                    $variasi = $size;
                }

                // Auto-generate SKU if blank
                $kodeBarangInput = isset($row['kode_barang']) ? trim($row['kode_barang']) : '';
                if (empty($kodeBarangInput)) {
                    $kodeBarang = \App\Services\SkuGeneratorService::generate(
                        trim($row['nama_produk']),
                        trim($row['kategori']),
                        $variasi
                    );
                } else {
                    $kodeBarang = $kodeBarangInput;
                }

                // Create product
                $product = Produk::create([
                    'kode_barang' => $kodeBarang,
                    'nama_produk' => trim($row['nama_produk']),
                    'nama_barang' => trim($row['nama_display']),
                    'kategori' => trim($row['kategori']),
                    'kategori_2' => 'Produk Berjalan', // Default Kategori 2 from PRD
                    'variasi_barang' => $variasi,
                    'warna' => $warna,
                    'size' => $size,
                    'satuan' => trim($row['satuan']),
                    'lokasi' => $lokasi,
                    'hpp' => $hpp,
                    'harga_jual' => (float)$row['harga_jual'],
                    'is_active' => true,
                    'stok_masuk' => $qty,
                    'stok_keluar' => 0,
                    'total_stok' => $qty,
                ]);

                // Create matching analytics entry
                AnalyticsProduk::create([
                    'produk_id' => $product->id,
                    'kategori' => $product->kategori,
                    'penjualan_jan' => 0,
                    'penjualan_apr' => 0,
                    'penjualan_mei' => 0,
                    'rata_rata_harian' => 0.00,
                    'leadtime_produksi' => 14,
                    'minimal_stock' => 0,
                    'safety_stock' => 0,
                    'maximum_stock' => 0,
                ]);

                $this->importedCount++;

                // Prepare detail for transaction header if there is initial stock
                if ($qty > 0) {
                    $itemsWithStock[] = [
                        'produk_id' => $product->id,
                        'qty_masuk' => $qty,
                        'satuan' => $product->satuan,
                        'lokasi_simpan' => $lokasi,
                        'hpp' => $hpp,
                    ];
                }
            }

            // Create a single "Stock Awal" transaction header for all initial stocks
            if (count($itemsWithStock) > 0) {
                $header = BarangMasuk::create([
                    'tanggal' => now()->format('Y-m-d'),
                    'tipe_nota' => 'Stock Awal',
                    'supplier_id' => null,
                    'nomor_nota' => 'SA-IMPORT-' . date('YmdHis'),
                    'keterangan_tambahan' => 'Import Awal via Excel',
                    'data_dimasukan_oleh' => Auth::user() ? Auth::user()->name : 'System',
                ]);

                foreach ($itemsWithStock as $item) {
                    $header->details()->create([
                        'produk_id' => $item['produk_id'],
                        'qty_masuk' => $item['qty_masuk'],
                        'satuan' => $item['satuan'],
                        'lokasi_simpan' => $item['lokasi_simpan'],
                        'hpp' => $item['hpp'],
                    ]);

                    // Log initial mutation log
                    $lokModel = \App\Models\Lokasi::where(['kode_lokasi' => $item['lokasi_simpan']])->first();
                    if ($lokModel) {
                        \App\Models\MutasiLokasi::create([
                            'produk_id' => $item['produk_id'],
                            'lokasi_asal_id' => null,
                            'lokasi_tujuan_id' => $lokModel->id,
                            'qty' => $item['qty_masuk'],
                            'tipe_mutasi' => 'MASUK',
                            'referensi_tipe' => get_class($header),
                            'referensi_id' => $header->id,
                            'keterangan' => 'Stock Awal via Import Excel',
                            'data_dimasukan_oleh' => $header->data_dimasukan_oleh,
                        ]);
                    }
                }
            }
        });
    }
}
