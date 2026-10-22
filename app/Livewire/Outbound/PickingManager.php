<?php

namespace App\Livewire\Outbound;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PickingList;
use App\Models\PickingDetail;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\StokLokasi;
use App\Services\PickingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class PickingManager extends Component
{
    use HasAlerts, WithPagination;

    protected $paginationTheme = 'tailwind';

    // Tabs: 'picking_list' (default) or 'pending_outbound'
    public string $activeTab = 'picking_list';

    // Tab 1 (Picking List) Filters & Pagination
    public string $search = '';
    public string $filterStatus = 'ALL'; // 'ALL', 'PENDING', 'PICKING', 'COMPLETED'
    public string $filterPic = ''; // '' = all, '__unassigned__' = unassigned, or picker_name
    public bool $filterOnlyMyTasks = false;
    public int $perPage = 10; // 10, 25, 50, 100

    // Tab 2 (Pending Outbound) Filters & Pagination
    public string $searchPending = '';
    public string $filterPendingType = 'ALL'; // 'ALL', 'BarangKeluar', 'TransferHeader'
    public int $perPagePending = 10; // 10, 25, 50, 100

    // Selected order for direct scanning / detail view
    public ?int $activePickingId = null;
    public ?int $activePendingOutboundId = null;
    public ?string $activePendingOutboundType = null; // 'BarangKeluar' or 'TransferHeader'

    // Assign / Reassign PIC Modal
    public bool $isAssignModalOpen = false;
    public ?int $assignPickingId = null;
    public string $selectedPicType = 'user'; // 'user', 'manual', 'unassigned'
    public string $selectedPicUserId = '';
    public string $manualPicName = '';

    // Generate Picking List Modal (with initial PIC)
    public bool $isGenerateModalOpen = false;
    public ?string $genRefType = null;
    public ?int $genRefId = null;
    public string $genPicType = 'user'; // 'user', 'manual', 'unassigned'
    public string $genPicUserId = '';
    public string $genManualPicName = '';

    // Scanning & verification inputs (for inline/compatibility)
    public string $scanRak = '';
    public string $scanProduct = '';
    public int $scanQty = 1;

    public string $successMessage = '';
    public string $errorMessage = '';

    // Barcode Viewer Modal
    public bool $isBarcodeModalOpen = false;
    public string $barcodeModalTitle = '';
    public string $barcodeModalValue = '';
    public string $barcodeModalType = ''; // 'location' or 'sku'

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->selectedPicUserId = (string) ($user->id ?? '');
        $this->genPicUserId = (string) ($user->id ?? '');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPendingType(): void
    {
        $this->resetPage('pendingPage');
    }

    public function updatedSearchPending(): void
    {
        $this->resetPage('pendingPage');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage('pickingPage');
    }

    public function updatedPerPagePending(): void
    {
        $this->resetPage('pendingPage');
    }

    public function toggleMyTasks(): void
    {
        $this->filterOnlyMyTasks = !$this->filterOnlyMyTasks;
        if ($this->filterOnlyMyTasks) {
            $this->filterPic = '';
        }
        $this->resetPage();
    }

    public function updatedFilterPic(): void
    {
        $this->filterOnlyMyTasks = false;
        $this->resetPage();
    }

    public function selectPicking(?int $id)
    {
        $this->activePickingId = $id;
        $this->activePendingOutboundId = null;
        $this->activePendingOutboundType = null;
        $this->scanRak = '';
        $this->scanProduct = '';
        $this->scanQty = 1;
        $this->clearAlerts();
        $this->dispatch('focus-product');
    }

    public function selectPendingOutbound(int $id, string $type)
    {
        $this->activePendingOutboundId = $id;
        $this->activePendingOutboundType = $type;
        $this->activePickingId = null;
        $this->clearAlerts();
    }

    public function closePicking(): void
    {
        $this->activePickingId = null;
        $this->scanRak = '';
        $this->scanProduct = '';
        $this->scanQty = 1;
        $this->clearAlerts();
    }

    public function startPicking()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->activePickingId) {
            $this->alertError("Silakan pilih Picking List terlebih dahulu.");
            return;
        }

        try {
            $picking = PickingList::findOrFail($this->activePickingId);
            $pickerName = $picking->picker_name ?: $user->name;
            PickingService::startPicking($this->activePickingId, $pickerName);
            $this->selectPicking($this->activePickingId);
            $this->toastSuccess("Picking List dimulai oleh " . $pickerName);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/startPicking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function openGenerateModal(string $refType, int $refId): void
    {
        $this->genRefType = $refType;
        $this->genRefId = $refId;
        $this->genPicType = 'user';
        $this->genPicUserId = (string) (Auth::id() ?? '');
        $this->genManualPicName = '';
        $this->isGenerateModalOpen = true;
    }

    public function closeGenerateModal(): void
    {
        $this->isGenerateModalOpen = false;
        $this->genRefType = null;
        $this->genRefId = null;
    }

    public function confirmGeneratePicking(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->genRefType || !$this->genRefId) {
            return;
        }

        $picName = null;
        if ($this->genPicType === 'user') {
            $u = \App\Models\User::find($this->genPicUserId);
            $picName = $u ? $u->name : (Auth::user()?->name ?? 'Operator');
        } elseif ($this->genPicType === 'manual') {
            $picName = trim($this->genManualPicName);
            if (empty($picName)) {
                $this->alertError("Silakan masukkan nama petugas manual.");
                return;
            }
        } elseif ($this->genPicType === 'unassigned') {
            $picName = null;
        }

        try {
            $picking = PickingService::generatePickingList($this->genRefType, $this->genRefId, $picName);
            $this->closeGenerateModal();
            $this->activeTab = 'picking_list';
            $this->selectPicking($picking->id);
            $this->toastSuccess("Picking List " . $picking->nomor_picking . " berhasil dibuat!");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/confirmGeneratePicking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function openAssignModal(int $pickingId): void
    {
        $this->assignPickingId = $pickingId;
        $picking = PickingList::find($pickingId);
        if ($picking && $picking->picker_name) {
            $existingUser = \App\Models\User::where('name', $picking->picker_name)->first();
            if ($existingUser) {
                $this->selectedPicType = 'user';
                $this->selectedPicUserId = (string) $existingUser->id;
                $this->manualPicName = '';
            } else {
                $this->selectedPicType = 'manual';
                $this->manualPicName = $picking->picker_name;
            }
        } else {
            $this->selectedPicType = 'user';
            $this->selectedPicUserId = (string) (Auth::id() ?? '');
            $this->manualPicName = '';
        }
        $this->isAssignModalOpen = true;
    }

    public function closeAssignModal(): void
    {
        $this->isAssignModalOpen = false;
        $this->assignPickingId = null;
        $this->manualPicName = '';
    }

    public function saveAssignPic(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!$this->assignPickingId) {
            return;
        }

        $picName = null;
        if ($this->selectedPicType === 'user') {
            $u = \App\Models\User::find($this->selectedPicUserId);
            $picName = $u ? $u->name : (Auth::user()?->name ?? 'Operator');
        } elseif ($this->selectedPicType === 'manual') {
            $picName = trim($this->manualPicName);
            if (empty($picName)) {
                $this->alertError("Silakan masukkan nama PIC manual.");
                return;
            }
        } elseif ($this->selectedPicType === 'unassigned') {
            $picName = null;
        }

        try {
            $targetId = $this->assignPickingId;
            PickingService::assignPicker($targetId, $picName);
            $this->toastSuccess("PIC berhasil diperbarui menjadi: " . ($picName ?: 'Belum Ditugaskan'));
            $this->closeAssignModal();
            if ($this->activePickingId === $targetId) {
                $this->selectPicking($targetId);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/saveAssignPic: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function scanBarcode()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->clearAlerts();

        if (empty($this->activePickingId)) {
            $this->alertError("Silakan pilih Picking List terlebih dahulu.");
            $this->dispatch('focus-product');
            return;
        }

        try {
            $picking = PickingList::with(['details.produk', 'details.lokasiRak'])->findOrFail($this->activePickingId);

            if (!empty($this->scanProduct)) {
                $product = Produk::where(['kode_barang' => trim($this->scanProduct)])->first();
                if (!$product) {
                    $this->alertError("Produk dengan SKU '" . $this->scanProduct . "' tidak ditemukan.");
                    $this->dispatch('focus-product');
                    return;
                }

                $detail = $picking->details->where('produk_id', $product->id)->where('is_verified', false)->first();

                if (!$detail) {
                    $this->alertError("Produk '" . $product->nama_barang . "' tidak diperlukan/sudah selesai diambil.");
                    $this->dispatch('focus-product');
                    return;
                }

                $newQtyPicked = $detail->qty_picked + (int)$this->scanQty;
                if ($newQtyPicked > $detail->qty_to_pick) {
                    $this->alertError("Jumlah picking melebihi permintaan! Diminta: " . $detail->qty_to_pick . ", Diambil: " . $newQtyPicked);
                    $this->dispatch('focus-product');
                    return;
                }

                PickingService::verifyPickItem($detail->id, $newQtyPicked);
                $this->scanProduct = '';
                $this->scanQty = 1;
                $this->toastSuccess("Berhasil mengambil " . $this->scanQty . " pcs " . $product->nama_barang);
                
                $this->dispatch('focus-product');
            } else {
                $this->dispatch('focus-product');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/scanBarcode: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
            $this->dispatch('focus-product');
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
            $this->dispatch('focus-product');
        }
    }

    public function completePicking()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $picking = PickingList::with('details')->findOrFail($this->activePickingId);
            
            $unverified = $picking->details->where('is_verified', false)->count();
            if ($unverified > 0) {
                $this->alertError("Masih ada " . $unverified . " item yang belum diambil/diverifikasi.");
                return;
            }

            PickingService::completePicking($this->activePickingId);

            $this->closePicking();
            $this->toastSuccess("Picking List berhasil diselesaikan!");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/completePicking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function generatePickingList(string $refTipe, int $refId)
    {
        $this->openGenerateModal($refTipe, $refId);
    }

    public function showBarcodeModal(string $value, string $type)
    {
        $this->barcodeModalValue = $value;
        $this->barcodeModalType = $type;
        $this->barcodeModalTitle = $type === 'location' ? 'Barcode Lokasi / Rak: ' . $value : 'Barcode SKU Produk: ' . $value;
        $this->isBarcodeModalOpen = true;
    }

    public function closeBarcodeModal()
    {
        $this->isBarcodeModalOpen = false;
        $this->barcodeModalValue = '';
        $this->barcodeModalType = '';
        $this->barcodeModalTitle = '';
    }

    public function render()
    {
        // 1. KPI Counts
        $countPicking = PickingList::where('status', 'PICKING')->count();
        $countPending = PickingList::where('status', 'PENDING')->count();
        $countCompletedToday = PickingList::where('status', 'COMPLETED')->whereDate('completed_at', today())->count();
        $countMyTasks = PickingList::whereIn('status', ['PENDING', 'PICKING'])
            ->where('picker_name', Auth::user()?->name)
            ->count();

        // 2. Pending Outbound Base Queries
        $bkWithNoPickingQuery = \App\Models\BarangKeluar::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('trx_picking_list')
                    ->whereRaw("referensi_tipe = 'BarangKeluar' AND referensi_id = trx_barang_keluar_header.id");
            })
            ->with(['pelanggan', 'details.produk']);

        $trfWithNoPickingQuery = \App\Models\TransferHeader::where(['status' => 'DRAFT'])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('trx_picking_list')
                    ->whereRaw("referensi_tipe = 'TransferHeader' AND referensi_id = trx_transfer_header.id");
            })
            ->with(['lokasiAsal', 'lokasiTujuan', 'details.produk']);

        $countPendingOutbound = (clone $bkWithNoPickingQuery)->count() + (clone $trfWithNoPickingQuery)->count();

        // 3. Tab 1: Picking List Query
        $pickingQuery = PickingList::with(['lokasi', 'details.produk', 'details.lokasiRak']);

        if ($this->filterStatus !== 'ALL') {
            $pickingQuery->where('status', $this->filterStatus);
        }

        if ($this->filterOnlyMyTasks) {
            $userName = Auth::user()?->name;
            $pickingQuery->where('picker_name', $userName);
        } elseif (!empty($this->filterPic)) {
            if ($this->filterPic === '__unassigned__') {
                $pickingQuery->where(function ($q) {
                    $q->whereNull('picker_name')->orWhere('picker_name', '');
                });
            } else {
                $pickingQuery->where('picker_name', $this->filterPic);
            }
        }

        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $pickingQuery->where(function ($q) use ($s) {
                $q->where('nomor_picking', 'like', $s)
                  ->orWhere('picker_name', 'like', $s)
                  ->orWhereHas('details.produk', function($pq) use ($s) {
                      $pq->where('nama_barang', 'like', $s)
                        ->orWhere('kode_barang', 'like', $s);
                  });
            });
        }

        $validPerPage = in_array((int)$this->perPage, [10, 25, 50, 100]) ? (int)$this->perPage : 10;
        $pickingLists = $pickingQuery->latest()->paginate($validPerPage, ['*'], 'pickingPage');

        // Distinct pickers for dropdown
        $distinctPickers = PickingList::whereNotNull('picker_name')
            ->where('picker_name', '!=', '')
            ->distinct()
            ->orderBy('picker_name')
            ->pluck('picker_name');

        $availableUsers = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        // 4. Tab 2: Pending Outbound Filtered Results
        if (!empty($this->searchPending)) {
            $sp = '%' . trim($this->searchPending) . '%';
            $bkWithNoPickingQuery->where(function($q) use ($sp) {
                $q->where('nomor_nota', 'like', $sp)
                  ->orWhereHas('pelanggan', function($pq) use ($sp) {
                      $pq->where('nama_pelanggan', 'like', $sp);
                  });
            });
            $trfWithNoPickingQuery->where(function($q) use ($sp) {
                $q->where('nomor_transfer', 'like', $sp);
            });
        }

        $pendingOutbounds = collect();
        if ($this->filterPendingType === 'ALL' || $this->filterPendingType === 'BarangKeluar') {
            $bkItems = $bkWithNoPickingQuery->latest()->get()->map(function($bk) {
                return (object)[
                    'id' => $bk->id,
                    'type' => 'BarangKeluar',
                    'nomor' => $bk->nomor_nota ?: 'BK #'.$bk->id,
                    'tanggal' => $bk->tanggal,
                    'tujuan' => $bk->pelanggan->nama_pelanggan ?? '-',
                    'asal' => 'Gudang Utama',
                    'total_item' => $bk->details->sum('qty_keluar'),
                    'details' => $bk->details,
                    'model' => $bk,
                ];
            });
            $pendingOutbounds = $pendingOutbounds->concat($bkItems);
        }

        if ($this->filterPendingType === 'ALL' || $this->filterPendingType === 'TransferHeader') {
            $trfItems = $trfWithNoPickingQuery->latest()->get()->map(function($trf) {
                return (object)[
                    'id' => $trf->id,
                    'type' => 'TransferHeader',
                    'nomor' => $trf->nomor_transfer,
                    'tanggal' => $trf->tanggal,
                    'tujuan' => $trf->lokasiTujuan->nama_lokasi ?? '-',
                    'asal' => $trf->lokasiAsal->nama_lokasi ?? '-',
                    'total_item' => $trf->details->sum('qty_requested'),
                    'details' => $trf->details,
                    'model' => $trf,
                ];
            });
            $pendingOutbounds = $pendingOutbounds->concat($trfItems);
        }

        $validPerPagePending = in_array((int)$this->perPagePending, [10, 25, 50, 100]) ? (int)$this->perPagePending : 10;
        $currentPendingPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage('pendingPage') ?: 1;
        $totalPendingCount = $pendingOutbounds->count();
        $paginatedPendingOutbounds = new \Illuminate\Pagination\LengthAwarePaginator(
            $pendingOutbounds->forPage($currentPendingPage, $validPerPagePending)->values(),
            $totalPendingCount,
            $validPerPagePending,
            $currentPendingPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'pendingPage',
            ]
        );

        // For backward compatibility / modal preview
        $activePicking = $this->activePickingId ? PickingList::with(['lokasi', 'details.produk', 'details.lokasiRak'])->find($this->activePickingId) : null;
        $activePendingOutbound = null;
        if ($this->activePendingOutboundId && $this->activePendingOutboundType) {
            if ($this->activePendingOutboundType === 'BarangKeluar') {
                $activePendingOutbound = \App\Models\BarangKeluar::with(['pelanggan', 'details.produk'])->find($this->activePendingOutboundId);
            } else {
                $activePendingOutbound = \App\Models\TransferHeader::with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])->find($this->activePendingOutboundId);
            }
        }

        return view('livewire.outbound.picking-manager', [
            'pickingLists' => $pickingLists,
            'pendingOutbounds' => $paginatedPendingOutbounds,
            'countPendingOutbound' => $countPendingOutbound,
            'countPicking' => $countPicking,
            'countPending' => $countPending,
            'countCompletedToday' => $countCompletedToday,
            'countMyTasks' => $countMyTasks,
            'distinctPickers' => $distinctPickers,
            'availableUsers' => $availableUsers,
            'activePicking' => $activePicking,
            'activePendingOutbound' => $activePendingOutbound,
        ]);
    }
}
