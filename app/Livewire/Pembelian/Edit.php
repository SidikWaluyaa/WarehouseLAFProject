<?php

namespace App\Livewire\Pembelian;

use App\Traits\HasAlerts;
use Livewire\Component;
use App\Models\Supplier;
use App\Models\Produk;
use App\Models\PoDetail;
use App\Models\BarangMasuk;
use App\Services\StockEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Component
{
    use HasAlerts;
    public int $transactionId;
    
    // Form fields (Header)
    public $tanggal = '';
    public $tipe_nota = '';
    public $supplier_id = '';
    public $nomor_nota = '';
    public $keterangan_tambahan = '';

    // Barcode Scanning state
    public $barcodeScan = '';

    // Dynamic items list
    public $items = [];

    // Lists for dropdowns
    public $suppliersList = [];
    public $productsList = [];
    public $locationsList = [];

    // Success/Error Alerts
    
    
    public ?int $justScannedProductId = null;

    protected function rules()
    {
        return [
            'tanggal' => 'required|date',
            'tipe_nota' => 'required|in:Stock Awal,Pembelian,Retur Masuk,Penyesuaian',
            'supplier_id' => $this->tipe_nota === 'Stock Awal' ? 'nullable|exists:mst_supplier,id' : 'required|exists:mst_supplier,id',
            'nomor_nota' => 'required|string|max:100',
            'keterangan_tambahan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:mst_produk,id',
            'items.*.qty_masuk' => 'required|integer|min:1',
            'items.*.lokasi_simpan' => 'required|exists:mst_lokasi,kode_lokasi',
            'items.*.hpp' => 'required|numeric|min:0',
            'items.*.po_detail_id' => 'nullable|exists:po_detail,id',
        ];
    }

    protected $validationAttributes = [
        'items.*.produk_id' => 'Produk',
        'items.*.qty_masuk' => 'Kuantitas',
        'items.*.hpp' => 'HPP/Harga Beli',
    ];

    public function mount(int $id)
    {
        // Check authorization
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->transactionId = $id;
        $transaction = BarangMasuk::with('details')->findOrFail($id);

        // Ownership check: non-admin/super_admin users can only edit their own inputs
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin') && $transaction->data_dimasukan_oleh !== $user->name) {
            abort(403, 'Unauthorized action. Anda tidak dapat mengubah transaksi milik user lain.');
        }

        $this->tanggal = \Carbon\Carbon::parse($transaction->tanggal)->format('Y-m-d');
        $this->tipe_nota = $transaction->tipe_nota;
        $this->supplier_id = $transaction->supplier_id ?: '';
        $this->nomor_nota = $transaction->nomor_nota;
        $this->keterangan_tambahan = $transaction->keterangan_tambahan ?: '';

        // Load details
        foreach ($transaction->details as $detail) {
            $this->items[] = [
                'produk_id' => $detail->produk_id,
                'qty_masuk' => $detail->qty_masuk,
                'hpp' => (float)$detail->hpp,
                'lokasi_simpan' => $detail->lokasi_simpan,
                'po_detail_id' => $detail->po_detail_id,
            ];
        }

        $this->suppliersList = Supplier::orderByRaw('nama_supplier')->get();
        $this->productsList = Produk::orderByRaw('nama_barang')->get();
        $this->locationsList = \App\Models\Lokasi::where(['is_active' => true])->orderByRaw('kode_lokasi')->get();
        
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function updatedTipeNota(string $value)
    {
        if ($value === 'Stock Awal') {
            $this->supplier_id = '';
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
                if (count($this->items) === 1 && empty($this->items[0]['produk_id'])) {
                    $this->items = [];
                }

                $firstLoc = collect($this->locationsList)->first();
                $defaultLokasi = $product->lokasi ?? (is_object($firstLoc) ? $firstLoc->kode_lokasi : ($firstLoc['kode_lokasi'] ?? 'GDG-A01'));
                $this->items[] = [
                    'produk_id' => $product->id,
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

    public function update()
    {
        $validatedData = $this->validate();
        $validatedData['data_dimasukan_oleh'] = Auth::user()->name;

        // If supplier_id is empty, set it to null
        if (empty($validatedData['supplier_id'])) {
            $validatedData['supplier_id'] = null;
        }

        try {
            DB::transaction(function () use ($validatedData) {
                StockEngineService::ubahBarangMasuk($this->transactionId, $validatedData);
            });

            session()->flash('success_message', "Transaksi barang masuk berhasil diubah dan stok terupdate.");
            return $this->redirect('/barang-masuk', navigate: true);
        } catch (Exception $e) {
            $this->alertError("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    

    public function render()
    {
        return view('livewire.pembelian.edit');
    }
}
