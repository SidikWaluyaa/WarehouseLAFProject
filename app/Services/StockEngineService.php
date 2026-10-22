<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\PoDetail;
use App\Models\StokLokasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockEngineService
{
    /**
     * Menambahkan transaksi barang masuk (Pembelian, Stock Awal, Retur Masuk, Penyesuaian)
     * dan mengupdate stok produk serta menghitung HPP dengan metode Weighted Average.
     */
    public static function tambahBarangMasuk(array $data): BarangMasuk
    {
        try {
            return DB::transaction(function () use ($data) {
                $nomorNota = $data['nomor_nota'] ?? null;
                if (empty($nomorNota)) {
                    $nomorNota = self::generateNomorNotaBarangMasuk($data['tanggal']);
                }
                // 1. Buat Header Transaksi Barang Masuk
                $header = BarangMasuk::create([
                    'tanggal' => $data['tanggal'],
                    'tipe_nota' => $data['tipe_nota'],
                    'supplier_id' => $data['supplier_id'],
                    'nomor_nota' => $nomorNota,
                    'po_header_id' => $data['po_header_id'] ?? null,
                    'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
                    'data_dimasukan_oleh' => $data['data_dimasukan_oleh'],
                ]);

                // 2. Loop & Proses Setiap Item Detail
                foreach ($data['items'] as $item) {
                    $produk = Produk::where(['id' => $item['produk_id']])->lockForUpdate()->firstOrFail();
                    $qtyMasuk = (int)$item['qty_masuk'];
                    $hargaTransaksi = (float)$item['hpp'];

                    // Dapatkan Lokasi Simpan
                    $lokasiCode = $item['lokasi_simpan'] ?? $produk->lokasi;
                    $lokasi = \App\Models\Lokasi::where(['kode_lokasi' => $lokasiCode])->first();
                    if (!$lokasi) {
                        $tipe = in_array(strtoupper($lokasiCode), ['TOKO', 'GUDANG']) ? strtoupper($lokasiCode) : 'GUDANG';
                        $lokasi = \App\Models\Lokasi::firstOrCreate(
                            ['kode_lokasi' => $lokasiCode],
                            ['nama_lokasi' => 'Lokasi ' . $lokasiCode, 'tipe_lokasi' => $tipe, 'is_active' => true]
                        );
                    }

                    // Hitung HPP Baru menggunakan metode Weighted Average
                    $stokSebelumnya = $produk->total_stok;
                    $hppSebelumnya = $produk->hpp;

                    if ($stokSebelumnya <= 0) {
                        $hppBaru = $hargaTransaksi;
                    } else {
                        $nilaiStokLama = $stokSebelumnya * $hppSebelumnya;
                        $nilaiStokBaru = $qtyMasuk * $hargaTransaksi;
                        $totalStokBaru = $stokSebelumnya + $qtyMasuk;
                        $hppBaru = ($nilaiStokLama + $nilaiStokBaru) / $totalStokBaru;
                    }

                    // Update data produk
                    $produk->hpp = $hppBaru;
                    $produk->stok_masuk += $qtyMasuk;
                    $produk->total_stok += $qtyMasuk;
                    $produk->save();

                    // Tambahkan ke stok lokasi (pessimistic lock)
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id,
                    ])->lockForUpdate()->first();

                    if (!$stokLokasi) {
                        $stokLokasi = StokLokasi::create([
                            'produk_id' => $produk->id,
                            'lokasi_id' => $lokasi->id,
                            'qty' => 0,
                        ]);
                    }

                    $stokLokasi->qty += $qtyMasuk;
                    $stokLokasi->save();

                    // Catat Log Mutasi Lokasi
                    \App\Models\MutasiLokasi::create([
                        'produk_id' => $produk->id,
                        'lokasi_asal_id' => null,
                        'lokasi_tujuan_id' => $lokasi->id,
                        'qty' => $qtyMasuk,
                        'tipe_mutasi' => 'MASUK',
                        'referensi_tipe' => get_class($header),
                        'referensi_id' => $header->id,
                        'keterangan' => 'Barang Masuk: ' . $header->nomor_nota,
                        'data_dimasukan_oleh' => $data['data_dimasukan_oleh'],
                    ]);

                    // Update data PO jika item ini merujuk ke PO (+ snapshot riwayat selisih)
                    $sisaSebelum = null;
                    $selisih = null;
                    if (!empty($item['po_detail_id'])) {
                        $poDetail = PoDetail::where(['id' => $item['po_detail_id']])->lockForUpdate()->first();
                        if ($poDetail) {
                            $sisaSebelum = (int) $poDetail->sisa;
                            $selisih = $qtyMasuk - $sisaSebelum;
                            $poDetail->barang_masuk += $qtyMasuk;
                            $poDetail->recalculateSisa();
                            $poDetail->save();
                        }
                    }

                    // Buat Detail Transaksi
                    $header->details()->create([
                        'produk_id' => $produk->id,
                        'qty_masuk' => $qtyMasuk,
                        'satuan' => $produk->satuan,
                        'lokasi_simpan' => $lokasiCode,
                        'hpp' => $hargaTransaksi,
                        'po_detail_id' => !empty($item['po_detail_id']) ? $item['po_detail_id'] : null,
                        'sisa_po_sebelum' => $sisaSebelum,
                        'selisih_po' => $selisih,
                        'alasan_selisih' => ($selisih !== null && $selisih !== 0) ? (trim($item['alasan_selisih'] ?? '') ?: null) : null,
                    ]);
                }

                // Audit Logging (Big 4 standard)
                $newValues = [
                    'header' => $header->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : ($data['data_dimasukan_oleh'] ?? 'System'),
                    'action' => 'CREATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => null,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in StockEngineService::tambahBarangMasuk: " . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Menambahkan transaksi barang keluar (Penjualan, Sample, Retur Keluar, Penyesuaian)
     * dan mengupdate stok produk dengan validasi sisa stok.
     */
    public static function tambahBarangKeluar(array $data): BarangKeluar
    {
        try {
            return DB::transaction(function () use ($data) {
                $nomorNota = $data['nomor_nota'] ?? null;
                if (empty($nomorNota)) {
                    $nomorNota = self::generateNomorNotaBarangKeluar($data['tanggal']);
                }
                // 1. Buat Header Transaksi Barang Keluar
                $header = BarangKeluar::create([
                    'tanggal' => $data['tanggal'],
                    'tipe_nota' => $data['tipe_nota'],
                    'pelanggan_id' => $data['pelanggan_id'],
                    'nomor_nota' => $nomorNota,
                    'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
                    'dikeluarkan_oleh' => $data['dikeluarkan_oleh'],
                ]);

                $affectedProductIds = [];

                // 2. Loop & Proses Setiap Item Detail
                foreach ($data['items'] as $item) {
                    $produk = Produk::where(['id' => $item['produk_id']])->lockForUpdate()->firstOrFail();
                    $affectedProductIds[] = $produk->id;
                    $qtyKeluar = (int)$item['qty_keluar'];

                    // Dapatkan Lokasi Pengambilan
                    $lokasiCode = $item['lokasi_pengambilan'] ?? $produk->lokasi;
                    $lokasi = \App\Models\Lokasi::where(['kode_lokasi' => $lokasiCode])->first();
                    if (!$lokasi) {
                        $tipe = in_array(strtoupper($lokasiCode), ['TOKO', 'GUDANG']) ? strtoupper($lokasiCode) : 'GUDANG';
                        $lokasi = \App\Models\Lokasi::firstOrCreate(
                            ['kode_lokasi' => $lokasiCode],
                            ['nama_lokasi' => 'Lokasi ' . $lokasiCode, 'tipe_lokasi' => $tipe, 'is_active' => true]
                        );
                    }

                    // Validasi stok di lokasi terpilih mencukupi (pessimistic lock)
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id,
                    ])->lockForUpdate()->first();
                    $stokTersedia = $stokLokasi ? $stokLokasi->qty : 0;

                    if ($stokTersedia < $qtyKeluar) {
                        throw new Exception("Stok produk '{$produk->nama_barang}' tidak mencukupi! Stok saat ini di lokasi '{$lokasi->kode_lokasi}': {$stokTersedia} {$produk->satuan}, diminta: {$qtyKeluar} {$produk->satuan}.");
                    }

                    // Update data produk
                    $produk->stok_keluar += $qtyKeluar;
                    $produk->total_stok -= $qtyKeluar;
                    $produk->save();

                    // Kurangi stok di lokasi
                    $stokLokasi->qty -= $qtyKeluar;
                    $stokLokasi->save();

                    // Catat Log Mutasi Lokasi
                    \App\Models\MutasiLokasi::create([
                        'produk_id' => $produk->id,
                        'lokasi_asal_id' => $lokasi->id,
                        'lokasi_tujuan_id' => null,
                        'qty' => $qtyKeluar,
                        'tipe_mutasi' => 'KELUAR',
                        'referensi_tipe' => get_class($header),
                        'referensi_id' => $header->id,
                        'keterangan' => 'Barang Keluar: ' . $header->nomor_nota,
                        'data_dimasukan_oleh' => $data['dikeluarkan_oleh'],
                    ]);

                    // Buat Detail Transaksi
                    $header->details()->create([
                        'produk_id' => $produk->id,
                        'qty_keluar' => $qtyKeluar,
                        'satuan' => $produk->satuan,
                        'lokasi_pengambilan' => $lokasiCode,
                        'hpp' => $produk->hpp, // Snapshot HPP produk saat ini sebagai harga pokok barang keluar
                    ]);
                }

                // Recalculate analytics reactively for all affected products in batch
                \App\Services\AnalyticsService::syncAndRecalculateProducts($affectedProductIds);

                // Audit Logging (Big 4 standard)
                $newValues = [
                    'header' => $header->toArray(),
                    'details' => $header->details->toArray()
                ];

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : ($data['dikeluarkan_oleh'] ?? 'System'),
                    'action' => 'CREATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => null,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return $header;
            });
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error in StockEngineService::tambahBarangKeluar: " . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate otomatis nomor nota untuk Barang Masuk (BM/YYYYMMDD/XXXX)
     */
    public static function generateNomorNotaBarangMasuk(string $date): string
    {
        $prefix = 'BM/' . date('Ymd', strtotime($date)) . '/';
        $latest = BarangMasuk::whereDate('tanggal', $date)
            ->where([['nomor_nota', 'like', $prefix . '%']])
            ->orderByRaw('nomor_nota desc')
            ->lockForUpdate()
            ->first();
            
        if ($latest) {
            $lastNumber = (int) substr($latest->nomor_nota, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate otomatis nomor nota untuk Barang Keluar (BK/YYYYMMDD/XXXX)
     */
    public static function generateNomorNotaBarangKeluar(string $date): string
    {
        $prefix = 'BK/' . date('Ymd', strtotime($date)) . '/';
        $latest = BarangKeluar::whereDate('tanggal', $date)
            ->where([['nomor_nota', 'like', $prefix . '%']])
            ->orderByRaw('nomor_nota desc')
            ->lockForUpdate()
            ->first();
            
        if ($latest) {
            $lastNumber = (int) substr($latest->nomor_nota, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate otomatis nomor Purchase Order (PO/YYYYMMDD/XXXX)
     */
    public static function generateNomorPo(string $date): string
    {
        $prefix = 'PO/' . date('Ymd', strtotime($date)) . '/';
        $latest = \App\Models\PoHeader::whereDate('tanggal', $date)
            ->where('nomor_po', 'like', $prefix . '%')
            ->orderByRaw('nomor_po desc')
            ->lockForUpdate()
            ->first();
            
        if ($latest && !empty($latest->nomor_po)) {
            $lastNumber = (int) substr($latest->nomor_po, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mengubah transaksi barang masuk (Pembelian, Stock Awal, Retur Masuk, Penyesuaian)
     * dengan metode Reversal dan Re-apply untuk menjaga konsistensi stok dan HPP,
     * serta mencatat riwayat ke dalam Audit Log.
     */
    public static function ubahBarangMasuk(int $id, array $data): BarangMasuk
    {
        return DB::transaction(function () use ($id, $data) {
            // Find existing header
            $header = BarangMasuk::with('details')->lockForUpdate()->findOrFail($id);

            // Capture old values for Audit Log
            $oldHeaderData = $header->toArray();
            $oldDetailsData = $header->details->toArray();

            $revertedProductIds = [];

            // 1. REVERSAL STEP
            foreach ($header->details as $oldDetail) {
                $produk = Produk::where('id', $oldDetail->produk_id)->lockForUpdate()->firstOrFail();
                $qtyOld = $oldDetail->qty_masuk;
                $revertedProductIds[] = $produk->id;

                // Revert StokLokasi
                $lokasiCode = $oldDetail->lokasi_simpan;
                $lokasi = \App\Models\Lokasi::where('kode_lokasi', $lokasiCode)->first();
                $stokLokasi = null;
                if ($lokasi) {
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id
                    ])->lockForUpdate()->first();
                }

                $stokLokasiQty = $stokLokasi ? $stokLokasi->qty : 0;

                // Guard: check if reversal would cause negative stock
                if ($produk->total_stok - $qtyOld < 0) {
                    throw new Exception("Tidak dapat membatalkan/mengubah transaksi barang masuk karena total stok produk '{$produk->nama_barang}' akan menjadi negatif ({$produk->total_stok} - {$qtyOld} = " . ($produk->total_stok - $qtyOld) . ").");
                }
                if ($stokLokasi && $stokLokasiQty - $qtyOld < 0) {
                    throw new Exception("Tidak dapat membatalkan/mengubah transaksi barang masuk karena stok produk '{$produk->nama_barang}' di lokasi '{$lokasiCode}' akan menjadi negatif ({$stokLokasiQty} - {$qtyOld} = " . ($stokLokasiQty - $qtyOld) . ").");
                }

                // Revert product stok
                $produk->stok_masuk -= $qtyOld;
                $produk->total_stok -= $qtyOld;
                
                // Revert HPP: Weighted Average Reversal
                $stokSebelumReversal = $produk->total_stok + $qtyOld;
                if ($produk->total_stok <= 0) {
                    if ($produk->total_stok == 0) {
                        $produk->hpp = 0;
                    }
                } else {
                    $nilaiTotalSebelum = $stokSebelumReversal * $produk->hpp;
                    $nilaiDikeluarkan = $qtyOld * (float)$oldDetail->hpp;
                    $nilaiSisa = max(0, $nilaiTotalSebelum - $nilaiDikeluarkan);
                    $produk->hpp = $nilaiSisa / $produk->total_stok;
                }
                $produk->save();

                if ($stokLokasi) {
                    $stokLokasi->qty -= $qtyOld;
                    $stokLokasi->save();
                }

                // Revert PO details sisa & barang_masuk
                if (!empty($oldDetail->po_detail_id)) {
                    $poDetail = PoDetail::where('id', $oldDetail->po_detail_id)->lockForUpdate()->first();
                    if ($poDetail) {
                        $poDetail->barang_masuk -= $qtyOld;
                        $poDetail->recalculateSisa();
                        $poDetail->save();
                    }
                }
            }

            // Delete old details & mutasi lokasi records
            $header->details()->delete();
            \App\Models\MutasiLokasi::where([
                'referensi_tipe' => get_class($header),
                'referensi_id' => $header->id
            ])->delete();

            // 2. RE-APPLY STEP
            $newDetailsData = [];
            foreach ($data['items'] as $item) {
                $produk = Produk::where('id', $item['produk_id'])->lockForUpdate()->firstOrFail();
                $qtyNew = (int)$item['qty_masuk'];
                $hargaNew = (float)$item['hpp'];

                // Dapatkan Lokasi Simpan
                $lokasiCode = $item['lokasi_simpan'] ?? $produk->lokasi;
                $lokasi = \App\Models\Lokasi::where('kode_lokasi', $lokasiCode)->first();
                if (!$lokasi) {
                    $tipe = in_array(strtoupper($lokasiCode), ['TOKO', 'GUDANG']) ? strtoupper($lokasiCode) : 'GUDANG';
                    $lokasi = \App\Models\Lokasi::firstOrCreate(
                        ['kode_lokasi' => $lokasiCode],
                        ['nama_lokasi' => 'Lokasi ' . $lokasiCode, 'tipe_lokasi' => $tipe, 'is_active' => true]
                    );
                }

                // Hitung HPP Baru menggunakan metode Weighted Average
                $stokSebelumnya = $produk->total_stok;
                $hppSebelumnya = $produk->hpp;

                if ($stokSebelumnya <= 0) {
                    $hppBaru = $hargaNew;
                } else {
                    $nilaiStokLama = $stokSebelumnya * $hppSebelumnya;
                    $nilaiStokBaru = $qtyNew * $hargaNew;
                    $totalStokBaru = $stokSebelumnya + $qtyNew;
                    $hppBaru = ($nilaiStokLama + $nilaiStokBaru) / $totalStokBaru;
                }

                // Update data produk
                $produk->hpp = $hppBaru;
                $produk->stok_masuk += $qtyNew;
                $produk->total_stok += $qtyNew;
                $produk->save();

                // Update / Buat stok per lokasi
                $stokLokasi = StokLokasi::firstOrCreate(
                    ['produk_id' => $produk->id, 'lokasi_id' => $lokasi->id],
                    ['qty' => 0]
                );
                $stokLokasi->qty += $qtyNew;
                $stokLokasi->save();

                // Catat Log Mutasi Lokasi
                \App\Models\MutasiLokasi::create([
                    'produk_id' => $produk->id,
                    'lokasi_asal_id' => null,
                    'lokasi_tujuan_id' => $lokasi->id,
                    'qty' => $qtyNew,
                    'tipe_mutasi' => 'MASUK',
                    'referensi_tipe' => get_class($header),
                    'referensi_id' => $header->id,
                    'keterangan' => 'Barang Masuk (Edit): ' . ($data['nomor_nota'] ?? $header->nomor_nota),
                    'data_dimasukan_oleh' => $data['data_dimasukan_oleh'],
                ]);

                // Update data PO jika item ini merujuk ke PO
                $sisaSebelum = null;
                $selisih = null;
                if (!empty($item['po_detail_id'])) {
                    $poDetail = PoDetail::where('id', $item['po_detail_id'])->lockForUpdate()->first();
                    if ($poDetail) {
                        $sisaSebelum = (int) $poDetail->sisa;
                        $selisih = $qtyNew - $sisaSebelum;
                        $poDetail->barang_masuk += $qtyNew;
                        $poDetail->recalculateSisa();
                        $poDetail->save();
                    }
                }

                // Buat Detail Transaksi baru
                $newDetail = $header->details()->create([
                    'produk_id' => $produk->id,
                    'qty_masuk' => $qtyNew,
                    'satuan' => $produk->satuan,
                    'lokasi_simpan' => $lokasiCode,
                    'hpp' => $hargaNew,
                    'po_detail_id' => !empty($item['po_detail_id']) ? $item['po_detail_id'] : null,
                    'sisa_po_sebelum' => $sisaSebelum,
                    'selisih_po' => $selisih,
                    'alasan_selisih' => ($selisih !== null && $selisih !== 0) ? (trim($item['alasan_selisih'] ?? '') ?: null) : null,
                ]);

                $newDetailsData[] = $newDetail->toArray();
            }

            // Update Header
            $header->update([
                'tanggal' => $data['tanggal'],
                'tipe_nota' => $data['tipe_nota'],
                'supplier_id' => $data['supplier_id'],
                'nomor_nota' => $data['nomor_nota'] ?? $header->nomor_nota,
                'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
            ]);

            // 3. AUDIT LOGGING (Big 4 standard)
            $oldValues = [
                'header' => $oldHeaderData,
                'details' => $oldDetailsData
            ];
            $newValues = [
                'header' => $header->fresh()->toArray(),
                'details' => $newDetailsData
            ];

            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'username' => Auth::user() ? Auth::user()->name : 'System',
                'action' => 'UPDATE',
                'auditable_type' => get_class($header),
                'auditable_id' => $header->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // 4. CLEANUP ORPHANED PRODUCTS
            foreach (array_unique($revertedProductIds) as $pId) {
                $p = Produk::find($pId);
                if ($p) {
                    self::bersihkanProdukTanpaRiwayat($p);
                }
            }

            return $header;
        });
    }

    /**
     * Menghapus transaksi barang masuk (Pembelian, Stock Awal, Retur Masuk, Penyesuaian)
     * dengan metode Reversal untuk memulihkan stok dan HPP ke kondisi sebelumnya,
     * serta mencatat riwayat ke dalam Audit Log.
     */
    public static function hapusBarangMasuk(int $id): void
    {
        DB::transaction(function () use ($id) {
            $header = BarangMasuk::with('details')->lockForUpdate()->findOrFail($id);

            // Capture old values for Audit Log
            $oldHeaderData = $header->toArray();
            $oldDetailsData = $header->details->toArray();

            $revertedProductIds = [];

            // 1. REVERSAL STEP
            foreach ($header->details as $oldDetail) {
                $produk = Produk::where('id', $oldDetail->produk_id)->lockForUpdate()->firstOrFail();
                $qtyOld = $oldDetail->qty_masuk;
                $revertedProductIds[] = $produk->id;

                // Revert StokLokasi
                $lokasiCode = $oldDetail->lokasi_simpan;
                $lokasi = \App\Models\Lokasi::where('kode_lokasi', $lokasiCode)->first();
                $stokLokasi = null;
                if ($lokasi) {
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id
                    ])->lockForUpdate()->first();
                }

                $stokLokasiQty = $stokLokasi ? $stokLokasi->qty : 0;

                // Guard: check if reversal would cause negative stock
                if ($produk->total_stok - $qtyOld < 0) {
                    throw new Exception("Tidak dapat membatalkan/mengubah transaksi barang masuk karena total stok produk '{$produk->nama_barang}' akan menjadi negatif ({$produk->total_stok} - {$qtyOld} = " . ($produk->total_stok - $qtyOld) . ").");
                }
                if ($stokLokasi && $stokLokasiQty - $qtyOld < 0) {
                    throw new Exception("Tidak dapat membatalkan/mengubah transaksi barang masuk karena stok produk '{$produk->nama_barang}' di lokasi '{$lokasiCode}' akan menjadi negatif ({$stokLokasiQty} - {$qtyOld} = " . ($stokLokasiQty - $qtyOld) . ").");
                }

                // Revert product stok
                $produk->stok_masuk -= $qtyOld;
                $produk->total_stok -= $qtyOld;
                
                // Revert HPP: Weighted Average Reversal
                $stokSebelumReversal = $produk->total_stok + $qtyOld;
                if ($produk->total_stok <= 0) {
                    if ($produk->total_stok == 0) {
                        $produk->hpp = 0;
                    }
                } else {
                    $nilaiTotalSebelum = $stokSebelumReversal * $produk->hpp;
                    $nilaiDikeluarkan = $qtyOld * (float)$oldDetail->hpp;
                    $nilaiSisa = max(0, $nilaiTotalSebelum - $nilaiDikeluarkan);
                    $produk->hpp = $nilaiSisa / $produk->total_stok;
                }
                $produk->save();

                if ($stokLokasi) {
                    $stokLokasi->qty -= $qtyOld;
                    $stokLokasi->save();
                }

                // Revert PO details sisa & barang_masuk
                if (!empty($oldDetail->po_detail_id)) {
                    $poDetail = PoDetail::where('id', $oldDetail->po_detail_id)->lockForUpdate()->first();
                    if ($poDetail) {
                        $poDetail->barang_masuk -= $qtyOld;
                        $poDetail->recalculateSisa();
                        $poDetail->save();
                    }
                }
            }

            // Delete details & mutasi lokasi records
            $header->details()->delete();
            \App\Models\MutasiLokasi::where([
                'referensi_tipe' => get_class($header),
                'referensi_id' => $header->id
            ])->delete();

            // 2. AUDIT LOGGING (Big 4 standard)
            $oldValues = [
                'header' => $oldHeaderData,
                'details' => $oldDetailsData
            ];

            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'username' => Auth::user() ? Auth::user()->name : 'System',
                'action' => 'DELETE',
                'auditable_type' => get_class($header),
                'auditable_id' => $header->id,
                'old_values' => $oldValues,
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Delete the header
            $header->delete();

            // 3. CLEANUP ORPHANED PRODUCTS
            foreach (array_unique($revertedProductIds) as $pId) {
                $p = Produk::find($pId);
                if ($p) {
                    self::bersihkanProdukTanpaRiwayat($p);
                }
            }
        });
    }

    /**
     * Menghapus transaksi barang keluar (Penjualan, Sample, Retur Keluar, Penyesuaian)
     * dengan metode Reversal untuk memulihkan stok ke kondisi sebelumnya,
     * serta mencatat riwayat ke dalam Audit Log.
     */
    public static function hapusBarangKeluar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $header = BarangKeluar::with('details')->lockForUpdate()->findOrFail($id);

            // Capture old values for Audit Log
            $oldHeaderData = $header->toArray();
            $oldDetailsData = $header->details->toArray();

            $revertedProductIds = [];

            // 1. REVERSAL STEP
            foreach ($header->details as $oldDetail) {
                $produk = Produk::where('id', $oldDetail->produk_id)->lockForUpdate()->firstOrFail();
                $qtyOld = $oldDetail->qty_keluar;
                $revertedProductIds[] = $produk->id;

                // Revert StokLokasi
                $lokasiCode = $oldDetail->lokasi_pengambilan;
                $lokasi = \App\Models\Lokasi::where('kode_lokasi', $lokasiCode)->first();
                $stokLokasi = null;
                if ($lokasi) {
                    $stokLokasi = StokLokasi::where([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id
                    ])->lockForUpdate()->first();
                }

                if (!$stokLokasi && $lokasi) {
                    $stokLokasi = StokLokasi::create([
                        'produk_id' => $produk->id,
                        'lokasi_id' => $lokasi->id,
                        'qty' => 0
                    ]);
                }

                // Revert product stok
                $produk->stok_keluar -= $qtyOld;
                $produk->total_stok += $qtyOld;
                $produk->save();

                if ($stokLokasi) {
                    $stokLokasi->qty += $qtyOld;
                    $stokLokasi->save();
                }

                // Catat Log Mutasi Lokasi Reversal
                \App\Models\MutasiLokasi::create([
                    'produk_id' => $produk->id,
                    'lokasi_asal_id' => null,
                    'lokasi_tujuan_id' => $lokasi ? $lokasi->id : null,
                    'qty' => $qtyOld,
                    'tipe_mutasi' => 'MASUK', // Reversal of KELUAR is MASUK
                    'referensi_tipe' => get_class($header),
                    'referensi_id' => $header->id,
                    'keterangan' => 'Barang Keluar (Batal/Hapus): ' . $header->nomor_nota,
                    'data_dimasukan_oleh' => Auth::user() ? Auth::user()->name : ($header->dikeluarkan_oleh ?? 'System'),
                ]);
            }

            // Delete Details
            $header->details()->delete();

            // 2. AUDIT LOGGING (Big 4 standard)
            $oldValues = [
                'header' => $oldHeaderData,
                'details' => $oldDetailsData
            ];

            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'username' => Auth::user() ? Auth::user()->name : ($header->dikeluarkan_oleh ?? 'System'),
                'action' => 'DELETE',
                'auditable_type' => get_class($header),
                'auditable_id' => $id,
                'old_values' => $oldValues,
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Delete the header
            $header->delete();

            // 3. CLEANUP ORPHANED PRODUCTS
            foreach (array_unique($revertedProductIds) as $pId) {
                $p = Produk::find($pId);
                if ($p) {
                    self::bersihkanProdukTanpaRiwayat($p);
                }
            }

            // 4. Recalculate analytics reactively for all affected products in batch
            \App\Services\AnalyticsService::syncAndRecalculateProducts($revertedProductIds);
        });
    }

    /**
     * Membersihkan produk dari master produk jika tidak memiliki riwayat transaksi lain
     * dan stoknya <= 0 (sepenuhnya dibalikkan).
     */
    private static function bersihkanProdukTanpaRiwayat(Produk $produk): void
    {
        // Cek apakah ada riwayat di transaksi barang masuk
        $hasBarangMasuk = \App\Models\BarangMasukDetail::where('produk_id', $produk->id)->exists();

        // Cek riwayat transaksi barang keluar
        $hasBarangKeluar = \App\Models\BarangKeluarDetail::where('produk_id', $produk->id)->exists();

        // Cek riwayat Purchase Order (PO)
        $hasPo = PoDetail::where('produk_id', $produk->id)->exists();

        // Cek riwayat Transfer & Picking
        $hasTransfer = \App\Models\TransferDetail::where('produk_id', $produk->id)->exists();
        $hasPicking = \App\Models\PickingDetail::where('produk_id', $produk->id)->exists();

        if (!$hasBarangMasuk && !$hasBarangKeluar && !$hasPo && !$hasTransfer && !$hasPicking && $produk->total_stok <= 0) {
            // Delete related tables first
            $produk->analyticsProduk()->delete();
            $produk->stokLokasi()->delete();
            $produk->delete();
        }
    }
}

