<?php

namespace App\Livewire\Analytics;

use App\Traits\HasAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AnalyticsProduk;
use App\Models\Produk;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class Index extends Component
{
    use HasAlerts;
    use WithPagination;

    public $search = '';
    
    // Modal & Edit state
    public bool $isOpen = false;
    public ?int $selectedAnalyticsId = null;
    public string $produkNama = '';
    public string $produkKode = '';
    public $leadtime_produksi = 14;
    public bool $is_manual_stock = false;
    public $minimal_stock = 0;
    public $safety_stock = 0;
    public $maximum_stock = 0;

    // Monthly sales fields
    public array $sales = [
        'jan' => 0, 'feb' => 0, 'mar' => 0, 'apr' => 0, 'mei' => 0, 'jun' => 0,
        'jul' => 0, 'ags' => 0, 'sep' => 0, 'okt' => 0, 'nov' => 0, 'des' => 0
    ];

    // Success/error messages
    
    

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit(int $analyticsId): void
    {
        $this->resetValidation();
        $analytics = AnalyticsProduk::with('produk')->findOrFail($analyticsId);
        
        $this->selectedAnalyticsId = $analyticsId;
        $this->produkNama = $analytics->produk->nama_barang;
        $this->produkKode = $analytics->produk->kode_barang;
        $this->leadtime_produksi = $analytics->leadtime_produksi;
        $this->is_manual_stock = (bool)$analytics->is_manual_stock;
        $this->minimal_stock = $analytics->minimal_stock;
        $this->safety_stock = $analytics->safety_stock;
        $this->maximum_stock = $analytics->maximum_stock;

        $this->sales = [
            'jan' => $analytics->penjualan_jan,
            'feb' => $analytics->penjualan_feb,
            'mar' => $analytics->penjualan_mar,
            'apr' => $analytics->penjualan_apr,
            'mei' => $analytics->penjualan_mei,
            'jun' => $analytics->penjualan_jun,
            'jul' => $analytics->penjualan_jul,
            'ags' => $analytics->penjualan_ags,
            'sep' => $analytics->penjualan_sep,
            'okt' => $analytics->penjualan_okt,
            'nov' => $analytics->penjualan_nov,
            'des' => $analytics->penjualan_des,
        ];

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->selectedAnalyticsId = null;
        $this->produkNama = '';
        $this->produkKode = '';
        $this->leadtime_produksi = 14;
        $this->is_manual_stock = false;
        $this->minimal_stock = 0;
        $this->safety_stock = 0;
        $this->maximum_stock = 0;
        $this->sales = [
            'jan' => 0, 'feb' => 0, 'mar' => 0, 'apr' => 0, 'mei' => 0, 'jun' => 0,
            'jul' => 0, 'ags' => 0, 'sep' => 0, 'okt' => 0, 'nov' => 0, 'des' => 0
        ];
    }

    public function syncFromDatabase()
    {
        if (!$this->selectedAnalyticsId) return;

        try {
            $analytics = AnalyticsProduk::findOrFail($this->selectedAnalyticsId);
            $actualSales = AnalyticsService::getActualSalesByMonth($analytics->produk_id);
            
            $this->sales = $actualSales;
            $this->toastSuccess("Data riwayat penjualan aktual untuk '{$this->produkNama}' berhasil ditarik dari database.");
        } catch (Exception $e) {
            $this->alertError("Gagal menarik data dari database: " . $e->getMessage());
        }
    }

    public function store(): void
    {
        $rules = [
            'leadtime_produksi' => 'required|integer|min:1|max:365',
            'sales.jan' => 'required|integer|min:0',
            'sales.feb' => 'required|integer|min:0',
            'sales.mar' => 'required|integer|min:0',
            'sales.apr' => 'required|integer|min:0',
            'sales.mei' => 'required|integer|min:0',
            'sales.jun' => 'required|integer|min:0',
            'sales.jul' => 'required|integer|min:0',
            'sales.ags' => 'required|integer|min:0',
            'sales.sep' => 'required|integer|min:0',
            'sales.okt' => 'required|integer|min:0',
            'sales.nov' => 'required|integer|min:0',
            'sales.des' => 'required|integer|min:0',
        ];

        if ($this->is_manual_stock) {
            $rules['minimal_stock'] = 'required|integer|min:0';
            $rules['safety_stock'] = 'required|integer|min:0';
            $rules['maximum_stock'] = 'required|integer|min:0';
        }

        $this->validate($rules, [
            'leadtime_produksi.required' => 'Leadtime harus diisi.',
            'leadtime_produksi.integer' => 'Leadtime harus berupa angka bulat.',
            'leadtime_produksi.min' => 'Leadtime minimal 1 hari.',
            'leadtime_produksi.max' => 'Leadtime maksimal 365 hari.',
            'sales.*.required' => 'Data penjualan harus diisi.',
            'sales.*.integer' => 'Data penjualan harus berupa angka.',
            'sales.*.min' => 'Data penjualan minimal 0.',
            'minimal_stock.required' => 'Min stock harus diisi jika menggunakan mode manual.',
            'minimal_stock.integer' => 'Min stock harus berupa angka.',
            'minimal_stock.min' => 'Min stock minimal 0.',
            'safety_stock.required' => 'Safety stock harus diisi jika menggunakan mode manual.',
            'safety_stock.integer' => 'Safety stock harus berupa angka.',
            'safety_stock.min' => 'Safety stock minimal 0.',
            'maximum_stock.required' => 'Max stock harus diisi jika menggunakan mode manual.',
            'maximum_stock.integer' => 'Max stock harus berupa angka.',
            'maximum_stock.min' => 'Max stock minimal 0.',
        ]);

        try {
            DB::transaction(function () {
                $analytics = AnalyticsProduk::findOrFail($this->selectedAnalyticsId);
                $analytics->leadtime_produksi = (int)$this->leadtime_produksi;
                $analytics->is_manual_stock = (bool)$this->is_manual_stock;

                if ($this->is_manual_stock) {
                    $analytics->minimal_stock = (int)$this->minimal_stock;
                    $analytics->safety_stock = (int)$this->safety_stock;
                    $analytics->maximum_stock = (int)$this->maximum_stock;
                }
                
                $analytics->penjualan_jan = (int)$this->sales['jan'];
                $analytics->penjualan_feb = (int)$this->sales['feb'];
                $analytics->penjualan_mar = (int)$this->sales['mar'];
                $analytics->penjualan_apr = (int)$this->sales['apr'];
                $analytics->penjualan_mei = (int)$this->sales['mei'];
                $analytics->penjualan_jun = (int)$this->sales['jun'];
                $analytics->penjualan_jul = (int)$this->sales['jul'];
                $analytics->penjualan_ags = (int)$this->sales['ags'];
                $analytics->penjualan_sep = (int)$this->sales['sep'];
                $analytics->penjualan_okt = (int)$this->sales['okt'];
                $analytics->penjualan_nov = (int)$this->sales['nov'];
                $analytics->penjualan_des = (int)$this->sales['des'];
                $analytics->save();

                // Recalculate this product's analytics (min, safety, max stock)
                AnalyticsService::recalculateProductAnalytics($analytics->produk_id);
            });

            $this->toastSuccess("Data analisis penjualan dan level stok untuk '{$this->produkNama}' berhasil diperbarui.");
            $this->closeModal();
        } catch (Exception $e) {
            $this->alertError("Gagal menyimpan perubahan: " . $e->getMessage());
        }
    }

    #[Computed]
    public function preview()
    {
        if (!$this->isOpen) {
            return [
                'totalSales' => 0,
                'rataRataHarian' => 0,
                'minimalStock' => 0,
                'safetyStock' => 0,
                'maximumStock' => 0
            ];
        }

        $totalSales = 0;
        foreach ($this->sales as $val) {
            $totalSales += (int)$val;
        }

        $rataRataHarian = $totalSales / 365;
        $leadtime = (int)$this->leadtime_produksi ?: 14;

        if ($this->is_manual_stock) {
            $minimalStock = (int)$this->minimal_stock;
            $safetyStock = (int)$this->safety_stock;
            $maximumStock = (int)$this->maximum_stock;
        } else {
            $minimalStock = (int)ceil($rataRataHarian * $leadtime);
            $safetyStock = (int)ceil($minimalStock * 1.5);
            $maximumStock = (int)ceil($safetyStock * 1.5);
        }

        return [
            'totalSales' => $totalSales,
            'rataRataHarian' => $rataRataHarian,
            'minimalStock' => $minimalStock,
            'safetyStock' => $safetyStock,
            'maximumStock' => $maximumStock
        ];
    }

    public function recalculateAll()
    {
        try {
            AnalyticsService::recalculateAll();
            $this->toastSuccess("Semua perhitungan level stok (Min, Safety, Max) berhasil diperbarui berdasarkan data penjualan saat ini.");
            $this->resetPage();
        } catch (Exception $e) {
            $this->alertError("Gagal melakukan kalkulasi ulang: " . $e->getMessage());
        }
    }

    

    public function render()
    {
        $query = AnalyticsProduk::with(['produk']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kategori', 'like', '%' . $this->search . '%')
                  ->orWhereHas('produk', function ($pQuery) {
                      $pQuery->where('nama_barang', 'like', '%' . $this->search . '%')
                             ->orWhere('kode_barang', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $colKategori = 'kategori';
        $colId = 'id';

        $analyticsData = $query->orderBy($colKategori)
            ->orderBy($colId)
            ->paginate(10);

        return view('livewire.analytics.index', [
            'analyticsList' => $analyticsData,
        ]);
    }
}
