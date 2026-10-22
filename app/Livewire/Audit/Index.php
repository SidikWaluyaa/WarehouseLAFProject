<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\StokLokasi;
use App\Models\MutasiLokasi;
use App\Models\OpnameHeader;
use App\Models\OpnameDetail;
use App\Exports\OpnameCountSheetExport;
use App\Imports\OpnameCountSheetImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Traits\HasAlerts;
use Exception;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasAlerts;

    // View state
    public $search = '';
    public ?int $activeSessionId = null;
    public bool $isCreatingSession = false;

    // Excel Import state
    public bool $isImportOpen = false;
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $importFile = null;
    public array $importErrors = [];
    public bool $isImportPreviewOpen = false;
    public array $importPreviewList = [];
    public array $importPreviewSummary = [];

    // Discrepancy Summary Modal state
    public bool $isDiscrepancyModalOpen = false;
    public string $discrepancyFilter = 'diff'; // 'all' or 'diff'

    // New Session Form
    public $new_tanggal = '';
    public $new_lokasi_id = '';
    public $new_keterangan = '';

    // Scanning input
    public $barcodeScan = '';

    // Selected Session properties
    public ?OpnameHeader $selectedSession = null;
    public $detailsList = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->new_tanggal = date('Y-m-d');
    }

    public function startNewSession()
    {
        Gate::authorize('input masuk');
        $this->new_tanggal = date('Y-m-d');
        $this->new_lokasi_id = '';
        $this->new_keterangan = '';
        $this->isCreatingSession = true;
    }

    public function cancelCreateSession()
    {
        $this->isCreatingSession = false;
    }

    public function createSession()
    {
        Gate::authorize('input masuk');
        $this->validate([
            'new_tanggal' => 'required|date',
            'new_lokasi_id' => 'required|exists:mst_lokasi,id',
            'new_keterangan' => 'nullable|string',
        ]);

        $lokasi = Lokasi::findOrFail($this->new_lokasi_id);

        // Check if there is already a DRAFT session for this location
        $existingDraft = OpnameHeader::where([
            'lokasi_id' => $lokasi->id,
            'status' => 'DRAFT',
        ])->first();

        if ($existingDraft) {
            $this->activeSessionId = $existingDraft->id;
            $this->loadSession($existingDraft->id);
            $this->isCreatingSession = false;
            $this->toastSuccess("Melanjutkan sesi DRAFT yang sudah ada di lokasi {$lokasi->kode_lokasi}.");
            return;
        }

        $nomorOpname = 'OPN/' . date('Ymd', strtotime($this->new_tanggal)) . '/' . strtoupper(bin2hex(random_bytes(3)));

        $session = OpnameHeader::create([
            'nomor_opname' => $nomorOpname,
            'tanggal' => $this->new_tanggal,
            'lokasi_id' => $this->new_lokasi_id,
            'status' => 'DRAFT',
            'keterangan' => $this->new_keterangan,
            'operator_name' => Auth::user()->name,
        ]);

        // Auto-populate all active products with current stock in this location (Optimized bulk insert)
        $now = now();
        $details = DB::table('mst_produk')
            ->leftJoin('trx_stok_lokasi', function ($join) {
                $join->on('mst_produk.id', '=', 'trx_stok_lokasi.produk_id')
                     ->where('trx_stok_lokasi.lokasi_id', '=', $this->new_lokasi_id);
            })
            ->select('mst_produk.id as produk_id', DB::raw('COALESCE(trx_stok_lokasi.qty, 0) as qty_sistem'))
            ->get()
            ->map(function ($row) use ($session, $now) {
                return [
                    'opname_header_id' => $session->id,
                    'produk_id' => $row->produk_id,
                    'qty_sistem' => $row->qty_sistem,
                    'qty_fisik' => $row->qty_sistem, // Default physical count matches system until audited
                    'selisih' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->toArray();

        OpnameDetail::insert($details);

        $this->activeSessionId = $session->id;
        $this->loadSession($session->id);
        $this->isCreatingSession = false;
        $this->toastSuccess("Sesi opname {$nomorOpname} berhasil dibuat.");
    }

    public function loadSession(int $id)
    {
        $this->activeSessionId = $id;
        $this->selectedSession = OpnameHeader::with(['lokasi', 'details.produk'])->findOrFail($id);
        $this->refreshDetails();
    }

    public function backToList()
    {
        $this->activeSessionId = null;
        $this->selectedSession = null;
        $this->detailsList = [];
        $this->barcodeScan = '';
    }

    public function refreshDetails()
    {
        if ($this->selectedSession) {
            $this->detailsList = OpnameDetail::where(['opname_header_id' => $this->selectedSession->id])
                ->join('mst_produk', function ($join) {
                    $join->on('trx_opname_detail.produk_id', '=', 'mst_produk.id');
                })
                ->select('trx_opname_detail.*', 'mst_produk.nama_barang', 'mst_produk.kode_barang', 'mst_produk.satuan', 'mst_produk.hpp', 'mst_produk.variasi_barang')
                ->orderByRaw('mst_produk.nama_barang')
                ->get()
                ->toArray();
        }
    }

    public function processBarcodeScan()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        $scanText = strtoupper(trim($this->barcodeScan));
        if (empty($scanText)) {
            return;
        }

        // 1. Check if scanText is a Location Code
        $scannedLocation = Lokasi::where(['kode_lokasi' => $scanText])->first();
        if ($scannedLocation) {
            $this->barcodeScan = '';
            
            // If we are already in a session
            if ($this->selectedSession) {
                if ($this->selectedSession->lokasi_id == $scannedLocation->id) {
                    $this->dispatch('play-beep');
                    $this->toastSuccess("Lokasi audit dikonfirmasi: {$scannedLocation->kode_lokasi}. Silakan lanjutkan memindai produk.");
                } else {
                    $this->dispatch('play-error-buzzer');
                    $this->alertError("Barcode lokasi '{$scanText}' tidak cocok dengan lokasi sesi audit saat ini ({$this->selectedSession->lokasi->kode_lokasi}).");
                }
            } else {
                // If not in a session, automatically create/open session for this location!
                $existingDraft = OpnameHeader::where([
                    'lokasi_id' => $scannedLocation->id,
                    'status' => 'DRAFT',
                ])->first();

                if ($existingDraft) {
                    $this->dispatch('play-beep');
                    $this->loadSession($existingDraft->id);
                    $this->toastSuccess("Melanjutkan sesi DRAFT untuk lokasi {$scannedLocation->kode_lokasi} via barcode scan.");
                } else {
                    // Create DRAFT session automatically
                    $this->dispatch('play-beep');
                    $nomorOpname = 'OPN/' . date('Ymd') . '/' . strtoupper(bin2hex(random_bytes(3)));
                    $session = OpnameHeader::create([
                        'nomor_opname' => $nomorOpname,
                        'tanggal' => date('Y-m-d'),
                        'lokasi_id' => $scannedLocation->id,
                        'status' => 'DRAFT',
                        'keterangan' => 'Dibuat otomatis via scan barcode lokasi.',
                        'operator_name' => Auth::user()->name,
                    ]);

                    $products = Produk::all();
                    foreach ($products as $p) {
                        $stokLokasi = StokLokasi::where([
                            'produk_id' => $p->id,
                            'lokasi_id' => $scannedLocation->id,
                        ])->first();
                        $qtySistem = $stokLokasi ? $stokLokasi->qty : 0;

                        OpnameDetail::create([
                            'opname_header_id' => $session->id,
                            'produk_id' => $p->id,
                            'qty_sistem' => $qtySistem,
                            'qty_fisik' => $qtySistem,
                            'selisih' => 0,
                        ]);
                    }

                    $this->loadSession($session->id);
                    $this->toastSuccess("Sesi opname baru {$nomorOpname} dibuat otomatis untuk lokasi {$scannedLocation->kode_lokasi}.");
                }
            }
            return;
        }

        // 2. Check if we are in a session. If not, we can't scan products
        if (!$this->selectedSession) {
            $this->dispatch('play-error-buzzer');
            $this->barcodeScan = '';
            $this->addError('barcodeScan', "Silakan scan barcode lokasi terlebih dahulu untuk membuka sesi audit.");
            return;
        }

        // 3. If in a session, search for Product by Barcode/SKU
        $product = Produk::where(['kode_barang' => $scanText])->first();
        if ($product) {
            $this->barcodeScan = '';
            $this->dispatch('play-beep');

            // Find or create in details
            $detail = OpnameDetail::where([
                'opname_header_id' => $this->selectedSession->id,
                'produk_id' => $product->id,
            ])->first();

            if ($detail) {
                // If it is in the list, increment physical count by 1
                $detail->qty_fisik += 1;
                $detail->selisih = $detail->qty_fisik - $detail->qty_sistem;
                $detail->save();
            } else {
                // If not in the list (e.g. new product added to system later), fetch current stock in this location
                $stokLokasi = StokLokasi::where([
                    'produk_id' => $product->id,
                    'lokasi_id' => $this->selectedSession->lokasi_id,
                ])->first();
                $qtySistem = $stokLokasi ? $stokLokasi->qty : 0;

                OpnameDetail::create([
                    'opname_header_id' => $this->selectedSession->id,
                    'produk_id' => $product->id,
                    'qty_sistem' => $qtySistem,
                    'qty_fisik' => 1,
                    'selisih' => 1 - $qtySistem,
                ]);
            }

            $this->refreshDetails();
            $this->toastSuccess("Scanned: {$product->nama_barang} (+1)");
        } else {
            $this->dispatch('play-error-buzzer');
            $this->barcodeScan = '';
            $this->addError('barcodeScan', "Barcode '{$scanText}' tidak terdaftar sebagai Lokasi maupun SKU Produk.");
        }
    }

    public function updateQtyFisik(int $detailId, mixed $newQty)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->selectedSession || $this->selectedSession->status !== 'DRAFT') {
            abort(403, 'Sesi audit tidak aktif atau sudah selesai.');
        }

        $detail = OpnameDetail::findOrFail($detailId);
        if ($detail->opname_header_id !== $this->selectedSession->id) {
            abort(403, 'Detail tidak cocok dengan sesi yang aktif.');
        }

        $qty = (int)$newQty;
        if ($qty < 0) {
            $qty = 0;
        }

        $detail->qty_fisik = $qty;
        $detail->selisih = $qty - $detail->qty_sistem;
        $detail->save();

        $this->refreshDetails();
        $this->toastSuccess("Kuantitas fisik diperbarui.");
    }

    public function updateCatatan(int $detailId, string $catatan)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->selectedSession || $this->selectedSession->status !== 'DRAFT') {
            abort(403, 'Sesi audit tidak aktif atau sudah selesai.');
        }

        $detail = OpnameDetail::findOrFail($detailId);
        if ($detail->opname_header_id !== $this->selectedSession->id) {
            abort(403, 'Detail tidak cocok dengan sesi yang aktif.');
        }

        $detail->catatan = $catatan;
        $detail->save();

        $this->refreshDetails();
        $this->toastSuccess("Catatan diperbarui.");
    }

    public function completeSession()
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession) {
            $this->alertError("Sesi audit tidak aktif.");
            return;
        }
        if ($this->selectedSession->status !== 'DRAFT') {
            $this->alertError("Hanya sesi audit berstatus DRAFT yang dapat diselesaikan.");
            return;
        }

        $this->selectedSession->status = 'COMPLETED';
        $this->selectedSession->save();

        $this->loadSession($this->selectedSession->id);
        $this->isDiscrepancyModalOpen = true;
        $this->toastSuccess("Pencatatan sesi audit selesai. Tinjau rangkuman selisih untuk persetujuan.");
    }

    public function reopenSession(): void
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession) {
            $this->alertError("Sesi audit tidak aktif.");
            return;
        }

        if ($this->selectedSession->status !== 'COMPLETED') {
            $this->alertError("Hanya sesi berstatus SELESAI (COMPLETED) yang dapat dikembalikan ke DRAFT.");
            return;
        }

        $this->selectedSession->status = 'DRAFT';
        $this->selectedSession->save();

        $this->loadSession($this->selectedSession->id);
        $this->isDiscrepancyModalOpen = false;
        $this->toastSuccess("Sesi audit dikembalikan ke status DRAFT. Anda dapat merevisi atau mengimpor ulang data.");
    }

    public function openDiscrepancyModal(): void
    {
        $this->isDiscrepancyModalOpen = true;
    }

    public function closeDiscrepancyModal(): void
    {
        $this->isDiscrepancyModalOpen = false;
    }

    public function exportExcel()
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession) {
            $this->alertError("Sesi audit tidak aktif.");
            return;
        }

        $cleanNomor = str_replace(['/', '\\', ' '], '_', $this->selectedSession->nomor_opname);
        $filename = "lembar_audit_{$cleanNomor}.xlsx";

        return Excel::download(new OpnameCountSheetExport($this->selectedSession->id), $filename);
    }

    public function openImportModal(): void
    {
        Gate::authorize('input masuk');
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

    public function importExcel()
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession || $this->selectedSession->status !== 'DRAFT') {
            $this->alertError("Hanya sesi opname berstatus DRAFT yang dapat diimpor datanya.");
            return;
        }

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'importFile.required' => 'Silakan pilih file Excel lembar audit terlebih dahulu.',
            'importFile.mimes' => 'File harus berupa Excel (.xlsx, .xls, .csv).',
            'importFile.max' => 'Ukuran file maksimal adalah 10MB.',
        ]);

        $importer = new OpnameCountSheetImport($this->selectedSession->id, isStagingOnly: true);

        try {
            Excel::import($importer, $this->importFile->getRealPath());

            $this->importPreviewList = $importer->previewList;
            $this->importPreviewSummary = $importer->previewSummary;
            $this->isImportOpen = false;
            $this->isImportPreviewOpen = true;
            $this->toastSuccess("File Excel berhasil dibaca. Silakan periksa pratinjau sebelum menerapkan.");
        } catch (Exception $e) {
            if (!empty($importer->errorsList)) {
                $this->importErrors = $importer->errorsList;
            } else {
                $this->importErrors = ["Terjadi kesalahan saat memproses file Excel: " . $e->getMessage()];
            }
            $this->alertError("Gagal membaca lembar audit. Silakan periksa file Anda.");
        }
    }

    public function applyImportPreview(): void
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession || $this->selectedSession->status !== 'DRAFT') {
            $this->alertError("Hanya sesi opname berstatus DRAFT yang dapat diperbarui.");
            return;
        }

        if (empty($this->importPreviewList)) {
            $this->alertError("Tidak ada data pratinjau untuk diterapkan.");
            return;
        }

        try {
            $updated = OpnameCountSheetImport::applyPreviewItems($this->selectedSession->id, $this->importPreviewList);

            $this->loadSession($this->selectedSession->id);
            $this->closeImportPreview();
            $this->toastSuccess("Berhasil menerapkan {$updated} data fisik dari Excel ke sesi audit.");
        } catch (Exception $e) {
            $this->alertError("Gagal menerapkan hasil import: " . $e->getMessage());
        }
    }

    public function closeImportPreview(): void
    {
        $this->isImportPreviewOpen = false;
        $this->importPreviewList = [];
        $this->importPreviewSummary = [];
        $this->importFile = null;
    }

    public function getDiscrepancyStatsProperty(): array
    {
        $totalItems = count($this->detailsList);
        $matched = 0;
        $surplus = 0;
        $deficit = 0;
        $totalQtySistem = 0;
        $totalQtyFisik = 0;
        $netNominal = 0;

        foreach ($this->detailsList as $item) {
            $totalQtySistem += (int)$item['qty_sistem'];
            $totalQtyFisik += (int)$item['qty_fisik'];
            $diff = (int)$item['selisih'];
            $hpp = isset($item['hpp']) ? (float)$item['hpp'] : 0;
            $netNominal += ($diff * $hpp);

            if ($diff == 0) {
                $matched++;
            } elseif ($diff > 0) {
                $surplus++;
            } else {
                $deficit++;
            }
        }

        return [
            'totalItems' => $totalItems,
            'matched' => $matched,
            'surplus' => $surplus,
            'deficit' => $deficit,
            'totalQtySistem' => $totalQtySistem,
            'totalQtyFisik' => $totalQtyFisik,
            'netSelisihQty' => $totalQtyFisik - $totalQtySistem,
            'netNominal' => $netNominal,
        ];
    }

    public function cancelSession()
    {
        Gate::authorize('input masuk');
        if (!$this->selectedSession) {
            $this->alertError("Sesi audit tidak aktif.");
            return;
        }
        if (!in_array($this->selectedSession->status, ['DRAFT', 'COMPLETED'])) {
            $this->alertError("Hanya sesi audit berstatus DRAFT atau COMPLETED yang dapat dibatalkan.");
            return;
        }

        $this->selectedSession->status = 'CANCELLED';
        $this->selectedSession->save();

        $this->isDiscrepancyModalOpen = false;
        $this->backToList();
        $this->toastSuccess("Sesi audit dibatalkan.");
    }

    public function executeAdjustment()
    {
        Gate::authorize('manage master');
        if (!$this->selectedSession || $this->selectedSession->status !== 'COMPLETED') {
            return;
        }

        DB::transaction(function () {
            $session = OpnameHeader::lockForUpdate()->findOrFail($this->selectedSession->id);
            if ($session->status !== 'COMPLETED') {
                throw new Exception("Sesi ini sudah di-adjust atau statusnya berubah.");
            }

            $details = OpnameDetail::where(['opname_header_id' => $session->id])->get();
            $lokasiId = $session->lokasi_id;

            $oldValues = [
                'session' => $session->toArray(),
                'details' => $details->toArray()
            ];

            foreach ($details as $d) {
                // If there's no discrepancy, no stock change in DB is needed
                if ($d->selisih == 0) {
                    continue;
                }

                $product = Produk::lockForUpdate()->findOrFail($d->produk_id);
                
                // Get or create stock record in this location
                $stokLokasi = StokLokasi::firstOrCreate(
                    ['produk_id' => $product->id, 'lokasi_id' => $lokasiId],
                    ['qty' => 0]
                );

                $qtySebelum = $stokLokasi->qty;
                $stokLokasi->qty = $d->qty_fisik;
                $stokLokasi->save();

                // Log dynamic mutation in trx_mutasi_lokasi
                $locAsal = $d->selisih < 0 ? $lokasiId : null;
                $locTujuan = $d->selisih > 0 ? $lokasiId : null;
                
                MutasiLokasi::create([
                    'produk_id' => $product->id,
                    'lokasi_asal_id' => $locAsal,
                    'lokasi_tujuan_id' => $locTujuan,
                    'qty' => abs($d->selisih),
                    'tipe_mutasi' => 'OPNAME_ADJUST',
                    'referensi_tipe' => get_class($session),
                    'referensi_id' => $session->id,
                    'keterangan' => 'Stock Opname Adjustment: ' . $session->nomor_opname,
                    'data_dimasukan_oleh' => Auth::user()->name,
                ]);

                // Recalculate global product total_stok cache
                $totalStok = StokLokasi::where(['produk_id' => $product->id])->sum('qty');
                
                // Keep stok_masuk / stok_keluar in sync too if needed, or just adjust total_stok
                if ($d->selisih > 0) {
                    $product->stok_masuk += $d->selisih;
                } else {
                    $product->stok_keluar += abs($d->selisih);
                }
                
                $product->total_stok = $totalStok;
                $product->save();
            }

            $session->status = 'ADJUSTED';
            $session->adjusted_by = Auth::user()->name;
            $session->adjusted_at = now();
            $session->save();

            $newValues = [
                'session' => $session->fresh()->toArray(),
                'details' => OpnameDetail::where('opname_header_id', $session->id)->get()->toArray()
            ];

            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'username' => Auth::user()->name,
                'action' => 'UPDATE',
                'auditable_type' => get_class($session),
                'auditable_id' => $session->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        });

        $this->loadSession($this->selectedSession->id);
        $this->isDiscrepancyModalOpen = false;
        $this->toastSuccess("Sinkronisasi stok berhasil diterapkan.");
    }

    public function render()
    {
        $locationsList = Lokasi::where(['is_active' => true])->orderByRaw('kode_lokasi')->get();

        $query = OpnameHeader::with(['lokasi']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nomor_opname', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('lokasi', function ($ql) {
                      $ql->where('kode_lokasi', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $sessions = $query->orderByRaw('created_at desc')->paginate(10);

        return view('livewire.audit.index', [
            'sessions' => $sessions,
            'locationsList' => $locationsList,
        ]);
    }
}
