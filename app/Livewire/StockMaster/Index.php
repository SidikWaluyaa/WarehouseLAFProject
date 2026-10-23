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
use App\Services\WhatsAppNotificationService;
use Illuminate\Support\Facades\Auth;

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
    public int $perPage = 10; // 10, 25, 50, 100

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // Form modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $productId = null;
    public bool $autoGenerateSku = true;

    // Threshold fields
    public bool $is_threshold_active = false;
    public ?int $min_stok = null;
    public $notif_phone = '';

    // WhatsApp Notification modal state
    public bool $isWaModalOpen = false;
    public string $waTargetPhone = '';
    public string $waMessagePreview = '';

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
        'perPage' => ['except' => 10],
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
        'is_threshold_active' => 'boolean',
        'min_stok' => 'nullable|integer|min:0',
        'notif_phone' => 'nullable|string|max:30',
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
        $this->is_threshold_active = false;
        $this->min_stok = null;
        $this->notif_phone = '';
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
        $validatedData['min_stok'] = $this->is_threshold_active ? (int)$this->min_stok : null;
        $validatedData['is_threshold_active'] = (bool)$this->is_threshold_active;
        $validatedData['notif_phone'] = !empty($this->notif_phone) ? trim($this->notif_phone) : null;

        DB::transaction(function () use ($validatedData) {
            if ($this->isEdit) {
                $product = Produk::findOrFail($this->productId);
                $oldValues = $product->toArray();
                $product->update($validatedData);

                if ($product->is_threshold_active && $product->min_stok > 0) {
                    AnalyticsProduk::updateOrCreate(
                        ['produk_id' => $product->id],
                        [
                            'minimal_stock' => (int)$product->min_stok,
                            'is_manual_stock' => true
                        ]
                    );
                }

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
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
                    'minimal_stock' => $product->min_stok ?? 0,
                    'safety_stock' => (int)ceil(($product->min_stok ?? 0) * 1.5),
                    'maximum_stock' => (int)ceil(($product->min_stok ?? 0) * 2.25),
                    'is_manual_stock' => (bool)$product->is_threshold_active,
                ]);

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
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
        $this->is_threshold_active = (bool)$product->is_threshold_active;
        $this->min_stok = $product->min_stok ?? 10;
        $this->notif_phone = $product->notif_phone ?? '';

        $this->openModal();
    }

    /**
     * @param string $propertyName
     * @return void
     */
    public function updated(string $propertyName)
    {
        if (in_array($propertyName, ['nama_produk', 'kategori', 'warna', 'size', 'variasi_barang', 'autoGenerateSku', 'kategori_2'])) {
            if ($propertyName === 'kategori_2') {
                if (strtoupper(trim((string)$this->kategori_2)) === 'TOP 20') {
                    $this->is_threshold_active = true;
                    $this->min_stok = 20;
                }
            }
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
            text: "Apakah Anda yakin ingin menghapus produk '{$product->nama_barang}'?",
            destructive: true
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
                    'user_id' => Auth::id(),
                    'username' => Auth::user() ? Auth::user()->name : 'System',
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
        return Excel::download(new ProductsExport, 'stock_master_' . now()->format('Ymd_His') . '.xlsx');
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
        $this->redirect('/');
    }

    public function openWaModal(): void
    {
        $lowStockProducts = Produk::lowStock()->get();

        if ($lowStockProducts->isEmpty()) {
            $this->alertWarning("Tidak ada produk ber-ambang batas yang stoknya menipis saat ini.");
            return;
        }

        $this->waTargetPhone = config('services.fonnte.default_phone', '') ?: '';
        $this->waMessagePreview = WhatsAppNotificationService::formatLowStockMessage(
            $lowStockProducts,
            Auth::user() ? Auth::user()->name : 'Admin'
        );
        $this->isWaModalOpen = true;
    }

    public function closeWaModal(): void
    {
        $this->isWaModalOpen = false;
    }

    public function sendWaFonnte(): void
    {
        if (empty(trim($this->waTargetPhone))) {
            $this->alertError("Silakan masukkan nomor WhatsApp tujuan.");
            return;
        }

        $result = WhatsAppNotificationService::sendViaFonnte(
            $this->waTargetPhone,
            $this->waMessagePreview
        );

        if ($result['success']) {
            $this->toastSuccess("Peringatan stok menipis berhasil dikirim ke {$this->waTargetPhone} via Fonnte.");
            $this->closeWaModal();
        } else {
            $this->alertError($result['message']);
        }
    }

    public function getWaDirectUrlProperty(): string
    {
        if (empty($this->waTargetPhone)) {
            return '#';
        }
        return WhatsAppNotificationService::generateWhatsAppUrl(
            $this->waTargetPhone,
            $this->waMessagePreview
        );
    }

    public function openWaModalSingleProduct(int $productId): void
    {
        $product = Produk::findOrFail($productId);

        $defaultPhone = !empty($product->notif_phone)
            ? $product->notif_phone
            : config('services.fonnte.default_phone', '');

        $this->waTargetPhone = $defaultPhone;
        $this->waMessagePreview = WhatsAppNotificationService::formatLowStockMessage([$product]);
        $this->isWaModalOpen = true;
    }

    public function updateThresholdInline(int $productId, string $field, mixed $value): void
    {
        Gate::authorize('manage master');

        $product = Produk::findOrFail($productId);

        if ($field === 'is_threshold_active') {
            $isActive = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $product->is_threshold_active = $isActive;
            if ($isActive && (empty($product->min_stok) || $product->min_stok <= 0)) {
                $product->min_stok = ($product->kategori_2 === 'TOP 20') ? 20 : 10;
            }
        } elseif ($field === 'min_stok') {
            $val = trim((string)$value);
            $product->min_stok = ($val === '' || !is_numeric($val)) ? null : max(0, (int)$val);
        } elseif ($field === 'notif_phone') {
            $product->notif_phone = !empty(trim((string)$value)) ? trim((string)$value) : null;
        }

        $product->save();

        // Sync to analytics_produk
        if ($product->is_threshold_active && $product->min_stok > 0) {
            $analytics = AnalyticsProduk::firstOrNew(['produk_id' => $product->id]);
            $analytics->kategori = $product->kategori ?? 'UMUM';
            $analytics->is_manual_stock = true;
            $analytics->minimal_stock = (int)$product->min_stok;
            $analytics->safety_stock = (int)ceil($product->min_stok * 1.5);
            $analytics->maximum_stock = (int)ceil($product->min_stok * 2.25);
            $analytics->save();
        }

        $this->toastSuccess("Ambang batas {$product->nama_barang} diperbarui.");
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
        $selectedLokasi = !empty($this->locationFilter)
            ? \App\Models\Lokasi::where('kode_lokasi', $this->locationFilter)->first()
            : null;

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

        // Apply location & stock filter based on trx_stok_lokasi
        if ($selectedLokasi) {
            if ($this->stockFilter === 'menipis') {
                $query->where('is_threshold_active', true)
                      ->where('min_stok', '>', 0)
                      ->whereHas('stokLokasi', function ($q) use ($selectedLokasi) {
                          $q->where('lokasi_id', $selectedLokasi->id)
                            ->whereColumn('qty', '<=', 'mst_produk.min_stok');
                      });
            } elseif ($this->stockFilter === 'threshold_only') {
                $query->where('is_threshold_active', true);
            } elseif ($this->stockFilter === 'habis') {
                $query->whereHas('stokLokasi', function ($q) use ($selectedLokasi) {
                    $q->where('lokasi_id', $selectedLokasi->id)
                      ->where('qty', '<=', 0);
                });
            } elseif ($this->stockFilter === 'melimpah') {
                $query->whereHas('stokLokasi', function ($q) use ($selectedLokasi) {
                    $q->where('lokasi_id', $selectedLokasi->id)
                      ->where('qty', '>', 5);
                });
            } else {
                // Default when location filter is selected: show products with active stock in this location
                $query->whereHas('stokLokasi', function ($q) use ($selectedLokasi) {
                    $q->where('lokasi_id', $selectedLokasi->id)
                      ->where('qty', '>', 0);
                });
            }
        } else {
            // Apply global stock filter on total_stok
            if ($this->stockFilter === 'menipis') {
                $query->lowStock();
            } elseif ($this->stockFilter === 'threshold_only') {
                $query->where('is_threshold_active', true);
            } elseif ($this->stockFilter === 'habis') {
                $query->where('total_stok', '=', 0);
            } elseif ($this->stockFilter === 'melimpah') {
                $query->where(function ($q) {
                    $q->where('is_threshold_active', false)
                      ->orWhereColumn('total_stok', '>', 'min_stok');
                });
            }
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
        if ($selectedLokasi && $this->sortField === 'total_stok') {
            $query->select('mst_produk.*')
                  ->leftJoin('trx_stok_lokasi', function ($join) use ($selectedLokasi) {
                      $join->on('trx_stok_lokasi.produk_id', '=', 'mst_produk.id')
                           ->where('trx_stok_lokasi.lokasi_id', '=', $selectedLokasi->id);
                  })
                  ->orderBy(DB::raw('COALESCE(trx_stok_lokasi.qty, 0)'), $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $validPerPage = in_array((int)$this->perPage, [10, 25, 50, 100]) ? (int)$this->perPage : 10;
        $products = $query->paginate($validPerPage);

        // Fetch categories list for filter
        $categories = Produk::distinct()->whereNotNull('kategori')->pluck('kategori');

        // Fetch dynamic lists for colors and sizes
        $colors = Produk::distinct()->whereNotNull('warna')->where('warna', '!=', '')->orderBy('warna')->pluck('warna');
        $sizes = Produk::distinct()->whereNotNull('size')->where('size', '!=', '')->orderByRaw('CAST(size AS UNSIGNED), size')->pluck('size');

        // Low stock count for WhatsApp badge and KPIs
        $lowStockCount = Produk::lowStock()->count();

        // Fetch KPIs (Dynamic when locationFilter is active)
        if ($selectedLokasi) {
            $totalSkus = \App\Models\StokLokasi::where('lokasi_id', $selectedLokasi->id)
                ->where('qty', '>', 0)
                ->count();

            $stokMenipis = Produk::where('is_threshold_active', true)
                ->where('min_stok', '>', 0)
                ->whereHas('stokLokasi', function ($q) use ($selectedLokasi) {
                    $q->where('lokasi_id', $selectedLokasi->id)
                      ->whereColumn('qty', '<=', 'mst_produk.min_stok');
                })
                ->count();

            $totalNilaiStokVal = \App\Models\StokLokasi::where('lokasi_id', $selectedLokasi->id)
                ->join('mst_produk', 'mst_produk.id', '=', 'trx_stok_lokasi.produk_id')
                ->select(DB::raw('SUM(trx_stok_lokasi.qty * mst_produk.hpp) as total'))
                ->value('total') ?? 0;

            $topMutasi = \App\Models\MutasiLokasi::where('lokasi_asal_id', $selectedLokasi->id)
                ->whereIn('tipe_mutasi', ['KELUAR', 'TRANSFER'])
                ->select('produk_id', DB::raw('SUM(qty) as total_out'))
                ->groupBy('produk_id')
                ->orderByDesc('total_out')
                ->first();

            $fastMovingProduct = $topMutasi ? Produk::find($topMutasi->produk_id) : null;
            if ($fastMovingProduct && $topMutasi) {
                $fastMovingProduct->lokasi_keluar = $topMutasi->total_out;
            }
            if (!$fastMovingProduct) {
                $topStok = \App\Models\StokLokasi::where('lokasi_id', $selectedLokasi->id)
                    ->where('qty', '>', 0)
                    ->orderByDesc('qty')
                    ->first();
                $fastMovingProduct = $topStok ? Produk::find($topStok->produk_id) : null;
            }
        } else {
            $totalSkus = Produk::count();
            $stokMenipis = $lowStockCount;
            $totalNilaiStokVal = Produk::query()->select(DB::raw('SUM(total_stok * hpp) as total'))->value('total') ?? 0;
            $fastMovingProduct = Produk::query()->orderBy('stok_keluar', 'desc')->first();
        }

        $totalNilaiStok = $this->formatRupiahAbbr($totalNilaiStokVal);

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
            'lowStockCount' => $lowStockCount,
            'totalNilaiStok' => $totalNilaiStok,
            'fastMovingProduct' => $fastMovingProduct,
            'locationsList' => $this->locationsList,
            'selectedLokasi' => $selectedLokasi,
        ]);
    }
}
