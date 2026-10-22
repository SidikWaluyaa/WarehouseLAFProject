<div x-init="$dispatch('update-header-title', 'Kalender Transaksi Gudang')"
     class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-margin-desktop space-y-6 custom-scrollbar">

    <!-- Top Control Bar (Navigation Month, Today, & Category Filters) -->
    <div class="glass-card rounded-3xl p-4 sm:p-5 border border-white/5 flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        <!-- Month Navigator -->
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-surface-container-low rounded-2xl border border-white/5 p-1 shadow-sm">
                <button wire:click="previousMonth"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-on-surface hover:bg-white/10 hover:text-primary transition-all active:scale-95"
                        title="Bulan Sebelumnya">
                    <span class="material-symbols-outlined text-lg">chevron_left</span>
                </button>
                <!-- Dropdown Pemilihan Bulan & Tahun Fleksibel -->
                <div class="flex items-center gap-1.5 px-1 sm:px-2">
                    <!-- Dropdown Bulan -->
                    <div class="relative">
                        <select wire:model.live="currentMonth"
                                class="appearance-none bg-surface-container/80 hover:bg-surface-container border border-white/10 hover:border-primary/40 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl py-1.5 pl-2.5 sm:pl-3 pr-7 text-xs sm:text-sm font-black text-on-surface transition-all cursor-pointer shadow-inner font-poppins">
                            @foreach ($monthNames as $mNum => $mName)
                                <option value="{{ $mNum }}" class="bg-surface-container-high text-on-surface font-semibold py-1">
                                    {{ $mName }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base material-symbols-outlined leading-none">expand_more</span>
                    </div>

                    <!-- Dropdown Tahun -->
                    <div class="relative">
                        <select wire:model.live="currentYear"
                                class="appearance-none bg-surface-container/80 hover:bg-surface-container border border-white/10 hover:border-primary/40 focus:border-primary focus:ring-1 focus:ring-primary rounded-xl py-1.5 pl-2.5 sm:pl-3 pr-7 text-xs sm:text-sm font-black text-on-surface transition-all cursor-pointer shadow-inner font-poppins">
                            @foreach ($yearsList as $yr)
                                <option value="{{ $yr }}" class="bg-surface-container-high text-on-surface font-semibold py-1">
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base material-symbols-outlined leading-none">expand_more</span>
                    </div>
                </div>
                <button wire:click="nextMonth"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-on-surface hover:bg-white/10 hover:text-primary transition-all active:scale-95"
                        title="Bulan Berikutnya">
                    <span class="material-symbols-outlined text-lg">chevron_right</span>
                </button>
            </div>

            <button wire:click="goToToday"
                    class="px-4 py-2.5 rounded-2xl bg-white/5 border border-white/10 hover:border-primary/40 text-xs font-bold text-on-surface hover:text-primary transition-all active:scale-95 flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-base text-primary">today</span>
                <span>Hari Ini</span>
            </button>
        </div>

        <!-- Filter Category Pills -->
        <div class="flex flex-wrap items-center gap-1.5 p-1 bg-surface-container-low rounded-2xl border border-white/5">
            <button wire:click="setFilterCategory('all')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all {{ $filterCategory === 'all' ? 'bg-primary text-on-primary-fixed shadow-md shadow-primary/20 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                Semua Kategori
            </button>
            <button wire:click="setFilterCategory('inbound')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $filterCategory === 'inbound' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Barang Masuk</span>
            </button>
            <button wire:click="setFilterCategory('outbound')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $filterCategory === 'outbound' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                <span>Barang Keluar</span>
            </button>
            <button wire:click="setFilterCategory('po')"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $filterCategory === 'po' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30 font-black' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Purchase Order</span>
            </button>
        </div>
    </div>

    <!-- Main Dual-Pane Layout: Left Grid Calendar + Right Sidebar Panel -->
    <div class="flex flex-col xl:flex-row items-start gap-6">

        <!-- SISI KIRI: Grid Kalender Bulanan -->
        <div class="flex-1 w-full glass-card rounded-3xl p-5 border border-white/5 shadow-2xl overflow-hidden">
            <!-- Header Nama Hari (7 Kolom) -->
            <div class="grid grid-cols-7 gap-2 mb-3 text-center">
                @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $idx => $dayName)
                    <div class="py-2 text-[11px] font-black uppercase tracking-wider {{ in_array($idx, [0, 6]) ? 'text-rose-400/80' : 'text-on-surface-variant/70' }}">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <!-- Grid Sel Tanggal (7 Kolom x N Baris) -->
            <div class="grid grid-cols-7 gap-2">
                @foreach ($calendarDays as $cell)
                    @php
                        $dStr = $cell['date'];
                        $isToday = $dStr === $todayDate;
                        $isSelected = $dStr === $selectedDate;
                        $evt = $eventsMap[$dStr] ?? [
                            'masuk_count' => 0, 'masuk_qty' => 0,
                            'keluar_count' => 0, 'keluar_qty' => 0,
                            'po_count' => 0, 'po_qty' => 0
                        ];
                        $hasActivity = ($evt['masuk_count'] + $evt['keluar_count'] + $evt['po_count']) > 0;
                    @endphp

                    <div wire:key="cal-cell-{{ $dStr }}"
                         wire:click="selectDate('{{ $dStr }}')"
                         class="group relative min-h-[96px] sm:min-h-[115px] p-2.5 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between
                                {{ $cell['isCurrentMonth'] ? 'bg-surface-container-lowest/40' : 'bg-surface-container-lowest/15 opacity-40 hover:opacity-80' }}
                                {{ $isSelected ? 'ring-2 ring-primary border-primary bg-primary/10 shadow-lg shadow-primary/10' : 'border-white/5 hover:border-white/20 hover:bg-white/[0.04]' }}">
                        
                        <!-- Header Tanggal -->
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs sm:text-sm font-black font-mono {{ $isToday ? 'text-primary font-black' : ($cell['isCurrentMonth'] ? 'text-on-surface' : 'text-on-surface-variant') }}">
                                {{ $cell['day'] }}
                            </span>
                            @if ($isToday)
                                <span class="px-1.5 py-0.5 rounded-md bg-primary text-on-primary-fixed text-[8px] font-black uppercase tracking-wider">
                                    Hari Ini
                                </span>
                            @endif
                        </div>

                        <!-- Chips Indikator 3 Warna -->
                        <div class="space-y-1 w-full">
                            @if (in_array($filterCategory, ['all', 'inbound']) && $evt['masuk_count'] > 0)
                                <div class="w-full flex items-center justify-between px-2 py-0.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold leading-tight"
                                     title="{{ $evt['masuk_count'] }} Nota Masuk ({{ number_format($evt['masuk_qty']) }} unit)">
                                    <div class="flex items-center gap-1 truncate">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                        <span class="truncate">Masuk</span>
                                    </div>
                                    <span class="font-mono text-[9px] shrink-0 font-black">+{{ number_format($evt['masuk_qty']) }}</span>
                                </div>
                            @endif

                            @if (in_array($filterCategory, ['all', 'outbound']) && $evt['keluar_count'] > 0)
                                <div class="w-full flex items-center justify-between px-2 py-0.5 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-bold leading-tight"
                                     title="{{ $evt['keluar_count'] }} Nota Keluar ({{ number_format($evt['keluar_qty']) }} unit)">
                                    <div class="flex items-center gap-1 truncate">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 shrink-0"></span>
                                        <span class="truncate">Keluar</span>
                                    </div>
                                    <span class="font-mono text-[9px] shrink-0 font-black">-{{ number_format($evt['keluar_qty']) }}</span>
                                </div>
                            @endif

                            @if (in_array($filterCategory, ['all', 'po']) && $evt['po_count'] > 0)
                                <div class="w-full flex items-center justify-between px-2 py-0.5 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[10px] font-bold leading-tight"
                                     title="{{ $evt['po_count'] }} PO ({{ number_format($evt['po_qty']) }} unit)">
                                    <div class="flex items-center gap-1 truncate">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                        <span class="truncate">PO</span>
                                    </div>
                                    <span class="font-mono text-[9px] shrink-0 font-black">{{ $evt['po_count'] }} PO</span>
                                </div>
                            @endif
                        </div>

                        <!-- Bar status mini jika banyak aktivitas -->
                        @if (!$hasActivity)
                            <div class="h-1"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Petunjuk Warna Kalender (Legend) -->
            <div class="mt-5 pt-4 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-[11px] text-on-surface-variant">
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-emerald-500/20 border border-emerald-500/40"></span>
                        <strong class="text-emerald-400">Barang Masuk</strong>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-rose-500/20 border border-rose-500/40"></span>
                        <strong class="text-rose-400">Barang Keluar</strong>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-amber-500/20 border border-amber-500/40"></span>
                        <strong class="text-amber-400">Purchase Order (PO)</strong>
                    </span>
                </div>
                <div class="text-[10px] text-on-surface-variant/60">
                    Klik kotak tanggal untuk melihat rincian nota &amp; item di panel kanan &rarr;
                </div>
            </div>
        </div>

        <!-- SISI KANAN: Sidebar Panel Rincian Transaksi Harian -->
        <aside class="w-full xl:w-[420px] 2xl:w-[460px] glass-card rounded-3xl p-5 border border-white/5 shadow-2xl flex flex-col gap-4 self-stretch custom-scrollbar">
            
            <!-- Header Tanggal Terpilih -->
            <div class="pb-3 border-b border-white/5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-lg">event_available</span>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/60">Rincian Transaksi</p>
                            <h3 class="text-sm sm:text-base font-black text-on-surface font-poppins">
                                {{ $selectedDateTitle }}
                            </h3>
                        </div>
                    </div>

                    @if ($selectedDate === $todayDate)
                        <span class="px-2 py-0.5 rounded-full bg-primary/20 border border-primary/30 text-primary text-[10px] font-bold uppercase">
                            Hari Ini
                        </span>
                    @endif
                </div>

                <!-- 3 Mini Metric Cards Hari Ini -->
                <div class="grid grid-cols-3 gap-2 mt-3.5">
                    <!-- Masuk -->
                    <div class="p-2.5 rounded-xl bg-emerald-500/5 border border-emerald-500/20">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-400/80">Masuk</p>
                        <p class="text-sm font-black text-emerald-400 font-mono mt-0.5">
                            +{{ number_format($dailyStats['masuk_qty']) }}
                        </p>
                        <p class="text-[9px] text-on-surface-variant/70">{{ $dailyStats['masuk_count'] }} Nota</p>
                    </div>

                    <!-- Keluar -->
                    <div class="p-2.5 rounded-xl bg-rose-500/5 border border-rose-500/20">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-rose-400/80">Keluar</p>
                        <p class="text-sm font-black text-rose-400 font-mono mt-0.5">
                            -{{ number_format($dailyStats['keluar_qty']) }}
                        </p>
                        <p class="text-[9px] text-on-surface-variant/70">{{ $dailyStats['keluar_count'] }} Nota</p>
                    </div>

                    <!-- PO -->
                    <div class="p-2.5 rounded-xl bg-amber-500/5 border border-amber-500/20">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-amber-400/80">PO</p>
                        <p class="text-sm font-black text-amber-400 font-mono mt-0.5">
                            {{ number_format($dailyStats['po_qty']) }}
                        </p>
                        <p class="text-[9px] text-on-surface-variant/70">{{ $dailyStats['po_count'] }} Pesanan</p>
                    </div>
                </div>
            </div>

            <!-- List Transaksi per Nota dengan Accordion Rincian Item -->
            <div class="space-y-4 max-h-[calc(100vh-340px)] overflow-y-auto custom-scrollbar pr-1">

                @php
                    $totalTrx = $selectedMasuk->count() + $selectedKeluar->count() + $selectedPo->count();
                @endphp

                @if ($totalTrx === 0)
                    <!-- Empty State -->
                    <div class="py-12 px-4 text-center flex flex-col items-center justify-center">
                        <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-on-surface-variant/40 mb-3">
                            <span class="material-symbols-outlined text-2xl">event_busy</span>
                        </div>
                        <p class="font-bold text-sm text-on-surface">Tidak Ada Aktivitas</p>
                        <p class="text-xs text-on-surface-variant/60 max-w-[220px] mt-1">
                            Belum ada transaksi barang masuk, keluar, atau PO pada tanggal ini.
                        </p>
                    </div>
                @else

                    <!-- 1. KELOMPOK BARANG MASUK -->
                    @if (in_array($filterCategory, ['all', 'inbound']) && $selectedMasuk->isNotEmpty())
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-black text-emerald-400 uppercase tracking-wider">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">move_to_inbox</span>
                                    Barang Masuk ({{ $selectedMasuk->count() }})
                                </span>
                                <span class="font-mono text-[11px]">+{{ number_format($dailyStats['masuk_qty']) }} pcs</span>
                            </div>

                            @foreach ($selectedMasuk as $m)
                                <div x-data="{ open: false }"
                                     class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-emerald-500/30 transition-all space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-mono text-xs font-bold text-emerald-400">
                                                    {{ $m->nomor_nota }}
                                                </span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    {{ $m->tipe_nota ?? 'Pembelian' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-on-surface font-semibold truncate mt-0.5">
                                                {{ $m->supplier?->nama_supplier ?? 'Tanpa Supplier' }}
                                            </p>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-mono font-black text-emerald-400 block">
                                                +{{ number_format($m->details->sum('qty_masuk')) }} pcs
                                            </span>
                                            <span class="text-[10px] text-on-surface-variant/60 block">
                                                {{ $m->details->count() }} Item
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Bar: Accordion Toggle & Direct Links -->
                                    <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px]">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-1 text-on-surface-variant hover:text-emerald-400 font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-400' : ''">expand_more</span>
                                            <span x-text="open ? 'Sembunyikan Item' : 'Lihat ' + {{ $m->details->count() }} + ' Item'">Lihat Item</span>
                                        </button>

                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('barang-masuk.print', $m->id) }}" target="_blank"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-emerald-400 transition-all"
                                               title="Cetak Surat Penerimaan">
                                                <span class="material-symbols-outlined text-sm">print</span>
                                            </a>
                                            <a href="{{ route('barang-masuk') }}"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-on-surface transition-all"
                                               title="Buka Daftar Barang Masuk">
                                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Accordion List Item Produk -->
                                    <div x-show="open" x-cloak class="pt-2 border-t border-white/5 space-y-1.5">
                                        @foreach ($m->details as $d)
                                            <div class="p-2 rounded-xl bg-surface-container-low/60 flex items-center justify-between text-xs">
                                                <div class="min-w-0 pr-2">
                                                    <p class="font-bold text-on-surface truncate">{{ $d->produk?->nama_barang ?? '-' }}</p>
                                                    <p class="text-[9px] font-mono text-on-surface-variant/70">{{ $d->produk?->kode_barang ?? '-' }} &bull; Lokasi: {{ $d->lokasi_simpan ?? '-' }}</p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="font-mono font-bold text-emerald-400">+{{ number_format($d->qty_masuk) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- 2. KELOMPOK BARANG KELUAR -->
                    @if (in_array($filterCategory, ['all', 'outbound']) && $selectedKeluar->isNotEmpty())
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-black text-rose-400 uppercase tracking-wider">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">outbox</span>
                                    Barang Keluar ({{ $selectedKeluar->count() }})
                                </span>
                                <span class="font-mono text-[11px]">-{{ number_format($dailyStats['keluar_qty']) }} pcs</span>
                            </div>

                            @foreach ($selectedKeluar as $k)
                                <div x-data="{ open: false }"
                                     class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-rose-500/30 transition-all space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-mono text-xs font-bold text-rose-400">
                                                    {{ $k->nomor_nota }}
                                                </span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                    {{ $k->tipe_nota ?? 'Penjualan' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-on-surface font-semibold truncate mt-0.5">
                                                {{ $k->pelanggan?->nama_pelanggan ?? 'Umum / Toko' }}
                                            </p>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-mono font-black text-rose-400 block">
                                                -{{ number_format($k->details->sum('qty_keluar')) }} pcs
                                            </span>
                                            <span class="text-[10px] text-on-surface-variant/60 block">
                                                {{ $k->details->count() }} Item
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Bar: Accordion Toggle & Direct Links -->
                                    <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px]">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-1 text-on-surface-variant hover:text-rose-400 font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180 text-rose-400' : ''">expand_more</span>
                                            <span x-text="open ? 'Sembunyikan Item' : 'Lihat ' + {{ $k->details->count() }} + ' Item'">Lihat Item</span>
                                        </button>

                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('barang-keluar.print', $k->id) }}" target="_blank"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-rose-400 transition-all"
                                               title="Cetak Surat Jalan / Pengeluaran">
                                                <span class="material-symbols-outlined text-sm">print</span>
                                            </a>
                                            <a href="{{ route('barang-keluar') }}"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-on-surface transition-all"
                                               title="Buka Daftar Barang Keluar">
                                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Accordion List Item Produk -->
                                    <div x-show="open" x-cloak class="pt-2 border-t border-white/5 space-y-1.5">
                                        @foreach ($k->details as $d)
                                            <div class="p-2 rounded-xl bg-surface-container-low/60 flex items-center justify-between text-xs">
                                                <div class="min-w-0 pr-2">
                                                    <p class="font-bold text-on-surface truncate">{{ $d->produk?->nama_barang ?? '-' }}</p>
                                                    <p class="text-[9px] font-mono text-on-surface-variant/70">{{ $d->produk?->kode_barang ?? '-' }} &bull; Dari: {{ $d->lokasi_pengambilan ?? '-' }}</p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="font-mono font-bold text-rose-400">-{{ number_format($d->qty_keluar) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- 3. KELOMPOK PURCHASE ORDER (PO) -->
                    @if (in_array($filterCategory, ['all', 'po']) && $selectedPo->isNotEmpty())
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-black text-amber-400 uppercase tracking-wider">
                                <span class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-sm">receipt_long</span>
                                    Purchase Order ({{ $selectedPo->count() }})
                                </span>
                                <span class="font-mono text-[11px]">{{ number_format($dailyStats['po_qty']) }} unit</span>
                            </div>

                            @foreach ($selectedPo as $po)
                                <div x-data="{ open: false }"
                                     class="p-3.5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-amber-500/30 transition-all space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="font-mono text-xs font-bold text-amber-400">
                                                    {{ $po->nomor_po ?? 'PO #'.$po->id }}
                                                </span>
                                                @if ($po->artikel)
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-white/5 text-primary/80 border border-white/10">
                                                        {{ $po->artikel }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-on-surface font-semibold truncate mt-0.5">
                                                {{ $po->supplier?->nama_supplier ?? 'Tanpa Vendor' }}
                                            </p>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-mono font-black text-amber-400 block">
                                                {{ number_format($po->poDetails->sum('jumlah')) }} unit
                                            </span>
                                            <span class="text-[10px] text-on-surface-variant/60 block">
                                                {{ $po->poDetails->count() }} SKU
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Bar: Accordion Toggle & Direct Links -->
                                    <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px]">
                                        <button type="button" @click="open = !open"
                                                class="inline-flex items-center gap-1 text-on-surface-variant hover:text-amber-400 font-semibold transition-colors">
                                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180 text-amber-400' : ''">expand_more</span>
                                            <span x-text="open ? 'Sembunyikan Item' : 'Lihat ' + {{ $po->poDetails->count() }} + ' SKU'">Lihat SKU</span>
                                        </button>

                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('purchase-order.print', $po->id) }}" target="_blank"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-amber-400 transition-all"
                                               title="Cetak Dokumen PO">
                                                <span class="material-symbols-outlined text-sm">print</span>
                                            </a>
                                            <a href="{{ route('purchase-order.show', $po->id) }}"
                                               class="p-1 rounded-lg hover:bg-white/10 text-on-surface-variant hover:text-on-surface transition-all"
                                               title="Buka Detail PO">
                                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Accordion List Item Produk PO -->
                                    <div x-show="open" x-cloak class="pt-2 border-t border-white/5 space-y-1.5">
                                        @foreach ($po->poDetails as $d)
                                            <div class="p-2 rounded-xl bg-surface-container-low/60 flex items-center justify-between text-xs">
                                                <div class="min-w-0 pr-2">
                                                    <p class="font-bold text-on-surface truncate">{{ $d->produk?->nama_barang ?? '-' }}</p>
                                                    <p class="text-[9px] font-mono text-on-surface-variant/70">{{ $d->produk?->kode_barang ?? '-' }}</p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="font-mono font-bold text-amber-400">{{ number_format($d->jumlah) }} unit</span>
                                                    <span class="block text-[9px] text-on-surface-variant/60">Sisa: {{ number_format($d->sisa) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                @endif
            </div>
        </aside>
    </div>
</div>
