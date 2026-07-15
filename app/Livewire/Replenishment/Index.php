<?php

namespace App\Livewire\Replenishment;

use Livewire\Component;
use App\Models\Lokasi;
use App\Services\ReplenishmentService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Index extends Component
{
    use HasAlerts;

    public $selectedTokoId = '';
    public $selectedItems = [];
    
    

    public function updatedSelectedTokoId()
    {
        $this->selectedItems = [];
    }

    public function toggleItem(int $produkId, int $lokasiTokoId, int $feasibleQty)
    {
        $key = $produkId . '-' . $lokasiTokoId;
        if (isset($this->selectedItems[$key])) {
            unset($this->selectedItems[$key]);
        } else {
            $this->selectedItems[$key] = [
                'produk_id' => $produkId,
                'lokasi_toko_id' => $lokasiTokoId,
                'feasible_qty' => $feasibleQty,
            ];
        }
    }

    public function createTransferOrder()
    {
        if (empty($this->selectedItems)) {
            $this->alertError("Pilih minimal 1 produk untuk di-replenish.");
            return;
        }

        try {
            $items = array_values($this->selectedItems);
            $header = ReplenishmentService::createReplenishmentTransfer($items, Auth::user()->name);
            $this->selectedItems = [];
            $this->toastSuccess("Transfer Order {$header->nomor_transfer} berhasil dibuat dari rekomendasi replenishment.");
        } catch (Exception $e) {
            $this->alertError("Gagal membuat Transfer Order: " . $e->getMessage());
        }
    }

    public function render()
    {
        $tokoLocations = Lokasi::where('tipe_lokasi', 'TOKO')
            ->where('is_active', true)
            ->orderBy('kode_lokasi')
            ->get();

        $lokasiTokoId = !empty($this->selectedTokoId) ? (int)$this->selectedTokoId : null;
        $recommendations = ReplenishmentService::getReplenishmentRecommendations($lokasiTokoId);

        return view('livewire.replenishment.index', [
            'tokoLocations' => $tokoLocations,
            'recommendations' => $recommendations,
        ]);
    }
}
