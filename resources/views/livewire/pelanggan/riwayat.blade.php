<div x-init="$dispatch('update-header-title', 'Master Pelanggan')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari produk, nota, keterangan..." type="text"/>
        </div>
    </template>

    <style>
        .rose-glow-strong {
            box-shadow: 0 0 30px rgba(244, 63, 94, 0.2);
        }
    </style>

    <!-- Tab Navigation -->
    <div class="flex border-b border-white/10 mb-6 space-x-8">
        <a href="{{ route('pelanggan') }}" class="pb-4 text-sm font-semibold text-on-surface-variant hover:text-on-surface flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-lg">list_alt</span>
            Daftar Pelanggan
        </a>
        <a href="{{ route('pelanggan.riwayat') }}" class="relative pb-4 text-sm font-bold text-rose-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg text-rose-400">history</span>
            Riwayat Pelanggan
            <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-rose-500 rounded-full shadow-[0_0_12px_#f43f5e]"></div>
        </a>
    </div>

    <!-- Alert Toast -->
    

    

    <!-- Pelanggan Autocomplete Selector -->
    <div class="glass-card rounded-3xl p-6 border border-white/5 bg-gradient-to-br from-rose-500/10 to-transparent relative overflow-hidden shadow-2xl rose-glow-strong">
        <div class="absolute -right-24 -top-24 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1 z-10 flex-1">
                <label class="text-[10px] font-bold tracking-[0.2em] text-rose-400 uppercase block">Pilih Pelanggan Untuk Difilter</label>
                @if ($selectedPelanggan)
                    <div class="flex items-center gap-3 bg-white/5 border border-rose-500/20 rounded-2xl px-5 py-3.5 mt-2">
                        <span class="material-symbols-outlined text-rose-400 text-2xl">person</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-on-surface truncate">{{ $selectedPelanggan->nama_pelanggan }}</h3>
                            <p class="text-[10px] text-on-surface-variant/70 font-mono">{{ $selectedPelanggan->kontak ?? 'Tidak Ada Kontak' }} • {{ $selectedPelanggan->alamat ?? 'Tidak Ada Alamat' }}</p>
                        </div>
                        <button wire:click="deselectPelanggan" class="p-2 hover:bg-white/10 rounded-xl text-on-surface-variant hover:text-on-surface transition-all">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                @else
                    <div class="relative mt-2" x-data="{ open: false }" @click.away="open = false">
                        <div class="flex items-center bg-surface-container-low border border-white/10 rounded-2xl px-4 py-3.5 focus-within:border-rose-500/50 transition-all">
                            <span class="material-symbols-outlined text-on-surface-variant mr-3">search</span>
                            <input wire:model.live.debounce.300ms="pelangganSearch" 
                                   @focus="open = true"
                                   type="text" 
                                   class="bg-transparent border-none p-0 text-sm focus:ring-0 text-on-surface w-full placeholder:text-on-surface-variant/50" 
                                   placeholder="Ketik nama pelanggan untuk memfilter..." />
                            @if ($pelangganSearch)
                                <button wire:click="$set('pelangganSearch', '')" class="text-on-surface-variant hover:text-on-surface">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Dropdown list -->
                        <div x-show="open && $wire.pelangganSearch.length >= 2" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute left-0 right-0 mt-2 bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl z-50 max-h-60 overflow-y-auto custom-scrollbar p-2"
                             style="display: none;">
                            @forelse ($this->pelanggans as $pel)
                                <button wire:click="selectPelanggan({{ $pel->id }})" 
                                        @click="open = false"
                                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-rose-500/10 text-left transition-colors">
                                    <span class="material-symbols-outlined text-rose-400">person</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-on-surface truncate">{{ $pel->nama_pelanggan }}</p>
                                        <p class="text-[9px] text-on-surface-variant/70 font-mono">{{ $pel->kontak ?? '-' }}</p>
                                    </div>
                                </button>
                            @empty
                                <div class="px-4 py-3 text-xs text-on-surface-variant text-center">Pelanggan tidak ditemukan</div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="flex flex-col items-start md:items-end gap-1 text-left md:text-right md:w-64">
                <span class="text-xs font-black text-rose-400 tracking-widest uppercase">Riwayat Pelanggan</span>
                <span class="text-[10px] text-on-surface-variant/60">Laporan transaksi penjualan barang per-pelanggan. Gunakan kolom pencarian untuk memfilter data.</span>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Date Range Picker (Flatpickr) -->
        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between space-y-2"
             wire:ignore
             x-data="{
                 fp: null,
                 init() {
                     this.$nextTick(() => {
                         this.fp = flatpickr(this.$refs.dateInput, {
                             mode: 'range',
                             dateFormat: 'Y-m-d',
                             altInput: true,
                             altFormat: 'd M Y',
                             allowInput: false,
                             showMonths: 1,
                             defaultDate: [
                                 $wire.tanggalStart || null,
                                 $wire.tanggalEnd || null
                             ].filter(d => d),
                             locale: {
                                 rangeSeparator: '  →  ',
                                 firstDayOfWeek: 1,
                             },
                             onReady: (selectedDates, dateStr, instance) => {
                                 instance.calendarContainer.classList.add('flatpickr-rose');
                             },
                             onChange: (selectedDates, dateStr, instance) => {
                                 if (selectedDates.length === 2) {
                                     const start = instance.formatDate(selectedDates[0], 'Y-m-d');
                                     const end = instance.formatDate(selectedDates[1], 'Y-m-d');
                                     $wire.setDateRange(start, end);
                                 } else if (selectedDates.length === 0) {
                                     $wire.setDateRange(null, null);
                                 }
                             }
                         });
                     });

                     this.$watch('$wire.tanggalStart', value => {
                         if (!value && this.fp) {
                             this.fp.clear();
                         }
                     });
                 },
                 destroy() {
                     if (this.fp) this.fp.destroy();
                 }
             }">
            <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-400 text-sm">date_range</span>
                Filter Tanggal
            </span>
            <div class="relative">
                <input x-ref="dateInput" type="text" readonly
                       class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2.5 px-3 pr-9 text-xs text-on-surface cursor-pointer focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all placeholder:text-on-surface-variant/40"
                       placeholder="Pilih rentang tanggal...">
                <template x-if="$wire.tanggalStart">
                    <button @click.stop="fp.clear(); $wire.setDateRange(null, null);" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant/50 hover:text-on-surface transition-colors z-10">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </template>
                <template x-if="!$wire.tanggalStart">
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant/30 text-sm pointer-events-none">calendar_month</span>
                </template>
            </div>
        </div>

        <!-- Tipe Transaksi -->
        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between space-y-2">
            <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-400 text-sm">category</span>
                Tipe Transaksi
            </span>
            <select wire:model.live="tipeFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all">
                <option value="">Semua Tipe</option>
                <option value="Penjualan">Penjualan</option>
                <option value="Retur Keluar">Retur Keluar</option>
                <option value="Penyesuaian">Penyesuaian</option>
            </select>
        </div>

        <!-- Lokasi Pengambilan -->
        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between space-y-2">
            <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-400 text-sm">location_on</span>
                Lokasi Pengambilan
            </span>
            <select wire:model.live="lokasiFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all">
                <option value="">Semua Lokasi</option>
                @foreach ($this->lokasiOptions as $loc)
                    <option value="{{ $loc }}">{{ $loc }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Aktif Summary -->
        <div class="glass-card rounded-2xl p-4 flex flex-col justify-between space-y-2">
            <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase flex items-center gap-1.5">
                <span class="material-symbols-outlined text-rose-400 text-sm">filter_alt</span>
                Filter Aktif
            </span>
            @php
                $activeCount = 0;
                if (!empty($tanggalStart) || !empty($tanggalEnd)) $activeCount++;
                if (!empty($tipeFilter)) $activeCount++;
                if (!empty($lokasiFilter)) $activeCount++;
                if (!empty($search)) $activeCount++;
                if ($selectedPelangganId) $activeCount++;
            @endphp
            @if ($activeCount > 0)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-500/15 text-rose-300 text-xs font-black">
                            {{ $activeCount }}
                        </span>
                        <span class="text-xs text-on-surface-variant">filter aktif</span>
                    </div>
                    <button wire:click="clearAllFilters" class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-bold text-on-surface-variant hover:text-on-surface hover:bg-white/10 transition-all uppercase tracking-wider">
                        <span class="material-symbols-outlined text-xs">clear_all</span>
                        Reset
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 py-1">
                    <span class="material-symbols-outlined text-on-surface-variant/20 text-lg">filter_alt_off</span>
                    <span class="text-xs text-on-surface-variant/40">Tidak ada filter aktif</span>
                </div>
            @endif
        </div>
    </section>

    <!-- Data Table -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8 border border-white/5">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-rose-500/5 text-[10px] font-bold tracking-[0.2em] text-rose-300 uppercase">
                        <th class="px-6 py-4.5">Tanggal</th>
                        <th class="px-6 py-4.5">Tipe Nota</th>
                        <th class="px-6 py-4.5">Nama Pelanggan</th>
                        <th class="px-6 py-4.5">No. Nota</th>
                        <th class="px-6 py-4.5">Keterangan Tambahan</th>
                        <th class="px-6 py-4.5">Produk</th>
                        <th class="px-6 py-4.5 text-center">QTY Keluar</th>
                        <th class="px-6 py-4.5 text-center">Satuan</th>
                        <th class="px-6 py-4.5">Data Dikeluarkan Oleh</th>
                        <th class="px-6 py-4.5 text-center">Lokasi Pengambilan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                    @forelse ($history as $h)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-on-surface">
                                {{ $h->barangKeluarHeader->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold border uppercase tracking-wider
                                    {{ $h->barangKeluarHeader->tipe_nota === 'Penjualan' ? 'bg-green-500/10 text-green-400 border-green-500/20' : '' }}
                                    {{ $h->barangKeluarHeader->tipe_nota === 'Retur Keluar' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                                    {{ $h->barangKeluarHeader->tipe_nota === 'Penyesuaian' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}
                                ">
                                    {{ $h->barangKeluarHeader->tipe_nota }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-on-surface">
                                {{ $h->barangKeluarHeader->pelanggan->nama_pelanggan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-rose-400/80">
                                {{ $h->barangKeluarHeader->nomor_nota ?? '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-on-surface-variant/80" title="{{ $h->barangKeluarHeader->keterangan_tambahan }}">
                                {{ $h->barangKeluarHeader->keterangan_tambahan ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-on-surface">{{ $h->produk->nama_barang }}</div>
                                <div class="text-[9px] text-on-surface-variant/50 font-mono">{{ $h->produk->kode_barang }} • {{ $h->produk->variasi_barang }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-rose-300 font-mono text-sm">
                                {{ number_format($h->qty_keluar) }}
                            </td>
                            <td class="px-6 py-4 text-center text-on-surface-variant/70">
                                {{ $h->satuan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-white/5 border border-white/5 rounded-lg text-[10px] text-on-surface-variant">
                                    {{ $h->barangKeluarHeader->dikeluarkan_oleh }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[9px] font-bold rounded-full border 
                                    {{ $h->lokasi_pengambilan === 'GUDANG' ? 'bg-purple-500/10 text-purple-300 border-purple-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}
                                ">
                                    {{ $h->lokasi_pengambilan }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">history</span>
                                <p>Tidak ada riwayat detail barang keluar ke pelanggan ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($history->hasPages())
            <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                <p class="text-xs text-on-surface-variant">
                    Menampilkan {{ $history->firstItem() ?? 0 }} s.d. {{ $history->lastItem() ?? 0 }} dari {{ $history->total() }} hasil
                </p>
                <div class="flex gap-2">
                    @if ($history->onFirstPage())
                        <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                    @else
                        <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                    @endif

                    @if ($history->hasMorePages())
                        <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                    @else
                        <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
