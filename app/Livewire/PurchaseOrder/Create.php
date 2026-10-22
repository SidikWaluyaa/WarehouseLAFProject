<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\PoHeader;
use App\Models\PoDetail;
use App\Models\Produk;
use App\Models\Supplier;
use App\Services\StockEngineService;
use App\Traits\HasAlerts;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    use HasAlerts;

    // Header Form
    public string $tanggal = '';
    public string $nomor_po = '';
    public string $artikel = '';
    public ?int $supplier_id = null;

    // Dynamic Items: [['produk_id' => int, 'kode_barang' => string, 'nama_barang' => string, 'satuan' => string, 'jumlah' => int, 'hpp' => float, 'search_query' => string]]
    public array $items = [];

    // Barcode scanner
    public string $barcodeScan = '';
    public ?int $justScannedProductId = null;

    // Dropdown / Auto-suggest state
    public $suppliersList = [];

    // Catalog Modal (Multi-select)
    public bool $isCatalogModalOpen = false;
    public string $catalogSearch = '';
    public string $catalogCategory = '';
    public array $selectedCatalogProducts = [];

    protected $rules = [
        'tanggal' => 'required|date',
        'supplier_id' => 'required|exists:mst_supplier,id',
        'nomor_po' => 'required|string|max:100|unique:po_header,nomor_po',
        'artikel' => 'nullable|string|max:200',
        'items' => 'required|array|min:1',
        'items.*.produk_id' => 'required|exists:mst_produk,id',
        'items.*.jumlah' => 'required|integer|min:1',
        'items.*.hpp' => 'required|numeric|min:0',
    ];

    protected $validationAttributes = [
        'supplier_id' => 'Supplier / Vendor',
        'nomor_po' => 'Nomor PO',
        'items.*.produk_id' => 'Produk',
        'items.*.jumlah' => 'Kuantitas',
        'items.*.hpp' => 'Estimasi HPP',
    ];

    public function mount(): void
    {
        Gate::authorize('manage po');

        $this->tanggal = date('Y-m-d');
        $this->nomor_po = StockEngineService::generateNomorPo($this->tanggal);
        $this->suppliersList = Supplier::orderBy('nama_supplier')->get();
    }

    public function updatedTanggal(mixed $value): void
    {
        if (!empty($value)) {
            $this->nomor_po = StockEngineService::generateNomorPo($value);
        }
    }

    public function addBarcodeItem(?string $barcode = null): void
    {
        $barcode = trim($barcode ?? $this->barcodeScan);
        if (empty($barcode)) {
            return;
        }

        $product = Produk::where('kode_barang', $barcode)->first();
        if ($product) {
            $found = false;
            foreach ($this->items as $index => $item) {
                if ($item['produk_id'] == $product->id) {
                    $this->items[$index]['jumlah']++;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->items[] = [
                    'produk_id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'satuan' => $product->satuan ?: 'pcs',
                    'jumlah' => 1,
                    'hpp' => (float) $product->hpp,
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

    public function addProductById(int $productId): void
    {
        $product = Produk::find($productId);
        if (!$product) {
            return;
        }

        $found = false;
        foreach ($this->items as $index => $item) {
            if ($item['produk_id'] == $product->id) {
                $this->items[$index]['jumlah']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->items[] = [
                'produk_id' => $product->id,
                'kode_barang' => $product->kode_barang,
                'nama_barang' => $product->nama_barang,
                'satuan' => $product->satuan ?: 'pcs',
                'jumlah' => 1,
                'hpp' => (float) $product->hpp,
            ];
        }

        $this->justScannedProductId = $product->id;
        $this->dispatch('play-beep');
        $this->dispatch('focus-barcode');
    }

    public function removeItem(int $index): void
    {
        $this->justScannedProductId = null;
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function clearAllItems(): void
    {
        $this->items = [];
        $this->justScannedProductId = null;
    }

    public function getProductsForSearch(string $query): array
    {
        $query = trim($query);
        if (strlen($query) < 2) {
            return [];
        }

        $term = '%' . $query . '%';
        return Produk::where('nama_barang', 'LIKE', $term)
            ->orWhere('kode_barang', 'LIKE', $term)
            ->orderBy('nama_barang')
            ->limit(15)
            ->get(['id', 'nama_barang', 'kode_barang', 'hpp', 'satuan', 'total_stok'])
            ->toArray();
    }

    public function store()
    {
        Gate::authorize('manage po');
        $this->validate();

        try {
            $poHeader = DB::transaction(function () {
                $totalQty = 0;
                foreach ($this->items as $item) {
                    $totalQty += (int) $item['jumlah'];
                }

                $header = PoHeader::create([
                    'nomor_po' => $this->nomor_po,
                    'tanggal' => $this->tanggal,
                    'supplier_id' => $this->supplier_id,
                    'artikel' => $this->artikel ?: null,
                    'jumlah' => $totalQty,
                ]);

                foreach ($this->items as $item) {
                    $qty = (int) $item['jumlah'];
                    $hpp = (float) $item['hpp'];
                    PoDetail::create([
                        'po_header_id' => $header->id,
                        'produk_id' => $item['produk_id'],
                        'jumlah' => $qty,
                        'barang_masuk' => 0,
                        'sisa' => $qty,
                        'hpp' => $hpp,
                        'total_hpp' => $qty * $hpp,
                    ]);
                }

                // Audit Log
                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
                    'action' => 'CREATE',
                    'auditable_type' => get_class($header),
                    'auditable_id' => $header->id,
                    'old_values' => null,
                    'new_values' => $header->load('poDetails')->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return $header;
            });

            session()->flash('success_message', "Purchase Order {$poHeader->nomor_po} berhasil diterbitkan.");
            return $this->redirect(route('purchase-order', ['highlight' => $poHeader->id]), navigate: false);
        } catch (Exception $e) {
            $this->alertError("Gagal menerbitkan Purchase Order: " . $e->getMessage());
        }
    }

    public function addItemRow(): void
    {
        $this->items[] = [
            'produk_id' => null,
            'kode_barang' => '',
            'nama_barang' => '',
            'satuan' => 'pcs',
            'jumlah' => 1,
            'hpp' => 0,
            'search_query' => '',
        ];
    }

    public function selectProductForRow(int $index, int $productId): void
    {
        $product = Produk::find($productId);
        if ($product && isset($this->items[$index])) {
            $this->items[$index]['produk_id'] = $product->id;
            $this->items[$index]['kode_barang'] = $product->kode_barang;
            $this->items[$index]['nama_barang'] = $product->nama_barang;
            $this->items[$index]['satuan'] = $product->satuan ?: 'pcs';
            $this->items[$index]['hpp'] = (float) $product->hpp;
            $this->items[$index]['search_query'] = $product->nama_barang;
            $this->justScannedProductId = $product->id;
            $this->dispatch('play-beep');
        }
    }

    public function openCatalogModal(): void
    {
        $this->catalogSearch = '';
        $this->catalogCategory = '';
        $this->selectedCatalogProducts = [];
        $this->isCatalogModalOpen = true;
    }

    public function closeCatalogModal(): void
    {
        $this->isCatalogModalOpen = false;
        $this->selectedCatalogProducts = [];
    }

    public function addSelectedCatalogProducts(): void
    {
        if (empty($this->selectedCatalogProducts)) {
            $this->closeCatalogModal();
            return;
        }

        $products = Produk::whereIn('id', $this->selectedCatalogProducts)->get();
        $addedCount = 0;

        foreach ($products as $product) {
            $found = false;
            foreach ($this->items as $idx => $item) {
                if ($item['produk_id'] == $product->id) {
                    $this->items[$idx]['jumlah']++;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // If there's an empty row without a produk_id, fill it
                $emptyIndex = null;
                foreach ($this->items as $idx => $item) {
                    if (empty($item['produk_id'])) {
                        $emptyIndex = $idx;
                        break;
                    }
                }

                $newItem = [
                    'produk_id' => $product->id,
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'satuan' => $product->satuan ?: 'pcs',
                    'jumlah' => 1,
                    'hpp' => (float) $product->hpp,
                    'search_query' => $product->nama_barang,
                ];

                if ($emptyIndex !== null) {
                    $this->items[$emptyIndex] = $newItem;
                } else {
                    $this->items[] = $newItem;
                }
            }
            $addedCount++;
        }

        $this->closeCatalogModal();
        $this->dispatch('play-beep');
        $this->toastSuccess("{$addedCount} produk berhasil ditambahkan ke daftar pesanan PO.");
    }

    public function render()
    {
        $totalItems = count($this->items);
        $totalQty = collect($this->items)->sum(fn($i) => (int)($i['jumlah'] ?? 0));
        $grandTotalHpp = collect($this->items)->sum(fn($i) => ((int)($i['jumlah'] ?? 0)) * ((float)($i['hpp'] ?? 0)));

        // Catalog query for modal
        $catalogCategories = Produk::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $catalogQuery = Produk::query();
        if (!empty($this->catalogSearch)) {
            $term = '%' . trim($this->catalogSearch) . '%';
            $catalogQuery->where(function ($q) use ($term) {
                $q->where('nama_barang', 'like', $term)
                  ->orWhere('kode_barang', 'like', $term)
                  ->orWhere('variasi_barang', 'like', $term);
            });
        }
        if (!empty($this->catalogCategory)) {
            $catalogQuery->where('kategori', $this->catalogCategory);
        }
        $catalogProducts = $catalogQuery->orderBy('nama_barang')->take(50)->get();

        return view('livewire.purchase-order.create', [
            'totalItems' => $totalItems,
            'totalQty' => $totalQty,
            'grandTotalHpp' => $grandTotalHpp,
            'catalogCategories' => $catalogCategories,
            'catalogProducts' => $catalogProducts,
        ]);
    }
}
