<div x-init="$dispatch('update-header-title', 'Dashboard WMS')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    <!-- Styles for custom analytics elements -->
    <style>
        .abc-badge-a {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .abc-badge-b {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        .abc-badge-c {
            background-color: rgba(107, 114, 128, 0.1);
            color: #9ca3af;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }
        html.light .abc-badge-a {
            background-color: #FEE2E2 !important;
            color: #B91C1C !important;
            border-color: #FCA5A5 !important;
        }
        html.light .abc-badge-b {
            background-color: #FEF3C7 !important;
            color: #B45309 !important;
            border-color: #FCD34D !important;
        }
        html.light .abc-badge-c {
            background-color: #F1F5F9 !important;
            color: #475569 !important;
            border-color: #CBD5E1 !important;
        }
    </style>

    <!-- KPI Section -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Produk -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary/10 text-6xl group-hover:scale-110 transition-transform duration-700">inventory</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Total Jenis Produk</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($totalProduk) }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                <span>Tercatat di Master Produk</span>
            </div>
        </div>

        <!-- Card 2: Total Stok Gudang -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary/10 text-6xl group-hover:scale-110 transition-transform duration-700">warehouse</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Total Stok Fisik</p>
            <h3 class="text-headline-lg font-bold text-on-surface">{{ number_format($totalStok) }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                <span>Akumulasi stok saat ini</span>
            </div>
        </div>

        <!-- Card 3: Inventory Value -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-primary/20 transition-all duration-500 amber-glow">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-primary/10 text-6xl group-hover:scale-110 transition-transform duration-700">payments</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Total Nilai Persediaan</p>
            <h3 class="text-headline-lg font-bold text-on-surface">Rp{{ number_format($inventoryValue, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider text-primary">
                <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                <span>Total Stok Gudang & Toko x HPP</span>
            </div>
        </div>

        <!-- Card 4: Dead Stock / Tied-up Capital -->
        <div class="glass-card rounded-3xl p-6 relative overflow-hidden group hover:border-error/20 transition-all duration-500 {{ $deadStockCount > 0 ? 'border-error/20 bg-error/5' : '' }}">
            <div class="absolute right-6 top-1/2 -translate-y-1/2 material-symbols-outlined text-error/10 text-6xl group-hover:scale-110 transition-transform duration-700">monetization_on</div>
            <p class="text-on-surface-variant text-sm font-medium mb-1">Modal Mengendap (Dead Stock)</p>
            <h3 class="text-headline-lg font-bold {{ $deadStockCount > 0 ? 'text-error' : 'text-green-500' }}">Rp{{ number_format($deadStockValue, 0, ',', '.') }}</h3>
            <div class="mt-4 flex items-center text-[10px] uppercase tracking-wider {{ $deadStockCount > 0 ? 'text-error' : 'text-green-500' }}">
                <span class="material-symbols-outlined text-sm mr-1">warning</span>
                <span>{{ $deadStockCount }} Persediaan Mati</span>
            </div>
        </div>
    </section>

    <!-- Warehouse Operations Section -->
    <section class="space-y-4">
        <h3 class="text-xs font-bold tracking-[0.15em] text-primary uppercase flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">local_shipping</span>
            Warehouse Operations Today
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Receiving Today -->
            <div class="glass-card rounded-3xl p-5 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
                <div class="absolute -top-2 -right-2 material-symbols-outlined text-primary/5 text-6xl">input</div>
                <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-wider mb-1">Receiving Today</p>
                <h3 class="text-2xl font-black text-on-surface">{{ number_format($receivingToday) }}</h3>
                <p class="text-[9px] text-on-surface-variant/60 mt-1">Total GRN Terbit Hari Ini</p>
            </div>
            
            <!-- Picking Today -->
            <div class="glass-card rounded-3xl p-5 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
                <div class="absolute -top-2 -right-2 material-symbols-outlined text-primary/5 text-6xl">fact_check</div>
                <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-wider mb-1">Picking Today</p>
                <h3 class="text-2xl font-black text-on-surface">{{ number_format($pickingToday) }}</h3>
                <p class="text-[9px] text-on-surface-variant/60 mt-1">Total Order Dipicking Hari Ini</p>
            </div>

            <!-- Shipment Today -->
            <div class="glass-card rounded-3xl p-5 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
                <div class="absolute -top-2 -right-2 material-symbols-outlined text-primary/5 text-6xl">output</div>
                <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-wider mb-1">Shipment Today</p>
                <h3 class="text-2xl font-black text-on-surface">{{ number_format($shipmentToday) }}</h3>
                <p class="text-[9px] text-on-surface-variant/60 mt-1">Total Barang Keluar Hari Ini</p>
            </div>

            <!-- Pending Transfer -->
            <div class="glass-card rounded-3xl p-5 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
                <div class="absolute -top-2 -right-2 material-symbols-outlined text-primary/5 text-6xl">swap_horiz</div>
                <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-wider mb-1">Pending Transfer</p>
                <h3 class="text-2xl font-black text-amber-400">{{ number_format($pendingTransfer) }}</h3>
                <p class="text-[9px] text-on-surface-variant/60 mt-1">Draft & In Transit Orders</p>
            </div>

            <!-- Replenishment Request -->
            <div class="glass-card rounded-3xl p-5 relative overflow-hidden group hover:border-primary/20 transition-all duration-500">
                <div class="absolute -top-2 -right-2 material-symbols-outlined text-primary/5 text-6xl">notifications_active</div>
                <p class="text-on-surface-variant text-[10px] font-bold uppercase tracking-wider mb-1">Replenishment Req</p>
                <h3 class="text-2xl font-black text-primary">{{ number_format($replenishmentRequest) }}</h3>
                <p class="text-[9px] text-on-surface-variant/60 mt-1">Rekomendasi Isi Stok Toko</p>
            </div>
        </div>
    </section>

    <!-- Split Section: Reorder Recommendations & ABC Analysis -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Reorder Recommendations Widget (Left 2 Columns) -->
        <div class="xl:col-span-2 glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/5 pb-3">
                <h4 class="text-base font-bold text-on-surface flex items-center">
                    <span class="material-symbols-outlined mr-2 text-primary">order_play_list</span>
                    Rekomendasi Pemesanan & Days of Inventory (DOI)
                </h4>
                <span class="text-xs text-on-surface-variant font-medium">Stok di Bawah Batas Minimum</span>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="py-3 px-2">Produk</th>
                            <th class="py-3 px-2 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-2 text-center">DOI (Ketahanan)</th>
                            <th class="py-3 px-2 text-center">Titik ROP</th>
                            <th class="py-3 px-2 text-center">Batas Maksimal</th>
                            <th class="py-3 px-2 text-right">Rekomendasi Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-on-surface-variant">
                        @forelse($reorderProducts as $p)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3.5 px-2">
                                    <div class="font-bold text-on-surface">{{ $p->nama_barang }}</div>
                                    <div class="text-[10px] text-primary/80 font-mono">{{ $p->kode_barang }}</div>
                                </td>
                                <td class="py-3.5 px-2 text-center font-bold text-error">
                                    {{ $p->total_stok }} {{ $p->satuan }}
                                </td>
                                <td class="py-3.5 px-2 text-center">
                                    @if($p->doi === 999)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-500/10 text-slate-400 font-bold border border-slate-500/20">Tidak Bergerak</span>
                                    @elseif($p->doi <= 7)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-red-500/10 text-red-400 font-bold border border-red-500/20 animate-pulse">{{ $p->doi }} Hari (Kritis)</span>
                                    @elseif($p->doi <= 14)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/10 text-amber-400 font-bold border border-amber-500/20">{{ $p->doi }} Hari (Rendah)</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-green-500/10 text-green-400 font-bold border border-green-500/20">{{ $p->doi }} Hari (Aman)</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-2 text-center font-medium text-on-surface">
                                    {{ $p->rop }} {{ $p->satuan }}
                                </td>
                                <td class="py-3.5 px-2 text-center">
                                    {{ $p->maximum_stock ?? '-' }} {{ $p->satuan }}
                                </td>
                                <td class="py-3.5 px-2 text-right">
                                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-primary/10 text-primary border border-primary/20">
                                        +{{ $p->reorder_qty }} {{ $p->satuan }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-on-surface-variant font-medium">Semua level stok produk aman di atas batas minimum.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ABC Classification & Dead Stock Summary (Right Column) -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl flex flex-col justify-between space-y-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-white/5 pb-3">
                    <h4 class="text-base font-bold text-on-surface flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">analytics</span>
                        Analisis Klasifikasi ABC
                    </h4>
                </div>
                
                <p class="text-xs text-on-surface-variant leading-relaxed">
                    Pengelompokan persediaan berdasarkan kontribusi nilai penjualan selama 12 bulan terakhir.
                </p>

                <!-- Class A -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/5 border border-white/5">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-lg abc-badge-a">Kelas A</span>
                        <div class="text-xs">
                            <div class="font-bold text-on-surface">Fast Moving (Penting)</div>
                            <div class="text-[10px] text-on-surface-variant/80">Kontribusi 80% Penjualan</div>
                        </div>
                    </div>
                    <span class="text-sm font-black text-on-surface">{{ $classACount }} SKU</span>
                </div>

                <!-- Class B -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/5 border border-white/5">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-lg abc-badge-b">Kelas B</span>
                        <div class="text-xs">
                            <div class="font-bold text-on-surface">Medium Moving</div>
                            <div class="text-[10px] text-on-surface-variant/80">Kontribusi 15% Penjualan</div>
                        </div>
                    </div>
                    <span class="text-sm font-black text-on-surface">{{ $classBCount }} SKU</span>
                </div>

                <!-- Class C -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-white/5 border border-white/5">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs font-black px-2.5 py-1 rounded-lg abc-badge-c">Kelas C</span>
                        <div class="text-xs">
                            <div class="font-bold text-on-surface">Slow Moving (Gudang)</div>
                            <div class="text-[10px] text-on-surface-variant/80">Kontribusi 5% Penjualan</div>
                        </div>
                    </div>
                    <span class="text-sm font-black text-on-surface">{{ $classCCount }} SKU</span>
                </div>
            </div>

            <div class="p-4 bg-error/5 border border-error/20 rounded-2xl space-y-2">
                <div class="flex items-center space-x-2 text-error">
                    <span class="material-symbols-outlined text-lg">warning</span>
                    <span class="text-xs font-bold font-outfit uppercase tracking-wider">Peringatan Dead Stock</span>
                </div>
                <p class="text-[11px] text-on-surface-variant leading-relaxed">
                    Terdapat **{{ $deadStockCount }} jenis barang** yang memiliki stok fisik tetapi **0 penjualan** selama setahun terakhir. Segera lakukan promo promosi penjualan untuk membebaskan kapasitas gudang.
                </p>
            </div>
        </div>
    </div>

    <!-- Seasonal Demand Trend Widget -->
    <section class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-white/5 pb-3 gap-2">
            <h4 class="text-base font-bold text-on-surface flex items-center">
                <span class="material-symbols-outlined mr-2 text-primary">show_chart</span>
                Pola Permintaan Musiman (*Seasonal Sales Trend*)
            </h4>
            
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-xs text-on-surface-variant/80 font-bold">Top 3 Bulan Peak Season:</span>
                @forelse($peakMonths as $m)
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] bg-primary/10 text-primary border border-primary/20 font-black">{{ $m }}</span>
                @empty
                    <span class="text-xs text-on-surface-variant/50">-</span>
                @endforelse
            </div>
        </div>

        <!-- Custom Tailwind CSS Bar Graph -->
        @php
            $maxMonthlySales = max(1, max($monthlySales));
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-4 pt-2">
            @foreach($monthlySales as $month => $sales)
                @php
                    $percentage = ($sales / $maxMonthlySales) * 100;
                @endphp
                <div class="flex flex-col items-center space-y-3">
                    <div class="relative w-full bg-white/5 rounded-xl h-24 flex items-end overflow-hidden border border-white/5">
                        <!-- Bar fill -->
                        <div class="w-full bg-gradient-to-t from-primary-container to-[#ff9f00] rounded-b-xl transition-all duration-1000 shadow-[0_0_8px_rgba(255,178,44,0.3)]" style="height: {{ max(4, $percentage) }}%"></div>
                        <!-- Floating quantity tooltip on hover -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 bg-[#0c1216]/90 transition-opacity duration-200">
                            <span class="text-[10px] font-black text-on-surface">{{ number_format($sales) }} pcs</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <span class="text-xs font-bold text-on-surface">{{ $month }}</span>
                        <div class="text-[9px] text-on-surface-variant/70 font-semibold">{{ number_format($sales) }} pcs</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Split Grid: Transaksi Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Panel 1: Barang Masuk Terbaru -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/5 pb-3">
                <h4 class="text-base font-bold text-on-surface flex items-center">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500 mr-2 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
                    Barang Masuk Terbaru (Pembelian)
                </h4>
                <a href="{{ route('barang-masuk') }}" class="text-xs font-semibold text-primary hover:underline">Kelola &rarr;</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="py-3 px-1">Tanggal</th>
                            <th class="py-3 px-1">Produk</th>
                            <th class="py-3 px-1 text-center">QTY</th>
                            <th class="py-3 px-1">Supplier</th>
                            <th class="py-3 px-1 text-right">HPP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-on-surface-variant">
                        @forelse($recentMasuk as $bm)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 px-1 font-medium">{{ $bm->barangMasukHeader->tanggal->format('d/m/Y') }}</td>
                                <td class="py-3 px-1">
                                    <div class="font-bold text-on-surface">{{ $bm->produk->nama_barang }}</div>
                                    <div class="text-[10px] text-primary/80 font-mono">{{ $bm->produk->kode_barang }}</div>
                                </td>
                                <td class="py-3 px-1 text-center font-black text-primary">{{ $bm->qty_masuk }}</td>
                                <td class="py-3 px-1 font-medium">{{ $bm->barangMasukHeader->supplier->nama_supplier ?? '-' }}</td>
                                <td class="py-3 px-1 text-right font-semibold text-on-surface">Rp{{ number_format($bm->hpp, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant">Belum ada transaksi barang masuk</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel 2: Barang Keluar Terbaru -->
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
            <div class="flex items-center justify-between mb-4 border-b border-white/5 pb-3">
                <h4 class="text-base font-bold text-on-surface flex items-center">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary-container mr-2 shadow-[0_0_8px_rgba(255,178,44,0.5)]"></span>
                    Barang Keluar Terbaru (Penjualan)
                </h4>
                <a href="{{ route('barang-keluar') }}" class="text-xs font-semibold text-primary hover:underline">Kelola &rarr;</a>
            </div>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="py-3 px-1">Tanggal</th>
                            <th class="py-3 px-1">Produk</th>
                            <th class="py-3 px-1 text-center">QTY</th>
                            <th class="py-3 px-1">Pelanggan</th>
                            <th class="py-3 px-1 text-right">HPP Snapshot</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-on-surface-variant">
                        @forelse($recentKeluar as $bk)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3 px-1 font-medium">{{ $bk->barangKeluarHeader->tanggal->format('d/m/Y') }}</td>
                                <td class="py-3 px-1">
                                    <div class="font-bold text-on-surface">{{ $bk->produk->nama_barang }}</div>
                                    <div class="text-[10px] text-primary/80 font-mono">{{ $bk->produk->kode_barang }}</div>
                                </td>
                                <td class="py-3 px-1 text-center font-black text-primary">{{ $bk->qty_keluar }}</td>
                                <td class="py-3 px-1 font-medium">{{ $bk->barangKeluarHeader->pelanggan->nama_pelanggan }}</td>
                                <td class="py-3 px-1 text-right font-semibold text-on-surface">Rp{{ number_format($bk->hpp, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-on-surface-variant">Belum ada transaksi barang keluar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
