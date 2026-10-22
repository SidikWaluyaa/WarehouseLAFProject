<?php

namespace App\Livewire\Outbound;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PickingList;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\TransferHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class PackingManager extends Component
{
    use HasAlerts, WithPagination;

    protected $paginationTheme = 'tailwind';

    // Tabs: 'ready' (Siap Packing QC) or 'completed' (Riwayat Kirim)
    public string $activeTab = 'ready';

    // Tab 1 (Ready to Pack) Filters & Pagination
    public string $search = '';
    public string $filterType = 'ALL'; // 'ALL', 'BarangKeluar', 'TransferHeader'
    public int $perPage = 10; // 10, 25, 50, 100

    // Tab 2 (Completed Shipments) Filters & Pagination
    public string $searchCompleted = '';
    public string $filterTypeCompleted = 'ALL'; // 'ALL', 'BarangKeluar', 'TransferHeader'
    public int $perPageCompleted = 10; // 10, 25, 50, 100

    // Row-level form data for QC and Shipping inputs: key = "{$type}_{$id}"
    public array $packingData = [];

    // Backward compatibility with modal/active transaction state
    public ?int $activeTransactionId = null;
    public ?string $activeTransactionType = null; // 'BarangKeluar' or 'TransferHeader'
    public string $resi_ekspedisi = '';
    public string $kurir = '';
    public array $qcItems = []; // id => boolean

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage('readyPage');
        $this->resetPage('completedPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('readyPage');
    }

    public function updatedFilterType(): void
    {
        $this->resetPage('readyPage');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage('readyPage');
    }

    public function updatedSearchCompleted(): void
    {
        $this->resetPage('completedPage');
    }

    public function updatedFilterTypeCompleted(): void
    {
        $this->resetPage('completedPage');
    }

    public function updatedPerPageCompleted(): void
    {
        $this->resetPage('completedPage');
    }

    public function initRowPacking(int $id, string $type): void
    {
        $key = "{$type}_{$id}";
        if (!isset($this->packingData[$key])) {
            $defaultKurir = 'JNE';
            $defaultResi = '';
            $qc = [];

            if ($type === 'BarangKeluar') {
                $bk = BarangKeluar::with('details')->find($id);
                if ($bk) {
                    $defaultKurir = $bk->kurir ?: 'JNE';
                    $defaultResi = $bk->resi_ekspedisi ?: '';
                    foreach ($bk->details as $d) {
                        $qc[$d->id] = (bool)$bk->is_qc_passed;
                    }
                }
            } elseif ($type === 'TransferHeader') {
                $defaultKurir = 'Transfer Antar Gudang';
                $defaultResi = 'TRF-' . $id;
                $trf = TransferHeader::with('details')->find($id);
                if ($trf) {
                    foreach ($trf->details as $d) {
                        $qc[$d->id] = true;
                    }
                }
            }

            $this->packingData[$key] = [
                'kurir' => $defaultKurir,
                'resi' => $defaultResi,
                'qc' => $qc,
            ];
        }
    }

    public function toggleQcItem(int $id, string $type, int $detailId): void
    {
        $key = "{$type}_{$id}";
        $this->initRowPacking($id, $type);
        $current = $this->packingData[$key]['qc'][$detailId] ?? false;
        $this->packingData[$key]['qc'][$detailId] = !$current;

        // Sync with legacy single-transaction state if active
        if ($this->activeTransactionId === $id && $this->activeTransactionType === $type) {
            $this->qcItems[$detailId] = $this->packingData[$key]['qc'][$detailId];
        }
    }

    public function checkAllQc(int $id, string $type): void
    {
        $key = "{$type}_{$id}";
        $this->initRowPacking($id, $type);

        if ($type === 'BarangKeluar') {
            $bk = BarangKeluar::with('details')->find($id);
            if ($bk) {
                foreach ($bk->details as $d) {
                    $this->packingData[$key]['qc'][$d->id] = true;
                    if ($this->activeTransactionId === $id) {
                        $this->qcItems[$d->id] = true;
                    }
                }
            }
        }
    }

    public function selectTransaction(int $id, string $type): void
    {
        $this->activeTransactionId = $id;
        $this->activeTransactionType = $type;
        $this->initRowPacking($id, $type);

        $key = "{$type}_{$id}";
        $this->resi_ekspedisi = $this->packingData[$key]['resi'] ?? '';
        $this->kurir = $this->packingData[$key]['kurir'] ?? 'JNE';
        $this->qcItems = $this->packingData[$key]['qc'] ?? [];
        $this->clearAlerts();
    }

    public function closeTransaction(): void
    {
        $this->activeTransactionId = null;
        $this->activeTransactionType = null;
        $this->clearAlerts();
    }

    public function toggleQc(int $detailId): void
    {
        $this->qcItems[$detailId] = !($this->qcItems[$detailId] ?? false);
        if ($this->activeTransactionId && $this->activeTransactionType) {
            $key = "{$this->activeTransactionType}_{$this->activeTransactionId}";
            $this->initRowPacking($this->activeTransactionId, $this->activeTransactionType);
            $this->packingData[$key]['qc'][$detailId] = $this->qcItems[$detailId];
        }
    }

    public function submitPacking(?int $id = null, ?string $type = null)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->clearAlerts();

        $targetId = $id ?: $this->activeTransactionId;
        $targetType = $type ?: $this->activeTransactionType;

        if (empty($targetId) || empty($targetType)) {
            $this->alertError("Transaksi tidak dipilih.");
            return;
        }

        $key = "{$targetType}_{$targetId}";
        $this->initRowPacking($targetId, $targetType);

        $targetKurir = $this->packingData[$key]['kurir'] ?? ($this->kurir ?: 'JNE');
        $targetResi = $this->packingData[$key]['resi'] ?? $this->resi_ekspedisi;
        $targetQc = $this->packingData[$key]['qc'] ?? $this->qcItems;

        try {
            DB::transaction(function () use ($targetId, $targetType, $targetKurir, $targetResi, $targetQc) {
                if ($targetType === 'BarangKeluar') {
                    $header = BarangKeluar::with('details')->findOrFail($targetId);
                    
                    if (empty($targetResi)) {
                        throw new Exception("Nomor resi ekspedisi harus diisi.");
                    }
                    if (empty($targetKurir)) {
                        throw new Exception("Nama kurir ekspedisi harus diisi.");
                    }

                    // Check if all details passed QC
                    foreach ($header->details as $detail) {
                        $isPassed = $targetQc[$detail->id] ?? false;
                        if (!$isPassed) {
                            throw new Exception("Semua item harus lulus Quality Control (QC) sebelum dikirim.");
                        }
                    }

                    $header->is_qc_passed = true;
                    $header->resi_ekspedisi = trim($targetResi);
                    $header->kurir = trim($targetKurir);
                    $header->save();

                    // Update corresponding picking list status if exists
                    $picking = PickingList::where(['referensi_tipe' => 'BarangKeluar'])
                        ->where(['referensi_id' => $header->id])
                        ->first();
                    if ($picking && $picking->status !== 'COMPLETED') {
                        $picking->status = 'COMPLETED';
                        $picking->completed_at = now();
                        $picking->save();
                    }

                } elseif ($targetType === 'TransferHeader') {
                    $header = TransferHeader::findOrFail($targetId);
                    \App\Services\TransferService::processShipment($header->id, Auth::user()?->name ?? 'System');
                }
            });

            unset($this->packingData[$key]);
            $this->closeTransaction();
            $this->toastSuccess("Quality Control & Pengiriman berhasil dikonfirmasi.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PackingManager/submitPacking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function render()
    {
        $readyTransactions = collect();

        // 1. Fetch completed picking lists
        $completedPickings = PickingList::where(['status' => 'COMPLETED'])
            ->orderByRaw('completed_at desc')
            ->get();

        $bkIds = [];
        $trfIds = [];

        foreach ($completedPickings as $picking) {
            if ($picking->referensi_tipe === 'BarangKeluar' || $picking->referensi_tipe === 'App\Models\BarangKeluar') {
                $bkIds[] = $picking->referensi_id;
            } elseif ($picking->referensi_tipe === 'TransferHeader' || $picking->referensi_tipe === 'App\Models\TransferHeader') {
                $trfIds[] = $picking->referensi_id;
            }
        }

        // Bulk pre-load referencing models to avoid N+1 queries
        $barangKeluars = collect();
        if (!empty($bkIds)) {
            $barangKeluars = BarangKeluar::with(['pelanggan', 'details.produk'])
                ->whereIn('id', array_unique($bkIds))
                ->get()
                ->keyBy('id');
        }

        $transferHeaders = collect();
        if (!empty($trfIds)) {
            $transferHeaders = TransferHeader::with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])
                ->whereIn('id', array_unique($trfIds))
                ->get()
                ->keyBy('id');
        }

        foreach ($completedPickings as $picking) {
            if ($picking->referensi_tipe === 'BarangKeluar' || $picking->referensi_tipe === 'App\Models\BarangKeluar') {
                $bk = $barangKeluars->get($picking->referensi_id);
                if ($bk) {
                    $isPacked = (bool)$bk->is_qc_passed;
                    if (!$isPacked) {
                        $readyTransactions->push((object)[
                            'id' => $bk->id,
                            'type' => 'BarangKeluar',
                            'nomor_nota' => $bk->nomor_nota ?: 'BK/' . $bk->id,
                            'tanggal' => $bk->tanggal,
                            'destinasi' => $bk->pelanggan->nama_pelanggan ?? '-',
                            'alamat' => $bk->pelanggan->alamat ?? '-',
                            'kontak' => $bk->pelanggan->kontak ?? '-',
                            'items_count' => $bk->details->sum('qty_keluar'),
                            'details' => $bk->details,
                            'picking_list' => $picking,
                            'model' => $bk,
                        ]);
                    }
                }
            } elseif ($picking->referensi_tipe === 'TransferHeader' || $picking->referensi_tipe === 'App\Models\TransferHeader') {
                $trf = $transferHeaders->get($picking->referensi_id);
                if ($trf && $trf->status === 'PICKING') {
                    $readyTransactions->push((object)[
                        'id' => $trf->id,
                        'type' => 'TransferHeader',
                        'nomor_nota' => $trf->nomor_transfer,
                        'tanggal' => $trf->tanggal,
                        'destinasi' => $trf->lokasiTujuan->nama_lokasi ?? '-',
                        'alamat' => 'Transfer ke ' . ($trf->lokasiTujuan->nama_lokasi ?? '-'),
                        'kontak' => '-',
                        'items_count' => $trf->details->sum('qty_requested'),
                        'details' => $trf->details,
                        'picking_list' => $picking,
                        'model' => $trf,
                    ]);
                }
            }
        }

        // Apply Tab 1 Filter Type
        if ($this->filterType !== 'ALL') {
            $readyTransactions = $readyTransactions->where('type', $this->filterType)->values();
        }

        // Apply Tab 1 Search
        if (!empty($this->search)) {
            $query = strtolower(trim($this->search));
            $readyTransactions = $readyTransactions->filter(function($item) use ($query) {
                if (str_contains(strtolower($item->nomor_nota), $query)) return true;
                if (str_contains(strtolower($item->destinasi), $query)) return true;
                foreach ($item->details as $d) {
                    if ($d->produk && (str_contains(strtolower($d->produk->nama_barang), $query) || str_contains(strtolower($d->produk->kode_barang), $query))) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Initialize row packing states for visible items
        foreach ($readyTransactions as $item) {
            $this->initRowPacking($item->id, $item->type);
        }

        // Paginator for Tab 1 (Ready to Pack)
        $validPerPage = in_array((int)$this->perPage, [10, 25, 50, 100]) ? (int)$this->perPage : 10;
        $currentReadyPage = LengthAwarePaginator::resolveCurrentPage('readyPage') ?: 1;
        $totalReadyCount = $readyTransactions->count();
        $paginatedReadyTransactions = new LengthAwarePaginator(
            $readyTransactions->forPage($currentReadyPage, $validPerPage)->values(),
            $totalReadyCount,
            $validPerPage,
            $currentReadyPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'readyPage',
            ]
        );

        // 2. Fetch Completed Outbound Shipments
        $completedTransactions = collect();

        if ($this->filterTypeCompleted === 'ALL' || $this->filterTypeCompleted === 'BarangKeluar') {
            $completedBks = BarangKeluar::where('is_qc_passed', true)
                ->with(['pelanggan', 'details.produk'])
                ->latest('id')
                ->get();

            $bkItems = $completedBks->map(function($bk) {
                return (object)[
                    'id' => $bk->id,
                    'type' => 'BarangKeluar',
                    'nomor_nota' => $bk->nomor_nota ?: 'BK/' . $bk->id,
                    'tanggal' => $bk->tanggal,
                    'destinasi' => $bk->pelanggan->nama_pelanggan ?? '-',
                    'alamat' => $bk->pelanggan->alamat ?? '-',
                    'kontak' => $bk->pelanggan->kontak ?? '-',
                    'items_count' => $bk->details->sum('qty_keluar'),
                    'resi_ekspedisi' => $bk->resi_ekspedisi ?? '-',
                    'kurir' => $bk->kurir ?? '-',
                    'details' => $bk->details,
                    'updated_at' => $bk->updated_at,
                    'model' => $bk,
                ];
            });
            $completedTransactions = $completedTransactions->concat($bkItems);
        }

        if ($this->filterTypeCompleted === 'ALL' || $this->filterTypeCompleted === 'TransferHeader') {
            $completedTransfers = TransferHeader::whereIn('status', ['IN_TRANSIT', 'RECEIVED', 'CLOSED'])
                ->with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])
                ->latest('id')
                ->get();

            $trfItems = $completedTransfers->map(function($trf) {
                return (object)[
                    'id' => $trf->id,
                    'type' => 'TransferHeader',
                    'nomor_nota' => $trf->nomor_transfer,
                    'tanggal' => $trf->tanggal,
                    'destinasi' => $trf->lokasiTujuan->nama_lokasi ?? '-',
                    'alamat' => 'Transfer ke ' . ($trf->lokasiTujuan->nama_lokasi ?? '-'),
                    'kontak' => '-',
                    'items_count' => $trf->details->sum('qty_sent'),
                    'resi_ekspedisi' => '-',
                    'kurir' => 'Transfer Antar Gudang',
                    'details' => $trf->details,
                    'updated_at' => $trf->updated_at,
                    'model' => $trf,
                ];
            });
            $completedTransactions = $completedTransactions->concat($trfItems);
        }

        // Sort completed items by newest
        $completedTransactions = $completedTransactions->sortByDesc(fn($t) => $t->updated_at ?? $t->id)->values();

        // Apply Tab 2 Search
        if (!empty($this->searchCompleted)) {
            $queryC = strtolower(trim($this->searchCompleted));
            $completedTransactions = $completedTransactions->filter(function($item) use ($queryC) {
                if (str_contains(strtolower($item->nomor_nota), $queryC)) return true;
                if (str_contains(strtolower($item->destinasi), $queryC)) return true;
                if (str_contains(strtolower($item->kurir), $queryC)) return true;
                if (str_contains(strtolower($item->resi_ekspedisi), $queryC)) return true;
                foreach ($item->details as $d) {
                    if ($d->produk && (str_contains(strtolower($d->produk->nama_barang), $queryC) || str_contains(strtolower($d->produk->kode_barang), $queryC))) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        // Paginator for Tab 2 (Completed Shipments)
        $validPerPageCompleted = in_array((int)$this->perPageCompleted, [10, 25, 50, 100]) ? (int)$this->perPageCompleted : 10;
        $currentCompletedPage = LengthAwarePaginator::resolveCurrentPage('completedPage') ?: 1;
        $totalCompletedCount = $completedTransactions->count();
        $paginatedCompletedTransactions = new LengthAwarePaginator(
            $completedTransactions->forPage($currentCompletedPage, $validPerPageCompleted)->values(),
            $totalCompletedCount,
            $validPerPageCompleted,
            $currentCompletedPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'completedPage',
            ]
        );

        // KPI Statistics
        $countReady = $totalReadyCount;
        $countCompletedToday = BarangKeluar::where('is_qc_passed', true)->whereDate('updated_at', today())->count()
            + TransferHeader::whereIn('status', ['IN_TRANSIT', 'RECEIVED', 'CLOSED'])->whereDate('updated_at', today())->count();
        $countTotalCompleted = BarangKeluar::where('is_qc_passed', true)->count()
            + TransferHeader::whereIn('status', ['IN_TRANSIT', 'RECEIVED', 'CLOSED'])->count();

        // Legacy active transaction preview
        $activeTransaction = null;
        if ($this->activeTransactionId && $this->activeTransactionType) {
            if ($this->activeTransactionType === 'BarangKeluar') {
                $activeTransaction = BarangKeluar::with(['pelanggan', 'details.produk'])->find($this->activeTransactionId);
            } elseif ($this->activeTransactionType === 'TransferHeader') {
                $activeTransaction = TransferHeader::with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])->find($this->activeTransactionId);
            }
        }

        return view('livewire.outbound.packing-manager', [
            'readyTransactions' => $paginatedReadyTransactions,
            'completedTransactions' => $paginatedCompletedTransactions,
            'countReady' => $countReady,
            'countCompletedToday' => $countCompletedToday,
            'countTotalCompleted' => $countTotalCompleted,
            'activeTransaction' => $activeTransaction,
        ]);
    }
}
