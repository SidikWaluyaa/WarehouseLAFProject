<div x-init="$dispatch('update-header-title', 'Picking Barang (Outbound)')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar pb-16">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-on-surface font-mono tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-lg shadow-primary/10">
                    <span class="material-symbols-outlined text-2xl">conveyor_belt</span>
                </div>
                <span>Picking Barang (Outbound)</span>
            </h1>
            <p class="text-xs text-on-surface-variant/70 mt-1.5 ml-0.5">
                Manajemen alur pengambilan barang dari rak gudang berbasis prioritas rekomendasi FIFO.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="px-4 py-2.5 rounded-2xl bg-white/5 border border-white/10 text-xs font-bold text-on-surface hover:bg-white/10 transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-base">refresh</span>
                Refresh
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center justify-between animate-in fade-in shadow-lg">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-lg text-rose-400">error</span>
                <span class="font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="clearAlerts" class="text-rose-400 hover:text-rose-200">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-green-500/10 border border-green-500/20 text-green-300 text-xs flex items-center justify-between animate-in fade-in shadow-lg">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-lg text-green-400">check_circle</span>
                <span class="font-medium">{{ $successMessage }}</span>
            </div>
            <button wire:click="clearAlerts" class="text-green-400 hover:text-green-200">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <!-- KPI Metric Summary Cards Grid (Lega & Spacious) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-5">
        <!-- 1. Pending Outbound Card -->
        <div wire:click="setTab('pending_outbound')" class="glass-card rounded-3xl p-5 border border-white/10 hover:border-amber-500/50 cursor-pointer transition-all duration-200 hover:-translate-y-1 group bg-surface-dark/90 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Pending Outbound</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-lg">pending_actions</span>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-amber-400">{{ $countPendingOutbound }}</div>
            <p class="text-xs text-on-surface-variant/60 mt-1">Nota menunggu dipick</p>
        </div>

        <!-- 2. Sedang Dipick Card -->
        <div wire:click="setTab('picking_list'); $set('filterStatus', 'PICKING')" class="glass-card rounded-3xl p-5 border border-white/10 hover:border-primary/50 cursor-pointer transition-all duration-200 hover:-translate-y-1 group bg-surface-dark/90 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Sedang Dipick</span>
                <div class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-lg">conveyor_belt</span>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-primary">{{ $countPicking }}</div>
            <p class="text-xs text-on-surface-variant/60 mt-1">Order in-progress</p>
        </div>

        <!-- 3. Siap Dipick (Pending) Card -->
        <div wire:click="setTab('picking_list'); $set('filterStatus', 'PENDING')" class="glass-card rounded-3xl p-5 border border-white/10 hover:border-cyan-500/50 cursor-pointer transition-all duration-200 hover:-translate-y-1 group bg-surface-dark/90 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Siap Dipick</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-lg">hourglass_empty</span>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-cyan-400">{{ $countPending }}</div>
            <p class="text-xs text-on-surface-variant/60 mt-1">Status pending</p>
        </div>

        <!-- 4. Selesai Hari Ini Card -->
        <div wire:click="setTab('picking_list'); $set('filterStatus', 'COMPLETED')" class="glass-card rounded-3xl p-5 border border-white/10 hover:border-green-500/50 cursor-pointer transition-all duration-200 hover:-translate-y-1 group bg-surface-dark/90 shadow-xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Selesai Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-lg">task_alt</span>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-green-400">{{ $countCompletedToday }}</div>
            <p class="text-xs text-on-surface-variant/60 mt-1">Order completed</p>
        </div>

        <!-- 5. Tugas Saya Card -->
        <div wire:click="setTab('picking_list'); toggleMyTasks();" class="glass-card rounded-3xl p-5 border cursor-pointer transition-all duration-200 hover:-translate-y-1 group shadow-xl {{ $filterOnlyMyTasks ? 'border-primary bg-primary/10 shadow-primary/10' : 'border-white/10 hover:border-primary/50 bg-surface-dark/90' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Tugas Saya</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-lg">person</span>
                </div>
            </div>
            <div class="text-2xl font-black font-mono text-on-surface">{{ $countMyTasks }}</div>
            <p class="text-xs font-bold mt-1 {{ $filterOnlyMyTasks ? 'text-primary' : 'text-on-surface-variant/60' }}">{{ $filterOnlyMyTasks ? '✓ Filter Aktif' : 'Klik untuk filter' }}</p>
        </div>
    </div>

    <!-- Navigation Tabs (Lega) -->
    <div class="flex items-center gap-3 border-b border-white/10 pb-2">
        <button type="button" wire:click="setTab('picking_list')"
                class="px-6 py-3 text-xs font-bold rounded-2xl transition-all duration-200 flex items-center gap-2.5 {{ $activeTab === 'picking_list' ? 'bg-primary text-on-primary shadow-xl shadow-primary/25 scale-[1.02]' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
            <span class="material-symbols-outlined text-base">view_list</span>
            <span>Daftar Picking</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'picking_list' ? 'bg-black/25 text-white' : 'bg-white/10 text-on-surface-variant' }}">
                {{ $pickingLists->total() }}
            </span>
        </button>

        <button type="button" wire:click="setTab('pending_outbound')"
                class="px-6 py-3 text-xs font-bold rounded-2xl transition-all duration-200 flex items-center gap-2.5 {{ $activeTab === 'pending_outbound' ? 'bg-primary text-on-primary shadow-xl shadow-primary/25 scale-[1.02]' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5' }}">
            <span class="material-symbols-outlined text-base">pending_actions</span>
            <span>Pending Outbound</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'pending_outbound' ? 'bg-black/25 text-white' : 'bg-amber-500/20 text-amber-300' }}">
                {{ $countPendingOutbound }}
            </span>
        </button>
    </div>

    <!-- TAB 1: DAFTAR PICKING (COLLAPSIBLE TABLE) -->
    @if ($activeTab === 'picking_list')
        @php
            $pickingIdsOnPage = $pickingLists->pluck('id')->values();
        @endphp
        <div x-data="{
                open: {},
                ids: @js($pickingIdsOnPage),
                toggle(id) { this.open[id] = !this.open[id] },
                get allOpen() { return this.ids.length > 0 && this.ids.every(i => this.open[i]) },
                setAll(state) { this.ids.forEach(i => this.open[i] = state) }
            }"
            class="space-y-6">

            <!-- Filter Bar 2 Baris yang Rapi & Bebas Berdempetan -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-xl space-y-4 bg-surface-dark/95">
                <!-- Baris 1: Kolom Pencarian Utama yang Luas & Nyaman -->
                <div class="relative w-full">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-primary">search</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari No. Picking, No. Nota, Petugas PIC, atau SKU/Nama Produk..."
                           class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface placeholder:text-on-surface-variant/40 focus:ring-1 focus:ring-primary focus:border-primary transition-all shadow-inner" />
                </div>

                <!-- Baris 2: Toolbar Filter & Tombol Kontrol (Terpisah Rapi) -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1 border-t border-white/5">
                    <!-- Sisi Kiri: Filter Dropdowns & Pills -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-on-surface-variant/60 uppercase">Status:</span>
                            <select wire:model.live="filterStatus" class="bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="ALL">Semua Status</option>
                                <option value="PENDING">Pending (Siap Dipick)</option>
                                <option value="PICKING">Picking In Progress</option>
                                <option value="COMPLETED">Completed (Selesai)</option>
                            </select>
                        </div>

                        <!-- PIC Filter Dropdown -->
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-on-surface-variant/60 uppercase">Petugas PIC:</span>
                            <select wire:model.live="filterPic" class="bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">Semua PIC</option>
                                <option value="__unassigned__">⚠️ Belum Ada PIC</option>
                                @foreach($distinctPickers as $picker)
                                    <option value="{{ $picker }}">{{ $picker }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tugas Saya Toggle Pill -->
                        <button type="button" wire:click="toggleMyTasks"
                                class="px-4 py-2 rounded-2xl border text-xs font-bold transition-all duration-150 flex items-center justify-center gap-2 whitespace-nowrap {{ $filterOnlyMyTasks ? 'bg-primary text-on-primary border-primary shadow-lg shadow-primary/25' : 'bg-surface-container-low text-on-surface-variant border-white/10 hover:text-on-surface hover:bg-white/5' }}">
                            <span class="material-symbols-outlined text-sm">assignment_ind</span>
                            <span>Tugas Saya</span>
                        </button>
                    </div>

                    <!-- Sisi Kanan: Buka / Tutup Semua Rincian -->
                    @if ($pickingIdsOnPage->isNotEmpty())
                        <div class="flex items-center justify-end">
                            <button type="button" @click="setAll(!allOpen)"
                                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold text-on-surface hover:bg-white/10 transition-all flex items-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-base transition-transform duration-300" :class="allOpen ? 'rotate-180' : ''">unfold_more</span>
                                <span x-text="allOpen ? 'Tutup Semua Rincian' : 'Buka Semua Rincian'">Buka Semua Rincian</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Collapsible Table Container (Spacious py-5 px-6) -->
            <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden bg-surface-dark/95">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase bg-white/[0.02]">
                                <th class="pl-6 pr-2 py-5 w-12"></th>
                                <th class="px-6 py-5">No. Picking</th>
                                <th class="px-6 py-5">Tanggal & Referensi</th>
                                <th class="px-6 py-5">Tujuan / Asal</th>
                                <th class="px-6 py-5">Petugas PIC</th>
                                <th class="px-6 py-5 text-center">Progress Item</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                            @forelse($pickingLists as $p)
                                @php
                                    $totalQtyToPick = $p->details->sum('qty_to_pick');
                                    $totalQtyPicked = $p->details->sum('qty_picked');
                                    $pct = $totalQtyToPick > 0 ? min(100, round($totalQtyPicked / $totalQtyToPick * 100)) : 0;
                                    $verifiedCount = $p->details->where('is_verified', true)->count();
                                    $totalCount = $p->details->count();
                                @endphp
                                <!-- Parent Row (Jarak Lega py-5 px-6) -->
                                <tr wire:key="picking-row-{{ $p->id }}"
                                    @click="if (!$event.target.closest('a,button,select,input')) toggle({{ $p->id }})"
                                    class="cursor-pointer hover:bg-white/5 transition-colors group"
                                    :class="open[{{ $p->id }}] ? 'bg-white/[0.03]' : ''">
                                    <td class="pl-6 pr-2 py-5">
                                        <button type="button" @click.stop="toggle({{ $p->id }})"
                                                class="w-8 h-8 rounded-xl flex items-center justify-center bg-white/5 border border-white/10 group-hover:border-primary/40 group-hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-lg transition-transform duration-300" :class="open[{{ $p->id }}] ? 'rotate-180 text-primary' : ''">expand_more</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-bold font-mono text-on-surface">{{ $p->nomor_picking }}</div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="font-medium text-on-surface">{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') . ' WIB' : '-' }}</div>
                                        <div class="text-[11px] text-on-surface-variant/70 font-mono mt-1">
                                            {{ $p->referensi_tipe === 'BarangKeluar' ? 'Penjualan #'.$p->referensi_id : 'Transfer #'.$p->referensi_id }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-1.5 text-on-surface font-semibold">
                                            <span class="material-symbols-outlined text-sm text-on-surface-variant/60">warehouse</span>
                                            <span>{{ $p->lokasi->nama_lokasi ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            @if ($p->picker_name)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-full font-bold text-xs">
                                                    <span class="material-symbols-outlined text-sm">person</span>
                                                    {{ $p->picker_name }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full font-bold text-xs">
                                                    <span class="material-symbols-outlined text-sm">person_off</span>
                                                    Belum Ditugaskan
                                                </span>
                                            @endif

                                            @if ($p->status !== 'COMPLETED')
                                                <button type="button" wire:click.stop="openAssignModal({{ $p->id }})" class="p-1.5 rounded-xl text-on-surface-variant/60 hover:text-primary hover:bg-white/10 transition-colors" title="Ubah PIC">
                                                    <span class="material-symbols-outlined text-base">edit</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex flex-col items-center gap-1.5">
                                            <span class="font-mono font-bold text-xs {{ $pct === 100 ? 'text-green-400' : 'text-on-surface' }}">
                                                {{ $totalQtyPicked }} / {{ $totalQtyToPick }} Unit ({{ $pct }}%)
                                            </span>
                                            <div class="w-32 h-2 rounded-full bg-white/10 overflow-hidden p-0.5">
                                                <div class="h-full rounded-full transition-all duration-300 {{ $pct === 100 ? 'bg-green-400' : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        @if ($p->status === 'COMPLETED')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black border bg-green-500/10 text-green-400 border-green-500/20 tracking-wider">COMPLETED</span>
                                        @elseif ($p->status === 'PICKING')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black border bg-primary/10 text-primary border-primary/20 tracking-wider animate-pulse">PICKING</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black border bg-amber-500/10 text-amber-400 border-amber-500/20 tracking-wider">PENDING</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('picking.process', $p->id) }}" wire:navigate
                                               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $p->status === 'COMPLETED' ? 'bg-white/5 border border-white/10 text-on-surface hover:bg-white/10' : 'bg-primary text-on-primary hover:bg-primary/90 shadow-lg shadow-primary/25' }}">
                                                <span class="material-symbols-outlined text-base">{{ $p->status === 'COMPLETED' ? 'visibility' : 'play_arrow' }}</span>
                                                <span>{{ $p->status === 'COMPLETED' ? 'Lihat' : ($p->status === 'PICKING' ? 'Lanjutkan' : 'Mulai') }}</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Collapsible Child Row (Lapang dengan p-8) -->
                                <tr x-show="open[{{ $p->id }}]" x-cloak class="bg-black/35 border-t border-white/5">
                                    <td colspan="8" class="p-8">
                                        <div class="glass-card rounded-3xl p-6 border border-white/10 space-y-5 bg-surface-dark/95 shadow-inner">
                                            <!-- Subheader Strip -->
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/5 pb-4">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary font-mono text-sm font-black">
                                                        {{ $totalCount }}
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-on-surface text-sm">Rincian Item Rak Rekomendasi FIFO</h4>
                                                        <p class="text-xs text-on-surface-variant/70 mt-0.5">{{ $verifiedCount }} dari {{ $totalCount }} rak telah selesai diverifikasi</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    @if ($p->started_at && $p->completed_at)
                                                        <span class="text-xs text-green-400 font-bold flex items-center gap-1 px-3 py-1.5 rounded-xl bg-green-500/10 border border-green-500/20">
                                                            <span class="material-symbols-outlined text-sm">timer</span>
                                                            Durasi: {{ $p->started_at->diffForHumans($p->completed_at, true) }}
                                                        </span>
                                                    @endif

                                                    @if ($p->status !== 'COMPLETED')
                                                        <button type="button" wire:click="openAssignModal({{ $p->id }})" class="px-3.5 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-semibold text-on-surface hover:bg-white/10 transition-all flex items-center gap-1.5">
                                                            <span class="material-symbols-outlined text-sm">manage_accounts</span>
                                                            Ubah PIC
                                                        </button>
                                                    @endif

                                                    <a href="{{ route('picking.process', $p->id) }}" wire:navigate class="px-5 py-2 bg-primary text-on-primary rounded-xl text-xs font-bold hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-md shadow-primary/20">
                                                        <span class="material-symbols-outlined text-base">open_in_new</span>
                                                        Buka Halaman Eksekusi
                                                    </a>
                                                </div>
                                            </div>

                                            <!-- Inner Items Table (Lega py-3.5 px-4) -->
                                            <div class="overflow-x-auto rounded-2xl border border-white/5 bg-white/[0.01]">
                                                <table class="w-full text-left border-collapse text-xs">
                                                    <thead>
                                                        <tr class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-wider border-b border-white/5 bg-white/[0.02]">
                                                            <th class="py-3 px-4">Produk</th>
                                                            <th class="py-3 px-4">Rekomendasi Rak (FIFO)</th>
                                                            <th class="py-3 px-4 text-center">Permintaan</th>
                                                            <th class="py-3 px-4 text-center">Diambil</th>
                                                            <th class="py-3 px-4 text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5">
                                                        @foreach($p->details as $d)
                                                            <tr class="hover:bg-white/5 transition-colors">
                                                                <td class="py-3.5 px-4 flex items-center gap-3">
                                                                    <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                                        {{ substr($d->produk->kode_barang, 0, 3) }}
                                                                    </div>
                                                                    <div>
                                                                        <p class="font-bold text-on-surface text-xs">{{ $d->produk->nama_barang }}</p>
                                                                        <div class="flex items-center gap-2 mt-0.5">
                                                                            <span class="text-[10px] text-on-surface-variant/70 font-mono">{{ $d->produk->kode_barang }}</span>
                                                                            <button type="button" wire:click="showBarcodeModal('{{ $d->produk->kode_barang }}', 'sku')" class="text-primary hover:text-primary/80" title="Barcode SKU">
                                                                                <span class="material-symbols-outlined text-xs">barcode_scanner</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3.5 px-4">
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-lg font-mono font-bold text-xs">
                                                                            {{ $d->lokasiRak->kode_lokasi }}
                                                                        </span>
                                                                        <button type="button" wire:click="showBarcodeModal('{{ $d->lokasiRak->kode_lokasi }}', 'location')" class="text-primary hover:text-primary/80" title="Barcode Rak">
                                                                            <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3.5 px-4 text-center font-bold text-on-surface font-mono">{{ $d->qty_to_pick }}</td>
                                                                <td class="py-3.5 px-4 text-center font-mono font-black {{ $d->qty_picked >= $d->qty_to_pick ? 'text-green-400' : 'text-amber-400' }}">{{ $d->qty_picked }}</td>
                                                                <td class="py-3.5 px-4 text-center">
                                                                    @if($d->is_verified)
                                                                        <span class="px-2.5 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[9px] font-bold uppercase">VERIFIED</span>
                                                                    @else
                                                                        <span class="px-2.5 py-0.5 bg-slate-500/10 text-slate-400 border border-slate-500/20 rounded-full text-[9px] font-bold uppercase">BELUM</span>
                                                                    @endif
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
                                    <td colspan="8" class="text-center py-16 text-on-surface-variant/50">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30">inventory_2</span>
                                            <p class="text-xs">Tidak ada data order picking yang sesuai dengan filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination & Per Page Selector (10, 25, 50, 100) -->
                <div class="p-6 border-t border-white/5 bg-white/[0.01] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant/80">
                            <span class="font-medium">Tampilkan:</span>
                            <select wire:model.live="perPage" class="bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-3 text-xs text-on-surface font-semibold focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                                <option value="100">100 per halaman</option>
                            </select>
                        </div>
                        <span class="text-xs text-on-surface-variant/60 font-mono">
                            Menampilkan {{ $pickingLists->firstItem() ?? 0 }} - {{ $pickingLists->lastItem() ?? 0 }} dari {{ $pickingLists->total() }} order
                        </span>
                    </div>

                    <div>
                        {{ $pickingLists->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 2: PENDING OUTBOUND (COLLAPSIBLE TABLE) -->
    @if ($activeTab === 'pending_outbound')
        @php
            $pendingIdsOnPage = $pendingOutbounds->pluck('id')->values();
        @endphp
        <div x-data="{
                open: {},
                ids: @js($pendingIdsOnPage),
                toggle(id) { this.open[id] = !this.open[id] },
                get allOpen() { return this.ids.length > 0 && this.ids.every(i => this.open[i]) },
                setAll(state) { this.ids.forEach(i => this.open[i] = state) }
            }"
            class="space-y-6">

            <!-- Filter Bar 2 Baris yang Rapi -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-xl space-y-4 bg-surface-dark/95">
                <!-- Baris 1: Search Input Luas -->
                <div class="relative w-full">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-lg text-primary">search</span>
                    <input wire:model.live.debounce.300ms="searchPending" type="text"
                           placeholder="Cari No. Nota, No. Transfer, atau Pelanggan..."
                           class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface placeholder:text-on-surface-variant/40 focus:ring-1 focus:ring-primary focus:border-primary transition-all shadow-inner" />
                </div>

                <!-- Baris 2: Filter & Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1 border-t border-white/5">
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-bold text-on-surface-variant/60 uppercase">Tipe Dokumen:</span>
                        <select wire:model.live="filterPendingType" class="bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                            <option value="ALL">Semua Tipe Dokumen</option>
                            <option value="BarangKeluar">Penjualan / Barang Keluar</option>
                            <option value="TransferHeader">Transfer Antar Gudang</option>
                        </select>
                    </div>

                    @if ($pendingIdsOnPage->isNotEmpty())
                        <div class="flex items-center justify-end">
                            <button type="button" @click="setAll(!allOpen)"
                                    class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold text-on-surface hover:bg-white/10 transition-all flex items-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-base transition-transform duration-300" :class="allOpen ? 'rotate-180' : ''">unfold_more</span>
                                <span x-text="allOpen ? 'Tutup Semua Rincian' : 'Buka Semua Rincian'">Buka Semua Rincian</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Outbound Table Container (Spacious py-5 px-6) -->
            <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden bg-surface-dark/95">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase bg-white/[0.02]">
                                <th class="pl-6 pr-2 py-5 w-12"></th>
                                <th class="px-6 py-5">No. Dokumen</th>
                                <th class="px-6 py-5">Tipe Transaksi</th>
                                <th class="px-6 py-5">Tanggal</th>
                                <th class="px-6 py-5">Tujuan / Asal</th>
                                <th class="px-6 py-5 text-center">Total Item</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                            @forelse($pendingOutbounds as $item)
                                <tr wire:key="pending-row-{{ $item->type }}-{{ $item->id }}"
                                    @click="if (!$event.target.closest('a,button,select,input')) toggle({{ $item->id }})"
                                    class="cursor-pointer hover:bg-white/5 transition-colors group"
                                    :class="open[{{ $item->id }}] ? 'bg-white/[0.03]' : ''">
                                    <td class="pl-6 pr-2 py-5">
                                        <button type="button" @click.stop="toggle({{ $item->id }})"
                                                class="w-8 h-8 rounded-xl flex items-center justify-center bg-white/5 border border-white/10 group-hover:border-primary/40 group-hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-lg transition-transform duration-300" :class="open[{{ $item->id }}] ? 'rotate-180 text-primary' : ''">expand_more</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-5 font-mono font-bold text-on-surface whitespace-nowrap">
                                        {{ $item->nomor }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border {{ $item->type === 'BarangKeluar' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                                            {{ $item->type === 'BarangKeluar' ? 'Penjualan' : 'Transfer Antar Gudang' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-on-surface">{{ $item->tujuan }}</div>
                                        @if ($item->type === 'TransferHeader')
                                            <div class="text-[11px] text-on-surface-variant/60 mt-0.5">Asal: {{ $item->asal }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center font-mono font-bold text-on-surface">
                                        {{ $item->total_item }} Unit
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black border bg-amber-500/10 text-amber-400 border-amber-500/20 tracking-wider">
                                            MENUNGGU PICKING
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right whitespace-nowrap">
                                        <button wire:click.stop="openGenerateModal('{{ $item->type }}', {{ $item->id }})"
                                                class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-lg shadow-primary/25 ml-auto">
                                            <span class="material-symbols-outlined text-sm">build</span>
                                            <span>Buat Picking List</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Collapsible Child Row (Lega p-8) -->
                                <tr x-show="open[{{ $item->id }}]" x-cloak class="bg-black/35 border-t border-white/5">
                                    <td colspan="8" class="p-8">
                                        <div class="glass-card rounded-3xl p-6 border border-white/10 space-y-5 bg-surface-dark/95 shadow-inner">
                                            <div class="flex items-center justify-between border-b border-white/5 pb-4">
                                                <h4 class="font-bold text-on-surface text-sm flex items-center gap-2">
                                                    <span class="material-symbols-outlined text-base text-primary">format_list_bulleted</span>
                                                    Daftar Permintaan Produk dalam Dokumen
                                                </h4>
                                                <button wire:click="openGenerateModal('{{ $item->type }}', {{ $item->id }})"
                                                        class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-md shadow-primary/20">
                                                    <span class="material-symbols-outlined text-sm">assignment_ind</span>
                                                    <span>Tetapkan PIC & Generate Picking</span>
                                                </button>
                                            </div>

                                            <div class="overflow-x-auto rounded-2xl border border-white/5 bg-white/[0.01]">
                                                <table class="w-full text-left border-collapse text-xs">
                                                    <thead>
                                                        <tr class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-wider border-b border-white/5 bg-white/[0.02]">
                                                            <th class="py-3 px-4">Produk</th>
                                                            <th class="py-3 px-4 text-center">Jumlah Diminta</th>
                                                            <th class="py-3 px-4 text-center">Satuan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5">
                                                        @foreach($item->details as $d)
                                                            @php
                                                                $qty = $item->type === 'BarangKeluar' ? $d->qty_keluar : $d->qty_requested;
                                                            @endphp
                                                            <tr class="hover:bg-white/5 transition-colors">
                                                                <td class="py-3.5 px-4 flex items-center gap-3">
                                                                    <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                                        {{ substr($d->produk->kode_barang, 0, 3) }}
                                                                    </div>
                                                                    <div>
                                                                        <p class="font-bold text-on-surface text-xs">{{ $d->produk->nama_barang }}</p>
                                                                        <p class="text-[10px] text-on-surface-variant/70 font-mono mt-0.5">{{ $d->produk->kode_barang }}</p>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3.5 px-4 text-center font-mono font-bold text-on-surface">{{ $qty }}</td>
                                                                <td class="py-3.5 px-4 text-center text-on-surface-variant/70">{{ $d->satuan }}</td>
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
                                    <td colspan="8" class="text-center py-16 text-on-surface-variant/50">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30">task_alt</span>
                                            <p class="text-xs">Semua dokumen keluar telah dibuatkan picking list.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination & Per Page Selector for Pending Outbound (10, 25, 50, 100) -->
                <div class="p-6 border-t border-white/5 bg-white/[0.01] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant/80">
                            <span class="font-medium">Tampilkan:</span>
                            <select wire:model.live="perPagePending" class="bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-3 text-xs text-on-surface font-semibold focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                                <option value="100">100 per halaman</option>
                            </select>
                        </div>
                        <span class="text-xs text-on-surface-variant/60 font-mono">
                            Menampilkan {{ $pendingOutbounds->firstItem() ?? 0 }} - {{ $pendingOutbounds->lastItem() ?? 0 }} dari {{ $pendingOutbounds->total() }} dokumen
                        </span>
                    </div>

                    <div>
                        {{ $pendingOutbounds->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 1: Generate Picking List with PIC Choice -->
    @if ($isGenerateModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeGenerateModal"></div>
        <div class="relative w-full max-w-md mx-auto my-6 z-50 animate-in fade-in zoom-in duration-200">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-xl">assignment_ind</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-on-surface">Buat Picking List</h3>
                            <p class="text-[11px] text-on-surface-variant/70">Tetapkan PIC (Petugas Pelaksana) untuk order ini</p>
                        </div>
                    </div>
                    <button wire:click="closeGenerateModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    <!-- Source Reference Info -->
                    <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-on-surface-variant/60 block">Sumber Dokumen</span>
                            <span class="font-bold text-on-surface font-mono">{{ $genRefType === 'BarangKeluar' ? 'Penjualan / Keluar' : 'Transfer Antar Gudang' }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">
                            ID #{{ $genRefId }}
                        </span>
                    </div>

                    <!-- PIC Selection Type Pills -->
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Penugasan PIC</label>
                        <div class="grid grid-cols-3 gap-2 p-1 bg-surface-container-low rounded-2xl border border-white/5">
                            <button type="button" wire:click="$set('genPicType', 'user')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $genPicType === 'user' ? 'bg-primary text-on-primary shadow-lg shadow-primary/25' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">badge</span> Operator
                            </button>
                            <button type="button" wire:click="$set('genPicType', 'manual')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $genPicType === 'manual' ? 'bg-primary text-on-primary shadow-lg shadow-primary/25' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">edit</span> Manual
                            </button>
                            <button type="button" wire:click="$set('genPicType', 'unassigned')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $genPicType === 'unassigned' ? 'bg-white/10 text-amber-400 border border-amber-500/30' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">hourglass_empty</span> Nanti
                            </button>
                        </div>
                    </div>

                    @if ($genPicType === 'user')
                        <div class="space-y-1.5 animate-in fade-in duration-150">
                            <label class="block text-xs font-semibold text-on-surface">Pilih Petugas Gudang / User</label>
                            <select wire:model="genPicUserId" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role ?? 'Operator' }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-on-surface-variant/60">Pilih dari akun operator gudang yang terdaftar di sistem.</p>
                        </div>
                    @elseif ($genPicType === 'manual')
                        <div class="space-y-1.5 animate-in fade-in duration-150">
                            <label class="block text-xs font-semibold text-on-surface">Nama Petugas / Kru Harian</label>
                            <input type="text" wire:model="genManualPicName" placeholder="Contoh: Budi (Kru Freelance)"
                                   class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-3.5 text-xs text-on-surface placeholder:text-on-surface-variant/40 focus:ring-1 focus:ring-primary focus:border-primary">
                            <p class="text-[10px] text-on-surface-variant/60">Ketik nama petugas jika belum memiliki akun pengguna sistem.</p>
                        </div>
                    @else
                        <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-2.5 text-xs text-amber-300">
                            <span class="material-symbols-outlined text-base mt-0.5 shrink-0">info</span>
                            <span class="text-[11px] leading-relaxed">Picking list akan berstatus <strong class="text-amber-200">Belum Ditugaskan</strong>. Petugas dapat dipilih kapan saja saat proses picking dimulai.</span>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50 gap-2">
                    <button wire:click="closeGenerateModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="confirmGeneratePicking" wire:loading.attr="disabled" class="px-5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-lg shadow-primary/20">
                        <span wire:loading.remove wire:target="confirmGeneratePicking" class="material-symbols-outlined text-sm">play_arrow</span>
                        <span wire:loading wire:target="confirmGeneratePicking" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                        Generate Picking List
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL 2: Assign / Reassign PIC Modal -->
    @if ($isAssignModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeAssignModal"></div>
        <div class="relative w-full max-w-md mx-auto my-6 z-50 animate-in fade-in zoom-in duration-200">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
                            <span class="material-symbols-outlined text-xl">manage_accounts</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-on-surface">Tugaskan / Ubah PIC</h3>
                            <p class="text-[11px] text-on-surface-variant/70">Perbarui petugas pelaksana untuk picking list ini</p>
                        </div>
                    </div>
                    <button wire:click="closeAssignModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5">
                    @php
                        $targetPicking = \App\Models\PickingList::find($assignPickingId);
                    @endphp
                    @if ($targetPicking)
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant/60 block">Nomor Picking List</span>
                                <span class="font-bold text-on-surface font-mono">{{ $targetPicking->nomor_picking }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-on-surface-variant/60 block">PIC Saat Ini</span>
                                @if ($targetPicking->picker_name)
                                    <span class="font-bold text-primary">{{ $targetPicking->picker_name }}</span>
                                @else
                                    <span class="font-semibold text-amber-400 italic">Belum Ditugaskan</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- PIC Selection Type Pills -->
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-2">Pilih Metode Penugasan</label>
                        <div class="grid grid-cols-3 gap-2 p-1 bg-surface-container-low rounded-2xl border border-white/5">
                            <button type="button" wire:click="$set('selectedPicType', 'user')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $selectedPicType === 'user' ? 'bg-primary text-on-primary shadow-lg shadow-primary/25' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">badge</span> Operator
                            </button>
                            <button type="button" wire:click="$set('selectedPicType', 'manual')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $selectedPicType === 'manual' ? 'bg-primary text-on-primary shadow-lg shadow-primary/25' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">edit</span> Manual
                            </button>
                            <button type="button" wire:click="$set('selectedPicType', 'unassigned')"
                                    class="py-2 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1.5 {{ $selectedPicType === 'unassigned' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'text-on-surface-variant hover:text-on-surface' }}">
                                <span class="material-symbols-outlined text-sm">person_off</span> Kosongkan
                            </button>
                        </div>
                    </div>

                    @if ($selectedPicType === 'user')
                        <div class="space-y-1.5 animate-in fade-in duration-150">
                            <label class="block text-xs font-semibold text-on-surface">Pilih Petugas Gudang / User</label>
                            <select wire:model="selectedPicUserId" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-3.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role ?? 'Operator' }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-on-surface-variant/60">Pilih akun operator gudang yang akan bertanggung jawab.</p>
                        </div>
                    @elseif ($selectedPicType === 'manual')
                        <div class="space-y-1.5 animate-in fade-in duration-150">
                            <label class="block text-xs font-semibold text-on-surface">Nama Petugas / Kru Harian</label>
                            <input type="text" wire:model="manualPicName" placeholder="Contoh: Slamet (Kru Harian)"
                                   class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-3.5 text-xs text-on-surface placeholder:text-on-surface-variant/40 focus:ring-1 focus:ring-primary focus:border-primary">
                            <p class="text-[10px] text-on-surface-variant/60">Ketik nama petugas manual jika bukan akun terdaftar.</p>
                        </div>
                    @else
                        <div class="p-3 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-start gap-2.5 text-xs text-rose-300">
                            <span class="material-symbols-outlined text-base mt-0.5 shrink-0">warning</span>
                            <span class="text-[11px] leading-relaxed">PIC akan dikosongkan. Order ini akan kembali berstatus <strong class="text-rose-200">Belum Ditugaskan</strong>.</span>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50 gap-2">
                    <button wire:click="closeAssignModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="saveAssignPic" wire:loading.attr="disabled" class="px-5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1.5 shadow-lg shadow-primary/20">
                        <span wire:loading.remove wire:target="saveAssignPic" class="material-symbols-outlined text-sm">check</span>
                        <span wire:loading wire:target="saveAssignPic" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL 3: Barcode View Modal -->
    @if ($isBarcodeModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeBarcodeModal"></div>
        <div class="relative w-full max-w-sm mx-auto my-6 z-50 animate-in fade-in zoom-in duration-200">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface">{{ $barcodeModalTitle }}</h3>
                    <button wire:click="closeBarcodeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                <div class="p-6 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-inner flex flex-col items-center justify-center max-w-full overflow-hidden">
                        {!! \App\Helpers\Barcode128::getSvg($barcodeModalValue, 80, 2) !!}
                        <span class="mt-3 font-mono text-xs font-bold text-black tracking-widest">{{ $barcodeModalValue }}</span>
                    </div>
                    <p class="text-[10px] text-on-surface-variant/60">Arahkan scanner hardware Anda ke barcode di atas untuk memindai.</p>
                </div>
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50">
                    <button wire:click="closeBarcodeModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
