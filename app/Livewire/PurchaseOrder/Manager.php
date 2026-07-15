<?php

namespace App\Livewire\PurchaseOrder;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PoHeader;
use App\Models\PoDetail;
use App\Models\Supplier;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Exports\PoHppRecapExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Manager extends Component
{
    use HasAlerts;
    use WithPagination;

    // Tab control: 'po_list', 'po_tracking', 'po_hpp_recap'
    public $activeTab = 'po_list';

    // Search query
    public $search = '';

    // Modal state
    public $isOpen = false;

    // PO Form fields
    public $tanggal = '';
    public $supplier_id = '';
    public $artikel = '';
    
    // Dynamic PO Items array: [['produk_id' => '', 'jumlah' => '', 'hpp' => '']]
    public $poItems = [];

    // Dropdowns
    public $suppliersList = [];
    public $productsList = [];

    // Alerts
    
    

    protected $rules = [
        'tanggal' => 'required|date',
        'supplier_id' => 'required|exists:mst_supplier,id',
        'artikel' => 'nullable|string|max:200',
        'poItems' => 'required|array|min:1',
        'poItems.*.produk_id' => 'required|exists:mst_produk,id',
        'poItems.*.jumlah' => 'required|integer|min:1',
        'poItems.*.hpp' => 'required|numeric|min:0',
    ];

    protected $validationAttributes = [
        'poItems.*.produk_id' => 'Produk',
        'poItems.*.jumlah' => 'Kuantitas',
        'poItems.*.hpp' => 'HPP',
    ];

    public function mount()
    {
        $this->tanggal = date('Y-m-d');
        $this->suppliersList = Supplier::orderBy('nama_supplier')->get();
        $this->productsList = Produk::orderBy('nama_barang')->get();
        $this->addPoItemRow(); // start with one blank item row
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function addPoItemRow()
    {
        $this->poItems[] = [
            'produk_id' => '',
            'jumlah' => '',
            'hpp' => '',
        ];
    }

    public function removePoItemRow(int $index): void
    {
        unset($this->poItems[$index]);
        $this->poItems = array_values($this->poItems); // rekey array
        if (empty($this->poItems)) {
            $this->addPoItemRow();
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->tanggal = date('Y-m-d');
        $this->supplier_id = '';
        $this->artikel = '';
        $this->poItems = [];
        $this->addPoItemRow();
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Calculate total quantity of items for po_header 'jumlah' field
                $totalJumlah = 0;
                foreach ($this->poItems as $item) {
                    $totalJumlah += (int)$item['jumlah'];
                }

                // 1. Create PoHeader
                $poHeader = PoHeader::create([
                    'tanggal' => $this->tanggal,
                    'supplier_id' => $this->supplier_id,
                    'artikel' => $this->artikel ?: null,
                    'jumlah' => $totalJumlah,
                ]);

                // 2. Create PoDetails
                foreach ($this->poItems as $item) {
                    PoDetail::create([
                        'po_header_id' => $poHeader->id,
                        'produk_id' => $item['produk_id'],
                        'jumlah' => $item['jumlah'],
                        'barang_masuk' => 0,
                        'sisa' => $item['jumlah'], // initially sisa equals jumlah
                        'hpp' => $item['hpp'],
                        'total_hpp' => (int)$item['jumlah'] * (float)$item['hpp'],
                    ]);
                }
            });

            $this->toastSuccess("Purchase Order baru berhasil diterbitkan.");
            $this->closeModal();
        } catch (Exception $e) {
            $this->alertError("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    

    public function render()
    {
        $poHeadersQuery = PoHeader::with(['supplier', 'poDetails.produk']);
        $poDetailsQuery = PoDetail::with(['poHeader.supplier', 'produk']);

        // Apply Search Filter on respective queries
        if (!empty($this->search)) {
            $poHeadersQuery->where(function ($q) {
                $q->where('artikel', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function ($sQ) {
                      $sQ->where('nama_supplier', 'like', '%' . $this->search . '%');
                  });
            });

            $poDetailsQuery->where(function ($q) {
                $q->whereHas('poHeader.supplier', function ($sQ) {
                    $sQ->where('nama_supplier', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('produk', function ($pQ) {
                    $pQ->where('nama_barang', 'like', '%' . $this->search . '%')
                       ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('poHeader', function ($hQ) {
                    $hQ->where('artikel', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Handle tabs paging separately
        if ($this->activeTab === 'po_list') {
            $data = $poHeadersQuery->latest('tanggal')->latest('id')->paginate(10);
        } elseif ($this->activeTab === 'po_tracking') {
            $data = $poDetailsQuery->latest('id')->paginate(10);
        } else {
            // Recalculate rekap HPP sisa PO
            $data = $poDetailsQuery->latest('id')->paginate(10);
        }

        return view('livewire.purchase-order.manager', [
            'dataList' => $data,
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(new PoHppRecapExport, 'po_hpp_recap_' . date('Ymd_His') . '.xlsx');
    }
}
