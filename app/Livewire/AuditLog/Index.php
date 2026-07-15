<?php

namespace App\Livewire\AuditLog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $actionFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'actionFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'actionFilter']);
        $this->resetPage();
    }

    public function render()
    {
        // Check authorization
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || (!$user->hasPermissionTo('manage master') && !$user->hasPermissionTo('manage system'))) {
            abort(403, 'Unauthorized action.');
        }

        $query = AuditLog::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhere('auditable_type', 'like', '%' . $this->search . '%')
                  ->orWhere('auditable_id', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->actionFilter)) {
            $query->where('action', $this->actionFilter);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.audit-log.index', [
            'logs' => $logs,
        ]);
    }
}
