<div x-init="$dispatch('update-header-title', 'Outbound QC & Packing')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar pb-16">
    
    {{-- Header & Subtitle --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shadow-accent-glow">
                    <span class="material-symbols-outlined text-2xl">package_2</span>
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-black text-on-surface tracking-tight">Outbound QC & Packing</h1>
                    <p class="text-xs text-on-surface-variant/80 mt-0.5">Pemeriksaan kualitas fisik barang (QC), pengemasan, nomor resi kurir, dan pencetakan shipping label sebelum pengiriman.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            <span class="px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white/5 border border-white/10 text-on-surface-variant/80 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                Stasiun QC Siap Operasi
            </span>
        </div>
    </div>

    {{-- KPI Metric Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        {{-- Card 1: Siap Packing --}}
        <div class="glass-card rounded-2xl p-5 border border-white/10 relative overflow-hidden group hover:border-primary/40 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-bold text-on-surface-variant/70">Siap Packing (QC)</p>
                    <h3 class="text-3xl font-black font-mono text-primary mt-2">{{ $countReady }}</h3>
                    <p class="text-[11px] text-on-surface-variant/60 mt-1">Order selesai picking & menunggu QC</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-amber-500 opacity-60"></div>
        </div>

        {{-- Card 2: Terkirim Hari Ini --}}
        <div class="glass-card rounded-2xl p-5 border border-white/10 relative overflow-hidden group hover:border-green-500/40 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-bold text-on-surface-variant/70">Terkirim Hari Ini</p>
                    <h3 class="text-3xl font-black font-mono text-green-400 mt-2">{{ $countCompletedToday }}</h3>
                    <p class="text-[11px] text-on-surface-variant/60 mt-1">Paket diberangkatkan hari ini</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-400 opacity-60"></div>
        </div>

        {{-- Card 3: Total Riwayat Kirim --}}
        <div class="glass-card rounded-2xl p-5 border border-white/10 relative overflow-hidden group hover:border-blue-500/40 transition-all duration-300 sm:col-span-2 lg:col-span-1">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-bold text-on-surface-variant/70">Total Arsip Kirim</p>
                    <h3 class="text-3xl font-black font-mono text-blue-400 mt-2">{{ $countTotalCompleted }}</h3>
                    <p class="text-[11px] text-on-surface-variant/60 mt-1">Seluruh order outbound terkirim</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">verified</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 opacity-60"></div>
        </div>
    </div>

    {{-- Main Tabs Navigation --}}
    <div class="flex items-center gap-3 border-b border-white/10 pb-4">
        <button wire:click="setTab('ready')" 
            class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-200 flex items-center gap-2.5 {{ $activeTab === 'ready' ? 'bg-primary text-black font-black shadow-accent-glow' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-white/5 border border-white/5' }}">
            <span class="material-symbols-outlined text-base">fact_check</span>
            <span>Siap Packing (QC)</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'ready' ? 'bg-black text-white font-bold' : 'bg-white/10 text-on-surface' }}">
                {{ $countReady }}
            </span>
        </button>

        <button wire:click="setTab('completed')" 
            class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-200 flex items-center gap-2.5 {{ $activeTab === 'completed' ? 'bg-primary text-black font-black shadow-accent-glow' : 'bg-surface-container-low text-on-surface-variant hover:text-on-surface hover:bg-white/5 border border-white/5' }}">
            <span class="material-symbols-outlined text-base">history</span>
            <span>Riwayat Pengiriman</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $activeTab === 'completed' ? 'bg-black text-white font-bold' : 'bg-white/10 text-on-surface' }}">
                {{ $countTotalCompleted }}
            </span>
        </button>
    </div>

    {{-- TAB 1: SIAP PACKING (COLLAPSIBLE TABLE) --}}
    @if ($activeTab === 'ready')
        @php
            $readyKeysOnPage = $readyTransactions->map(fn($t) => $t->type.'_'.$t->id)->values();
        @endphp
        <div x-data="{
                open: {},
                keys: @js($readyKeysOnPage),
                toggle(key) { this.open[key] = !this.open[key] },
                get allOpen() { return this.keys.length > 0 && this.keys.every(k => this.open[k]) },
                setAll(state) { this.keys.forEach(k => this.open[k] = state) }
            }"
            class="space-y-4">

            {{-- Toolbar: Search & Filters (2 Rows) --}}
            <div class="glass-card rounded-2xl p-5 border border-white/10 space-y-4">
                {{-- Row 1: Search Bar Full Width --}}
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 text-xl pointer-events-none">search</span>
                    <input wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Cari No. Order, No. Nota, Tujuan/Penerima, atau SKU/Nama Barang..." 
                        class="w-full bg-surface-container border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-xs lg:text-sm text-on-surface placeholder:text-on-surface-variant/40 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                </div>

                {{-- Row 2: Secondary Filters & Collapse Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 border-t border-white/5">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Filter Tipe --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-on-surface-variant/70 font-medium">Tipe:</span>
                            <select wire:model.live="filterType" class="bg-surface-container border border-white/10 rounded-xl py-2 px-3 text-xs text-on-surface font-semibold focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="ALL">Semua Tipe Transaksi</option>
                                <option value="BarangKeluar">Penjualan (Barang Keluar)</option>
                                <option value="TransferHeader">Transfer Antar Gudang</option>
                            </select>
                        </div>
                    </div>

                    {{-- Collapse Toggle All Button --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="setAll(!allOpen)" 
                            class="px-4 py-2 rounded-xl border border-white/10 text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm" x-text="allOpen ? 'unfold_less' : 'unfold_more'"></span>
                            <span x-text="allOpen ? 'Tutup Semua Rincian' : 'Buka Semua Rincian'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Collapsible Table Container --}}
            <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="w-full overflow-hidden">
                    <table class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/[0.02] text-[11px] font-black uppercase tracking-[0.2em] text-on-surface-variant/70">
                                <th class="py-4 px-3 w-12 text-center"></th>
                                <th class="py-4 px-4">No. Order & Tanggal</th>
                                <th class="py-4 px-4">Tujuan / Penerima</th>
                                <th class="py-4 px-4 text-center">Total Item</th>
                                <th class="py-4 px-4 text-center">Status QC</th>
                                <th class="py-4 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs">
                            @forelse ($readyTransactions as $t)
                                @php
                                    $rowKey = $t->type . '_' . $t->id;
                                    $rowData = $packingData[$rowKey] ?? ['kurir' => 'JNE', 'resi' => '', 'qc' => []];
                                    $totalDetailsCount = $t->details->count();
                                    $passedQcCount = 0;
                                    foreach ($t->details as $d) {
                                        if (!empty($rowData['qc'][$d->id])) {
                                            $passedQcCount++;
                                        }
                                    }
                                    $allPassed = ($totalDetailsCount > 0 && $passedQcCount === $totalDetailsCount);
                                @endphp
                                {{-- Parent Row --}}
                                <tr @click="toggle('{{ $rowKey }}')" class="hover:bg-white/[0.03] transition-colors cursor-pointer group">
                                    {{-- Expand Toggle Chevron --}}
                                    <td class="py-4 px-3 text-center">
                                        <button type="button" class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center transition-transform duration-200 group-hover:bg-primary/10 group-hover:border-primary/30"
                                            :class="open['{{ $rowKey }}'] ? 'rotate-90 text-primary bg-primary/10' : 'text-on-surface-variant/60'">
                                            <span class="material-symbols-outlined text-base">chevron_right</span>
                                        </button>
                                    </td>

                                    {{-- No. Order & Tanggal --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-sm text-on-surface group-hover:text-primary transition-colors">
                                                {{ $t->nomor_nota }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border {{ $t->type === 'BarangKeluar' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                                                {{ $t->type === 'BarangKeluar' ? 'PENJUALAN' : 'TRANSFER' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-on-surface-variant/60 font-mono mt-0.5">
                                            {{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d M Y') : '-' }}
                                        </p>
                                    </td>

                                    {{-- Tujuan / Penerima --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm text-primary/70">location_on</span>
                                            <div>
                                                <p class="font-bold text-on-surface">{{ $t->destinasi }}</p>
                                                @if (!empty($t->kontak) && $t->kontak !== '-')
                                                    <p class="text-[10px] text-on-surface-variant/60 font-mono">{{ $t->kontak }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Total Item --}}
                                    <td class="py-4 px-4 text-center font-mono font-bold text-on-surface">
                                        <span class="px-2.5 py-1 rounded-xl bg-white/5 border border-white/10 inline-block">
                                            {{ $t->items_count }} pcs
                                        </span>
                                    </td>

                                    {{-- Status QC --}}
                                    <td class="py-4 px-4 text-center">
                                        @if ($allPassed)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-500/10 text-green-400 border border-green-500/20">
                                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                                QC LULUS ({{ $passedQcCount }}/{{ $totalDetailsCount }})
                                            </span>
                                        @elseif ($passedQcCount > 0)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                <span class="material-symbols-outlined text-xs">pending</span>
                                                SEBAGIAN ({{ $passedQcCount }}/{{ $totalDetailsCount }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-500/10 text-red-400 border border-red-500/20">
                                                <span class="material-symbols-outlined text-xs">cancel</span>
                                                BELUM QC (0/{{ $totalDetailsCount }})
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-4 px-4 text-center" @click.stop>
                                        <button type="button" @click="toggle('{{ $rowKey }}')" 
                                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 mx-auto"
                                            :class="open['{{ $rowKey }}'] ? 'bg-white/10 text-on-surface border border-white/10' : 'bg-primary text-black font-black shadow-accent-glow hover:opacity-90'">
                                            <span class="material-symbols-outlined text-sm" x-text="open['{{ $rowKey }}'] ? 'expand_less' : 'fact_check'"></span>
                                            <span x-text="open['{{ $rowKey }}'] ? 'Tutup QC' : 'Proses QC & Kirim'"></span>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Collapsible Child Row: QC Checklist & Shipping Form --}}
                                <tr x-show="open['{{ $rowKey }}']" x-cloak class="bg-surface-dark/95 border-b border-white/10">
                                    <td colspan="6" class="p-6 lg:p-8">
                                        <div class="space-y-6">
                                            {{-- Top Sub-Header: Order Info & Quick Bar --}}
                                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-white/5">
                                                <div>
                                                    <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-primary text-base">verified</span>
                                                        Pemeriksaan Quality Control & Ekspedisi — {{ $t->nomor_nota }}
                                                    </h3>
                                                    <p class="text-[11px] text-on-surface-variant/70 mt-0.5">
                                                        Penerima: <strong class="text-on-surface">{{ $t->destinasi }}</strong> | Alamat: {{ $t->alamat }}
                                                    </p>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    {{-- Button: Luluskan Semua QC --}}
                                                    @if ($t->type === 'BarangKeluar')
                                                        <button type="button" wire:click="checkAllQc({{ $t->id }}, '{{ $t->type }}')"
                                                            class="px-3.5 py-1.5 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 hover:bg-green-500/20 text-xs font-bold transition-all flex items-center gap-1.5">
                                                            <span class="material-symbols-outlined text-xs">done_all</span>
                                                            Luluskan Semua (Check All)
                                                        </button>
                                                    @endif

                                                    {{-- Button: Cetak Label Alamat --}}
                                                    <button type="button" 
                                                        onclick="printShippingLabel('{{ $t->nomor_nota }}', '{{ $t->tanggal }}', '{{ addslashes($t->destinasi) }}', '{{ addslashes($t->alamat) }}', '{{ addslashes($t->kontak) }}')"
                                                        class="px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 text-xs font-bold transition-all flex items-center gap-1.5">
                                                        <span class="material-symbols-outlined text-xs">print</span>
                                                        Cetak Label Alamat
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Grid: Left (QC Checklist Table) & Right (Shipping / Courier Form) --}}
                                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                                {{-- Left Column: QC Item Checklist --}}
                                                <div class="lg:col-span-7 space-y-3">
                                                    <div class="flex items-center justify-between">
                                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                                                            <span class="material-symbols-outlined text-sm">checklist</span>
                                                            Daftar Barang & Verifikasi QC
                                                        </h4>
                                                        <span class="text-[11px] font-mono text-on-surface-variant/60">
                                                            {{ $passedQcCount }} dari {{ $totalDetailsCount }} item lulus
                                                        </span>
                                                    </div>

                                                    <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/[0.02]">
                                                        <table class="w-full text-left text-xs">
                                                            <thead>
                                                                <tr class="border-b border-white/5 bg-white/[0.03] text-[10px] font-bold text-on-surface-variant/60 uppercase">
                                                                    <th class="py-3 px-4">Produk / SKU</th>
                                                                    <th class="py-3 px-4 text-center">Jumlah</th>
                                                                    <th class="py-3 px-4 text-center">Status QC</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-white/5">
                                                                @foreach ($t->details as $d)
                                                                    @php
                                                                        $qty = $t->type === 'BarangKeluar' ? $d->qty_keluar : $d->qty_requested;
                                                                        $isItemPassed = !empty($rowData['qc'][$d->id]);
                                                                    @endphp
                                                                    <tr class="hover:bg-white/[0.02] transition-colors">
                                                                        <td class="py-3.5 px-4 flex items-center gap-3">
                                                                            <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                                                {{ substr($d->produk->kode_barang ?? 'PRD', 0, 3) }}
                                                                            </div>
                                                                            <div>
                                                                                <p class="font-bold text-on-surface text-xs">{{ $d->produk->nama_barang ?? '-' }}</p>
                                                                                <p class="text-[10px] text-on-surface-variant/60 font-mono mt-0.5">{{ $d->produk->kode_barang ?? '-' }}</p>
                                                                            </div>
                                                                        </td>
                                                                        <td class="py-3.5 px-4 text-center font-mono font-bold text-on-surface">
                                                                            {{ $qty }} {{ $d->satuan }}
                                                                        </td>
                                                                        <td class="py-3.5 px-4 text-center">
                                                                            @if ($t->type === 'BarangKeluar')
                                                                                <button type="button" wire:click="toggleQcItem({{ $t->id }}, '{{ $t->type }}', {{ $d->id }})"
                                                                                    class="px-3 py-1 rounded-xl border text-[10px] font-bold uppercase transition-all flex items-center gap-1 mx-auto {{ $isItemPassed ? 'bg-green-500/10 text-green-400 border-green-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30' }}">
                                                                                    <span class="material-symbols-outlined text-xs">{{ $isItemPassed ? 'check' : 'close' }}</span>
                                                                                    {{ $isItemPassed ? 'PASSED ✓' : 'PENDING ✕' }}
                                                                                </button>
                                                                            @else
                                                                                <span class="px-2.5 py-1 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-bold uppercase">
                                                                                    TRANSFER OK
                                                                                </span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                {{-- Right Column: Kurir & Ekspedisi Form --}}
                                                <div class="lg:col-span-5 space-y-4">
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                                                        <span class="material-symbols-outlined text-sm">local_shipping</span>
                                                        Informasi Ekspedisi & Pengiriman
                                                    </h4>

                                                    <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-4">
                                                        @if ($t->type === 'BarangKeluar')
                                                            <div>
                                                                <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Pilih Kurir / Ekspedisi</label>
                                                                <select wire:model.live="packingData.{{ $rowKey }}.kurir" 
                                                                    class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary">
                                                                    <option value="JNE">JNE Express</option>
                                                                    <option value="J&T">J&T Express</option>
                                                                    <option value="Sicepat">Sicepat</option>
                                                                    <option value="Anteraja">Anteraja</option>
                                                                    <option value="Wahana">Wahana</option>
                                                                    <option value="POS">POS Indonesia</option>
                                                                    <option value="Internal / Kurir Toko">Kurir Toko / Internal Gudang</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Nomor Resi Pengiriman</label>
                                                                <input wire:model.live="packingData.{{ $rowKey }}.resi" 
                                                                    type="text" 
                                                                    placeholder="Contoh: JNE8291039820..." 
                                                                    class="w-full bg-surface-container border border-white/10 rounded-xl py-2.5 px-3 text-xs text-on-surface font-mono placeholder:font-sans placeholder:text-on-surface-variant/40 focus:outline-none focus:ring-1 focus:ring-primary">
                                                            </div>
                                                        @else
                                                            <div class="p-4 rounded-xl bg-purple-500/10 border border-purple-500/20 text-xs space-y-2">
                                                                <p class="font-bold text-purple-300 flex items-center gap-1.5">
                                                                    <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                                                    Transfer Antar Gudang
                                                                </p>
                                                                <p class="text-on-surface-variant/80 text-[11px]">Pengiriman logistik internal dari gudang asal ke tujuan. Menekan tombol konfirmasi akan mengubah status transfer menjadi <strong class="text-on-surface">IN_TRANSIT</strong>.</p>
                                                            </div>
                                                        @endif

                                                        {{-- Submit Button --}}
                                                        <div class="pt-2">
                                                            <button type="button" wire:click="submitPacking({{ $t->id }}, '{{ $t->type }}')"
                                                                class="w-full py-3 px-4 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container font-black text-xs rounded-xl shadow-xl hover:shadow-primary/20 transition-all duration-200 amber-glow-strong flex items-center justify-center gap-2">
                                                                <span class="material-symbols-outlined text-sm">send</span>
                                                                <span>{{ $t->type === 'BarangKeluar' ? 'Konfirmasi QC & Kirim Barang' : 'Kirim Transfer Gudang' }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-16 text-on-surface-variant/50">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30">inventory_2</span>
                                            <p class="text-xs">Tidak ada order yang sedang menunggu QC & Packing.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Per Page Selector for Ready to Pack (10, 25, 50, 100) --}}
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
                            Menampilkan {{ $readyTransactions->firstItem() ?? 0 }} - {{ $readyTransactions->lastItem() ?? 0 }} dari {{ $readyTransactions->total() }} order
                        </span>
                    </div>

                    <div>
                        {{ $readyTransactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 2: RIWAYAT PENGIRIMAN (COLLAPSIBLE TABLE) --}}
    @if ($activeTab === 'completed')
        @php
            $completedKeysOnPage = $completedTransactions->map(fn($t) => $t->type.'_'.$t->id)->values();
        @endphp
        <div x-data="{
                open: {},
                keys: @js($completedKeysOnPage),
                toggle(key) { this.open[key] = !this.open[key] },
                get allOpen() { return this.keys.length > 0 && this.keys.every(k => this.open[k]) },
                setAll(state) { this.keys.forEach(k => this.open[k] = state) }
            }"
            class="space-y-4">

            {{-- Toolbar: Search & Filters (2 Rows) --}}
            <div class="glass-card rounded-2xl p-5 border border-white/10 space-y-4">
                {{-- Row 1: Search Bar Full Width --}}
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 text-xl pointer-events-none">search</span>
                    <input wire:model.live.debounce.300ms="searchCompleted" 
                        type="text" 
                        placeholder="Cari No. Order, No. Resi, Kurir, Penerima, atau SKU Barang..." 
                        class="w-full bg-surface-container border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-xs lg:text-sm text-on-surface placeholder:text-on-surface-variant/40 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                </div>

                {{-- Row 2: Secondary Filters & Collapse Controls --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 border-t border-white/5">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Filter Tipe --}}
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-on-surface-variant/70 font-medium">Tipe:</span>
                            <select wire:model.live="filterTypeCompleted" class="bg-surface-container border border-white/10 rounded-xl py-2 px-3 text-xs text-on-surface font-semibold focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="ALL">Semua Tipe Transaksi</option>
                                <option value="BarangKeluar">Penjualan (Barang Keluar)</option>
                                <option value="TransferHeader">Transfer Antar Gudang</option>
                            </select>
                        </div>
                    </div>

                    {{-- Collapse Toggle All Button --}}
                    <div class="flex items-center gap-2">
                        <button type="button" @click="setAll(!allOpen)" 
                            class="px-4 py-2 rounded-xl border border-white/10 text-xs font-semibold text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm" x-text="allOpen ? 'unfold_less' : 'unfold_more'"></span>
                            <span x-text="allOpen ? 'Tutup Semua Rincian' : 'Buka Semua Rincian'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Collapsible Table Container --}}
            <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="w-full overflow-hidden">
                    <table class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/[0.02] text-[11px] font-black uppercase tracking-[0.2em] text-on-surface-variant/70">
                                <th class="py-4 px-3 w-12 text-center"></th>
                                <th class="py-4 px-4">No. Order & Tanggal</th>
                                <th class="py-4 px-4">Tujuan / Penerima</th>
                                <th class="py-4 px-4">Kurir & Resi</th>
                                <th class="py-4 px-4 text-center">Total Item</th>
                                <th class="py-4 px-4 text-center">Status</th>
                                <th class="py-4 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs">
                            @forelse ($completedTransactions as $t)
                                @php
                                    $rowKey = $t->type . '_' . $t->id;
                                @endphp
                                {{-- Parent Row --}}
                                <tr @click="toggle('{{ $rowKey }}')" class="hover:bg-white/[0.03] transition-colors cursor-pointer group">
                                    {{-- Expand Toggle Chevron --}}
                                    <td class="py-4 px-3 text-center">
                                        <button type="button" class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center transition-transform duration-200 group-hover:bg-primary/10 group-hover:border-primary/30"
                                            :class="open['{{ $rowKey }}'] ? 'rotate-90 text-primary bg-primary/10' : 'text-on-surface-variant/60'">
                                            <span class="material-symbols-outlined text-base">chevron_right</span>
                                        </button>
                                    </td>

                                    {{-- No. Order & Tanggal --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-sm text-on-surface group-hover:text-primary transition-colors">
                                                {{ $t->nomor_nota }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border {{ $t->type === 'BarangKeluar' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-purple-500/10 text-purple-400 border-purple-500/20' }}">
                                                {{ $t->type === 'BarangKeluar' ? 'PENJUALAN' : 'TRANSFER' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-on-surface-variant/60 font-mono mt-0.5">
                                            {{ $t->tanggal ? \Carbon\Carbon::parse($t->tanggal)->format('d M Y') : '-' }}
                                        </p>
                                    </td>

                                    {{-- Tujuan / Penerima --}}
                                    <td class="py-4 px-4">
                                        <p class="font-bold text-on-surface">{{ $t->destinasi }}</p>
                                    </td>

                                    {{-- Kurir & Resi --}}
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white/5 border border-white/10 font-bold text-on-surface text-xs whitespace-nowrap">
                                                <span class="material-symbols-outlined text-xs text-primary">local_shipping</span>
                                                {{ $t->kurir }}
                                            </span>
                                            @if (!empty($t->resi_ekspedisi) && $t->resi_ekspedisi !== '-')
                                                <span class="font-mono text-[10px] text-primary font-bold">
                                                    Resi: {{ $t->resi_ekspedisi }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Total Item --}}
                                    <td class="py-4 px-4 text-center font-mono font-bold text-on-surface">
                                        <span class="px-2.5 py-1 rounded-xl bg-white/5 border border-white/10 inline-block text-xs">
                                            {{ $t->items_count }} pcs
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-4 px-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-500/10 text-green-400 border border-green-500/20 whitespace-nowrap">
                                            <span class="material-symbols-outlined text-xs">local_shipping</span>
                                            TERKIRIM
                                        </span>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-4 px-4 text-center" @click.stop>
                                        <button type="button" 
                                            onclick="printShippingLabel('{{ $t->nomor_nota }}', '{{ $t->tanggal }}', '{{ addslashes($t->destinasi) }}', '{{ addslashes($t->alamat) }}', '{{ addslashes($t->kontak) }}')"
                                            class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 text-xs font-bold transition-all flex items-center gap-1 mx-auto whitespace-nowrap">
                                            <span class="material-symbols-outlined text-xs">print</span>
                                            Cetak Label
                                        </button>
                                    </td>
                                </tr>

                                {{-- Collapsible Child Row: Item Details --}}
                                <tr x-show="open['{{ $rowKey }}']" x-cloak class="bg-surface-dark/95 border-b border-white/10">
                                    <td colspan="7" class="p-6 lg:p-8">
                                        <div class="space-y-4">
                                            <div class="flex items-center justify-between pb-3 border-b border-white/5">
                                                <div>
                                                    <h4 class="text-xs font-bold text-on-surface flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-sm text-green-400">check_circle</span>
                                                        Rincian Barang yang Telah Dikirim ({{ $t->nomor_nota }})
                                                    </h4>
                                                    <p class="text-[11px] text-on-surface-variant/70 mt-0.5">
                                                        Alamat Pengiriman: {{ $t->alamat }} | Telepon/Kontak: {{ $t->kontak }}
                                                    </p>
                                                </div>

                                                <span class="text-xs font-mono text-primary font-bold">
                                                    Ekspedisi: {{ $t->kurir }} (Resi: {{ $t->resi_ekspedisi }})
                                                </span>
                                            </div>

                                            <div class="border border-white/10 rounded-2xl overflow-hidden bg-white/[0.02]">
                                                <table class="w-full text-left text-xs">
                                                    <thead>
                                                        <tr class="border-b border-white/5 bg-white/[0.03] text-[10px] font-bold text-on-surface-variant/60 uppercase">
                                                            <th class="py-3 px-4">Produk / SKU</th>
                                                            <th class="py-3 px-4 text-center">Jumlah Dikirim</th>
                                                            <th class="py-3 px-4 text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5">
                                                        @foreach ($t->details as $d)
                                                            @php
                                                                $qty = $t->type === 'BarangKeluar' ? $d->qty_keluar : $d->qty_sent;
                                                            @endphp
                                                            <tr class="hover:bg-white/[0.02] transition-colors">
                                                                <td class="py-3.5 px-4 flex items-center gap-3">
                                                                    <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                                        {{ substr($d->produk->kode_barang ?? 'PRD', 0, 3) }}
                                                                    </div>
                                                                    <div>
                                                                        <p class="font-bold text-on-surface text-xs">{{ $d->produk->nama_barang ?? '-' }}</p>
                                                                        <p class="text-[10px] text-on-surface-variant/60 font-mono mt-0.5">{{ $d->produk->kode_barang ?? '-' }}</p>
                                                                    </div>
                                                                </td>
                                                                <td class="py-3.5 px-4 text-center font-mono font-bold text-on-surface">
                                                                    {{ $qty }} {{ $d->satuan }}
                                                                </td>
                                                                <td class="py-3.5 px-4 text-center">
                                                                    <span class="px-2.5 py-1 rounded-xl bg-green-500/10 text-green-400 border border-green-500/20 text-[10px] font-bold uppercase">
                                                                        TERKIRIM ✓
                                                                    </span>
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
                                    <td colspan="7" class="text-center py-16 text-on-surface-variant/50">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <span class="material-symbols-outlined text-5xl text-on-surface-variant/30">task_alt</span>
                                            <p class="text-xs">Belum ada riwayat pengiriman.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Per Page Selector for Completed Shipments (10, 25, 50, 100) --}}
                <div class="p-6 border-t border-white/5 bg-white/[0.01] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant/80">
                            <span class="font-medium">Tampilkan:</span>
                            <select wire:model.live="perPageCompleted" class="bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-3 text-xs text-on-surface font-semibold focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                                <option value="100">100 per halaman</option>
                            </select>
                        </div>
                        <span class="text-xs text-on-surface-variant/60 font-mono">
                            Menampilkan {{ $completedTransactions->firstItem() ?? 0 }} - {{ $completedTransactions->lastItem() ?? 0 }} dari {{ $completedTransactions->total() }} riwayat kirim
                        </span>
                    </div>

                    <div>
                        {{ $completedTransactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Universal JavaScript for Printing Shipping Label via Hidden Iframe --}}
    @assets
    <script>
        function printShippingLabel(noOrder, tanggal, penerima, alamat, kontak) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }

            const labelHtml = `
                <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 24px; border: 2px solid #000; width: 100mm; max-width: 100%; box-sizing: border-box;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px;">
                        <span style="font-weight: 900; font-size: 16px; letter-spacing: 1px;">WMS SHIPPING LABEL</span>
                        <span style="font-size: 11px; font-weight: bold;">STANDARD QC</span>
                    </div>
                    <div style="font-size: 12px; margin-bottom: 12px; line-height: 1.4;">
                        <div><strong>No. Order:</strong> <span style="font-family: monospace; font-size: 13px;">${noOrder}</span></div>
                        <div><strong>Tanggal:</strong> ${tanggal}</div>
                    </div>
                    <div style="border-top: 1px dashed #000; padding-top: 12px; font-size: 12px; line-height: 1.4;">
                        <div style="text-transform: uppercase; font-size: 10px; color: #555; font-weight: bold;">Tujuan Pengiriman / Penerima:</div>
                        <div style="font-weight: 800; font-size: 15px; margin: 4px 0;">${penerima}</div>
                        <div style="color: #333; margin-bottom: 4px;">${alamat || '-'}</div>
                        <div><strong>Kontak / Tlp:</strong> ${kontak || '-'}</div>
                    </div>
                    <div style="margin-top: 16px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 10px; text-align: center; color: #777;">
                        Dicetak otomatis oleh Sistem WMS LAF Market
                    </div>
                </div>
            `;

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write('<html><head><title>Shipping Label - ' + noOrder + '</title><style>@page { size: auto; margin: 5mm; } body { margin: 0; padding: 0; }</style></head><body>' + labelHtml + '</body></html>');
            doc.close();

            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 300);
        }
    </script>
    @endassets
</div>
