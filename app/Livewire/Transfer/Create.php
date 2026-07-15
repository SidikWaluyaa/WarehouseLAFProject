<?php

namespace App\Livewire\Transfer;

use Livewire\Component;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Services\TransferService;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Create extends Component
{
    use HasAlerts;

    public $tanggal = '';
    public $lokasi_asal_id = '';
    public $lokasi_tujuan_id = '';
    public $keterangan = '';
    public $items = [];
    public $locationsList = [];

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'lokasi_asal_id' => 'required|exists:mst_lokasi,id',
            'lokasi_tujuan_id' => 'required|exists:mst_lokasi,id|different:lokasi_asal_id',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:mst_produk,id',
            'items.*.search_query' => 'nullable|string',
            'items.*.qty_requested' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'lokasi_asal_id.required' => 'Lokasi asal wajib dipilih.',
            'lokasi_asal_id.exists' => 'Lokasi asal tidak valid.',
            'lokasi_tujuan_id.required' => 'Lokasi tujuan wajib dipilih.',
            'lokasi_tujuan_id.exists' => 'Lokasi tujuan tidak valid.',
            'lokasi_tujuan_id.different' => 'Lokasi tujuan harus berbeda dengan lokasi asal.',
            'items.required' => 'Minimal harus ada 1 item barang.',
            'items.min' => 'Minimal harus ada 1 item barang.',
            'items.*.produk_id.required' => 'Pilih produk terlebih dahulu.',
            'items.*.produk_id.exists' => 'Produk tidak ditemukan.',
            'items.*.qty_requested.required' => 'Kuantitas wajib diisi.',
            'items.*.qty_requested.integer' => 'Kuantitas harus berupa angka bulat.',
            'items.*.qty_requested.min' => 'Kuantitas minimal adalah 1.',
        ];
    }

    public function mount()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->tanggal = date('Y-m-d');
        $this->locationsList = Lokasi::where('is_active', true)->orderBy('kode_lokasi')->get();
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'produk_id' => '',
            'search_query' => '',
            'qty_requested' => 1,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function getProductsForSearch(string $query, mixed $selectedProductId = null)
    {
        $query = trim($query);
        $selectedProductId = !empty($selectedProductId) ? (int)$selectedProductId : null;
        if (strlen($query) < 2) {
            if ($selectedProductId) {
                return Produk::where('id', $selectedProductId)->get();
            }
            return [];
        }

        $term = '%' . $query . '%';
        return Produk::where('nama_barang', 'LIKE', $term)
            ->orWhere('kode_barang', 'LIKE', $term)
            ->orderBy('nama_barang')
            ->limit(20)
            ->get();
    }

    public function store()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->can('manage master') && !$user->can('input masuk') && !$user->can('input keluar'))) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate();

        try {
            $header = TransferService::createTransferOrder([
                'tanggal' => $this->tanggal,
                'lokasi_asal_id' => $this->lokasi_asal_id,
                'lokasi_tujuan_id' => $this->lokasi_tujuan_id,
                'keterangan' => $this->keterangan,
                'created_by' => Auth::user()->name,
                'items' => $this->items,
            ]);

            session()->flash('success_message', "Transfer Order {$header->nomor_transfer} berhasil dibuat.");
            return $this->redirect('/transfer', navigate: true);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error("Database Error in Transfer/Create: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->alertError("Terjadi kesalahan pada server. Silakan coba lagi atau hubungi administrator.");
        } catch (Exception $e) {
            $this->alertError("Gagal membuat Transfer Order: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transfer.create');
    }
}
