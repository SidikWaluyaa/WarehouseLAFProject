<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\AuditLog;
use App\Models\BarangMasukDetail;
use App\Models\PoDetail;
use App\Models\PoHeader;
use App\Traits\HasAlerts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasAlerts;

    public PoHeader $poHeader;

    /** @var array<int|string, bool> po_detail_id => dicentang */
    public array $selected = [];

    /** @var array<int|string, int|string> po_detail_id => qty aktual yang akan diterima */
    public array $qtyTerima = [];

    // Modal Tutup Sisa
    public bool $isCloseModalOpen = false;
    public ?int $closingDetailId = null; // null = semua sisa dalam PO
    public string $alasanTutup = '';

    public function mount(PoHeader $poHeader): void
    {
        $this->poHeader = $poHeader;
        $this->resetReceiveForm();
    }

    protected function resetReceiveForm(): void
    {
        $this->selected = [];
        $this->qtyTerima = [];
        foreach ($this->poHeader->poDetails()->get() as $d) {
            $this->qtyTerima[$d->id] = (int) $d->sisa;
        }
    }

    protected function canReceive(): bool
    {
        return Auth::check() && Gate::any(['manage master', 'input masuk']);
    }

    public function selectAllOutstanding(): void
    {
        foreach ($this->poHeader->poDetails()->where('sisa', '>', 0)->pluck('id') as $id) {
            $this->selected[$id] = true;
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    /**
     * Arahkan ke form Barang Masuk yang hanya berisi item terpilih + qty aktual.
     */
    public function receiveSelected()
    {
        if (!$this->canReceive()) {
            $this->alertError('Anda tidak memiliki izin untuk menerima barang.');
            return;
        }

        $ids = collect($this->selected)->filter()->keys()->map(fn($k) => (int) $k)->all();
        if (empty($ids)) {
            $this->toastError('Centang minimal 1 item yang datang.');
            return;
        }

        $details = $this->poHeader->poDetails()->whereIn('id', $ids)->where('sisa', '>', 0)->get();
        $terima = [];
        foreach ($details as $d) {
            $qty = (int) ($this->qtyTerima[$d->id] ?? 0);
            if ($qty < 1) {
                $this->addError("qtyTerima.{$d->id}", 'Qty minimal 1.');
                return;
            }
            $terima[$d->id] = $qty;
        }

        if (empty($terima)) {
            $this->toastError('Item terpilih sudah tidak memiliki sisa.');
            return;
        }

        return $this->redirect(route('barang-masuk.create', [
            'po_id' => $this->poHeader->id,
            'terima' => $terima,
        ]), navigate: false);
    }

    public function openCloseModal(?int $detailId = null): void
    {
        if (!Gate::allows('manage po')) {
            $this->alertError('Anda tidak memiliki izin menutup sisa PO.');
            return;
        }
        $this->closingDetailId = $detailId;
        $this->alasanTutup = '';
        $this->resetValidation();
        $this->isCloseModalOpen = true;
    }

    public function cancelCloseModal(): void
    {
        $this->isCloseModalOpen = false;
        $this->closingDetailId = null;
        $this->alasanTutup = '';
    }

    /**
     * Tutup sisa PO (per item atau seluruh PO) karena supplier tidak akan mengirim lagi.
     */
    public function closeRemaining(): void
    {
        if (!Gate::allows('manage po')) {
            $this->alertError('Anda tidak memiliki izin menutup sisa PO.');
            return;
        }

        $this->validate(
            ['alasanTutup' => 'required|string|min:3|max:255'],
            ['alasanTutup.required' => 'Alasan penutupan wajib diisi.', 'alasanTutup.min' => 'Alasan minimal 3 karakter.']
        );

        $closed = DB::transaction(function () {
            $query = PoDetail::where('po_header_id', $this->poHeader->id)->where('sisa', '>', 0)->lockForUpdate();
            if ($this->closingDetailId) {
                $query->where('id', $this->closingDetailId);
            }
            $details = $query->get();
            $user = Auth::user();
            $log = [];

            foreach ($details as $d) {
                $before = $d->only(['sisa', 'qty_ditutup']);
                $qtyTutup = (int) $d->sisa;
                $d->qty_ditutup = (int) $d->qty_ditutup + $qtyTutup;
                $d->alasan_tutup = trim($this->alasanTutup);
                $d->ditutup_oleh = $user?->name ?? 'System';
                $d->ditutup_pada = now();
                $d->recalculateSisa();
                $d->save();
                $log[] = ['po_detail_id' => $d->id, 'qty_ditutup' => $qtyTutup, 'before' => $before];
            }

            if (!empty($log)) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'username' => $user?->name ?? 'System',
                    'action' => 'UPDATE',
                    'auditable_type' => PoHeader::class,
                    'auditable_id' => $this->poHeader->id,
                    'old_values' => null,
                    'new_values' => ['tutup_sisa' => $log, 'alasan' => trim($this->alasanTutup)],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return count($log);
        });

        $this->cancelCloseModal();
        $this->poHeader->refresh();
        $this->resetReceiveForm();

        $closed > 0
            ? $this->toastSuccess("Sisa PO pada {$closed} item berhasil ditutup.")
            : $this->toastError('Tidak ada sisa yang bisa ditutup.');
    }

    public function render()
    {
        $details = $this->poHeader->poDetails()->with('produk')->orderBy('id')->get();
        $this->poHeader->loadMissing('supplier');

        // Riwayat penerimaan (seluruh barang masuk yang terhubung ke item PO ini)
        $receipts = BarangMasukDetail::with(['barangMasukHeader', 'produk'])
            ->whereIn('po_detail_id', $details->pluck('id'))
            ->orderByDesc('id')
            ->get()
            ->groupBy('barang_masuk_header_id');

        $totalDipesan = (int) $details->sum('jumlah');
        $totalDiterima = (int) $details->sum('barang_masuk');
        $totalSisa = (int) $details->sum('sisa');
        $totalDitutup = (int) $details->sum('qty_ditutup');
        $nilaiSisa = (float) $details->sum(fn($d) => $d->sisa * (float) $d->hpp);
        $progress = $totalDipesan > 0 ? min(100, round(($totalDiterima / $totalDipesan) * 100)) : 0;

        if ($totalSisa === 0) {
            $statusPo = $totalDitutup > 0 ? 'ditutup' : 'selesai';
        } else {
            $statusPo = $totalDiterima > 0 ? 'sebagian' : 'terbuka';
        }

        return view('livewire.purchase-order.show', [
            'details' => $details,
            'receipts' => $receipts,
            'closures' => $details->filter(fn($d) => $d->ditutup_pada !== null)->sortByDesc('ditutup_pada'),
            'totalDipesan' => $totalDipesan,
            'totalDiterima' => $totalDiterima,
            'totalSisa' => $totalSisa,
            'totalDitutup' => $totalDitutup,
            'nilaiSisa' => $nilaiSisa,
            'progress' => $progress,
            'statusPo' => $statusPo,
            'canReceive' => $this->canReceive(),
            'canManage' => Gate::allows('manage po'),
        ]);
    }
}
