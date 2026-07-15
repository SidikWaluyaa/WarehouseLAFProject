<?php

namespace App\Livewire\Outbound;

use Livewire\Component;
use App\Models\PickingList;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarDetail;
use App\Models\TransferHeader;
use Illuminate\Support\Facades\DB;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class PackingManager extends Component
{
    use HasAlerts;

    public $search = '';
    public $activeTab = 'ready'; // ready, completed
    public ?int $activeTransactionId = null;
    public ?string $activeTransactionType = null; // BarangKeluar or TransferHeader

    // Form inputs for packing
    public $resi_ekspedisi = '';
    public $kurir = '';
    public $qcItems = []; // id => boolean

    
    

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function selectTransaction(int $id, string $type)
    {
        $this->activeTransactionId = $id;
        $this->activeTransactionType = $type;
        $this->resi_ekspedisi = '';
        $this->kurir = 'JNE';
        $this->qcItems = [];
        $this->clearAlerts();

        // Initialize QC values
        if ($type === 'BarangKeluar') {
            $header = BarangKeluar::with('details')->findOrFail($id);
            $this->resi_ekspedisi = $header->resi_ekspedisi;
            $this->kurir = $header->kurir ?: 'JNE';
            foreach ($header->details as $detail) {
                $this->qcItems[$detail->id] = (bool)$header->is_qc_passed;
            }
        }
    }

    public function closeTransaction()
    {
        $this->activeTransactionId = null;
        $this->activeTransactionType = null;
        $this->clearAlerts();
    }

    public function toggleQc(int $detailId)
    {
        $this->qcItems[$detailId] = !($this->qcItems[$detailId] ?? false);
    }

    public function submitPacking()
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->clearAlerts();

        if (empty($this->activeTransactionId)) {
            $this->alertError("Transaksi tidak dipilih.");
            return;
        }

        try {
            DB::transaction(function () {
                if ($this->activeTransactionType === 'BarangKeluar') {
                    $header = BarangKeluar::with('details')->findOrFail($this->activeTransactionId);
                    
                    // Validate resi and kurir
                    if (empty($this->resi_ekspedisi)) {
                        throw new Exception("Nomor resi ekspedisi harus diisi.");
                    }
                    if (empty($this->kurir)) {
                        throw new Exception("Nama kurir ekspedisi harus diisi.");
                    }

                    // Check if all details passed QC
                    foreach ($header->details as $detail) {
                        $isPassed = $this->qcItems[$detail->id] ?? false;
                        if (!$isPassed) {
                            throw new Exception("Semua item harus lulus Quality Control (QC) sebelum dikirim.");
                        }
                    }

                    $header->is_qc_passed = true;
                    $header->resi_ekspedisi = trim($this->resi_ekspedisi);
                    $header->kurir = trim($this->kurir);
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

                } elseif ($this->activeTransactionType === 'TransferHeader') {
                    // For transfers, we process shipment using TransferService and mark as shipped
                    $header = TransferHeader::findOrFail($this->activeTransactionId);
                    \App\Services\TransferService::processShipment($header->id, \Illuminate\Support\Facades\Auth::user()?->name ?? 'System');
                }
            });

            $this->closeTransaction();
            $this->toastSuccess("Quality Control & Packing Slip berhasil disimpan.");
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

        // Fetch completed picking lists
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
                            'destinasi' => $bk->pelanggan->nama_pelanggan,
                            'items_count' => $bk->details->sum('qty_keluar'),
                            'picking_list' => $picking,
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
                        'destinasi' => $trf->lokasiTujuan->nama_lokasi,
                        'items_count' => $trf->details->sum('qty_requested'),
                        'picking_list' => $picking,
                    ]);
                }
            }
        }

        $completedTransactions = collect();
        $completedBks = BarangKeluar::where(['is_qc_passed' => true])
            ->with(['pelanggan', 'details.produk'])
            ->orderByRaw('id desc')
            ->take(15)
            ->get();
 
        foreach ($completedBks as $bk) {
            $completedTransactions->push((object)[
                'id' => $bk->id,
                'type' => 'BarangKeluar',
                'nomor_nota' => $bk->nomor_nota ?: 'BK/' . $bk->id,
                'tanggal' => $bk->tanggal,
                'destinasi' => $bk->pelanggan->nama_pelanggan ?? '-',
                'items_count' => $bk->details->sum('qty_keluar'),
                'resi_ekspedisi' => $bk->resi_ekspedisi ?? '-',
                'kurir' => $bk->kurir ?? '-',
            ]);
        }

        $completedTransfers = TransferHeader::whereIn('status', ['IN_TRANSIT', 'RECEIVED', 'CLOSED'])
            ->with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])
            ->orderByRaw('id desc')
            ->take(15)
            ->get();

        foreach ($completedTransfers as $trf) {
            $completedTransactions->push((object)[
                'id' => $trf->id,
                'type' => 'TransferHeader',
                'nomor_nota' => $trf->nomor_transfer,
                'tanggal' => $trf->tanggal,
                'destinasi' => $trf->lokasiTujuan->nama_lokasi ?? '-',
                'items_count' => $trf->details->sum('qty_sent'),
                'resi_ekspedisi' => '-',
                'kurir' => 'Transfer Gudang',
            ]);
        }

        $completedTransactions = $completedTransactions->sortByDesc(fn($t) => $t->id)->take(15);

        $activeTransaction = null;
        if ($this->activeTransactionId) {
            if ($this->activeTransactionType === 'BarangKeluar') {
                $activeTransaction = BarangKeluar::with(['pelanggan', 'details.produk'])->find($this->activeTransactionId);
            } elseif ($this->activeTransactionType === 'TransferHeader') {
                $activeTransaction = TransferHeader::with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])->find($this->activeTransactionId);
            }
        }

        return view('livewire.outbound.packing-manager', [
            'readyTransactions' => $readyTransactions,
            'completedTransactions' => $completedTransactions,
            'activeTransaction' => $activeTransaction,
        ]);
    }
}
