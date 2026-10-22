<?php

namespace App\Livewire\Pengkodean;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AturanKode;
use App\Models\Produk;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Traits\HasAlerts;
use Exception;

/**
 * @property-read string $modalPreview
 */
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use HasAlerts;

    public $search = '';
    public $sortField = 'kategori';
    public $sortDirection = 'asc';
    public int $perPage = 10;

    // Form Modal state
    public bool $isOpen = false;
    public bool $isEdit = false;
    public ?int $aturanId = null;

    // Fields
    public $kategori = '';
    public $prefix = '';
    public bool $include_category_prefix = true;
    public bool $include_product_initials = true;
    public bool $include_variant_code = true;
    public $increment_digits = 3;
    public string $format_tipe = 'INCREMENT'; // INCREMENT, PRD_STYLE

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $rules = [
        'kategori' => 'nullable|string|max:100',
        'prefix' => 'nullable|string|max:50',
        'include_category_prefix' => 'required|boolean',
        'include_product_initials' => 'required|boolean',
        'include_variant_code' => 'required|boolean',
        'increment_digits' => 'required|integer|min:1|max:10',
        'format_tipe' => 'required|string|in:INCREMENT,PRD_STYLE',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
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
        $this->aturanId = null;
        $this->isEdit = false;
        $this->kategori = '';
        $this->prefix = '';
        $this->include_category_prefix = true;
        $this->include_product_initials = true;
        $this->include_variant_code = true;
        $this->increment_digits = 3;
        $this->format_tipe = 'INCREMENT';
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
        $rules = $this->rules;
        
        $kat = !empty($this->kategori) ? strtoupper(trim($this->kategori)) : null;
        
        if ($this->isEdit) {
            $rules['kategori'] = 'nullable|string|max:100|unique:mst_aturan_kode,kategori,' . $this->aturanId;
        } else {
            $rules['kategori'] = 'nullable|string|max:100|unique:mst_aturan_kode,kategori';
        }

        $validatedData = $this->validate($rules);
        $validatedData['kategori'] = $kat;
        if (!empty($validatedData['prefix'])) {
            $validatedData['prefix'] = strtoupper(trim($validatedData['prefix']));
        }

        if ($this->isEdit) {
            $aturan = AturanKode::findOrFail($this->aturanId);
            
            // Prevent changing category of seeded global fallback rule
            if (is_null($aturan->kategori) && !is_null($validatedData['kategori'])) {
                $this->alertError("Aturan Default Global tidak boleh diubah kategori spesifiknya.");
                return;
            }

            $aturan->update($validatedData);
            $this->toastSuccess("Aturan pengkodean berhasil diperbarui.");
        } else {
            AturanKode::create($validatedData);
            $this->toastSuccess("Aturan pengkodean baru berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function edit(int $id): void
    {
        Gate::authorize('manage master');
        $this->resetForm();
        $aturan = AturanKode::findOrFail($id);
        $this->aturanId = $id;
        $this->isEdit = true;
        
        $this->kategori = $aturan->kategori;
        $this->prefix = $aturan->prefix;
        $this->include_category_prefix = (bool)$aturan->include_category_prefix;
        $this->include_product_initials = (bool)$aturan->include_product_initials;
        $this->include_variant_code = (bool)$aturan->include_variant_code;
        $this->increment_digits = $aturan->increment_digits;
        $this->format_tipe = $aturan->format_tipe ?? 'INCREMENT';

        $this->openModal();
    }

    public function destroy(int $id): void
    {
        Gate::authorize('manage master');
        $aturan = AturanKode::findOrFail($id);

        // Prevent deletion of the default global rule
        if (is_null($aturan->kategori)) {
            $this->alertError("Aturan Default Global tidak boleh dihapus.");
            return;
        }

        $this->alertConfirm(
            action: 'confirmed-delete-rule',
            params: ['id' => $id],
            title: 'Hapus Aturan?',
            text: "Apakah Anda yakin ingin menghapus aturan pengkodean untuk kategori '{$aturan->kategori}'? Sistem akan menggunakan aturan global sebagai gantinya."
        );
    }

    #[On('confirmed-delete-rule')]
    public function confirmedDeleteRule(array|int|null $params = null, ?int $id = null): void
    {
        Gate::authorize('manage master');
        if (is_array($params)) {
            $id = $params['id'] ?? $id;
        } elseif (is_numeric($params)) {
            $id = (int)$params;
        }

        try {
            if (!$id) {
                throw new Exception("ID aturan tidak valid.");
            }

            $aturan = AturanKode::findOrFail($id);

            if (is_null($aturan->kategori)) {
                throw new Exception("Aturan Default Global tidak boleh dihapus.");
            }

            $aturan->delete();
            $this->toastSuccess("Aturan pengkodean berhasil dihapus.");
        } catch (Exception $e) {
            $this->alertError($e->getMessage());
        }
    }

    public function getModalPreviewProperty(): string
    {
        if ($this->format_tipe === 'PRD_STYLE') {
            $p = !empty($this->prefix) ? strtoupper(trim($this->prefix)) : 'SandalSlide';
            return "{$p}-Gorka-FullBlack-36";
        }

        $parts = [];
        if (!empty($this->prefix)) {
            $parts[] = strtoupper(trim($this->prefix));
        } else {
            $parts[] = 'LAF';
        }

        if ($this->include_category_prefix) {
            $parts[] = !empty($this->kategori) ? substr(preg_replace('/[^A-Za-z0-9]/', '', strtoupper($this->kategori)), 0, 3) : 'CAT';
        }
        if ($this->include_product_initials) {
            $parts[] = 'PRO';
        }
        if ($this->include_variant_code) {
            $parts[] = 'VAR';
        }

        $digits = max(1, min(10, (int)$this->increment_digits));
        $parts[] = str_pad('1', $digits, '0', STR_PAD_LEFT);

        return implode('-', $parts);
    }

    public function render()
    {
        $query = AturanKode::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kategori', 'like', '%' . $this->search . '%')
                  ->orWhere('prefix', 'like', '%' . $this->search . '%');
            });
        }

        // Sorting by category treats null values special (first or last)
        $rules = $query->orderByRaw('CASE WHEN kategori IS NULL THEN 0 ELSE 1 END')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalRules = AturanKode::count();
        $prdCount = AturanKode::where('format_tipe', 'PRD_STYLE')->count();
        $standardCount = AturanKode::where(function ($q) {
            $q->where('format_tipe', 'INCREMENT')
              ->orWhereNull('format_tipe');
        })->count();

        return view('livewire.pengkodean.index', [
            'rules' => $rules,
            'totalRules' => $totalRules,
            'prdCount' => $prdCount,
            'standardCount' => $standardCount,
            'modalPreview' => $this->getModalPreviewProperty(),
        ]);
    }
}
