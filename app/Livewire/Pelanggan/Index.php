<?php

namespace App\Livewire\Pelanggan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pelanggan;
use Livewire\Attributes\Layout;
use Exception;
use App\Traits\HasAlerts;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    // Search & Sort state
    public $search = '';
    public $sortField = 'nama_pelanggan';
    public $sortDirection = 'asc';

    // Form modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $pelangganId = null;

    // Form fields
    public $nama_pelanggan = '';
    public $kontak = '';
    public $alamat = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'required|string|max:200',
            'kontak' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 200 karakter.',
            'kontak.max' => 'Kontak maksimal 100 karakter.',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->pelangganId = null;
        $this->isEdit = false;
        $this->nama_pelanggan = '';
        $this->kontak = '';
        $this->alamat = '';
    }

    public function create()
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $this->isEdit = false;
        $this->openModal();
    }

    public function store()
    {
        Gate::authorize('manage master');
        $validatedData = $this->validate();

        if ($this->isEdit) {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);
            $pelanggan->update($validatedData);
            $this->toastSuccess("Pelanggan '{$pelanggan->nama_pelanggan}' berhasil diperbarui.");
        } else {
            $pelanggan = Pelanggan::create($validatedData);
            $this->toastSuccess("Pelanggan '{$pelanggan->nama_pelanggan}' berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $pelanggan = Pelanggan::findOrFail($id);
        $this->pelangganId = $id;
        $this->isEdit = true;
        
        $this->nama_pelanggan = $pelanggan->nama_pelanggan;
        $this->kontak = $pelanggan->kontak;
        $this->alamat = $pelanggan->alamat;

        $this->openModal();
    }

    public function destroy(int $id): void
    {
        Gate::authorize('manage master');
        $pelanggan = Pelanggan::findOrFail($id);

        // Pre-check if pelanggan has transactions
        if ($pelanggan->barangKeluar()->exists()) {
            $this->alertError("Pelanggan '{$pelanggan->nama_pelanggan}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang keluar.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete-pelanggan',
            params: ['id' => $id],
            title: 'Hapus Pelanggan?',
            text: "Apakah Anda yakin ingin menghapus pelanggan '{$pelanggan->nama_pelanggan}'?"
        );
    }

    #[On('confirmed-delete-pelanggan')]
    public function confirmedDelete(array|int|null $params = null, ?int $id = null): void
    {
        Gate::authorize('manage master');
        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        try {
            if (!$id) {
                throw new Exception("ID pelanggan tidak valid.");
            }

            $pelanggan = Pelanggan::findOrFail($id);

            // Check if pelanggan has transactions
            if ($pelanggan->barangKeluar()->exists()) {
                throw new Exception("Pelanggan '{$pelanggan->nama_pelanggan}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang keluar.");
            }

            $pelanggan->delete();
            $this->toastSuccess("Pelanggan '{$pelanggan->nama_pelanggan}' berhasil dihapus.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    

    public function render()
    {
        $query = Pelanggan::query();

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_pelanggan', 'like', '%' . $this->search . '%')
                  ->orWhere('kontak', 'like', '%' . $this->search . '%')
                  ->orWhere('alamat', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        $pelanggans = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.pelanggan.index', [
            'pelanggans' => $pelanggans,
        ]);
    }
}
