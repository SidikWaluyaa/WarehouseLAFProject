<?php

namespace App\Livewire\Transfer;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransferHeader;
use Livewire\Attributes\Layout;
use App\Traits\HasAlerts;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    public function mount()
    {
        if (session()->has('success_message')) {
            $this->toastSuccess(session('success_message'));
        }
    }

    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = TransferHeader::with(['lokasiAsal', 'lokasiTujuan']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nomor_transfer', 'like', '%' . $this->search . '%')
                  ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                  ->orWhere('created_by', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $transfers = $query->orderByRaw('created_at desc')->paginate(10);

        $counts = TransferHeader::selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['DRAFT', 'IN_TRANSIT', 'RECEIVED'])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusCounts = [
            'DRAFT' => $counts['DRAFT'] ?? 0,
            'IN_TRANSIT' => $counts['IN_TRANSIT'] ?? 0,
            'RECEIVED' => $counts['RECEIVED'] ?? 0,
        ];

        return view('livewire.transfer.index', [
            'transfers' => $transfers,
            'statusCounts' => $statusCounts,
        ]);
    }
}
