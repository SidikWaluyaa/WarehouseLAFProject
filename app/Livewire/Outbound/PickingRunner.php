<?php

namespace App\Livewire\Outbound;

use App\Models\PickingList;
use App\Models\Produk;
use App\Services\PickingService;
use App\Traits\HasAlerts;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PickingRunner extends Component
{
    use HasAlerts;

    public int $pickingId;
    public ?string $scanProduct = '';
    public int $scanQty = 1;
    public ?string $errorMessage = null;

    // Barcode Simulation Modal
    public bool $isBarcodeModalOpen = false;
    public string $barcodeModalValue = '';
    public string $barcodeModalType = '';
    public string $barcodeModalTitle = '';

    public function clearAlerts(): void
    {
        $this->errorMessage = null;
    }

    public function mount(int $id)
    {
        $this->pickingId = $id;

        // Auto start if status is PENDING and user has permission
        $picking = PickingList::findOrFail($this->pickingId);
        if ($picking->status === 'PENDING') {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if ($user && ($user->can('manage master') || $user->can('input keluar'))) {
                try {
                    PickingService::startPicking($this->pickingId, $picking->picker_name ?: $user->name);
                } catch (Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not auto-start picking: " . $e->getMessage());
                }
            }
        }
    }

    public function scanBarcode(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->clearAlerts();

        try {
            $picking = PickingList::with(['details.produk', 'details.lokasiRak'])->findOrFail($this->pickingId);

            if ($picking->status === 'COMPLETED') {
                $this->alertError("Picking list ini sudah selesai (COMPLETED).");
                return;
            }

            if (!empty($this->scanProduct)) {
                $product = Produk::where('kode_barang', trim($this->scanProduct))->first();
                if (!$product) {
                    $this->errorMessage = "Produk dengan SKU '" . $this->scanProduct . "' tidak ditemukan.";
                    $this->dispatch('focus-product');
                    return;
                }

                // Find detail matching the product which is not verified yet
                /** @var \App\Models\PickingDetail|null $detail */
                $detail = $picking->details->where('produk_id', $product->id)->where('is_verified', false)->first();

                if (!$detail) {
                    $this->errorMessage = "Produk '" . $product->nama_barang . "' tidak diperlukan atau sudah selesai diambil.";
                    $this->dispatch('focus-product');
                    return;
                }

                // Increment qty_picked
                $newQtyPicked = $detail->qty_picked + (int)$this->scanQty;
                if ($newQtyPicked > $detail->qty_to_pick) {
                    $this->errorMessage = "Jumlah picking melebihi permintaan! Diminta: " . $detail->qty_to_pick . ", Diambil: " . $newQtyPicked;
                    $this->dispatch('focus-product');
                    return;
                }

                $pickedQty = (int)$this->scanQty;
                PickingService::verifyPickItem($detail->id, $newQtyPicked);
                $this->scanProduct = '';
                $this->scanQty = 1;
                $this->toastSuccess("Berhasil mengambil " . $pickedQty . " pcs " . $product->nama_barang);
                
                $this->dispatch('focus-product');
            } else {
                $this->dispatch('focus-product');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingRunner/scanBarcode: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
            $this->dispatch('focus-product');
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
            $this->dispatch('focus-product');
        }
    }

    public function completePicking(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $picking = PickingList::with('details')->findOrFail($this->pickingId);
            
            // Check if there are unverified items
            $unverified = $picking->details->where('is_verified', false)->count();
            if ($unverified > 0) {
                $this->alertError("Masih ada " . $unverified . " item yang belum diambil/diverifikasi.");
                return;
            }

            PickingService::completePicking($this->pickingId);

            $this->toastSuccess("Picking List " . $picking->nomor_picking . " berhasil diselesaikan!");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingRunner/completePicking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function showBarcodeModal(string $value, string $type): void
    {
        $this->barcodeModalValue = $value;
        $this->barcodeModalType = $type;
        $this->barcodeModalTitle = $type === 'location' ? 'Barcode Lokasi / Rak: ' . $value : 'Barcode SKU Produk: ' . $value;
        $this->isBarcodeModalOpen = true;
    }

    public function closeBarcodeModal(): void
    {
        $this->isBarcodeModalOpen = false;
        $this->barcodeModalValue = '';
        $this->barcodeModalType = '';
        $this->barcodeModalTitle = '';
    }

    public function render()
    {
        $picking = PickingList::with(['lokasi', 'details.produk', 'details.lokasiRak'])->findOrFail($this->pickingId);

        // Calculate progress
        $totalItems = $picking->details->count();
        $verifiedItems = $picking->details->where('is_verified', true)->count();
        $totalQtyToPick = $picking->details->sum('qty_to_pick');
        $totalQtyPicked = $picking->details->sum('qty_picked');
        $progressPct = $totalQtyToPick > 0 ? min(100, round(($totalQtyPicked / $totalQtyToPick) * 100)) : 0;

        return view('livewire.outbound.picking-runner', [
            'picking' => $picking,
            'totalItems' => $totalItems,
            'verifiedItems' => $verifiedItems,
            'totalQtyToPick' => $totalQtyToPick,
            'totalQtyPicked' => $totalQtyPicked,
            'progressPct' => $progressPct,
        ]);
    }
}
