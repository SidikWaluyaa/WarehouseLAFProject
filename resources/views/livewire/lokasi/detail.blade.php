<div x-init="$dispatch('update-header-title', 'Detail Lokasi: {{ $lokasi->kode_lokasi }}')" class="flex-1 overflow-y-auto p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Kode atau Nama Barang..." type="text"/>
        </div>
    </template>

    <!-- Navigation Header -->
    <div class="flex items-center justify-between pb-2">
        <a href="{{ route('lokasi') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 hover:bg-white/10 text-on-surface font-semibold text-xs rounded-xl transition-all active:scale-95">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Kembali ke Manajemen Lokasi
        </a>
    </div>

    <!-- Location Header Card -->
    <section class="glass-card rounded-3xl p-6 relative overflow-hidden group border border-white/10 shadow-2xl">
        <div class="absolute -top-6 -right-6 material-symbols-outlined text-primary/5 text-[10rem] pointer-events-none select-none">place</div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $lokasi->tipe_lokasi === 'GUDANG' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-tertiary/10 text-tertiary border border-tertiary/20' }}">
                    {{ $lokasi->tipe_lokasi }}
                </span>
                <h2 class="text-2xl font-black text-on-surface mt-2">{{ $lokasi->nama_lokasi }}</h2>
                <p class="text-xs text-on-surface-variant/70 mt-1 font-mono">Kode Lokasi: {{ $lokasi->kode_lokasi }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-on-surface-variant">Status Keaktifan:</span>
                @if($lokasi->is_active)
                    <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[10px] font-bold uppercase tracking-wider">AKTIF</span>
                @else
                    <span class="px-3 py-1 bg-white/5 text-on-surface-variant/50 border border-white/5 rounded-full text-[10px] font-bold uppercase tracking-wider">NON-AKTIF</span>
                @endif
            </div>
        </div>
    </section>

    <!-- KPI Section -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Unique SKU -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">qr_code_2</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Jumlah SKU Unik</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($totalSkus) }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span>Jenis Produk Terdaftar</span>
            </div>
        </div>

        <!-- Card 2: Total Quantity Stored -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">inventory_2</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Total Unit Barang</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($totalQty) }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span>Kuantitas Fisik Terhitung</span>
            </div>
        </div>

        <!-- Card 3: Total Value -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">monetization_on</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Nilai Inventaris (HPP)</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ $totalValueFormatted }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span>Akumulasi Nilai Modal HPP</span>
            </div>
        </div>
    </section>

    <!-- Actions / Search for Mobile -->
    <section class="md:hidden flex flex-col gap-4">
        <div class="relative w-full">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Cari Kode atau Nama Barang..."/>
        </div>
    </section>

    <!-- Stored Products Data Table -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-sm font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">list_alt</span>
                Daftar Barang di Lokasi Ini
            </h3>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5">
                        <th wire:click="sortBy('mst_produk.kode_barang')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kode Barang
                                @if ($sortField === 'mst_produk.kode_barang')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('mst_produk.nama_barang')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Nama Barang
                                @if ($sortField === 'mst_produk.nama_barang')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('mst_produk.kategori')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kategori
                                @if ($sortField === 'mst_produk.kategori')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Varian</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-right">HPP Satuan</th>
                        <th wire:click="sortBy('trx_stok_lokasi.qty')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-right cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center justify-end gap-1">
                                Qty Stok
                                @if ($sortField === 'trx_stok_lokasi.qty')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-right">Total Nilai HPP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs">
                    @forelse ($items as $item)
                        @php
                            $totalValItem = $item->qty * $item->hpp;
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 font-mono text-primary/80 text-sm font-bold">{{ $item->kode_barang }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-on-surface">{{ $item->nama_barang }}</span>
                                    <span class="text-[10px] text-on-surface-variant/60">{{ $item->nama_produk }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $item->kategori }}</td>
                            <td class="px-6 py-4 text-on-surface-variant">{{ $item->variasi_barang }}</td>
                            <td class="px-6 py-4 text-right text-on-surface-variant">Rp{{ number_format($item->hpp, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black {{ $item->qty <= 5 ? 'text-error font-extrabold' : 'text-primary font-bold' }}">
                                    {{ number_format($item->qty) }} {{ $item->satuan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-primary font-semibold">
                                Rp{{ number_format($totalValItem, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada barang yang disimpan di lokasi ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $items->firstItem() ?? 0 }} s.d. {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($items->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                    @if ($page == $items->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($items->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>
</div>
