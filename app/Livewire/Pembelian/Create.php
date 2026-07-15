<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use App\Models\Supplier;
use App\Models\Produk;
use App\Models\PoDetail;
use App\Services\StockEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Create extends Component
{
    use HasAlerts;
    // Form fields (Header)
    public $tanggal = '';
    public $tipe_nota = 'Pembelian';
    public $supplier_id = '';
    public $nomor_nota = '';
    public $keterangan_tambahan = '';

    // Barcode Scanning state
    public $barcodeScan = '';

    // Dynamic items list
    public $items = [];

    // Lists for dropdowns
    public $suppliersList = [];
    public $locationsList = [];

    public ?int $justScannedProductId = null;

    protected function rules()
    {
        return [
            'tanggal' => 'required|date',
            'tipe_nota' => 'required|in:Stock Awal,Pembelian,Retur Masuk,Penyesuaian',
            'supplier_id' => $this->tipe_nota === 'Stock Awal' ? 'nullable|exists:mst_supplier,id' : 'required|exists:mst_supplier,id',
            'nomor_nota' => 'required|string|max:100|unique:trx_barang_masuk_header,nomor_nota',
            'keterangan_tambahan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:mst_produk,id',
            'items.*.search_query' => 'nullable|string',
            'items.*.qty_masuk' => 'required|integer|min:1',
            'items.*.lokasi_simpan' => 'required|exists:mst_lokasi,kode_lokasi',
            'items.*.hpp' => 'required|numeric|min:0',
            'items.*.po_detail_id' => 'nullable|exists:po_detail,id',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tipe_nota.required' => 'Tipe nota wajib dipilih.',
            'tipe_nota.in' => 'Tipe nota yang dipilih tidak valid.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak terdaftar.',
            'nomor_nota.required' => 'Nomor nota wajib diisi.',
            'nomor_nota.unique' => 'Nomor nota sudah digunakan.',
            'items.required' => 'Minimal harus ada 1 item barang.',
            'items.min' => 'Minimal harus ada 1 item barang.',
            'items.*.produk_id.required' => 'Pilih produk terlebih dahulu.',
            'items.*.produk_id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty_masuk.required' => 'Kuantitas wajib diisi.',
            'items.*.qty_masuk.integer' => 'Kuantitas harus berupa angka bulat.',
            'items.*.qty_masuk.min' => 'Kuantitas minimal adalah 1.',
            'items.*.lokasi_simpan.required' => 'Lokasi simpan wajib dipilih.',
            'items.*.lokasi_simpan.exists' => 'Lokasi simpan tidak valid.',
            'items.*.hpp.required' => 'Harga beli/HPP wajib diisi.',
            'items.*.hpp.numeric' => 'Harga beli harus berupa angka.',
            'items.*.hpp.min' => 'Harga beli minimal adalah 0.',
        ];
    }

    protected $validationAttributes = [
        'items.*.produk_id' => 'Produk',
        'items.*.qty_masuk' => 'Kuantitas',
        'items.*.hpp' => 'HPP/Harga Beli',
    ];

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->tanggal = date('Y-m-d');
        $this->nomor_nota = StockEngineService::generateNomorNotaBarangMasuk($this->tanggal);
        $this->suppliersList = Supplier::orderByRaw('nama_supplier')->get();
        $this->locationsList = \App\Models\Lokasi::where(['is_active' => true])->orderByRaw('kode_lokasi')->get();
        $this->addItem();
    }

    public function updatedTanggal(mixed $value)
    {
        if (!empty($value)) {
            $this->nomor_nota = StockEngineService::generateNomorNotaBarangMasuk($value);
        }
    }

    public function updatedSupplierId()
    {
        // Reset PO detail on all items when supplier changes
        foreach ($this->items as $index => $item) {
            $this->items[$index]['po_detail_id'] = null;
        }
    }

    public function updated(string $propertyName): void
    {
        if (str_starts_with($propertyName, 'items.') && str_ends_with($propertyName, '.produk_id')) {
            $parts = explode('.', $propertyName);
            $index = (int)$parts[1];
            
            if (isset($this->items[$index]['produk_id'])) {
                $productId = $this->items[$index]['produk_id'];
                if (!empty($productId)) {
                    $product = Produk::find($productId);
                    if ($product) {
                        $this->items[$index]['hpp'] = $product->hpp;
                        if (!empty($product->lokasi)) {
                            $this->items[$index]['lokasi_simpan'] = $product->lokasi;
                        }
                    }
                }
            }
        }
    }

    public function addItem(): void
    {
        $this->justScannedProductId = null;
        $firstLoc = collect($this->locationsList)->first();
        $defaultLokasi = is_object($firstLoc) ? $firstLoc->kode_lokasi : ($firstLoc['kode_lokasi'] ?? 'GDG-A01');
        $this->items[] = [
            'produk_id' => '',
            'search_query' => '',
            'qty_masuk' => 1,
            'hpp' => 0,
            'lokasi_simpan' => $defaultLokasi,
            'po_detail_id' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        $this->justScannedProductId = null;
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addBarcodeItem(?string $barcode = null): void
    {
        $barcode = trim($barcode ?? $this->barcodeScan);
        if (empty($barcode)) {
            return;
        }

        $product = Produk::where(['kode_barang' => $barcode])->first();
        if ($product) {
            // Check if already in items
            $found = false;
            foreach ($this->items as $index => $item) {
                if ($item['produk_id'] == $product->id) {
                    $this->items[$index]['qty_masuk']++;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // If the only item is a blank placeholder, overwrite it
                if (count($this->items) === 1 && empty($this->items[0]['produk_id'])) {
                    $this->items = [];
                }

                $firstLoc = collect($this->locationsList)->first();
                $defaultLokasi = $product->lokasi ?? (is_object($firstLoc) ? $firstLoc->kode_lokasi : ($firstLoc['kode_lokasi'] ?? 'GDG-A01'));
                $this->items[] = [
                    'produk_id' => $product->id,
                    'search_query' => $product->nama_barang,
                    'qty_masuk' => 1,
                    'hpp' => $product->hpp,
                    'lokasi_simpan' => $defaultLokasi,
                    'po_detail_id' => null,
                ];
            }

            $this->justScannedProductId = $product->id;
            $this->barcodeScan = '';
            $this->dispatch('play-beep');
            $this->dispatch('focus-barcode');
        } else {
            $this->justScannedProductId = null;
            $this->barcodeScan = '';
            $this->dispatch('play-error-buzzer');
            $this->dispatch('focus-barcode');
            $this->addError('barcodeScan', "Produk dengan kode '{$barcode}' tidak ditemukan!");
        }
    }

    public function getPoDetailsForProduct(int $produkId): array
    {
        if (empty($this->supplier_id) || empty($produkId)) {
            return [];
        }

        return PoDetail::join('po_header', function ($join) {
                $join->on('po_detail.po_header_id', '=', 'po_header.id');
            })
            ->where([
                'po_header.supplier_id' => $this->supplier_id,
                'po_detail.produk_id' => $produkId,
            ])
            ->where([['po_detail.sisa', '>', 0]])
            ->select('po_detail.*', 'po_header.tanggal as po_tanggal', 'po_header.id as po_header_id')
            ->get()
            ->toArray();
    }

    public function getProductsForSearch(string $query, mixed $selectedProductId = null)
    {
        $query = trim($query);
        $selectedProductId = !empty($selectedProductId) ? (int)$selectedProductId : null;
        if (strlen($query) < 2) {
            if ($selectedProductId) {
                return Produk::where('id', $selectedProductId)->get();
            }
            return [];
        }

        $term = '%' . $query . '%';
        return Produk::where('nama_barang', 'LIKE', $term)
            ->orWhere('kode_barang', 'LIKE', $term)
            ->orderBy('nama_barang')
            ->limit(20)
            ->get();
    }

    public function store()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        $validatedData = $this->validate();
        $validatedData['data_dimasukan_oleh'] = Auth::user()->name;

        try {
            DB::transaction(function () use ($validatedData) {
                StockEngineService::tambahBarangMasuk($validatedData);
            });

            session()->flash('success_message', "Transaksi barang masuk berhasil disimpan dan stok terupdate.");
            return $this->redirect('/barang-masuk', navigate: true);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Pembelian/Create: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan pada server. Silakan coba lagi atau hubungi administrator.");
        } catch (Exception $e) {
            $this->alertError("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pembelian.create');
    }
}
