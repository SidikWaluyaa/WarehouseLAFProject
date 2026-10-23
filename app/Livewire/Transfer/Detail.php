<?php

namespace App\Livewire\Transfer;

use Livewire\Component;
use App\Models\TransferHeader;
use App\Services\TransferService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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

    public function requestProcessShipment(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->transfer->status !== 'DRAFT') {
            $this->alertError("Transfer order tidak dapat diproses karena statusnya bukan DRAFT.");
            return;
        }

        $namaAsal = $this->transfer->lokasiAsal->nama_lokasi ?? 'Lokasi Asal';
        $namaTujuan = $this->transfer->lokasiTujuan->nama_lokasi ?? 'Lokasi Tujuan';

        $this->alertConfirm(
            action: 'confirmed-process-shipment',
            params: ['id' => $this->transfer->id],
            title: 'Kirim Barang Transfer?',
            text: "Kirim barang untuk transfer '{$this->transfer->nomor_transfer}' dari {$namaAsal} ke {$namaTujuan}? Stok lokasi asal akan berkurang dan status menjadi IN_TRANSIT.",
            confirmText: 'Ya, Kirim Barang!',
            cancelText: 'Batal',
            icon: 'warning'
        );
    }

    #[On('confirmed-process-shipment')]
    public function confirmedProcessShipment(?int $id = null): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($id !== null && (int)$id !== (int)$this->transfer->id) {
            $this->alertError("ID transfer tidak cocok dengan dokumen yang sedang dibuka.");
            return;
        }

        $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);

        if ($this->transfer->status !== 'DRAFT') {
            $this->alertError("Hanya transfer berstatus DRAFT yang dapat diproses kirim.");
            return;
        }

        try {
            TransferService::processShipment($this->transfer->id, Auth::user()->name);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Barang transfer {$this->transfer->nomor_transfer} berhasil dikirim (IN_TRANSIT).");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/processShipment: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function processShipment(): void
    {
        $this->confirmedProcessShipment();
    }

    public function requestProcessReceive(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->transfer->status !== 'IN_TRANSIT') {
            $this->alertError("Penerimaan barang hanya dapat diproses untuk transfer berstatus IN_TRANSIT.");
            return;
        }

        $namaTujuan = $this->transfer->lokasiTujuan->nama_lokasi ?? 'Lokasi Tujuan';

        $this->alertConfirm(
            action: 'confirmed-process-receive',
            params: ['id' => $this->transfer->id],
            title: 'Konfirmasi Penerimaan Barang?',
            text: "Konfirmasi bahwa seluruh barang transfer '{$this->transfer->nomor_transfer}' telah diterima di {$namaTujuan}? Stok akan ditambahkan ke lokasi tujuan dan status menjadi RECEIVED.",
            confirmText: 'Ya, Konfirmasi Terima!',
            cancelText: 'Batal',
            icon: 'question'
        );
    }

    #[On('confirmed-process-receive')]
    public function confirmedProcessReceive(?int $id = null): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($id !== null && (int)$id !== (int)$this->transfer->id) {
            $this->alertError("ID transfer tidak cocok dengan dokumen yang sedang dibuka.");
            return;
        }

        $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);

        if ($this->transfer->status !== 'IN_TRANSIT') {
            $this->alertError("Hanya transfer berstatus IN_TRANSIT yang dapat dikonfirmasi penerimaannya.");
            return;
        }

        try {
            TransferService::processReceive($this->transfer->id, Auth::user()->name);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Barang transfer {$this->transfer->nomor_transfer} berhasil diterima (RECEIVED).");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/processReceive: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function processReceive(): void
    {
        $this->confirmedProcessReceive();
    }

    public function requestCloseTransfer(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->can('manage master')) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->transfer->status !== 'RECEIVED') {
            $this->alertError("Hanya transfer berstatus RECEIVED yang dapat ditutup/diselesaikan.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-close-transfer',
            params: ['id' => $this->transfer->id],
            title: 'Selesaikan & Tutup Transfer?',
            text: "Tutup dan selesaikan transfer order '{$this->transfer->nomor_transfer}'? Transaksi dinyatakan selesai (COMPLETED) dan tidak dapat diubah lagi.",
            confirmText: 'Ya, Selesaikan!',
            cancelText: 'Batal',
            icon: 'warning'
        );
    }

    #[On('confirmed-close-transfer')]
    public function confirmedCloseTransfer(?int $id = null): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->can('manage master')) {
            abort(403, 'Unauthorized action.');
        }

        if ($id !== null && (int)$id !== (int)$this->transfer->id) {
            $this->alertError("ID transfer tidak cocok dengan dokumen yang sedang dibuka.");
            return;
        }

        $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);

        if ($this->transfer->status !== 'RECEIVED') {
            $this->alertError("Hanya transfer berstatus RECEIVED yang dapat ditutup/diselesaikan.");
            return;
        }

        try {
            TransferService::closeTransfer($this->transfer->id);
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Transfer Order {$this->transfer->nomor_transfer} berhasil diselesaikan (COMPLETED).");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/closeTransfer: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function closeTransfer(): void
    {
        $this->confirmedCloseTransfer();
    }

    public function requestCancelTransfer(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->transfer->status !== 'DRAFT') {
            $this->alertError("Hanya transfer berstatus DRAFT yang dapat dibatalkan.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-cancel-transfer',
            params: ['id' => $this->transfer->id],
            title: 'Batalkan Transfer Order?',
            text: "Apakah Anda yakin ingin membatalkan transfer order '{$this->transfer->nomor_transfer}'? Tindakan ini tidak dapat dibatalkan.",
            confirmText: 'Ya, Batalkan!',
            cancelText: 'Tutup',
            icon: 'warning',
            destructive: true
        );
    }

    #[On('confirmed-cancel-transfer')]
    public function confirmedCancelTransfer(?int $id = null): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        if ($id !== null && (int)$id !== (int)$this->transfer->id) {
            $this->alertError("ID transfer tidak cocok dengan dokumen yang sedang dibuka.");
            return;
        }

        $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);

        try {
            if ($this->transfer->status !== 'DRAFT') {
                throw new Exception("Hanya transfer berstatus DRAFT yang dapat dibatalkan.");
            }
            $this->transfer->status = 'CANCELLED';
            $this->transfer->save();
            $this->transfer = $this->transfer->fresh(['lokasiAsal', 'lokasiTujuan', 'details.produk']);
            $this->toastSuccess("Transfer Order {$this->transfer->nomor_transfer} berhasil dibatalkan.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Detail/cancelTransfer: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function cancelTransfer(): void
    {
        $this->confirmedCancelTransfer();
    }

    public function render()
    {
        return view('livewire.transfer.detail');
    }
}
