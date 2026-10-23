<div x-init="$dispatch('update-header-title', 'Stock Master')" class="flex-1 overflow-y-auto p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Search Master Data..." type="text"/>
        </div>
    </template>



    <!-- KPI Section -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- KPI Card 1 -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">inventory</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">{{ $selectedLokasi ? 'SKU di ' . $selectedLokasi->kode_lokasi : 'Total SKUs' }}</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($totalSkus, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                <span>{{ $selectedLokasi ? 'Tersedia di Lokasi' : 'Produk Aktif' }}</span>
            </div>
        </div>
        <!-- KPI Card 2 -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-error/20 transition-all duration-500 {{ $stokMenipis > 0 ? 'border-error/30 bg-error/5' : '' }}">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-error/10 text-8xl group-hover:scale-110 transition-transform duration-700">warning</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">{{ $selectedLokasi ? 'Stok Menipis (' . $selectedLokasi->kode_lokasi . ')' : 'Stok Menipis' }}</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($stokMenipis, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-error">
                <span class="material-symbols-outlined text-sm mr-1">priority_high</span>
                <span>Butuh Perhatian</span>
            </div>
        </div>
        <!-- KPI Card 3 -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">payments</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">{{ $selectedLokasi ? 'Nilai Stok (' . $selectedLokasi->kode_lokasi . ')' : 'Total Nilai Stok' }}</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ $totalNilaiStok }}</h3>
            <div class="mt-4 w-full h-1 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-primary amber-glow-strong" style="width: 75%"></div>
            </div>
        </div>
        <!-- KPI Card 4 -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute -top-4 -right-4 material-symbols-outlined text-primary/10 text-8xl group-hover:scale-110 transition-transform duration-700">rocket_launch</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">{{ $selectedLokasi ? 'Fast Moving (' . $selectedLokasi->kode_lokasi . ')' : 'Fast Moving' }}</p>
            <h3 class="text-xl font-bold text-on-surface truncate">
                {{ $fastMovingProduct ? $fastMovingProduct->nama_barang : 'N/A' }}
            </h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-on-tertiary-container">
                <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                <span>{{ $selectedLokasi ? ($fastMovingProduct && isset($fastMovingProduct->lokasi_keluar) ? $fastMovingProduct->lokasi_keluar . ' Keluar dari ' . $selectedLokasi->kode_lokasi : 'Stok Aktif di Lokasi') : 'Permintaan Tinggi (' . ($fastMovingProduct ? $fastMovingProduct->stok_keluar : 0) . ' Keluar)' }}</span>
            </div>
        </div>
    </section>

    <!-- Actions Bar with collapsible panel -->
    <div x-data="{ showFilters: false }" class="space-y-4">
        <!-- Actions Bar -->
        <section class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Mobile Search Bar (Only visible on screens < md) -->
                <div class="md:hidden relative w-full">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                    <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Search Master Data..." type="text"/>
                </div>

                <div class="relative w-full md:w-60">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">filter_list</span>
                    <select wire:model.live="categoryFilter" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Toggle Advanced Filters Button -->
                <button @click="showFilters = !showFilters" 
                        :class="showFilters ? 'bg-primary/20 text-primary border-primary/30' : 'bg-surface-container-high border-white/10 hover:bg-white/5'"
                        class="flex items-center gap-2 px-4 py-2.5 border rounded-2xl text-sm font-medium transition-all active:scale-95">
                    <span class="material-symbols-outlined text-primary">tune</span>
                    <span>Filter Lanjutan</span>
                    @if($locationFilter || $stockFilter || $warnaFilter || $sizeFilter || $statusFilter)
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    @endif
                </button>

                <button wire:click="exportExcel" class="flex items-center gap-2 px-4 py-2.5 bg-surface-container-high border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-primary">download</span>
                    Ekspor Excel
                </button>
                @can('manage master')
                <button wire:click="downloadTemplate" class="flex items-center gap-2 px-4 py-2.5 bg-surface-container-high border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-primary">download</span>
                    Template Excel
                </button>
                <button wire:click="openImportModal" class="flex items-center gap-2 px-4 py-2.5 bg-surface-container-high border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-primary">upload</span>
                    Impor Excel
                </button>
                <button wire:click="openWaModal" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm font-semibold hover:bg-emerald-500/20 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-emerald-400">chat</span>
                    Peringatan Stok WA
                    @if($lowStockCount > 0)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black">{{ $lowStockCount }}</span>
                    @endif
                </button>
                @endcan
            </div>
            @can('manage master')
            <button wire:click="create" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                <span class="material-symbols-outlined">add</span>
                Tambah Produk
            </button>
            @endcan
        </section>

        <!-- Advanced Collapsible Filter Panel -->
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="glass-card rounded-3xl p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 relative overflow-hidden" 
             style="display: none;">
            
            <!-- Location Filter -->
            <div class="space-y-1.5">
                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold">Filter Lokasi Stok</label>
                <div class="relative">
                    <select wire:model.live="locationFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 pl-3 pr-8 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Lokasi</option>
                        @foreach($locationsList as $loc)
                            <option value="{{ $loc->kode_lokasi }}">{{ $loc->kode_lokasi }} - {{ $loc->nama_lokasi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Stock Status Filter -->
            <div class="space-y-1.5">
                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold">Status Stok</label>
                <div class="relative">
                    <select wire:model.live="stockFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 pl-3 pr-8 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Stok</option>
                        <option value="menipis">Stok Menipis (Di Bawah Ambang Batas)</option>
                        <option value="threshold_only">Hanya Ber-Ambang Batas</option>
                        <option value="habis">Stok Habis (= 0)</option>
                        <option value="melimpah">Stok Melimpah (> 5)</option>
                    </select>
                </div>
            </div>

            <!-- Warna Filter -->
            <div class="space-y-1.5">
                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold">Warna</label>
                <div class="relative">
                    <select wire:model.live="warnaFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 pl-3 pr-8 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Warna</option>
                        @foreach($colors as $color)
                            <option value="{{ $color }}">{{ $color }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Size Filter -->
            <div class="space-y-1.5">
                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold">Ukuran (Size)</label>
                <div class="relative">
                    <select wire:model.live="sizeFilter" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 pl-3 pr-8 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Ukuran</option>
                        @foreach($sizes as $sz)
                            <option value="{{ $sz }}">{{ $sz }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Filter (Aktif/Tidak Aktif) -->
            <div class="space-y-1.5">
                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold">Status Keaktifan</label>
                <div class="relative flex items-center gap-2">
                    <select wire:model.live="statusFilter" class="flex-1 bg-surface-container-low border border-white/10 rounded-xl py-2 pl-3 pr-8 text-xs text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>

                    @if($search || $categoryFilter || $locationFilter || $stockFilter || $warnaFilter || $sizeFilter || $statusFilter)
                        <button wire:click="resetFilters" class="p-2 bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 rounded-xl text-xs flex items-center justify-center transition-all active:scale-95" title="Reset semua filter">
                            <span class="material-symbols-outlined text-sm">clear_all</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5">
                        <th wire:click="sortBy('nama_barang')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Produk
                                @if ($sortField === 'nama_barang')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('kategori')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kategori & Detail
                                @if ($sortField === 'kategori')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Lokasi</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Mutasi</th>
                        <th wire:click="sortBy('total_stok')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-right cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center justify-end gap-1">
                                {{ $selectedLokasi ? 'Stok (' . $selectedLokasi->kode_lokasi . ')' : 'Total Stok' }}
                                @if ($sortField === 'total_stok')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('hpp')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-right cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center justify-end gap-1">
                                Harga (Beli/Jual)
                                @if ($sortField === 'hpp')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody x-data="{ expandedId: null }" class="divide-y divide-white/5 text-xs">
                    @forelse ($products as $p)
                        <tr @click="expandedId = (expandedId === {{ $p->id }} ? null : {{ $p->id }})" class="hover:bg-white/5 transition-colors group cursor-pointer {{ !$p->is_active ? 'opacity-50' : '' }}">
                            <!-- PRODUK (Nama, Kode, Deskripsi/Sub-name) -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ $p->nama_barang }}</span>
                                    <span class="font-mono text-[10px] text-primary/80 mt-0.5">{{ $p->kode_barang }}</span>
                                    @if($p->nama_produk && $p->nama_produk !== $p->nama_barang)
                                        <span class="text-[10px] text-on-surface-variant/50 mt-0.5">{{ $p->nama_produk }}</span>
                                    @endif
                                    @if(!$p->is_active)
                                        <span class="inline-block w-max mt-1 px-2 py-0.5 bg-error/10 text-error rounded text-[9px] font-bold uppercase tracking-wider">Non-Aktif</span>
                                    @endif
                                </div>
                            </td>
                            <!-- KATEGORI & DETAIL (Kategori, Varian, Varian attributes) -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-on-surface font-semibold text-xs">{{ $p->kategori }}</span>
                                    @if($p->kategori_2)
                                        <span class="text-[10px] text-on-surface-variant/60 mt-0.5">{{ $p->kategori_2 }}</span>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="px-2 py-0.5 bg-white/5 border border-white/10 rounded-md text-[10px] text-on-surface-variant font-mono">{{ $p->variasi_barang }}</span>
                                        @if($p->warna || $p->size)
                                            <span class="text-[10px] text-on-surface-variant/40">
                                                ({{ $p->warna ?: '-' }} / {{ $p->size ?: '-' }})
                                            </span>
                                        @endif
                                                           </div>
                            </td>
                            <!-- LOKASI (Badge - Clickable to Expand Detail Row) -->
                            <td class="px-6 py-4">
                                @php
                                    $stokLokasi = $p->stokLokasi->filter(fn($sl) => $sl->lokasi !== null && $sl->qty > 0);
                                    $lokasiCount = $stokLokasi->count();
                                @endphp
                                <div class="group/loc flex flex-wrap items-center gap-1.5 transition-all">
                                    @if($lokasiCount > 0)
                                        @foreach($stokLokasi->take(2) as $sl)
                                            @php 
                                                $loc = $sl->lokasi; 
                                                $isHighlighted = $selectedLokasi && $selectedLokasi->id === $loc->id;
                                            @endphp
                                            @if(str_contains(strtoupper($loc->kode_lokasi), 'TKO') || str_contains(strtoupper($loc->kode_lokasi), 'TOKO'))
                                                <span class="px-2.5 py-0.5 bg-tertiary/10 text-tertiary border {{ $isHighlighted ? 'border-tertiary ring-1 ring-tertiary/50 font-black' : 'border-tertiary/20' }} rounded-full text-[10px] font-bold uppercase tracking-wider" title="{{ $loc->nama_lokasi }}: {{ $sl->qty }} {{ $p->satuan }}">{{ $loc->kode_lokasi }} ({{ $sl->qty }})</span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-primary/10 text-primary border {{ $isHighlighted ? 'border-primary ring-1 ring-primary/50 font-black' : 'border-primary/20' }} rounded-full text-[10px] font-bold uppercase tracking-wider" title="{{ $loc->nama_lokasi }}: {{ $sl->qty }} {{ $p->satuan }}">{{ $loc->kode_lokasi }} ({{ $sl->qty }})</span>
                                            @endif
                                        @endforeach
                                        @if($lokasiCount > 2)
                                            <span class="px-2 py-0.5 bg-white/10 text-on-surface-variant border border-white/10 rounded-full text-[10px] font-bold">+{{ $lokasiCount - 2 }}</span>
                                        @endif
                                    @else
                                        @if(str_contains(strtoupper($p->lokasi), 'TKO') || str_contains(strtoupper($p->lokasi), 'TOKO'))
                                            <span class="px-2.5 py-0.5 bg-tertiary/10 text-tertiary border border-tertiary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ $p->lokasi }}</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ $p->lokasi }}</span>
                                        @endif
                                    @endif
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant/40 group-hover/loc:text-primary transition-transform duration-200" :class="expandedId === {{ $p->id }} ? 'rotate-180 text-primary' : ''">
                                        expand_more
                                    </span>
                                </div>
                            </td>
                            <!-- MUTASI (Masuk / Keluar) -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center justify-center space-y-1">
                                    <div class="flex items-center text-green-400 gap-1 text-[10px] font-bold" title="Stok Masuk">
                                        <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                                        <span>{{ number_format($p->stok_masuk, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center text-error gap-1 text-[10px] font-bold" title="Stok Keluar">
                                        <span class="material-symbols-outlined text-[14px]">arrow_downward</span>
                                        <span>{{ number_format($p->stok_keluar, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </td>
                            <!-- TOTAL STOK -->
                            <td class="px-6 py-4 text-right">
                                @if($selectedLokasi)
                                    @php
                                        $currentLokasiItem = $p->stokLokasi->first(fn($sl) => $sl->lokasi_id === $selectedLokasi->id);
                                        $qtyAtLokasi = $currentLokasiItem ? $currentLokasiItem->qty : 0;
                                    @endphp
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-black {{ $qtyAtLokasi <= 5 ? 'text-error' : 'text-primary' }}">
                                            {{ number_format($qtyAtLokasi, 0, ',', '.') }}
                                            <span class="text-[9px] font-bold text-on-surface-variant/70 uppercase">{{ $p->satuan }}</span>
                                        </span>
                                        <span class="text-[9px] text-on-surface-variant/50 mt-0.5">
                                            Total Semua: <strong class="text-on-surface-variant/80 font-mono">{{ number_format($p->total_stok, 0, ',', '.') }}</strong>
                                        </span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-end">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-black {{ $p->isLowStock() ? 'text-error' : 'text-primary' }}">{{ number_format($p->total_stok, 0, ',', '.') }}</span>
                                            <span class="text-[9px] text-on-surface-variant/40 uppercase font-bold tracking-wider">{{ $p->satuan }}</span>
                                        </div>
                                        @if($p->is_threshold_active)
                                            <div class="mt-1 flex items-center gap-1">
                                                @if($p->isLowStock())
                                                    <span class="px-1.5 py-0.5 rounded bg-error/20 border border-error/30 text-error text-[9px] font-black uppercase tracking-wider animate-pulse flex items-center gap-0.5" title="Di bawah batas minimum: {{ $p->min_stok }}">
                                                        <span class="material-symbols-outlined text-[10px]">warning</span> Menipis (Min: {{ $p->min_stok }})
                                                    </span>
                                                @else
                                                    <span class="px-1.5 py-0.5 rounded bg-white/5 border border-white/10 text-on-surface-variant/70 text-[9px] font-mono" title="Ambang Batas Minimum: {{ $p->min_stok }}">
                                                        Min: {{ $p->min_stok }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <!-- HARGA (Beli / Jual) -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-xs text-on-surface-variant">Beli: <span class="font-mono text-on-surface">Rp{{ number_format($p->hpp, 0, ',', '.') }}</span></span>
                                    <span class="text-[10px] text-primary mt-0.5 font-semibold">Jual: <span class="font-mono">Rp{{ number_format($p->harga_jual, 0, ',', '.') }}</span></span>
                                </div>
                            </td>
                            <td @click.stop class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="showBarcode({{ $p->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Barcode">
                                        <span class="material-symbols-outlined text-lg">barcode_scanner</span>
                                    </button>
                                    @can('manage master')
                                    <button wire:click="edit({{ $p->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="destroy({{ $p->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" title="Delete">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                                      @php
                            $stokPerLokasi = $p->stokLokasi->filter(fn($sl) => $sl->lokasi !== null && $sl->qty > 0)->sortByDesc('qty');
                            $mutasiTerakhir = $p->mutasiLokasi->take(5);
                            $totalStokExpanded = $stokPerLokasi->sum('qty');
                        @endphp
                        <tr x-show="expandedId === {{ $p->id }}" x-cloak class="bg-surface-container-low/20 border-b border-white/5">
                            <td colspan="7" class="px-8 py-5">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-on-surface">
                                    
                                    <!-- Column 1: Stok per Lokasi -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="material-symbols-outlined text-primary text-base">location_on</span>
                                            <span class="text-xs font-black uppercase tracking-wider text-on-surface-variant/80">Distribusi Stok per Lokasi</span>
                                        </div>
                                        @if($stokPerLokasi->count() > 0)
                                            <div class="space-y-2">
                                                @foreach($stokPerLokasi as $sl)
                                                    @php
                                                        $pct = $totalStokExpanded > 0 ? round(($sl->qty / $totalStokExpanded) * 100) : 0;
                                                        $isToko = str_contains(strtoupper($sl->lokasi->tipe_lokasi), 'TOKO');
                                                    @endphp
                                                    <div class="bg-white/5 border border-white/5 rounded-xl p-3">
                                                        <div class="flex items-center justify-between mb-1.5">
                                                            <div class="flex items-center gap-2">
                                                                <span class="material-symbols-outlined text-xs {{ $isToko ? 'text-tertiary' : 'text-primary' }}">{{ $isToko ? 'storefront' : 'warehouse' }}</span>
                                                                <span class="text-xs font-bold">{{ $sl->lokasi->kode_lokasi }}</span>
                                                                <span class="text-[9px] text-on-surface-variant/40">({{ $sl->lokasi->tipe_lokasi }})</span>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="text-xs font-bold">{{ number_format($sl->qty, 0, ',', '.') }}</span>
                                                                <span class="text-[9px] text-on-surface-variant/50 ml-1">({{ $pct }}%)</span>
                                                            </div>
                                                        </div>
                                                        <!-- Progress -->
                                                        <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                                                            <div class="h-full {{ $isToko ? 'bg-tertiary' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-on-surface-variant/50 italic py-2">Belum ada data stok per lokasi.</p>
                                        @endif
                                    </div>

                                    <!-- Column 2: Harga & Margin -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="material-symbols-outlined text-primary text-base">payments</span>
                                            <span class="text-xs font-black uppercase tracking-wider text-on-surface-variant/80">Informasi Harga</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="bg-white/5 border border-white/5 rounded-xl p-3">
                                                <span class="block text-[9px] text-on-surface-variant/50 uppercase font-bold tracking-wider mb-0.5">Beli (HPP)</span>
                                                <span class="text-xs font-bold font-mono">Rp{{ number_format($p->hpp, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="bg-white/5 border border-white/5 rounded-xl p-3">
                                                <span class="block text-[9px] text-on-surface-variant/50 uppercase font-bold tracking-wider mb-0.5">Harga Jual</span>
                                                <span class="text-xs font-bold text-primary font-mono">Rp{{ number_format($p->harga_jual, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        @php
                                            $margin = $p->harga_jual > 0 && $p->hpp > 0 ? round((($p->harga_jual - $p->hpp) / $p->hpp) * 100, 1) : 0;
                                        @endphp
                                        <div class="bg-white/5 border border-white/5 rounded-xl p-3 flex items-center justify-between">
                                            <div>
                                                <span class="block text-[9px] text-on-surface-variant/50 uppercase font-bold tracking-wider">Margin Keuntungan</span>
                                                <span class="text-[10px] text-on-surface-variant/40">Selisih: Rp{{ number_format($p->harga_jual - $p->hpp, 0, ',', '.') }}</span>
                                            </div>
                                            <span class="text-xs font-black text-green-400 font-mono">{{ $margin }}%</span>
                                        </div>
                                    </div>

                                    <!-- Column 3: Mutasi & Riwayat Transaksi -->
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="material-symbols-outlined text-primary text-base">history</span>
                                            <span class="text-xs font-black uppercase tracking-wider text-on-surface-variant/80">Riwayat Mutasi Terakhir</span>
                                        </div>
                                        @if($mutasiTerakhir->count() > 0)
                                            <div class="space-y-1.5 max-h-48 overflow-y-auto custom-scrollbar pr-1">
                                                @foreach($mutasiTerakhir as $m)
                                                    <div class="bg-white/5 border border-white/5 rounded-xl p-2.5 text-[10px]">
                                                        <div class="flex items-center gap-1">
                                                            <span class="font-bold text-on-surface">{{ $m->lokasiAsal?->kode_lokasi ?? '-' }}</span>
                                                            <span class="material-symbols-outlined text-[10px] text-primary">arrow_forward</span>
                                                            <span class="font-bold text-on-surface">{{ $m->lokasiTujuan?->kode_lokasi ?? '-' }}</span>
                                                            <span class="ml-auto font-black text-primary">{{ $m->qty }} unit</span>
                                                        </div>
                                                        <div class="flex justify-between text-[9px] text-on-surface-variant/40 mt-1">
                                                            <span>{{ $m->created_at->format('d/m/Y H:i') }} WIB</span>
                                                            <span>{{ $m->tipe_mutasi }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-on-surface-variant/50 italic py-2">Belum ada riwayat mutasi lokasi.</p>
                                        @endif
                                    </div>

                                </div>

                                <!-- Section 4: Pengaturan Ambang Batas & PIC WhatsApp (Card Lebar Horizontal) -->
                                <div @click.stop 
                                     x-data="{ 
                                        saved: false, 
                                        saving: false,
                                        isActive: {{ $p->is_threshold_active ? 'true' : 'false' }},
                                        minStok: '{{ $p->min_stok ?? '' }}',
                                        notifPhone: '{{ $p->notif_phone ?? '' }}',
                                        timeout: null,
                                        triggerSave(field, val) {
                                            this.saving = true;
                                            this.saved = false;
                                            if (field === 'is_threshold_active' && val && (!this.minStok || parseInt(this.minStok) <= 0)) {
                                                this.minStok = '{{ $p->kategori_2 === "TOP 20" ? "20" : "10" }}';
                                            }
                                            $wire.updateThresholdInline({{ $p->id }}, field, val).then(() => {
                                                this.saving = false;
                                                this.saved = true;
                                                clearTimeout(this.timeout);
                                                this.timeout = setTimeout(() => { this.saved = false; }, 3000);
                                            });
                                        }
                                     }"
                                     class="mt-6 p-5 rounded-2xl bg-gradient-to-r from-surface-container-low/90 to-surface-container/70 border border-white/10 shadow-lg relative overflow-hidden space-y-4">
                                    
                                    <!-- Header Card -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                                <span class="material-symbols-outlined text-lg">notifications_active</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black uppercase tracking-wider text-on-surface">PENGATURAN AMBANG BATAS & PIC WHATSAPP</span>
                                                    @if($p->kategori_2 === 'TOP 20')
                                                        <span class="px-2 py-0.5 bg-primary/20 text-primary border border-primary/30 rounded text-[9px] font-bold uppercase tracking-wider">TOP 20</span>
                                                    @endif
                                                </div>
                                                <p class="text-[10px] text-on-surface-variant/60 mt-0.5">Pantau batas minimum stok produk ini dan hubungkan dengan nomor PIC penerima peringatan WhatsApp.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <!-- Inline Auto-Save Feedback -->
                                            <div class="h-6 flex items-center">
                                                <span x-show="saving" x-cloak class="text-[11px] text-amber-400 flex items-center gap-1.5 font-mono">
                                                    <div class="w-3 h-3 border-2 border-amber-400 border-t-transparent rounded-full animate-spin"></div>
                                                    Menyimpan...
                                                </span>
                                                <span x-show="saved" x-cloak class="text-[11px] text-emerald-400 flex items-center gap-1 font-mono font-bold transition-all">
                                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                                    Tersimpan otomatis
                                                </span>
                                            </div>

                                            <!-- Status Pill -->
                                            @if($p->isLowStock())
                                                <span class="px-2.5 py-1 rounded-full bg-error/20 border border-error/30 text-error text-[10px] font-black uppercase tracking-wider animate-pulse flex items-center gap-1" title="Stok di bawah batas minimum!">
                                                    <span class="material-symbols-outlined text-xs">warning</span>
                                                    Menipis (Defisit {{ max(0, $p->min_stok - $p->total_stok) }} {{ $p->satuan }})
                                                </span>
                                            @elseif($p->is_threshold_active)
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1" title="Stok aman di atas batas minimum">
                                                    <span class="material-symbols-outlined text-xs">verified</span>
                                                    Stok Aman ({{ $p->total_stok }} {{ $p->satuan }})
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-on-surface-variant/50 text-[10px] font-bold uppercase tracking-wider">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Body Form Inputs Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                                        <!-- Field 1: Toggle Switch -->
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold block">Status Pemantauan</label>
                                            <div class="h-10 px-3 bg-surface-dark/60 border border-white/10 rounded-xl flex items-center justify-between">
                                                <span class="text-xs text-on-surface font-semibold">Pantau Ambang Batas</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" x-model="isActive" @change="triggerSave('is_threshold_active', isActive)" class="sr-only peer">
                                                    <div class="w-9 h-5 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Field 2: Minimum Stock (Qty) -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold block">Batas Minimum (Qty)</label>
                                                <span class="text-[9px] text-on-surface-variant/50 font-mono">{{ $p->satuan }}</span>
                                            </div>
                                            <div class="relative">
                                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">inventory_2</span>
                                                <input type="number" 
                                                       min="1" 
                                                       x-model="minStok" 
                                                       @input.debounce.500ms="triggerSave('min_stok', minStok)" 
                                                       :disabled="!isActive" 
                                                       placeholder="{{ $p->kategori_2 === 'TOP 20' ? '20' : '10' }}" 
                                                       class="w-full h-10 bg-surface-dark/60 border border-white/10 rounded-xl py-2 pl-9 pr-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary font-mono transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                                            </div>
                                        </div>

                                        <!-- Field 3: PIC WhatsApp Phone -->
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold block">Nomor WA PIC (Opsional)</label>
                                                <span class="text-[9px] text-emerald-400 font-mono">Fonnte</span>
                                            </div>
                                            <div class="relative">
                                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">call</span>
                                                <input type="text" 
                                                       x-model="notifPhone" 
                                                       @input.debounce.500ms="triggerSave('notif_phone', notifPhone)" 
                                                       :disabled="!isActive" 
                                                       placeholder="08xxxxxxxxxx (Default Sistem)" 
                                                       class="w-full h-10 bg-surface-dark/60 border border-white/10 rounded-xl py-2 pl-9 pr-3 text-xs text-on-surface focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 font-mono transition-all disabled:opacity-40 disabled:cursor-not-allowed placeholder:text-on-surface-variant/30">
                                            </div>
                                        </div>

                                        <!-- Field 4: Quick Action / Info -->
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] uppercase tracking-wider text-on-surface-variant/80 font-bold block">Tindakan Cepat</label>
                                            @if($p->isLowStock())
                                                <button wire:click.stop="openWaModalSingleProduct({{ $p->id }})" 
                                                        type="button" 
                                                        class="w-full h-10 px-4 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/30 rounded-xl text-xs font-bold transition-all active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/10">
                                                    <span class="material-symbols-outlined text-base">chat</span>
                                                    <span>Kirim WA Produk Ini</span>
                                                </button>
                                            @else
                                                <div class="h-10 px-3 bg-white/5 border border-white/5 rounded-xl flex items-center gap-2 text-on-surface-variant/50 text-[10px]">
                                                    <span class="material-symbols-outlined text-sm text-emerald-400/70">auto_mode</span>
                                                    <span>Tersimpan otomatis saat diedit.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8">
                                <x-empty-state icon="inventory_2" title="Tidak Ada Produk" description="Tidak ada produk ditemukan yang sesuai dengan kriteria pencarian Anda." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination & Per Page Selector -->
        <div class="px-6 py-4 border-t border-white/5 bg-white/[0.01] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                    <span class="font-medium text-slate-400">Tampilkan:</span>
                    <select wire:model.live="perPage" class="bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-3 text-xs text-on-surface font-semibold focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                        <option value="100">100 per halaman</option>
                    </select>
                </div>
                <p class="text-xs text-on-surface-variant font-mono">
                    Menampilkan {{ $products->firstItem() ?? 0 }} s.d. {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} hasil
                </p>
            </div>
            <div class="flex gap-2 items-center">
                @if ($products->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                    @if ($page == $products->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($products->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- CRUD Modal (Rebuilt Wide 2-Column Layout) -->
    @if ($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none p-4 md:p-6">
        <!-- Backdrop with Blur -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

        <!-- Modal Content (max-w-4xl for wide, comfortable 2-column layout) -->
        <div class="relative w-full max-w-4xl mx-auto my-auto z-50">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/10 bg-white/[0.02]">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-inner">
                            <span class="material-symbols-outlined text-xl">inventory_2</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                                <span>{{ $isEdit ? 'Ubah Data Produk' : 'Tambah Produk Baru' }}</span>
                                @if($isEdit)
                                    <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary border border-primary/20 font-mono text-[10px] font-bold">Edit Mode</span>
                                @endif
                            </h3>
                            <p class="text-xs text-on-surface-variant/70">Lengkapi data master produk, spesifikasi varian, penetapan harga, dan pemantauan stok.</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-all cursor-pointer border border-white/5">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body: 2-Column Responsive Grid -->
                <div class="p-6 max-h-[78vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- LEFT COLUMN: Identitas & Varian Produk -->
                        <div class="space-y-5">
                            
                            <!-- Card 1: Identitas & Klasifikasi -->
                            <div class="p-5 rounded-2xl bg-surface-container-low/60 border border-white/5 space-y-4">
                                <div class="flex items-center gap-2 pb-2.5 border-b border-white/5">
                                    <span class="material-symbols-outlined text-primary text-base">category</span>
                                    <span class="text-xs font-bold text-on-surface uppercase tracking-wider">Identitas & Klasifikasi</span>
                                </div>

                                <!-- Nama Model Utama -->
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                        Nama Produk (Model Utama) <span class="text-primary">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="nama_produk" type="text" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30 font-medium" placeholder="Contoh: Sandal Slide Black">
                                    @if($errors->has('nama_produk')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('nama_produk') }}</span> @endif
                                </div>

                                <!-- Nama Display Rak -->
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                        Nama Display (Etalase / Rak) <span class="text-primary">*</span>
                                    </label>
                                    <input wire:model="nama_barang" type="text" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30 font-medium" placeholder="Contoh: Sandal Slide Hitam Kasual">
                                    @if($errors->has('nama_barang')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('nama_barang') }}</span> @endif
                                </div>

                                <!-- Kategori 1 & Kategori 2 in 2-Col -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                            Kategori (1) <span class="text-primary">*</span>
                                        </label>
                                        <select wire:model.live="kategori" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer">
                                            <option value="">Pilih Kategori</option>
                                            <option value="Sandal Anak">Sandal Anak</option>
                                            <option value="Sepatu Anak">Sepatu Anak</option>
                                            <option value="Sandal Dewasa">Sandal Dewasa</option>
                                            <option value="Sepatu Dewasa">Sepatu Dewasa</option>
                                            <option value="Tas">Tas</option>
                                            <option value="Aksesoris">Aksesoris</option>
                                            <option value="Apparel">Apparel</option>
                                            <option value="Packaging">Packaging</option>
                                        </select>
                                        @if($errors->has('kategori')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('kategori') }}</span> @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                            Status Siklus (Kategori 2)
                                        </label>
                                        <select wire:model.live="kategori_2" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer">
                                            <option value="">Pilih Status Siklus</option>
                                            <option value="TOP 20">TOP 20</option>
                                            <option value="On Hold">On Hold</option>
                                            <option value="Produk Berjalan">Produk Berjalan</option>
                                            <option value="Deadstock">Deadstock</option>
                                        </select>
                                        @if($errors->has('kategori_2')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('kategori_2') }}</span> @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Varian & SKU Otomatis -->
                            <div class="p-5 rounded-2xl bg-surface-container-low/60 border border-white/5 space-y-4">
                                <div class="flex items-center gap-2 pb-2.5 border-b border-white/5">
                                    <span class="material-symbols-outlined text-primary text-base">palette</span>
                                    <span class="text-xs font-bold text-on-surface uppercase tracking-wider">Spesifikasi Varian & Kode SKU</span>
                                </div>

                                <!-- Warna & Size -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Warna</label>
                                        <input wire:model.live.debounce.300ms="warna" type="text" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: Hitam">
                                        @if($errors->has('warna')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('warna') }}</span> @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Ukuran / Size</label>
                                        <input wire:model.live.debounce.300ms="size" type="text" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: 40">
                                        @if($errors->has('size')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('size') }}</span> @endif
                                    </div>
                                </div>

                                <!-- Variasi Otomatis & Satuan -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Variasi (Otomatis)</label>
                                        <div class="relative flex items-center">
                                            <input wire:model="variasi_barang" type="text" class="w-full bg-white/[0.03] border border-white/10 rounded-xl py-2.5 pl-3 pr-8 text-xs text-primary font-mono font-semibold transition-all" placeholder="Hitam / 40" readonly>
                                            <span class="material-symbols-outlined absolute right-2.5 text-sm text-primary/60" title="Terisi otomatis dari warna & ukuran">auto_fix_high</span>
                                        </div>
                                        @if($errors->has('variasi_barang')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('variasi_barang') }}</span> @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Satuan</label>
                                        <input wire:model="satuan" type="text" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="pasang / pcs">
                                        @if($errors->has('satuan')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('satuan') }}</span> @endif
                                    </div>
                                </div>

                                <!-- SKU Smart Card -->
                                <div class="p-3.5 rounded-xl bg-surface-container border border-white/10 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-sm text-primary">qr_code_2</span>
                                            Kode Barang (SKU) <span class="text-primary">*</span>
                                        </label>
                                        @if(!$isEdit)
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/10 border border-primary/25 text-[11px] text-primary font-bold cursor-pointer select-none hover:bg-primary/20 transition-all">
                                            <input type="checkbox" wire:model.live="autoGenerateSku" class="h-3.5 w-3.5 rounded bg-surface-container border-white/20 text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer" />
                                            Auto-Generate SKU
                                        </label>
                                        @endif
                                    </div>

                                    <div class="relative flex items-center">
                                        <input wire:model="kode_barang" type="text" class="w-full {{ ($isEdit || $autoGenerateSku) ? 'bg-primary/[0.04] text-primary border-primary/30 font-bold' : 'bg-surface-container-low text-on-surface border-white/10' }} border rounded-xl py-2.5 px-3.5 text-xs font-mono tracking-wider focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Contoh: SDL-SLI-BLK-001" {{ ($isEdit || $autoGenerateSku) ? 'readonly' : '' }}>
                                    </div>

                                    @if($autoGenerateSku && !$isEdit)
                                        <div class="flex items-center gap-1.5 text-[10px] text-primary/80 pt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                            <span>SKU otomatis mengikuti format kategori terpilih pada menu Pengkodean.</span>
                                        </div>
                                    @endif
                                    @if($errors->has('kode_barang')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('kode_barang') }}</span> @endif
                                </div>

                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Lokasi, Finansial & Ambang Batas -->
                        <div class="space-y-5">
                            
                            <!-- Card 3: Lokasi & Finansial -->
                            <div class="p-5 rounded-2xl bg-surface-container-low/60 border border-white/5 space-y-4">
                                <div class="flex items-center gap-2 pb-2.5 border-b border-white/5">
                                    <span class="material-symbols-outlined text-primary text-base">payments</span>
                                    <span class="text-xs font-bold text-on-surface uppercase tracking-wider">Lokasi & Finansial</span>
                                </div>

                                <!-- Lokasi Default -->
                                <div>
                                    <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                        Lokasi Simpan Default <span class="text-primary">*</span>
                                    </label>
                                    <div class="relative">
                                        <select wire:model="lokasi" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer">
                                            @foreach ($locationsList as $loc)
                                                <option value="{{ $loc->kode_lokasi }}">{{ $loc->kode_lokasi }} — {{ $loc->nama_lokasi }} ({{ $loc->tipe_lokasi }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if($errors->has('lokasi')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('lokasi') }}</span> @endif
                                </div>

                                <!-- HPP & Harga Jual in 2-Col -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                            HPP Beli (Rp) <span class="text-primary">*</span>
                                        </label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-xs font-mono font-bold text-on-surface-variant/50">Rp</span>
                                            <input wire:model.live.debounce.300ms="hpp" type="number" min="0" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 pl-9 pr-3 text-xs text-on-surface font-mono font-medium focus:ring-1 focus:ring-primary focus:border-primary transition-all {{ $isEdit ? 'opacity-60 cursor-not-allowed' : '' }}" placeholder="0" {{ $isEdit ? 'readonly' : '' }}>
                                        </div>
                                        @if($errors->has('hpp')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('hpp') }}</span> @endif
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                            Harga Jual (Rp) <span class="text-primary">*</span>
                                        </label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-xs font-mono font-bold text-primary">Rp</span>
                                            <input wire:model.live.debounce.300ms="harga_jual" type="number" min="0" class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 pl-9 pr-3 text-xs text-on-surface font-mono font-bold focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="0">
                                        </div>
                                        @if($errors->has('harga_jual')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('harga_jual') }}</span> @endif
                                    </div>
                                </div>

                                <!-- Profit & Margin Live Calculator Pill -->
                                @php
                                    $hppNum = (float)($hpp ?? 0);
                                    $jualNum = (float)($harga_jual ?? 0);
                                    $profitNominal = $jualNum - $hppNum;
                                    $marginPercent = $hppNum > 0 ? round(($profitNominal / $hppNum) * 100, 1) : ($jualNum > 0 ? 100 : 0);
                                    $isProfitable = $profitNominal >= 0;
                                @endphp
                                <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base {{ $isProfitable ? 'text-emerald-400' : 'text-error' }}">
                                            {{ $isProfitable ? 'trending_up' : 'trending_down' }}
                                        </span>
                                        <span class="text-on-surface-variant font-medium">Estimasi Margin:</span>
                                        <strong class="{{ $isProfitable ? 'text-emerald-400' : 'text-error' }} font-bold font-mono">
                                            {{ $marginPercent > 0 ? '+' : '' }}{{ $marginPercent }}%
                                        </strong>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-on-surface-variant/60 block">Profit per Unit:</span>
                                        <strong class="{{ $isProfitable ? 'text-emerald-400' : 'text-error' }} font-mono font-bold">
                                            Rp {{ number_format($profitNominal, 0, ',', '.') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Pemantauan Ambang Batas & WhatsApp PIC -->
                            <div class="p-5 rounded-2xl bg-surface-container-low/60 border border-white/5 space-y-4">
                                <div class="flex items-center justify-between pb-2.5 border-b border-white/5">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-emerald-400 text-base">notifications_active</span>
                                        <span class="text-xs font-bold text-on-surface uppercase tracking-wider">Pemantauan Ambang Batas</span>
                                    </div>
                                    @if($kategori_2 === 'TOP 20')
                                        <span class="px-2 py-0.5 rounded-full bg-primary/20 text-primary border border-primary/30 text-[9px] font-extrabold uppercase">TOP 20 Auto</span>
                                    @endif
                                </div>

                                <!-- Toggle Pantau Ambang Batas -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-surface-container border border-white/10">
                                    <div>
                                        <p class="text-xs font-bold text-on-surface">Pantau Ambang Batas Stok</p>
                                        <p class="text-[10px] text-on-surface-variant/60">Picu notifikasi otomatis saat stok mendekati batas minimum.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="is_threshold_active" class="sr-only peer">
                                        <div class="w-10 h-5 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>

                                <!-- Detail Ambang Batas & WA PIC (Saat Aktif) -->
                                @if($is_threshold_active)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                                        <div>
                                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">
                                                Batas Min. Stok (Qty) <span class="text-emerald-400">*</span>
                                            </label>
                                            <div class="relative flex items-center">
                                                <span class="material-symbols-outlined absolute left-3 text-sm text-emerald-400">inventory_2</span>
                                                <input wire:model="min_stok" type="number" min="1" class="w-full bg-surface-dark border border-white/10 rounded-xl py-2.5 pl-9 pr-3 text-xs text-on-surface font-mono font-bold focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400 transition-all" placeholder="Contoh: 10">
                                            </div>
                                            @if($errors->has('min_stok')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('min_stok') }}</span> @endif
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-1.5">
                                                <label class="text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider">No. WA PIC</label>
                                                <span class="text-[9px] text-emerald-400 font-mono font-semibold">Fonnte WA</span>
                                            </div>
                                            <div class="relative flex items-center">
                                                <span class="material-symbols-outlined absolute left-3 text-sm text-emerald-400">call</span>
                                                <input wire:model="notif_phone" type="text" class="w-full bg-surface-dark border border-white/10 rounded-xl py-2.5 pl-9 pr-3 text-xs text-on-surface font-mono focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400 transition-all placeholder:text-on-surface-variant/30" placeholder="08xxxxxxxxxx">
                                            </div>
                                            @if($errors->has('notif_phone')) <span class="text-[11px] text-error mt-1 block font-medium">{{ $errors->first('notif_phone') }}</span> @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Status Aktif Produk -->
                                <div class="flex items-center justify-between pt-2 border-t border-white/5">
                                    <div>
                                        <p class="text-xs font-bold text-on-surface">Status Keaktifan Produk</p>
                                        <p class="text-[10px] text-on-surface-variant/60">Produk aktif dapat dipilih dalam transaksi penjualan dan pembelian.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div class="w-10 h-5 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between px-8 py-4.5 border-t border-white/10 bg-surface-container-low/80 gap-3">
                    <p class="text-[11px] text-on-surface-variant/60 hidden sm:block">
                        <span class="text-primary">*</span> Kolom bertanda bintang wajib diisi sebelum menyimpan.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button wire:click="closeModal" type="button" class="px-5 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-xs font-semibold transition-all text-on-surface cursor-pointer">
                            Batal
                        </button>
                        <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-xl font-bold text-xs shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong cursor-pointer flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="store">
                            <span class="material-symbols-outlined text-sm font-bold" wire:loading.remove wire:target="store">check</span>
                            <div class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-on-primary-container border-t-transparent" wire:loading wire:target="store"></div>
                            <span wire:loading.remove wire:target="store">{{ $isEdit ? 'Perbarui Produk' : 'Simpan Produk Baru' }}</span>
                            <span wire:loading wire:target="store">Menyimpan...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- Barcode Modal -->
    @if ($isBarcodeOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeBarcodeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-sm mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">Label Barcode Produk</h3>
                    <button wire:click="closeBarcodeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col items-center justify-center text-center space-y-4" id="barcode-print-area">
                    <div class="bg-white p-4 border border-gray-200 rounded-2xl shadow-inner flex items-center justify-center max-w-full overflow-hidden">
                        {!! \App\Helpers\Barcode128::getTagSvg($barcodeProductSku, $barcodeProductName, $barcodeProductVariation) !!}
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end px-6 py-5 border-t border-white/5 gap-2 bg-surface-container-low/50">
                    <button wire:click="closeBarcodeModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                    <button onclick="printBarcodeLabel('{{ $barcodeProductSku }}')" type="button" class="px-4 py-2 bg-primary-container text-on-primary-container text-sm font-bold rounded-2xl hover:bg-primary-container/80 transition-all flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Cetak
                    </button>
                    <button wire:click="downloadBarcode('{{ $barcodeProductSku }}')" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">download</span>
                        SVG
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Import Excel Modal -->
    @if ($isImportOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeImportModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-lg mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">Impor Produk via Excel</h3>
                    <button wire:click="closeImportModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Instructions -->
                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-4 text-xs text-on-surface-variant space-y-2">
                        <p class="font-bold text-primary text-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">info</span> Petunjuk Pengisian Template
                        </p>
                        <ol class="list-decimal pl-4 space-y-1">
                            <li>Unduh template Excel yang telah disediakan menggunakan tombol di bawah.</li>
                            <li>Gunakan kolom-kolom template secara persis dan jangan diubah namanya.</li>
                            <li>Kolom <strong>Kode Barang</strong> (SKU) harus unik dan belum terdaftar di sistem.</li>
                            <li>Kolom <strong>Lokasi Default</strong> harus diisi <code class="px-1.5 py-0.5 bg-white/5 rounded font-bold text-primary">GUDANG</code> atau <code class="px-1.5 py-0.5 bg-white/5 rounded font-bold text-primary">TOKO</code>.</li>
                            <li>Kolom <strong>HPP per Pcs</strong> dan <strong>Stok Awal</strong> harus berupa angka >= 0.</li>
                        </ol>
                        <div class="pt-2">
                            <button wire:click="downloadTemplate" type="button" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary/10 border border-primary/20 text-primary font-bold rounded-xl text-xs hover:bg-primary/20 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-sm">download</span>
                                Unduh Template Excel
                            </button>
                        </div>
                    </div>

                    <!-- File Input -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        
                        <div class="relative group border-2 border-dashed border-white/10 hover:border-primary/30 rounded-2xl transition-all duration-300 p-8 flex flex-col items-center justify-center text-center cursor-pointer">
                            <input type="file" wire:model="importFile" class="absolute inset-0 opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv" id="excel-file-selector" />
                            
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 group-hover:text-primary transition-colors duration-300 mb-2">cloud_upload</span>
                            
                            @if ($importFile)
                                <p class="text-sm font-semibold text-primary truncate max-w-xs">{{ $importFile->getClientOriginalName() }}</p>
                                <p class="text-[10px] text-on-surface-variant/60 mt-1">Ukuran: {{ number_format($importFile->getSize() / 1024, 1) }} KB</p>
                            @else
                                <p class="text-sm font-semibold text-on-surface-variant group-hover:text-on-surface transition-colors duration-300">Klik atau seret file ke sini</p>
                                <p class="text-[10px] text-on-surface-variant/40 mt-1">Ukuran file maksimal: 10MB</p>
                            @endif
                        </div>
                        
                        @if($errors->has('importFile')) 
                            <span class="text-xs text-error mt-1.5 block font-medium">{{ $errors->first('importFile') }}</span> 
                        @endif
                    </div>

                    <!-- Validation Errors List -->
                    @if (count($importErrors) > 0)
                        <div class="bg-error/5 border border-error/20 rounded-2xl p-4 text-xs space-y-2">
                            <p class="font-bold text-error flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">error</span> Kesalahan Validasi File Excel:
                            </p>
                            <ul class="list-disc pl-4 space-y-1 text-on-surface-variant max-h-40 overflow-y-auto custom-scrollbar">
                                @foreach ($importErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeImportModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="importExcel" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="importExcel">
                        <span class="material-symbols-outlined text-sm" wire:loading.remove wire:target="importExcel">upload</span>
                        <div class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-on-primary-container border-t-transparent" wire:loading wire:target="importExcel"></div>
                        <span wire:loading.remove wire:target="importExcel">Impor Sekarang</span>
                        <span wire:loading wire:target="importExcel">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- WhatsApp Stock Alert Modal -->
    @if ($isWaModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeWaModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-xl mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <span class="material-symbols-outlined text-2xl">chat</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-on-surface">Peringatan Stok Menipis WhatsApp</h3>
                            <p class="text-[11px] text-on-surface-variant/70">{{ $lowStockCount }} produk aktif di bawah ambang batas minimum</p>
                        </div>
                    </div>
                    <button wire:click="closeWaModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Target Phone Input -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">
                            Nomor WhatsApp Tujuan (PIC / Tim Gudang)
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">call</span>
                            <input wire:model.live.debounce.300ms="waTargetPhone" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-11 pr-4 text-sm font-mono text-on-surface focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder:text-on-surface-variant/30" placeholder="08xxxxxxxxxx atau 628xxxxxxxxxx">
                        </div>
                        @if($errors->has('waTargetPhone')) <span class="text-xs text-error mt-1 block font-medium">{{ $errors->first('waTargetPhone') }}</span> @endif
                    </div>

                    <!-- Message Preview Box -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider">
                                Preview Pesan Notifikasi
                            </label>
                            <span class="text-[10px] text-emerald-400/80 font-mono">Format Otomatis</span>
                        </div>
                        <div class="relative">
                            <textarea readonly rows="8" class="w-full bg-black/40 border border-emerald-500/20 rounded-2xl p-4 text-xs font-mono text-emerald-400/90 focus:ring-0 leading-relaxed custom-scrollbar resize-none">{{ $waMessagePreview }}</textarea>
                        </div>
                    </div>

                    <!-- Info Tip -->
                    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-3.5 flex items-start gap-3">
                        <span class="material-symbols-outlined text-emerald-400 text-lg shrink-0 mt-0.5">info</span>
                        <div class="text-[11px] text-on-surface-variant leading-relaxed">
                            <p class="font-bold text-emerald-400 mb-0.5">Dua Metode Pengiriman Tersedia:</p>
                            <ul class="list-disc pl-4 space-y-0.5">
                                <li><strong>Buka Chat WhatsApp:</strong> Langsung membuka link wa.me di browser/aplikasi WA Anda tanpa perlu setup API bot.</li>
                                <li><strong>Kirim via Fonnte:</strong> Otomatis dikirim dari background server menggunakan Fonnte Bot (pastikan token <code class="px-1 py-0.2 bg-white/5 rounded text-emerald-300">FONNTE_TOKEN</code> aktif di .env).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeWaModal" type="button" class="w-full sm:w-auto px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full sm:w-auto">
                        <!-- Direct wa.me link -->
                        <a href="{{ $this->waDirectUrl }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 rounded-2xl text-xs font-bold hover:bg-emerald-500/25 transition-all active:scale-95">
                            <span class="material-symbols-outlined text-base">open_in_new</span>
                            Buka Chat WhatsApp
                        </a>

                        <!-- Fonnte API Dispatch -->
                        <button wire:click="sendWaFonnte" type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-black rounded-2xl text-xs font-extrabold shadow-lg hover:shadow-emerald-500/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled" wire:target="sendWaFonnte">
                            <span class="material-symbols-outlined text-base" wire:loading.remove wire:target="sendWaFonnte">send</span>
                            <div class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-black border-t-transparent" wire:loading wire:target="sendWaFonnte"></div>
                            <span wire:loading.remove wire:target="sendWaFonnte">Kirim via Fonnte</span>
                            <span wire:loading wire:target="sendWaFonnte">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function printBarcodeLabel(sku) {
            const svgElement = document.querySelector('#barcode-print-area svg');
            if (!svgElement) return;
            
            const svgHtml = svgElement.outerHTML;
            const printWindow = window.open('', '_blank', 'width=650,height=420');
            
            let html = '<html><head><title>Print Tag - ' + sku + '</title>';
            html += '<style>';
            html += '@page { size: 80mm 50mm; margin: 0; }';
            html += 'html, body { margin: 0; padding: 0; width: 80mm; height: 50mm; display: flex; align-items: center; justify-content: center; background: #fff; }';
            html += 'svg { width: 80mm; height: 50mm; }';
            html += '</style></' + 'head>';
            html += '<body>';
            html += svgHtml;
            html += '<script>window.onload = function() { window.print(); window.close(); };</' + 'script>';
            html += '</' + 'body></' + 'html>';
            
            printWindow.document.write(html);
            printWindow.document.close();
        }
    </script>



</div>
