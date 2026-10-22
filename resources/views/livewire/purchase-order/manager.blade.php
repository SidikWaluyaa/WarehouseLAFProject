<div x-init="$dispatch('update-header-title', 'Purchase Order (PO)')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari vendor, artikel, atau produk..." type="text"/>
        </div>
    </template>

    <!-- Tab Bar -->
    <div class="glass-card rounded-2xl p-1.5 mb-6 border border-white/5 flex flex-wrap gap-2">
        <button wire:click="switchTab('po_list')" 
                class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'po_list' ? 'bg-primary text-on-primary-fixed shadow-md shadow-primary/20 font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            <span class="material-symbols-outlined text-base">format_list_bulleted</span>
            <span>Daftar &amp; Buat PO (PO A)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'po_list' ? 'bg-black/30 text-on-primary-fixed' : 'bg-white/10 text-on-surface-variant' }}">
                {{ $stats['total_po'] ?? 0 }}
            </span>
        </button>
        <button wire:click="switchTab('po_tracking')" 
                class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'po_tracking' ? 'bg-primary text-on-primary-fixed shadow-md shadow-primary/20 font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            <span class="material-symbols-outlined text-base">radar</span>
            <span>Tracking Sisa PO (PO B)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'po_tracking' ? 'bg-black/30 text-on-primary-fixed' : 'bg-white/10 text-on-surface-variant' }}">
                {{ $stats['outstanding_items'] ?? 0 }}
            </span>
        </button>
        <button wire:click="switchTab('po_hpp_recap')" 
                class="flex-1 min-w-[200px] py-2.5 px-4 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'po_hpp_recap' ? 'bg-primary text-on-primary-fixed shadow-md shadow-primary/20 font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            <span class="material-symbols-outlined text-base">payments</span>
            <span>Rekap HPP Sisa PO (PO C)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $activeTab === 'po_hpp_recap' ? 'bg-black/30 text-on-primary-fixed' : 'bg-white/10 text-on-surface-variant' }}">
                {{ $stats['total_items'] ?? 0 }}
            </span>
        </button>
    </div>

    @if ($activeTab === 'po_tracking')
        <!-- 4 KPI Summary Cards for Tracking PO -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Card 1: Total SKU -->
            <div class="glass-card rounded-2xl p-4 border border-white/10 flex items-center gap-3.5 hover:border-primary/30 transition-all">
                <div class="w-11 h-11 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">inventory_2</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant/60">Total SKU PO</p>
                    <p class="text-base sm:text-lg font-black text-on-surface">{{ number_format($stats['total_items'] ?? 0) }} <span class="text-xs font-normal text-on-surface-variant">Item</span></p>
                </div>
            </div>

            <!-- Card 2: Item Belum Datang -->
            <div class="glass-card rounded-2xl p-4 border border-white/10 flex items-center gap-3.5 hover:border-amber-500/30 transition-all">
                <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                    <span class="material-symbols-outlined text-xl">hourglass_top</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant/60">Belum Lengkap</p>
                    <p class="text-base sm:text-lg font-black text-amber-400">{{ number_format($stats['outstanding_items'] ?? 0) }} <span class="text-xs font-semibold text-amber-400/70">({{ number_format($stats['total_sisa_qty'] ?? 0) }} unit)</span></p>
                </div>
            </div>

            <!-- Card 3: Selisih & Ditutup -->
            <div class="glass-card rounded-2xl p-4 border border-white/10 flex items-center gap-3.5 hover:border-sky-500/30 transition-all">
                <div class="w-11 h-11 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 shrink-0">
                    <span class="material-symbols-outlined text-xl">rule</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant/60">Selisih &amp; Ditutup</p>
                    <p class="text-base sm:text-lg font-black text-sky-400">{{ number_format($stats['variance_count'] ?? 0) }} <span class="text-xs font-normal text-on-surface-variant">Item</span></p>
                </div>
            </div>

            <!-- Card 4: Nilai HPP Sisa -->
            <div class="glass-card rounded-2xl p-4 border border-white/10 flex items-center gap-3.5 hover:border-primary/40 transition-all">
                <div class="w-11 h-11 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase font-bold tracking-wider text-on-surface-variant/60">Nilai HPP Sisa</p>
                    <p class="text-base sm:text-lg font-black text-primary truncate">Rp{{ number_format($stats['nilai_sisa_hpp'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Controls Row & Actions -->
    <section class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4 {{ $activeTab === 'po_tracking' ? 'hidden' : '' }}">
        <!-- Mobile Search Bar (Only visible on screens < md) -->
        <div class="md:hidden relative w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Cari vendor, artikel, atau produk..." type="text"/>
        </div>

        <div class="flex-1"></div>

        <div class="flex flex-col sm:flex-row gap-3">
            @if ($activeTab === 'po_hpp_recap')
                <button wire:click="exportExcel" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/5 border border-white/10 text-on-surface rounded-xl font-bold text-xs shadow-md hover:bg-white/10 transition-all active:scale-95 duration-200">
                    <span class="material-symbols-outlined text-base">download</span>
                    Ekspor Rekap HPP
                </button>
                <a href="{{ route('purchase-order.rekap.print', ['tab' => 'po_hpp_recap', 'search' => $search]) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/5 border border-white/10 text-on-surface rounded-xl font-bold text-xs shadow-md hover:bg-white/10 transition-all active:scale-95 duration-200">
                    <span class="material-symbols-outlined text-base text-primary">print</span>
                    Cetak PDF Rekap
                </a>
            @endif
            @if ($activeTab === 'po_list')
                @can('manage po')
                    <a href="{{ route('purchase-order.create') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        <span class="material-symbols-outlined">add</span>
                        Buat Purchase Order
                    </a>
                @endcan
            @endif
        </div>
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <!-- Tab 1: Daftar PO (PO A) -->
        @if ($activeTab === 'po_list')
            @php $poIdsOnPage = $dataList->pluck('id')->values(); @endphp
            <div x-data="{
                    open: {{ $highlightId ? json_encode([$highlightId => true]) : '{}' }},
                    ids: @js($poIdsOnPage),
                    toggle(id) { this.open[id] = !this.open[id] },
                    get allOpen() { return this.ids.length > 0 && this.ids.every(i => this.open[i]) },
                    setAll(state) { this.ids.forEach(i => this.open[i] = state) }
                 }">
            <div class="px-6 pt-5 flex items-center justify-between gap-3">
                <p class="text-[11px] text-on-surface-variant/70">Klik baris untuk melihat rincian item pesanan.</p>
                @if ($poIdsOnPage->isNotEmpty())
                    <button type="button" @click="setAll(!allOpen)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl text-[11px] font-semibold text-on-surface hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-sm transition-transform duration-300" :class="allOpen ? 'rotate-180' : ''">unfold_more</span>
                        <span x-text="allOpen ? 'Tutup Semua' : 'Buka Semua'">Buka Semua</span>
                    </button>
                @endif
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="pl-6 pr-2 py-5 w-8"></th>
                            <th class="px-4 py-5">Tanggal PO</th>
                            <th class="px-4 py-5">No. PO</th>
                            <th class="px-4 py-5">Supplier / Vendor</th>
                            <th class="px-4 py-5">Artikel</th>
                            <th class="px-4 py-5 text-center">Total Item</th>
                            <th class="px-4 py-5">Ringkasan Item</th>
                            <th class="px-6 py-5 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-on-surface-variant">
                        @forelse ($dataList as $po)
                            @php
                                $isNew = $highlightId && $po->id === $highlightId;
                                $dipesan = (int) $po->poDetails->sum('jumlah');
                                $diterima = (int) $po->poDetails->sum('barang_masuk');
                                $sisaPo = (int) $po->poDetails->sum('sisa');
                                $pct = $dipesan > 0 ? min(100, round($diterima / $dipesan * 100)) : 0;
                            @endphp
                            <tr wire:key="po-row-{{ $po->id }}"
                                @click="if (!$event.target.closest('a,button')) toggle({{ $po->id }})"
                                class="border-t border-white/5 cursor-pointer hover:bg-white/5 transition-colors {{ $isNew ? 'bg-primary/10 ring-1 ring-inset ring-primary/40' : '' }}"
                                :class="open[{{ $po->id }}] ? 'bg-white/[0.03]' : ''">
                                <td class="pl-6 pr-2 py-4">
                                    <button type="button" @click.stop="toggle({{ $po->id }})" :aria-expanded="!!open[{{ $po->id }}]"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center bg-white/5 border border-white/10 hover:border-primary/40 hover:text-primary transition-all"
                                            title="Tampilkan rincian item">
                                        <span class="material-symbols-outlined text-base transition-transform duration-300" :class="open[{{ $po->id }}] ? 'rotate-180 text-primary' : ''">expand_more</span>
                                    </button>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap font-medium text-on-surface">
                                    {{ $po->tanggal->format('d/m/Y') }}
                                    @if ($isNew)
                                        <span class="ml-1 px-1.5 py-0.5 rounded-md bg-primary text-on-primary-fixed text-[9px] font-black uppercase animate-pulse">Baru</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap font-mono font-bold text-amber-400">
                                    <a href="{{ route('purchase-order.show', $po->id) }}" class="hover:underline hover:text-primary transition-colors" title="Lihat detail PO">
                                        {{ $po->nomor_po ?? 'PO #'.$po->id }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-on-surface font-semibold">{{ $po->supplier->nama_supplier }}</td>
                                <td class="px-4 py-4 font-mono text-primary/80 font-bold">{{ $po->artikel ?? '-' }}</td>
                                <td class="px-4 py-4 text-center font-black text-on-surface text-sm">{{ number_format($po->jumlah) }}</td>
                                <td class="px-4 py-4 min-w-[190px]">
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/5 border border-white/10 font-bold text-on-surface">
                                            <span class="material-symbols-outlined text-[13px] text-primary">inventory_2</span>{{ $po->poDetails->count() }} SKU
                                        </span>
                                        <span class="font-mono text-on-surface-variant">{{ number_format($dipesan) }} unit</span>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-white/5 overflow-hidden">
                                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-green-400' : 'bg-gradient-to-r from-primary-container to-[#ff9f00]' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-mono font-bold {{ $pct >= 100 ? 'text-green-400' : 'text-primary' }}">{{ $pct }}%</span>
                                    </div>
                                    <p class="text-[9px] text-on-surface-variant/60 mt-0.5">Diterima {{ number_format($diterima) }} &bull; Sisa {{ number_format($sisaPo) }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('purchase-order.show', $po->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 rounded-xl font-bold text-[10px] transition-all" title="Lihat detail">
                                                <span class="material-symbols-outlined text-xs">visibility</span>
                                                Detail
                                            </a>
                                            <a href="{{ route('purchase-order.print', $po->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1.5 bg-white/5 border border-white/10 text-primary hover:bg-primary/10 hover:border-primary/30 rounded-xl font-bold text-[10px] transition-all" title="Cetak PO / PDF">
                                                <span class="material-symbols-outlined text-xs">print</span>
                                            </a>
                                        </div>
                                        @if ($po->poDetails->sum('sisa') > 0)
                                            <a href="{{ route('purchase-order.show', $po->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20 rounded-xl font-bold text-[10px] transition-all shadow-sm">
                                                <span class="material-symbols-outlined text-xs">call_received</span>
                                                Terima Barang
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white/5 border border-white/10 text-on-surface-variant/40 rounded-xl text-[10px] font-semibold">
                                                <span class="material-symbols-outlined text-xs">task_alt</span>
                                                Selesai
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Baris rincian (collapsible) --}}
                            <tr wire:key="po-row-detail-{{ $po->id }}" x-show="open[{{ $po->id }}]" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                <td colspan="8" class="px-6 pb-5 pt-1 bg-white/[0.02]">
                                    <div class="ml-9 border border-white/5 rounded-2xl overflow-hidden">
                                        <div class="max-h-72 overflow-y-auto custom-scrollbar">
                                            <table class="w-full text-left text-[11px]">
                                                <thead class="sticky top-0 bg-neutral-900/95 backdrop-blur">
                                                    <tr class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant/60">
                                                        <th class="px-4 py-2.5">Produk</th>
                                                        <th class="px-4 py-2.5">SKU</th>
                                                        <th class="px-4 py-2.5 text-center">Dipesan</th>
                                                        <th class="px-4 py-2.5 text-center">Diterima</th>
                                                        <th class="px-4 py-2.5 text-center">Sisa</th>
                                                        <th class="px-4 py-2.5 text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-white/5">
                                                    @foreach ($po->poDetails as $detail)
                                                        @php
                                                            [$stLabel, $stClass] = [
                                                                'belum'    => ['Belum Datang', 'bg-white/5 border-white/10 text-on-surface-variant'],
                                                                'sebagian' => ['Sebagian', 'bg-amber-500/10 border-amber-500/20 text-amber-400'],
                                                                'selesai'  => ['Lengkap', 'bg-green-500/10 border-green-500/20 text-green-400'],
                                                                'lebih'    => ['Lebih Kirim', 'bg-sky-500/10 border-sky-500/20 text-sky-400'],
                                                                'ditutup'  => ['Ditutup', 'bg-white/5 border-white/10 text-on-surface-variant/60'],
                                                            ][$detail->statusPenerimaan()];
                                                        @endphp
                                                        <tr class="hover:bg-white/5 transition-colors">
                                                            <td class="px-4 py-2 font-semibold text-on-surface">{{ $detail->produk?->nama_barang ?? '-' }}</td>
                                                            <td class="px-4 py-2 font-mono text-primary/80">{{ $detail->produk?->kode_barang }}</td>
                                                            <td class="px-4 py-2 text-center font-mono">{{ number_format($detail->jumlah) }}</td>
                                                            <td class="px-4 py-2 text-center font-mono text-green-400">{{ number_format($detail->barang_masuk) }}</td>
                                                            <td class="px-4 py-2 text-center font-mono font-bold {{ $detail->sisa > 0 ? 'text-primary' : 'text-on-surface-variant/30' }}">{{ number_format($detail->sisa) }}</td>
                                                            <td class="px-4 py-2 text-center">
                                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border uppercase tracking-wider whitespace-nowrap {{ $stClass }}">{{ $stLabel }}</span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada data Purchase Order ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>

        <!-- Tab 2: Tracking Sisa PO (PO B) -->
        @elseif ($activeTab === 'po_tracking')
            <!-- Filter Toolbar & Action Row inside Card Header -->
            <div class="px-6 py-4 border-b border-white/5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-surface-container-low/40">
                <!-- Quick Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 p-1 bg-surface-container-low rounded-2xl border border-white/5">
                    <button type="button" wire:click="setTrackingFilter('all')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $trackingFilter === 'all' ? 'bg-primary text-on-primary-fixed shadow-sm font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                        Semua ({{ $stats['total_items'] ?? 0 }})
                    </button>
                    <button type="button" wire:click="setTrackingFilter('outstanding')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $trackingFilter === 'outstanding' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                        Belum Lengkap ({{ $stats['outstanding_items'] ?? 0 }})
                    </button>
                    <button type="button" wire:click="setTrackingFilter('variance')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $trackingFilter === 'variance' ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                        Ada Selisih ({{ $stats['variance_count'] ?? 0 }})
                    </button>
                    <button type="button" wire:click="setTrackingFilter('closed')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $trackingFilter === 'closed' ? 'bg-white/20 text-on-surface border border-white/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                        Ditutup ({{ $stats['closed_count'] ?? 0 }})
                    </button>
                </div>

                <!-- Right Action: Cetak PDF Rekap -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('purchase-order.rekap.print', ['tab' => 'po_tracking', 'search' => $search, 'filter' => $trackingFilter]) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/5 border border-white/10 hover:border-primary/40 hover:bg-white/10 text-on-surface rounded-xl font-bold text-xs shadow-md transition-all active:scale-95 duration-200">
                        <span class="material-symbols-outlined text-base text-primary">print</span>
                        <span>Cetak PDF Rekap</span>
                    </a>
                </div>
            </div>

            <!-- Tracking Data Table -->
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="px-5 py-4">Tgl &amp; No. PO</th>
                            <th class="px-5 py-4">Supplier / Vendor</th>
                            <th class="px-5 py-4">Produk &amp; SKU</th>
                            <th class="px-5 py-4 text-center min-w-[200px]">Progres Kuantitas</th>
                            <th class="px-5 py-4 text-center">Selisih</th>
                            <th class="px-5 py-4 text-center">Status</th>
                            <th class="px-5 py-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                        @forelse ($dataList as $row)
                            @php
                                $dipesan = (int) $row->jumlah;
                                $diterima = (int) $row->barang_masuk;
                                $sisa = (int) $row->sisa;
                                $ditutup = (int) ($row->qty_ditutup ?? 0);
                                $pct = $dipesan > 0 ? min(100, round(($diterima / $dipesan) * 100)) : 0;
                                $st = $row->statusPenerimaan();
                                $sel = (int) ($row->total_selisih ?? 0);
                            @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-mono text-xs font-bold text-amber-400">
                                        <a href="{{ route('purchase-order.show', $row->po_header_id) }}" class="hover:underline hover:text-primary transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">receipt_long</span>
                                            {{ $row->poHeader?->nomor_po ?? 'PO #'.$row->po_header_id }}
                                        </a>
                                    </div>
                                    <div class="text-[10px] text-on-surface-variant/60 mt-0.5">
                                        {{ $row->poHeader?->tanggal ? \Carbon\Carbon::parse($row->poHeader->tanggal)->format('d/m/Y') : '-' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-on-surface">{{ $row->poHeader?->supplier?->nama_supplier ?? '-' }}</div>
                                    @if ($row->poHeader?->artikel)
                                        <div class="text-[10px] font-mono text-primary/80 font-bold mt-0.5">{{ $row->poHeader->artikel }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-on-surface">{{ $row->produk?->nama_barang ?? '-' }}</div>
                                    <div class="text-[10px] text-primary/80 font-mono mt-0.5">{{ $row->produk?->kode_barang ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 min-w-[200px]">
                                    <div class="flex items-center justify-between text-[11px] mb-1.5 font-mono">
                                        <span>Dipesan: <strong class="text-on-surface">{{ number_format($dipesan) }}</strong></span>
                                        <span>Diterima: <strong class="text-green-400">{{ number_format($diterima) }}</strong></span>
                                        <span>Sisa: <strong class="{{ $sisa > 0 ? 'text-amber-400 font-black' : 'text-on-surface-variant/50' }}">{{ number_format($sisa) }}</strong></span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-white/10 overflow-hidden flex">
                                        <div class="h-full bg-green-400 transition-all duration-300" style="width: {{ $pct }}%"></div>
                                        @if ($ditutup > 0 && $dipesan > 0)
                                            @php $pctTutup = min(100 - $pct, round(($ditutup / $dipesan) * 100)); @endphp
                                            <div class="h-full bg-sky-400/80 transition-all duration-300" style="width: {{ $pctTutup }}%"></div>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between text-[9px] text-on-surface-variant/60 mt-1">
                                        <span>{{ $pct }}% terpenuhi</span>
                                        @if ($ditutup > 0)
                                            <span class="text-sky-400" title="{{ $row->alasan_tutup }}">{{ number_format($ditutup) }} ditutup</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($row->jumlah_adjust > 0)
                                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md font-mono text-[11px] font-bold {{ $sel < 0 ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : ($sel > 0 ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-white/5 text-on-surface-variant') }}"
                                              title="{{ $row->jumlah_adjust }}x penyesuaian penerimaan">
                                            {{ $sel > 0 ? '+' : '' }}{{ $sel }}
                                        </span>
                                        <span class="block text-[9px] text-on-surface-variant/60 mt-0.5">{{ $row->jumlah_adjust }}x adjust</span>
                                    @else
                                        <span class="text-on-surface-variant/30 font-mono">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if (in_array($st, ['selesai', 'lebih']))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-green-500/20 bg-green-500/10 text-green-400 uppercase tracking-wider">
                                            <span class="material-symbols-outlined text-xs">task_alt</span>
                                            {{ $st === 'lebih' ? 'Lebih Kirim' : 'Selesai' }}
                                        </span>
                                    @elseif ($st === 'ditutup')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-sky-500/20 bg-sky-500/10 text-sky-400 uppercase tracking-wider" title="{{ $row->alasan_tutup }}">
                                            <span class="material-symbols-outlined text-xs">lock</span>
                                            Ditutup
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-amber-500/20 bg-amber-500/10 text-amber-400 uppercase tracking-wider">
                                            <span class="material-symbols-outlined text-xs">hourglass_top</span>
                                            Outstanding
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('purchase-order.show', $row->po_header_id) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 hover:border-primary/40 rounded-xl font-bold text-[10px] transition-all"
                                           title="Lihat Detail PO Lengkap">
                                            <span class="material-symbols-outlined text-xs">visibility</span>
                                            Detail
                                        </a>
                                        @if ($sisa > 0)
                                            <a href="{{ route('barang-masuk.create', ['po_id' => $row->po_header_id, 'items_csv' => $row->id]) }}"
                                               class="inline-flex items-center gap-1 px-2 py-1.5 bg-primary/10 border border-primary/30 text-primary hover:bg-primary hover:text-on-primary-fixed rounded-xl font-bold text-[10px] transition-all"
                                               title="Terima Barang di Barang Masuk">
                                                <span class="material-symbols-outlined text-xs">input</span>
                                                Terima
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2 text-on-surface-variant">
                                        <span class="material-symbols-outlined text-4xl text-on-surface-variant/40">inventory_2</span>
                                        <p class="font-bold text-sm text-on-surface">Tidak ada data tracking PO ditemukan</p>
                                        <p class="text-xs text-on-surface-variant/60">Coba ubah kriteria filter pill atau kata kunci pencarian Anda.</p>
                                        @if ($trackingFilter !== 'all' || !empty($search))
                                            <button type="button" wire:click="setTrackingFilter('all'); $set('search', '')"
                                                    class="mt-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs font-semibold text-primary hover:bg-white/10 transition-all">
                                                Reset Filter &amp; Pencarian
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- Tab 3: Rekap HPP Sisa PO (PO C) -->
        @elseif ($activeTab === 'po_hpp_recap')
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="px-6 py-5">Supplier / Vendor</th>
                            <th class="px-6 py-5">Artikel</th>
                            <th class="px-6 py-5">Produk</th>
                            <th class="px-6 py-5 text-center">Kuantitas PO</th>
                            <th class="px-6 py-5 text-center">Barang Masuk</th>
                            <th class="px-6 py-5 text-center">Sisa PO</th>
                            <th class="px-6 py-5 text-right">HPP PO</th>
                            <th class="px-6 py-5 text-right">Total Nilai HPP Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                        @php $sumHppSisa = 0; @endphp
                        @forelse ($dataList as $row)
                            @php $sumHppSisa += ($row->sisa * $row->hpp); @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ $row->poHeader->supplier->nama_supplier }}</td>
                                <td class="px-6 py-4 font-mono text-primary/80 font-bold">{{ $row->poHeader->artikel ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-on-surface">{{ $row->produk->nama_barang }}</div>
                                    <div class="text-[9px] text-primary/80 font-mono">{{ $row->produk->kode_barang }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">{{ number_format($row->jumlah) }}</td>
                                <td class="px-6 py-4 text-center text-green-400">{{ number_format($row->barang_masuk) }}</td>
                                <td class="px-6 py-4 text-center font-bold text-on-surface">{{ number_format($row->sisa) }}</td>
                                <td class="px-6 py-4 text-right">Rp{{ number_format($row->hpp, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-primary">Rp{{ number_format($row->sisa * $row->hpp, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada rekap HPP sisa PO</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($dataList) > 0)
                        <tfoot class="bg-white/[0.02] font-black border-t border-white/10">
                            <tr>
                                <td colspan="7" class="px-6 py-5 text-right text-xs uppercase tracking-wider text-on-surface-variant/80">Total HPP Outstanding:</td>
                                <td class="px-6 py-5 text-right text-sm text-primary font-black">Rp{{ number_format($sumHppSisa, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $dataList->firstItem() ?? 0 }} s.d. {{ $dataList->lastItem() ?? 0 }} dari {{ $dataList->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($dataList->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($dataList->getUrlRange(max(1, $dataList->currentPage() - 2), min($dataList->lastPage(), $dataList->currentPage() + 2)) as $page => $url)
                    @if ($page == $dataList->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($dataList->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- Create PO Modal -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-2xl mx-auto my-6 z-50">
                <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                        <h3 class="text-base font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined">assignment</span>
                            Terbitkan Purchase Order Baru
                        </h3>
                        <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 pb-36 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                        <!-- Tanggal & Supplier -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tanggal PO</label>
                                <input wire:model="tanggal" type="date" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                @error('tanggal') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Supplier / Vendor</label>
                                <select wire:model="supplier_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliersList as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_supplier }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Artikel -->
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nomor Artikel PO / Keterangan PO</label>
                            <input wire:model="artikel" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: ART-JULY-SNDL">
                            @error('artikel') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Dynamic Items Subform -->
                        <div class="border border-white/5 rounded-2xl p-4 bg-white/[0.02] space-y-4">
                            <div class="flex items-center justify-between border-b border-white/5 pb-2">
                                <h4 class="text-xs font-black text-primary uppercase tracking-wider">Daftar Barang Pesanan</h4>
                                <button type="button" wire:click="addPoItemRow" class="inline-flex items-center px-4 py-1.5 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                                    + Tambah Baris
                                </button>
                            </div>

                            <!-- List of Item Rows -->
                            <div class="space-y-4">
                                @foreach ($poItems as $index => $item)
                                    <div class="grid grid-cols-12 gap-3 items-start" wire:key="po-item-row-{{ $index }}">
                                        <!-- Product select (col-5) -->
                                        <div class="col-span-12 md:col-span-5 space-y-2">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Produk</label>
                                            
                                            @if (!empty($item['produk_id']))
                                                @php
                                                    $selectedProd = \App\Models\Produk::find($item['produk_id']);
                                                @endphp
                                                @if ($selectedProd)
                                                    <!-- Selected Product Card -->
                                                    <div class="flex items-center gap-2 bg-primary/10 border border-primary/20 rounded-2xl p-2 mt-1">
                                                        <span class="material-symbols-outlined text-primary text-base">inventory_2</span>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-[11px] font-bold text-on-surface truncate">{{ $selectedProd->nama_barang }}</p>
                                                            <p class="text-[9px] text-on-surface-variant/60 font-mono">{{ $selectedProd->kode_barang }}</p>
                                                        </div>
                                                        <button type="button" wire:click="deselectProductForItem({{ $index }})" class="p-1 hover:bg-white/10 rounded-lg text-on-surface-variant hover:text-on-surface transition-all flex items-center justify-center">
                                                            <span class="material-symbols-outlined text-xs">close</span>
                                                        </button>
                                                    </div>
                                                @endif
                                            @else
                                                <!-- Autocomplete Input with suggestions list -->
                                                <div class="relative" x-data="{ open: false, queryLength: {{ strlen($item['search_query'] ?? '') }} }" @click.away="open = false">
                                                    <div class="flex items-center bg-surface-container-low border border-white/10 rounded-2xl px-3 py-1.5 focus-within:border-primary transition-all">
                                                        <span class="material-symbols-outlined text-on-surface-variant/40 text-xs mr-2">search</span>
                                                        <input wire:model.live.debounce.300ms="poItems.{{ $index }}.search_query" 
                                                               @focus="open = true"
                                                               @input="queryLength = $el.value.length"
                                                               type="text" 
                                                               placeholder="Ketik SKU / nama produk..." 
                                                               class="bg-transparent border-none p-0 text-xs focus:ring-0 text-on-surface w-full placeholder:text-on-surface-variant/30" />
                                                    </div>
                                                    
                                                    <!-- Suggestions Dropdown (Opens Upwards to prevent footer clipping) -->
                                                    @php
                                                        $searchQuery = $item['search_query'] ?? '';
                                                        $options = strlen($searchQuery) >= 2 ? $this->getProductsForSearch($searchQuery) : [];
                                                    @endphp
                                                    <div x-show="open && queryLength >= 2"
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 -translate-y-1"
                                                         x-transition:enter-end="opacity-100 translate-y-0"
                                                         class="absolute left-0 right-0 bottom-full mb-2 bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl z-50 max-h-42 overflow-y-auto custom-scrollbar p-1"
                                                         style="display: none;">
                                                        @forelse ($options as $p)
                                                            <button type="button" 
                                                                    wire:click="selectProductForItem({{ $index }}, {{ $p->id }})" 
                                                                    @click="open = false"
                                                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-primary/10 text-left transition-colors">
                                                                <span class="material-symbols-outlined text-primary text-sm">inventory_2</span>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-[11px] font-bold text-on-surface truncate">{{ $p->nama_barang }}</p>
                                                                    <p class="text-[9px] text-on-surface-variant/60 font-mono">{{ $p->kode_barang }}</p>
                                                                </div>
                                                            </button>
                                                        @empty
                                                            <div class="px-3 py-2 text-[10px] text-on-surface-variant/60 text-center">Produk tidak ditemukan</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endif
                                            @error("poItems.{$index}.produk_id") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Quantity (col-3) -->
                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">QTY</label>
                                            <input wire:model="poItems.{{ $index }}.jumlah" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3 text-xs text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold" placeholder="Qty">
                                            @error("poItems.{$index}.jumlah") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Price / HPP (col-3) -->
                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Harga HPP PO</label>
                                            <input wire:model="poItems.{{ $index }}.hpp" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3 text-xs text-right text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold" placeholder="Harga per pcs">
                                            @error("poItems.{$index}.hpp") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Delete row (col-1) -->
                                        <div class="col-span-12 md:col-span-1 text-center md:pt-6">
                                            <button type="button" wire:click="removePoItemRow({{ $index }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" title="Hapus Baris">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 bg-surface-container-low/50 space-x-3">
                        <button wire:click="closeModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                            Batal
                        </button>
                        <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                            Terbitkan PO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
