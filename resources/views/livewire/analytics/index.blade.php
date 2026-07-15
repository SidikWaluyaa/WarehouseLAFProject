<div x-init="$dispatch('update-header-title', 'Analisis & Peramalan Stok')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari produk atau kategori..." type="text"/>
        </div>
    </template>

    <!-- Alert Toast -->
    

    

    <!-- Controls Row & Actions -->
    <section class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        <!-- Mobile Search Bar (Only visible on screens < md) -->
        <div class="md:hidden relative w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Cari produk atau kategori..." type="text"/>
        </div>

        <div class="flex-1"></div>

        <div>
            <button wire:click="recalculateAll" 
                    class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong w-full md:w-auto justify-center">
                <span class="material-symbols-outlined">sync</span>
                Kalkulasi Ulang Semua
            </button>
        </div>
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                        <th class="px-6 py-5">Produk</th>
                        <th class="px-6 py-5">Kategori</th>
                        <th class="px-6 py-5 text-center">Total Penjualan Setahun</th>
                        <th class="px-6 py-5 text-center">Rata-Rata Harian</th>
                        <th class="px-6 py-5 text-center">Leadtime</th>
                        <th class="px-6 py-5 text-center">Min Stock</th>
                        <th class="px-6 py-5 text-center">Safety Stock</th>
                        <th class="px-6 py-5 text-center">Max Stock</th>
                        <th class="px-6 py-5 text-center">Stok Terkini</th>
                        <th class="px-6 py-5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody x-data="{ expandedId: null }" class="divide-y divide-white/5 text-xs text-on-surface-variant">
                    @forelse ($analyticsList as $row)
                        @php
                            $totalSales = (int)$row->penjualan_jan + (int)$row->penjualan_feb + (int)$row->penjualan_mar +
                                         (int)$row->penjualan_apr + (int)$row->penjualan_mei + (int)$row->penjualan_jun +
                                         (int)$row->penjualan_jul + (int)$row->penjualan_ags + (int)$row->penjualan_sep +
                                         (int)$row->penjualan_okt + (int)$row->penjualan_nov + (int)$row->penjualan_des;
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors">
                            <!-- Produk -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                    <span class="font-bold text-on-surface">{{ $row->produk->nama_barang }}</span>
                                    @if ($row->is_manual_stock)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black border border-primary/20 bg-primary/10 text-primary uppercase tracking-wider" title="Batas level stok ditentukan secara manual">MANUAL</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black border border-tertiary/20 bg-tertiary/10 text-tertiary uppercase tracking-wider" title="Batas level stok dihitung otomatis oleh sistem">AUTO</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-primary/80 font-mono">{{ $row->produk->kode_barang }}</div>
                            </td>
                            <!-- Kategori -->
                            <td class="px-6 py-4 font-semibold text-on-surface">{{ $row->kategori }}</td>
                            <!-- Total Penjualan Setahun -->
                            <td class="px-6 py-4 text-center">
                                <button type="button" @click="expandedId = (expandedId === {{ $row->id }} ? null : {{ $row->id }})" class="inline-flex items-center gap-1.5 font-bold text-on-surface hover:text-primary focus:outline-none bg-white/5 hover:bg-white/10 py-1.5 px-3 rounded-xl border border-white/10 transition-all">
                                    <span>{{ number_format($totalSales) }} pasang</span>
                                    <span class="material-symbols-outlined text-sm transform transition-transform duration-200" :class="expandedId === {{ $row->id }} ? 'rotate-180 text-primary' : 'text-on-surface-variant/60'">
                                        keyboard_arrow_down
                                    </span>
                                </button>
                            </td>
                            <!-- Rata Harian -->
                            <td class="px-6 py-4 text-center font-bold text-on-surface">
                                {{ number_format($row->rata_rata_harian, 2, ',', '.') }}
                            </td>
                            <!-- Leadtime -->
                            <td class="px-6 py-4 text-center font-semibold text-on-surface">
                                {{ $row->leadtime_produksi }} hari
                            </td>
                            <!-- Min Stock -->
                            <td class="px-6 py-4 text-center font-bold text-on-surface">{{ number_format($row->minimal_stock) }}</td>
                            <!-- Safety Stock -->
                            <td class="px-6 py-4 text-center font-bold">{{ number_format($row->safety_stock) }}</td>
                            <!-- Max Stock -->
                            <td class="px-6 py-4 text-center font-bold">{{ number_format($row->maximum_stock) }}</td>
                            <!-- Stok Terkini & Alert Comparison -->
                            <td class="px-6 py-4 text-center">
                                @php 
                                    $actualStok = $row->produk->total_stok;
                                    $isBelowMin = $actualStok < $row->minimal_stock;
                                    $isAboveMax = $actualStok > $row->maximum_stock;
                                @endphp

                                @if ($isBelowMin)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold border border-error/20 bg-error/10 text-error uppercase tracking-wider">
                                        {{ $actualStok }} (MINIMUM)
                                    </span>
                                @elseif ($isAboveMax)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold border border-primary/20 bg-primary/10 text-primary uppercase tracking-wider">
                                        {{ $actualStok }} (OVERSTOK)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold border border-green-500/20 bg-green-500/10 text-green-400 uppercase tracking-wider">
                                        {{ $actualStok }} (AMAN)
                                    </span>
                                @endif
                            </td>
                            <!-- Aksi -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button wire:click="edit({{ $row->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all mx-auto" title="Edit Data Penjualan & Leadtime">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Baris Rincian Detail 12 Bulan (Accordion) -->
                        <tr x-show="expandedId === {{ $row->id }}" x-cloak class="bg-white/[0.01]">
                            <td colspan="10" class="px-6 py-4">
                                <div class="glass-card rounded-2xl p-5 space-y-4 bg-surface-dark/40 border-white/5">
                                    <div class="flex items-center justify-between border-b border-white/5 pb-2">
                                        <h5 class="text-xs font-black text-primary uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-sm text-primary">bar_chart</span>
                                            Rincian Penjualan Bulanan (pasang)
                                        </h5>
                                        <button wire:click="edit({{ $row->id }})" class="inline-flex items-center px-4 py-1.5 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                                            <span class="material-symbols-outlined text-sm mr-1">edit</span>
                                            Ubah Data Penjualan
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-3 text-center">
                                        @php
                                            $months = [
                                                'Jan' => $row->penjualan_jan, 'Feb' => $row->penjualan_feb, 'Mar' => $row->penjualan_mar,
                                                'Apr' => $row->penjualan_apr, 'Mei' => $row->penjualan_mei, 'Jun' => $row->penjualan_jun,
                                                'Jul' => $row->penjualan_jul, 'Ags' => $row->penjualan_ags, 'Sep' => $row->penjualan_sep,
                                                'Okt' => $row->penjualan_okt, 'Nov' => $row->penjualan_nov, 'Des' => $row->penjualan_des
                                            ];
                                        @endphp
                                        @foreach ($months as $mName => $mVal)
                                            <div class="p-2.5 bg-white/5 rounded-2xl border border-white/5">
                                                <span class="block text-[10px] font-bold text-on-surface-variant/40 uppercase mb-1">{{ $mName }}</span>
                                                <span class="text-xs font-black text-on-surface">{{ number_format($mVal) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada data analisis stok ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $analyticsList->firstItem() ?? 0 }} s.d. {{ $analyticsList->lastItem() ?? 0 }} dari {{ $analyticsList->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($analyticsList->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($analyticsList->getUrlRange(max(1, $analyticsList->currentPage() - 2), min($analyticsList->lastPage(), $analyticsList->currentPage() + 2)) as $page => $url)
                    @if ($page == $analyticsList->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($analyticsList->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- 12-Month CRUD Modal -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-4xl mx-auto my-6 z-50">
                <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                        <div>
                            <h3 class="text-base font-bold text-primary flex items-center gap-2">
                                <span class="material-symbols-outlined">analytics</span>
                                Input Manual Penjualan & Leadtime
                            </h3>
                            <p class="text-xs text-on-surface-variant/80 font-semibold mt-1">{{ $produkNama }} ({{ $produkKode }})</p>
                        </div>
                        <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 max-h-[65vh] overflow-y-auto custom-scrollbar">
                        <!-- Left & Middle: 12 Months Input Form (Grid 4x3) -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="flex items-center justify-between border-b border-white/5 pb-2">
                                <h4 class="text-xs font-black text-primary uppercase tracking-wider">Data Penjualan Per Bulan (Pcs)</h4>
                                <button type="button" wire:click="syncFromDatabase" class="inline-flex items-center px-4 py-1.5 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm" title="Tarik data transaksi riil dari database">
                                    <span class="material-symbols-outlined text-sm mr-1.5">sync</span>
                                    Tarik Data Otomatis
                                </button>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                @php
                                    $monthFields = [
                                        'jan' => 'Januari', 'feb' => 'Februari', 'mar' => 'Maret',
                                        'apr' => 'April', 'mei' => 'Mei', 'jun' => 'Juni',
                                        'jul' => 'Juli', 'ags' => 'Agustus', 'sep' => 'September',
                                        'okt' => 'Oktober', 'nov' => 'November', 'des' => 'Desember'
                                    ];
                                @endphp
                                @foreach ($monthFields as $mKey => $mName)
                                    <div>
                                        <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">{{ $mName }}</label>
                                        <input wire:model.live.debounce.200ms="sales.{{ $mKey }}" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs font-bold text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        @error("sales.{$mKey}") <span class="text-[9px] text-error block mt-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                @endforeach
                            </div>

                            <!-- Leadtime Input -->
                            <div class="pt-4 border-t border-white/5">
                                <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Leadtime Produksi / Waktu Tunggu Vendor (Hari)</label>
                                <input wire:model.live.debounce.200ms="leadtime_produksi" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm font-bold text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Default: 14 hari">
                                @error('leadtime_produksi') <span class="text-xs text-error mt-1.5 block font-semibold">{{ $message }}</span> @enderror
                            </div>

                            <!-- Mode Hibrida Toggle -->
                            <div class="pt-6 border-t border-white/5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-xs font-black text-on-surface uppercase tracking-wider block">Gunakan Level Stok Manual</label>
                                        <span class="text-[10px] text-on-surface-variant/60 font-semibold block mt-0.5">Aktifkan untuk menentukan sendiri batas Min, Safety, & Max Stock</span>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer">
                                        <!-- Custom Toggle Switch -->
                                        <button type="button" 
                                                @click="$wire.set('is_manual_stock', !$wire.is_manual_stock)"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                                :class="$wire.is_manual_stock ? 'bg-primary-container' : 'bg-white/10'">
                                            <span class="sr-only">Toggle manual stock override</span>
                                            <span aria-hidden="true" 
                                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                  :class="$wire.is_manual_stock ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Stock Inputs -->
                            <div x-show="$wire.is_manual_stock" x-transition class="pt-6 space-y-4 border-t border-white/5">
                                <h4 class="text-xs font-black text-primary uppercase tracking-wider">Batas Level Stok Kustom (Pcs)</h4>
                                <div class="grid grid-cols-3 gap-4">
                                    <!-- Min Stock -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Min Stock</label>
                                        <input wire:model.live.debounce.200ms="minimal_stock" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs font-bold text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        @error('minimal_stock') <span class="text-[9px] text-error block mt-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <!-- Safety Stock -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Safety Stock</label>
                                        <input wire:model.live.debounce.200ms="safety_stock" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs font-bold text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        @error('safety_stock') <span class="text-[9px] text-error block mt-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                    <!-- Max Stock -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Max Stock</label>
                                        <input wire:model.live.debounce.200ms="maximum_stock" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs font-bold text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        @error('maximum_stock') <span class="text-[9px] text-error block mt-1 font-semibold">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Live Calculated Preview Card -->
                        <div class="lg:col-span-1">
                            <div class="glass-card rounded-2xl p-5 h-full flex flex-col justify-between border-white/5 bg-white/[0.02]">
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black text-primary uppercase tracking-wider mb-4 pb-2 border-b border-white/5 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Live Forecast Preview
                                    </h4>

                                    <div class="space-y-4">
                                        <!-- Total Sales -->
                                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl">
                                            <span class="text-[10px] font-bold text-on-surface-variant/40 uppercase">Total Sales Setahun</span>
                                            <p class="text-base font-black text-on-surface mt-1">{{ number_format($this->preview['totalSales']) }} pasang</p>
                                        </div>

                                        <!-- Daily Average -->
                                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl">
                                            <span class="text-[10px] font-bold text-on-surface-variant/40 uppercase">Rata-rata Penjualan Harian</span>
                                            <p class="text-base font-black text-primary mt-1">{{ number_format($this->preview['rataRataHarian'], 3, ',', '.') }} pasang / hari</p>
                                        </div>

                                        <!-- Min Stock -->
                                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl">
                                            <span class="text-[10px] font-bold text-on-surface-variant/40 uppercase">Minimal Stock (Reorder Point)</span>
                                            <p class="text-base font-black text-on-surface mt-1">{{ number_format($this->preview['minimalStock']) }} pasang</p>
                                            <span class="text-[9px] text-on-surface-variant/50 block mt-1">Rata-rata x Leadtime ({{ $leadtime_produksi }} hari)</span>
                                        </div>

                                        <!-- Safety Stock -->
                                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl">
                                            <span class="text-[10px] font-bold text-on-surface-variant/40 uppercase">Safety Stock (Buffer)</span>
                                            <p class="text-base font-black text-primary mt-1">{{ number_format($this->preview['safetyStock']) }} pasang</p>
                                            <span class="text-[9px] text-on-surface-variant/50 block mt-1">Minimal Stock x 1.5 (Buffer 50%)</span>
                                        </div>

                                        <!-- Max Stock -->
                                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl">
                                            <span class="text-[10px] font-bold text-on-surface-variant/40 uppercase">Maximum Stock</span>
                                            <p class="text-base font-black text-tertiary mt-1">{{ number_format($this->preview['maximumStock']) }} pasang</p>
                                            <span class="text-[9px] text-on-surface-variant/50 block mt-1">Safety Stock x 1.5</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-white/5 mt-4">
                                    <span class="text-[9px] text-on-surface-variant/40 font-semibold block leading-relaxed">
                                        *Perhitungan di atas dimutakhirkan secara instan saat Anda mengubah input angka penjualan atau leadtime.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 bg-surface-container-low/50 space-x-3">
                        <button wire:click="closeModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                            Batal
                        </button>
                        <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
