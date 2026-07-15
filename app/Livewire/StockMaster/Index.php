<?php

namespace App\Livewire\StockMaster;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Produk;
use App\Models\AnalyticsProduk;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasAlerts;

    // Search & Filter state
    public $search = '';
    public $categoryFilter = '';
    public $locationFilter = '';
    public $stockFilter = '';
    public $statusFilter = '';
    public $warnaFilter = '';
    public $sizeFilter = '';
    public $sortField = 'nama_barang';
    public $sortDirection = 'asc';

    // Form modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $productId = null;
    public bool $autoGenerateSku = true;

    // Import Excel state
    public bool $isImportOpen = false;
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $importFile = null;
    public array $importErrors = [];

    // Barcode modal state
    public bool $isBarcodeOpen = false;
    public ?string $barcodeProductSku = null;
    public ?string $barcodeProductName = null;
    public ?string $barcodeProductVariation = null;

    // Product fields
    public $nama_produk = '';
    public $kategori = '';
    public $kategori_2 = '';
    public $kode_barang = '';
    public $nama_barang = '';
    public $variasi_barang = '';
    public $warna = '';
    public $size = '';
    public $satuan = 'pasang';
    public $lokasi = 'GDG-A01';
    public $hpp = 0;
    public $harga_jual = 0;
    public $is_active = true;
    public $locationsList = [];

    // Alert toast state
    
    

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'locationFilter' => ['except' => ''],
        'stockFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'warnaFilter' => ['except' => ''],
        'sizeFilter' => ['except' => ''],
    ];

    protected $rules = [
        'nama_produk' => 'required|string|max:200',
        'kategori' => 'required|string|max:100',
        'kategori_2' => 'nullable|string|max:100',
        'kode_barang' => 'required|string|max:100',
        'nama_barang' => 'required|string|max:200',
        'variasi_barang' => 'required|string|max:100',
        'warna' => 'nullable|string|max:100',
        'size' => 'nullable|string|max:50',
        'satuan' => 'required|string|max:20',
        'lokasi' => 'required|exists:mst_lokasi,kode_lokasi',
        'hpp' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingLocationFilter()
    {
        $this->resetPage();
    }

    public function updatingStockFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingWarnaFilter()
    {
        $this->resetPage();
    }

    public function updatingSizeFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->locationFilter = '';
        $this->stockFilter = '';
        $this->statusFilter = '';
        $this->warnaFilter = '';
        $this->sizeFilter = '';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->isEdit = false;
        $this->nama_produk = '';
        $this->kategori = '';
        $this->kategori_2 = '';
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->variasi_barang = '';
        $this->warna = '';
        $this->size = '';
        $this->satuan = 'pasang';
        $this->lokasi = 'GDG-A01';
        $this->hpp = 0;
        $this->harga_jual = 0;
        $this->is_active = true;
        $this->autoGenerateSku = true;
    }

    public function create()
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $this->isEdit = false;
        $this->openModal();
    }

    public function store()
    {
        Gate::authorize('manage master');
        // Check uniqueness manually for edits or custom rules
        $rules = $this->rules;
        if ($this->isEdit) {
            $rules['kode_barang'] = 'required|string|max:100|unique:mst_produk,kode_barang,' . $this->productId;
        } else {
            $rules['kode_barang'] = 'required|string|max:100|unique:mst_produk,kode_barang';
        }

        $validatedData = $this->validate($rules);

        DB::transaction(function () use ($validatedData) {
            if ($this->isEdit) {
                $product = Produk::findOrFail($this->productId);
                $oldValues = $product->toArray();
                $product->update($validatedData);

                \App\Models\AuditLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'username' => \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->name : 'System',
                    'action' => 'UPDATE',
                    'auditable_type' => get_class($product),
                    'auditable_id' => $product->id,
                    'old_values' => $oldValues,
                    'new_values' => $product->fresh()->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                $this->toastSuccess("Produk '{$product->nama_barang}' berhasil diperbarui.");
            } else {
                $product = Produk::create($validatedData);

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

                \App\Models\AuditLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'username' => \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->name : 'System',
                    'action' => 'CREATE',
                    'auditable_type' => get_class($product),
                    'auditable_id' => $product->id,
                    'old_values' => null,
                    'new_values' => $product->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                $this->toastSuccess("Produk '{$product->nama_barang}' berhasil ditambahkan.");
            }
        });

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $product = Produk::findOrFail($id);
        $this->productId = $id;
        $this->isEdit = true;
        $this->autoGenerateSku = false;
        
        $this->nama_produk = $product->nama_produk;
        $this->kategori = $product->kategori;
        $this->kategori_2 = $product->kategori_2;
        $this->kode_barang = $product->kode_barang;
        $this->nama_barang = $product->nama_barang;
        $this->variasi_barang = $product->variasi_barang;
        $this->warna = $product->warna;
        $this->size = $product->size;
        $this->satuan = $product->satuan;
        $this->lokasi = $product->lokasi;
        $this->hpp = $product->hpp;
        $this->harga_jual = $product->harga_jual;
        $this->is_active = (bool)$product->is_active;

        $this->openModal();
    }

    /**
     * @param string $propertyName
     * @return void
     */
    public function updated(string $propertyName)
    {
        if (in_array($propertyName, ['nama_produk', 'kategori', 'warna', 'size', 'variasi_barang', 'autoGenerateSku'])) {
            if (in_array($propertyName, ['warna', 'size'])) {
                $this->generateVariasiBarang();
            } elseif ($propertyName === 'variasi_barang') {
                $this->extractWarnaSize();
                $this->generateSku();
            } else {
                $this->generateSku();
            }
        }
    }

    public function generateVariasiBarang(): void
    {
        $warna = trim($this->warna ?? '');
        $size = trim($this->size ?? '');

        if ($warna !== '' && $size !== '') {
            $this->variasi_barang = "{$warna} / {$size}";
        } elseif ($warna !== '') {
            $this->variasi_barang = $warna;
        } else {
            $this->variasi_barang = $size;
        }

        $this->generateSku();
    }

    public function extractWarnaSize(): void
    {
        $variasi = $this->variasi_barang ?? '';
        $this->warna = '';
        $this->size = '';

        if (!empty($variasi)) {
            if (str_contains($variasi, ' / ')) {
                $parts = explode(' / ', $variasi, 2);
                $this->warna = trim($parts[0]);
                $this->size = trim($parts[1] ?? '');
            } elseif (str_contains($variasi, '/')) {
                $parts = explode('/', $variasi, 2);
                $this->warna = trim($parts[0]);
                $this->size = trim($parts[1] ?? '');
            } elseif (str_contains($variasi, ' - ')) {
                $parts = explode(' - ', $variasi, 2);
                $this->warna = trim($parts[0]);
                $this->size = trim($parts[1] ?? '');
            } else {
                if (is_numeric(trim($variasi))) {
                    $this->size = trim($variasi);
                } else {
                    $this->warna = trim($variasi);
                }
            }
        }
    }

    public function generateSku(): void
    {
        if ($this->autoGenerateSku && !$this->isEdit) {
            if (!empty($this->nama_produk) && !empty($this->kategori)) {
                $this->kode_barang = \App\Services\SkuGeneratorService::generate(
                    $this->nama_produk,
                    $this->kategori,
                    $this->variasi_barang ?? ''
                );
            } else {
                $this->kode_barang = '';
            }
        }
    }

    public function destroy(int $id): void
    {
        Gate::authorize('manage master');
        $product = Produk::findOrFail($id);

        // Pre-check if it can be deleted to prevent showing confirmation popup uselessly
        if ($product->barangMasuk()->exists() || $product->barangKeluar()->exists() || $product->poDetails()->exists()) {
            $this->alertError("Produk '{$product->nama_barang}' tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete',
            params: ['id' => $id],
            title: 'Hapus Produk?',
            text: "Apakah Anda yakin ingin menghapus produk '{$product->nama_barang}'?"
        );
    }

    #[On('confirmed-delete')]
    public function confirmedDelete(array|int|null $params = null, ?int $id = null): void
    {
        Gate::authorize('manage master');
        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        try {
            if (!$id) {
                throw new Exception("ID produk tidak valid.");
            }

            $product = Produk::findOrFail($id);

            // Check if product has transactions
            if ($product->barangMasuk()->exists() || $product->barangKeluar()->exists() || $product->poDetails()->exists()) {
                throw new Exception("Produk '{$product->nama_barang}' tidak dapat dihapus karena sudah memiliki riwayat transaksi.");
            }

            $oldValues = $product->toArray();

            DB::transaction(function () use ($product, $oldValues) {
                $product->delete();

                \App\Models\AuditLog::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'username' => \Illuminate\Support\Facades\Auth::user() ? \Illuminate\Support\Facades\Auth::user()->name : 'System',
                    'action' => 'DELETE',
                    'auditable_type' => get_class($product),
                    'auditable_id' => $product->id,
                    'old_values' => $oldValues,
                    'new_values' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            });

            $this->toastSuccess("Produk '{$product->nama_barang}' berhasil dihapus.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function showBarcode(int $id): void
    {
        $product = Produk::findOrFail($id);
        $this->barcodeProductSku = $product->kode_barang;
        $this->barcodeProductName = $product->nama_barang;
        $this->barcodeProductVariation = $product->variasi_barang;
        $this->isBarcodeOpen = true;
    }

    public function closeBarcodeModal(): void
    {
        $this->isBarcodeOpen = false;
        $this->barcodeProductSku = null;
        $this->barcodeProductName = null;
        $this->barcodeProductVariation = null;
    }

    public function downloadBarcode(string $sku)
    {
        $product = Produk::query()->whereRaw('kode_barang = ?', [$sku])->firstOrFail();
        $svg = \App\Helpers\Barcode128::getTagSvg($sku, $product->nama_barang, $product->variasi_barang);
        
        return response()->streamDownload(function () use ($svg) {
            echo $svg;
        }, 'tag-' . strtolower($sku) . '.svg', [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    

    public function exportExcel()
    {
        return Excel::download(new ProductsExport, 'stock_master_' . date('Ymd_His') . '.xlsx');
    }

    public function openImportModal(): void
    {
        Gate::authorize('manage master');
        $this->importFile = null;
        $this->importErrors = [];
        $this->isImportOpen = true;
    }

    public function closeImportModal(): void
    {
        $this->isImportOpen = false;
        $this->importFile = null;
        $this->importErrors = [];
    }

    public function downloadTemplate()
    {
        Gate::authorize('manage master');
        return Excel::download(new ProductsTemplateExport, 'template_produk_wms.xlsx');
    }

    public function importExcel()
    {
        Gate::authorize('manage master');
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ], [
            'importFile.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'importFile.mimes' => 'File harus berupa Excel (.xlsx, .xls, .csv).',
            'importFile.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $importer = new ProductsImport();

        try {
            Excel::import($importer, $this->importFile->getRealPath());

            $this->toastSuccess("Berhasil mengimpor {$importer->importedCount} produk baru.");
            $this->closeImportModal();
        } catch (Exception $e) {
            if (!empty($importer->errorsList)) {
                $this->importErrors = $importer->errorsList;
            } else {
                Log::error("Excel Import Database/System Error: " . $e->getMessage(), [
                    'exception' => $e
                ]);
                $this->importErrors = ["Terjadi kesalahan internal pada server saat mengimpor data. Silakan hubungi administrator."];
            }
            $this->alertError("Gagal mengimpor data produk. Silakan perbaiki kesalahan berikut.");
        }
    }

    public function logout(\App\Livewire\Actions\Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function formatRupiahAbbr(float|int $value): string
    {
        if ($value >= 1_000_000_000) {
            return 'Rp' . number_format($value / 1_000_000_000, 1, ',', '.') . 'M';
        }
        if ($value >= 1_000_000) {
            return 'Rp' . number_format($value / 1_000_000, 1, ',', '.') . 'Jt';
        }
        return 'Rp' . number_format($value, 0, ',', '.');
    }

    public function render()
    {
        $query = Produk::with([
            'stokLokasi.lokasi',
            'mutasiLokasi' => function ($q) {
                $q->with(['lokasiAsal', 'lokasiTujuan'])->latest();
            }
        ]);

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_barang', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_produk', 'like', '%' . $this->search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $this->search . '%')
                  ->orWhere('variasi_barang', 'like', '%' . $this->search . '%');
            });
        }

        // Apply category filter
        if (!empty($this->categoryFilter)) {
            $query->whereRaw('kategori = ?', [$this->categoryFilter]);
        }

        // Apply location filter
        if (!empty($this->locationFilter)) {
            $query->where('lokasi', $this->locationFilter);
        }

        // Apply stock filter
        if ($this->stockFilter === 'menipis') {
            $query->where('total_stok', '<=', 5)->where('total_stok', '>', 0);
        } elseif ($this->stockFilter === 'habis') {
            $query->where('total_stok', '=', 0);
        } elseif ($this->stockFilter === 'melimpah') {
            $query->where('total_stok', '>', 5);
        }

        // Apply status filter
        if ($this->statusFilter === 'aktif') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'tidak_aktif') {
            $query->where('is_active', false);
        }

        // Apply warna filter
        if (!empty($this->warnaFilter)) {
            $query->where('warna', $this->warnaFilter);
        }

        // Apply size filter
        if (!empty($this->sizeFilter)) {
            $query->where('size', $this->sizeFilter);
        }

        // Apply sorting
        $products = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Fetch categories list for filter
        $categories = Produk::distinct()->whereNotNull('kategori')->pluck('kategori');

        // Fetch dynamic lists for colors and sizes
        $colors = Produk::distinct()->whereNotNull('warna')->where('warna', '!=', '')->orderBy('warna')->pluck('warna');
        $sizes = Produk::distinct()->whereNotNull('size')->where('size', '!=', '')->orderByRaw('CAST(size AS UNSIGNED), size')->pluck('size');

        // Fetch KPIs
        $totalSkus = Produk::count();
        $stokMenipis = Produk::query()->whereRaw('total_stok <= 5')->count();
        $totalNilaiStokVal = Produk::query()->select(DB::raw('SUM(total_stok * hpp) as total'))->value('total') ?? 0;
        $totalNilaiStok = $this->formatRupiahAbbr($totalNilaiStokVal);
        $columnKeluar = 'stok_keluar';
        $fastMovingProduct = Produk::query()->orderBy($columnKeluar, 'desc')->first();

        $this->locationsList = \App\Models\Lokasi::query()
            ->whereRaw('is_active = ?', [true])
            ->orderByRaw('kode_lokasi')
            ->get();

        return view('livewire.stock-master.index', [
            'products' => $products,
            'categories' => $categories,
            'colors' => $colors,
            'sizes' => $sizes,
            'totalSkus' => $totalSkus,
            'stokMenipis' => $stokMenipis,
            'totalNilaiStok' => $totalNilaiStok,
            'fastMovingProduct' => $fastMovingProduct,
            'locationsList' => $this->locationsList,
        ]);
    }
}
