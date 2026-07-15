<?php

namespace App\Livewire\Transfer;

use Livewire\Component;
use App\Models\TransferHeader;
use App\Services\TransferService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Detail extends Component
{
    use HasAlerts;

    public TransferHeader $transfer;


    public function mount(TransferHeader $transfer)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->transfer = $transfer->load(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
    }

    public function processShipment()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            TransferService::processShipment($this->transfer->id, Auth::user()->name);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Barang berhasil dikirim. Status berubah ke IN_TRANSIT.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/processShipment: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function processReceive()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            TransferService::processReceive($this->transfer->id, Auth::user()->name);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Barang berhasil diterima. Status berubah ke RECEIVED.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/processReceive: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function closeTransfer()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->can('manage master')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            TransferService::closeTransfer($this->transfer->id);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Transfer Order berhasil ditutup.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/closeTransfer: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function cancelTransfer()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if ($this->transfer->status !== 'DRAFT') {
                throw new Exception("Hanya transfer berstatus DRAFT yang dapat dibatalkan.");
            }
            $this->transfer->status = 'CANCELLED';
            $this->transfer->save();
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Transfer Order berhasil dibatalkan.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/cancelTransfer: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transfer.detail');
    }
}
