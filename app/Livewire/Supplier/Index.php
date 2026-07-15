<?php

namespace App\Livewire\Supplier;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
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
    public $sortField = 'nama_supplier';
    public $sortDirection = 'asc';

    // Form modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $supplierId = null;

    // Form fields
    public $nama_supplier = '';
    public $kontak = '';
    public $alamat = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function rules(): array
    {
        return [
            'nama_supplier' => 'required|string|max:200',
            'kontak' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
            'nama_supplier.max' => 'Nama supplier maksimal 200 karakter.',
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
        $this->supplierId = null;
        $this->isEdit = false;
        $this->nama_supplier = '';
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
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update($validatedData);
            $this->toastSuccess("Supplier '{$supplier->nama_supplier}' berhasil diperbarui.");
        } else {
            $supplier = Supplier::create($validatedData);
            $this->toastSuccess("Supplier '{$supplier->nama_supplier}' berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $id;
        $this->isEdit = true;
        
        $this->nama_supplier = $supplier->nama_supplier;
        $this->kontak = $supplier->kontak;
        $this->alamat = $supplier->alamat;

        $this->openModal();
    }

    public function destroy(int $id): void
    {
        Gate::authorize('manage master');
        $supplier = Supplier::findOrFail($id);

        // Pre-check if supplier has transactions or POs
        if ($supplier->barangMasuk()->exists()) {
            $this->alertError("Supplier '{$supplier->nama_supplier}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang masuk.");
            return;
        }

        if ($supplier->poHeaders()->exists()) {
            $this->alertError("Supplier '{$supplier->nama_supplier}' tidak dapat dihapus karena sudah terikat ke Purchase Order.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete-supplier',
            params: ['id' => $id],
            title: 'Hapus Supplier?',
            text: "Apakah Anda yakin ingin menghapus supplier '{$supplier->nama_supplier}'?"
        );
    }

    #[On('confirmed-delete-supplier')]
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
                throw new Exception("ID supplier tidak valid.");
            }

            $supplier = Supplier::findOrFail($id);

            if ($supplier->barangMasuk()->exists()) {
                throw new Exception("Supplier '{$supplier->nama_supplier}' tidak dapat dihapus karena sudah memiliki riwayat transaksi barang masuk.");
            }

            if ($supplier->poHeaders()->exists()) {
                throw new Exception("Supplier '{$supplier->nama_supplier}' tidak dapat dihapus karena sudah terikat ke Purchase Order.");
            }

            $supplier->delete();
            $this->toastSuccess("Supplier '{$supplier->nama_supplier}' berhasil dihapus.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    

    public function render()
    {
        $query = Supplier::query();

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_supplier', 'like', '%' . $this->search . '%')
                  ->orWhere('kontak', 'like', '%' . $this->search . '%')
                  ->orWhere('alamat', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        $suppliers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.supplier.index', [
            'suppliers' => $suppliers,
        ]);
    }
}
