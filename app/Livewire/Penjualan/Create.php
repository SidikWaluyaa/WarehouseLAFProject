<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use App\Models\Pelanggan;
use App\Models\Produk;
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
    public $tipe_nota = 'Penjualan';
    public $pelanggan_id = '';
    public $nomor_nota = '';
    public $keterangan_tambahan = '';

    // Barcode Scanning state
    public $barcodeScan = '';

    // Dynamic items list
    public $items = [];

    // Lists for dropdowns
    public $pelangganList = [];
    public $locationsList = [];

    public ?int $justScannedProductId = null;

    protected function rules()
    {
        return [
            'tanggal' => 'required|date',
            'tipe_nota' => 'required|in:Penjualan,Retur Keluar,Penyesuaian,Sample',
            'pelanggan_id' => 'required|exists:mst_pelanggan,id',
            'nomor_nota' => 'required|string|max:100|unique:trx_barang_keluar_header,nomor_nota',
            'keterangan_tambahan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:mst_produk,id',
            'items.*.search_query' => 'nullable|string',
            'items.*.qty_keluar' => 'required|integer|min:1',
            'items.*.lokasi_pengambilan' => 'required|exists:mst_lokasi,kode_lokasi',
        ];
    }

    protected function messages()
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tipe_nota.required' => 'Tipe nota wajib dipilih.',
            'tipe_nota.in' => 'Tipe nota yang dipilih tidak valid.',
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_id.exists' => 'Pelanggan tidak terdaftar.',
            'nomor_nota.required' => 'Nomor nota wajib diisi.',
            'nomor_nota.unique' => 'Nomor nota sudah digunakan.',
            'items.required' => 'Minimal harus ada 1 item barang.',
            'items.min' => 'Minimal harus ada 1 item barang.',
            'items.*.produk_id.required' => 'Pilih produk terlebih dahulu.',
            'items.*.produk_id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty_keluar.required' => 'Kuantitas wajib diisi.',
            'items.*.qty_keluar.integer' => 'Kuantitas harus berupa angka bulat.',
            'items.*.qty_keluar.min' => 'Kuantitas minimal adalah 1.',
            'items.*.lokasi_pengambilan.required' => 'Lokasi pengambilan wajib dipilih.',
            'items.*.lokasi_pengambilan.exists' => 'Lokasi pengambilan tidak valid.',
        ];
    }

    protected $validationAttributes = [
        'items.*.produk_id' => 'Produk',
        'items.*.qty_keluar' => 'Kuantitas',
    ];

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->tanggal = date('Y-m-d');
        $this->nomor_nota = StockEngineService::generateNomorNotaBarangKeluar($this->tanggal);
        $this->pelangganList = Pelanggan::orderByRaw('nama_pelanggan')->get();
        $this->locationsList = \App\Models\Lokasi::where(['is_active' => true])->orderByRaw('kode_lokasi')->get();
        $this->addItem();
    }

    public function updatedTanggal(mixed $value)
    {
        if (!empty($value)) {
            $this->nomor_nota = StockEngineService::generateNomorNotaBarangKeluar($value);
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
            'qty_keluar' => 1,
            'lokasi_pengambilan' => $defaultLokasi,
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
                    $this->items[$index]['qty_keluar']++;
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
                    'qty_keluar' => 1,
                    'lokasi_pengambilan' => $defaultLokasi,
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
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate();
        
        // Aggregate requested quantities by product_id and location code
        $requestedQuantities = [];
        foreach ($this->items as $index => $item) {
            $prodId = $item['produk_id'];
            $locCode = $item['lokasi_pengambilan'];
            if (!empty($prodId) && !empty($locCode)) {
                $key = "{$prodId}-{$locCode}";
                if (!isset($requestedQuantities[$key])) {
                    $requestedQuantities[$key] = [
                        'produk_id' => $prodId,
                        'lokasi_code' => $locCode,
                        'qty' => 0,
                        'item_indices' => []
                    ];
                }
                $requestedQuantities[$key]['qty'] += (int)$item['qty_keluar'];
                $requestedQuantities[$key]['item_indices'][] = $index;
            }
        }

        // Validate stock for each product + location combination
        foreach ($requestedQuantities as $key => $req) {
            $product = Produk::find($req['produk_id']);
            $lokasi = \App\Models\Lokasi::where(['kode_lokasi' => $req['lokasi_code']])->first();
            
            $stokLokasi = null;
            if ($product && $lokasi) {
                $stokLokasi = \App\Models\StokLokasi::where([
                    'produk_id' => $product->id,
                    'lokasi_id' => $lokasi->id,
                ])->first();
            }
            $stokTersedia = $stokLokasi ? $stokLokasi->qty : 0;

            if ($req['qty'] > $stokTersedia) {
                foreach ($req['item_indices'] as $idx) {
                    $this->addError("items.{$idx}.qty_keluar", "Stok tidak mencukupi! Sisa stok '{$product->nama_barang}' di lokasi '{$req['lokasi_code']}' adalah {$stokTersedia} {$product->satuan}, total diminta di nota ini: {$req['qty']} {$product->satuan}.");
                }
                return;
            }
        }

        $data = [
            'tanggal' => $this->tanggal,
            'tipe_nota' => $this->tipe_nota,
            'pelanggan_id' => $this->pelanggan_id,
            'nomor_nota' => $this->nomor_nota,
            'keterangan_tambahan' => $this->keterangan_tambahan,
            'items' => $this->items,
            'dikeluarkan_oleh' => Auth::user()->name,
        ];

        try {
            DB::transaction(function () use ($data) {
                StockEngineService::tambahBarangKeluar($data);
            });

            session()->flash('success_message', "Transaksi barang keluar berhasil disimpan dan stok terupdate.");
            return $this->redirect('/barang-keluar', navigate: true);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Penjualan/Create: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan pada server. Silakan coba lagi atau hubungi administrator.");
        } catch (Exception $e) {
            $this->alertError("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.penjualan.create');
    }
}
