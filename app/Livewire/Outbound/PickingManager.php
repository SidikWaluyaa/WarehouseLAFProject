<?php

namespace App\Livewire\Outbound;

use Livewire\Component;
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
    use HasAlerts;

    public string $activeTab = 'pending'; // pending, history
    public string $search = '';
    public ?int $activePickingId = null;
    public ?int $activePendingOutboundId = null;
    public ?string $activePendingOutboundType = null; // 'BarangKeluar' or 'TransferHeader'

    // Scanning & verification inputs
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
    }

    public function selectPicking(int $id)
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

    public function closePicking()
    {
        $this->activePickingId = null;
        $this->activePendingOutboundId = null;
        $this->activePendingOutboundType = null;
        $this->clearAlerts();
    }

    public function startPicking(int $id)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            PickingService::startPicking($id, Auth::user()->name);
            $this->selectPicking($id);
            $this->toastSuccess("Proses picking dimulai.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/startPicking: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

            // 1. Scan product barcode/sku
            if (!empty($this->scanProduct)) {
                $product = Produk::where(['kode_barang' => trim($this->scanProduct)])->first();
                if (!$product) {
                    $this->alertError("Produk dengan SKU '" . $this->scanProduct . "' tidak ditemukan.");
                    $this->dispatch('focus-product');
                    return;
                }

                // Find detail matching the product
                $detail = $picking->details->where('produk_id', $product->id)->where('is_verified', false)->first();

                if (!$detail) {
                    $this->alertError("Produk '" . $product->nama_barang . "' tidak diperlukan/sudah selesai diambil.");
                    $this->dispatch('focus-product');
                    return;
                }

                // Increment qty_picked
                $newQtyPicked = $detail->qty_picked + (int)$this->scanQty;
                if ($newQtyPicked > $detail->qty_to_pick) {
                    $this->alertError("Jumlah picking melebihi permintaan! Diminta: " . $detail->qty_to_pick . ", Diambil: " . $newQtyPicked);
                    $this->dispatch('focus-product');
                    return;
                }

                PickingService::verifyPickItem($detail->id, $newQtyPicked);
                $this->scanProduct = ''; // Reset product scan input
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
            
            // Check if there are unverified items
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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $picking = PickingService::generatePickingList($refTipe, $refId, Auth::user()->name);
            $this->toastSuccess("Picking List {$picking->nomor_picking} berhasil dibuat.");
            $this->selectPicking($picking->id);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in PickingManager/generatePickingList: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan database pada server.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
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
        $pendingLists = PickingList::whereIn('status', ['PENDING', 'PICKING'])
            ->with(['lokasi', 'details.produk'])
            ->latest()
            ->get();

        $completedLists = PickingList::where(['status' => 'COMPLETED'])
            ->with(['lokasi', 'details.produk', 'details.lokasiRak'])
            ->orderByRaw('completed_at desc')
            ->take(15)
            ->get();

        $bkWithNoPicking = \App\Models\BarangKeluar::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('trx_picking_list')
                    ->whereRaw("referensi_tipe = 'BarangKeluar' AND referensi_id = trx_barang_keluar_header.id");
            })
            ->with('pelanggan')
            ->get();

        $trfWithNoPicking = \App\Models\TransferHeader::where(['status' => 'DRAFT'])
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('trx_picking_list')
                    ->whereRaw("referensi_tipe = 'TransferHeader' AND referensi_id = trx_transfer_header.id");
            })
            ->with(['lokasiAsal', 'lokasiTujuan'])
            ->get();

        $activePicking = null;
        if ($this->activePickingId) {
            $activePicking = PickingList::with(['lokasi', 'details.produk', 'details.lokasiRak'])
                ->findOrFail($this->activePickingId);
        }

        $activePendingOutbound = null;
        if ($this->activePendingOutboundId && $this->activePendingOutboundType) {
            if ($this->activePendingOutboundType === 'BarangKeluar') {
                $activePendingOutbound = \App\Models\BarangKeluar::with(['pelanggan', 'details.produk'])
                    ->find($this->activePendingOutboundId);
            } elseif ($this->activePendingOutboundType === 'TransferHeader') {
                $activePendingOutbound = \App\Models\TransferHeader::with(['lokasiAsal', 'lokasiTujuan', 'details.produk'])
                    ->find($this->activePendingOutboundId);
            }
        }

        return view('livewire.outbound.picking-manager', [
            'pendingLists' => $pendingLists,
            'completedLists' => $completedLists,
            'bkWithNoPicking' => $bkWithNoPicking,
            'trfWithNoPicking' => $trfWithNoPicking,
            'activePicking' => $activePicking,
            'activePendingOutbound' => $activePendingOutbound,
        ]);
    }
}
