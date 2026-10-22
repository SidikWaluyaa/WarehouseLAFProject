<div x-init="$dispatch('update-header-title', 'Transfer Gudang')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">


    {{-- KPI Cards --}}
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary/10 text-6xl group-hover:scale-110 transition-transform duration-700">draft</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Draft</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ $statusCounts['DRAFT'] ?? 0 }}</h3>
        </div>
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-amber-500/20 transition-all duration-500">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-amber-500/10 text-6xl group-hover:scale-110 transition-transform duration-700">local_shipping</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Dalam Perjalanan</p>
            <h3 class="text-headline-lg font-bold text-amber-400">{{ $statusCounts['IN_TRANSIT'] ?? 0 }}</h3>
        </div>
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-green-500/20 transition-all duration-500">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-green-500/10 text-6xl group-hover:scale-110 transition-transform duration-700">inventory</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Diterima</p>
            <h3 class="text-headline-lg font-bold text-green-400">{{ $statusCounts['RECEIVED'] ?? 0 }}</h3>
        </div>
    </section>

    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex gap-3 items-center w-full sm:w-auto">
            <div class="relative flex-1 sm:w-80">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nomor transfer, keterangan..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 placeholder:text-on-surface-variant/30" />
            </div>
            <select wire:model.live="statusFilter" class="px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Semua Status</option>
                <option value="DRAFT">Draft</option>
                <option value="PICKING">Picking</option>
                <option value="IN_TRANSIT">In Transit</option>
                <option value="RECEIVED">Received</option>
                <option value="CLOSED">Closed</option>
                <option value="CANCELLED">Cancelled</option>
            </select>
        </div>
        <a href="{{ route('transfer.create') }}" class="btn-primary flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold whitespace-nowrap bg-primary text-on-primary hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-lg">add</span>
            Buat Transfer Order
        </a>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                        <th class="py-4 px-5">No. Transfer</th>
                        <th class="py-4 px-5">Tanggal</th>
                        <th class="py-4 px-5">Asal</th>
                        <th class="py-4 px-5">Tujuan</th>
                        <th class="py-4 px-5 text-center">Status</th>
                        <th class="py-4 px-5">Dibuat Oleh</th>
                        <th class="py-4 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($transfers as $t)
                        <tr class="hover:bg-white/5 transition-colors text-on-surface-variant">
                            <td class="py-4 px-5 font-bold text-on-surface font-mono text-xs">{{ $t->nomor_transfer }}</td>
                            <td class="py-4 px-5">{{ $t->tanggal->format('d/m/Y') }}</td>
                            <td class="py-4 px-5">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20 font-bold">{{ $t->lokasiAsal->kode_lokasi }}</span>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] bg-green-500/10 text-green-400 border border-green-500/20 font-bold">{{ $t->lokasiTujuan->kode_lokasi }}</span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <x-status-badge :status="$t->status" class="px-2.5 py-1 text-[10px]" />
                            </td>
                            <td class="py-4 px-5 text-sm">{{ $t->created_by }}</td>
                            <td class="py-4 px-5 text-center">
                                <a href="{{ route('transfer.detail', $t->id) }}" class="text-primary hover:underline text-xs font-bold">Detail &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8">
                                <x-empty-state icon="swap_horiz" title="Tidak Ada Transfer Order" description="Belum ada transaksi transfer antar gudang yang dicatat." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-white/5">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
