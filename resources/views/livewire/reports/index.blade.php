<div x-init="$dispatch('update-header-title', 'Laporan & Analitik')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    {{-- Report Type Tabs --}}
    <div class="flex gap-2 flex-wrap bg-surface-container/30 p-2.5 rounded-3xl border border-white/5 backdrop-blur-md">
        <button wire:click="$set('reportType', 'stock-card')" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-300 flex items-center gap-2 {{ $reportType === 'stock-card' ? 'bg-primary text-on-primary shadow-lg' : 'bg-transparent text-on-surface-variant hover:bg-white/5 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-sm">credit_card</span> Kartu Stok
        </button>
        <button wire:click="$set('reportType', 'stock-movement')" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-300 flex items-center gap-2 {{ $reportType === 'stock-movement' ? 'bg-primary text-on-primary shadow-lg' : 'bg-transparent text-on-surface-variant hover:bg-white/5 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-sm">swap_vert</span> Mutasi Stok
        </button>
        <button wire:click="$set('reportType', 'aging-stock')" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-300 flex items-center gap-2 {{ $reportType === 'aging-stock' ? 'bg-primary text-on-primary shadow-lg' : 'bg-transparent text-on-surface-variant hover:bg-white/5 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-sm">hourglass_empty</span> Usia & Dead Stock
        </button>
        <button wire:click="$set('reportType', 'sales-support')" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-300 flex items-center gap-2 {{ $reportType === 'sales-support' ? 'bg-primary text-on-primary shadow-lg' : 'bg-transparent text-on-surface-variant hover:bg-white/5 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-sm">trending_up</span> Analisis Penjualan & Stok
        </button>
        <button wire:click="$set('reportType', 'warehouse-performance')" class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all duration-300 flex items-center gap-2 {{ $reportType === 'warehouse-performance' ? 'bg-primary text-on-primary shadow-lg' : 'bg-transparent text-on-surface-variant hover:bg-white/5 hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-sm">precision_manufacturing</span> Kinerja Gudang
        </button>
    </div>

    {{-- 1. Stock Card Report --}}
    @if($reportType === 'stock-card')
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">credit_card</span>
                        Kartu Stok (Stock Card)
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-1">Lacak riwayat masuk-keluar mendetail untuk satu SKU tertentu.</p>
                </div>
                @if(!empty($stockCardData))
                    <div class="flex gap-2">
                        <button wire:click="exportCsv('stock-card')" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined text-sm">download</span> Ekspor CSV
                        </button>
                        <a href="{{ route('reports.print') }}?reportType=stock-card&sc_produk_id={{ $sc_produk_id }}&sc_date_from={{ $sc_date_from }}&sc_date_to={{ $sc_date_to }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined text-sm">print</span> Cetak PDF
                        </a>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white/5 p-4 rounded-2xl border border-white/5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Pilih Produk *</label>
                    <select wire:model="sc_produk_id" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($productsList as $p)
                            <option value="{{ $p->id }}">{{ $p->kode_barang }} — {{ $p->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Dari Tanggal</label>
                    <input wire:model="sc_date_from" type="date" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Sampai Tanggal</label>
                    <input wire:model="sc_date_to" type="date" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                </div>
            </div>

            <button wire:click="generateStockCard" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-lg">search</span>
                Tampilkan Kartu Stok
            </button>
        </div>

        {{-- Stock Card Results --}}
        @if(!empty($stockCardData))
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-white/5 pb-4">
                    <div>
                        <h4 class="text-base font-bold text-on-surface">{{ $stockCardData['produk']->nama_barang }}</h4>
                        <p class="text-xs text-primary font-mono mt-0.5">{{ $stockCardData['produk']->kode_barang }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold">Saldo Awal Periode</p>
                        <p class="text-lg font-black text-on-surface">{{ $stockCardData['saldo_awal'] }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                                <th class="py-3 px-3">Tanggal</th>
                                <th class="py-3 px-3">Tipe</th>
                                <th class="py-3 px-3">Keterangan</th>
                                <th class="py-3 px-3">Asal</th>
                                <th class="py-3 px-3">Tujuan</th>
                                <th class="py-3 px-3 text-center">Masuk</th>
                                <th class="py-3 px-3 text-center">Keluar</th>
                                <th class="py-3 px-3 text-center">Saldo</th>
                                <th class="py-3 px-3">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($stockCardData['rows'] as $row)
                                <tr class="hover:bg-white/5 transition-colors text-on-surface-variant">
                                    <td class="py-2.5 px-3 text-on-surface whitespace-nowrap">{{ $row['tanggal'] }}</td>
                                    <td class="py-2.5 px-3">
                                        @php
                                            $tipeColors = [
                                                'MASUK' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                'KELUAR' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'TRANSFER' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'OPNAME_ADJUST' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black border {{ $tipeColors[$row['tipe']] ?? 'bg-white/5 text-on-surface-variant' }}">{{ $row['tipe'] }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 max-w-[200px] truncate">{{ $row['keterangan'] }}</td>
                                    <td class="py-2.5 px-3 font-mono text-[10px]">{{ $row['lokasi_asal'] }}</td>
                                    <td class="py-2.5 px-3 font-mono text-[10px]">{{ $row['lokasi_tujuan'] }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold {{ $row['qty_masuk'] > 0 ? 'text-green-400' : 'text-on-surface-variant/30' }}">{{ $row['qty_masuk'] > 0 ? '+' . $row['qty_masuk'] : '-' }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold {{ $row['qty_keluar'] > 0 ? 'text-red-400' : 'text-on-surface-variant/30' }}">{{ $row['qty_keluar'] > 0 ? '-' . $row['qty_keluar'] : '-' }}</td>
                                    <td class="py-2.5 px-3 text-center font-black text-on-surface">{{ $row['saldo'] }}</td>
                                    <td class="py-2.5 px-3 text-on-surface-variant/60">{{ $row['operator'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-2 border-t border-white/5">
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold">Saldo Akhir Periode</p>
                        <p class="text-xl font-black text-primary">{{ $stockCardData['saldo_akhir'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- 2. Stock Movement Report --}}
    @if($reportType === 'stock-movement')
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">swap_vert</span>
                        Laporan Mutasi Stok (Stock Movement)
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-1">Daftar lengkap pergerakan stok lintas lokasi penyimpanan gudang & retail.</p>
                </div>
                @if(!empty($movementData))
                    <div class="flex gap-2">
                        <button wire:click="exportCsv('stock-movement')" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined text-sm">download</span> Ekspor CSV
                        </button>
                        <a href="{{ route('reports.print') }}?reportType=stock-movement&sm_date_from={{ $sm_date_from }}&sm_date_to={{ $sm_date_to }}&sm_tipe={{ $sm_tipe }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                            <span class="material-symbols-outlined text-sm">print</span> Cetak PDF
                        </a>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/5 p-4 rounded-2xl border border-white/5">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Dari Tanggal</label>
                    <input wire:model="sm_date_from" type="date" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Sampai Tanggal</label>
                    <input wire:model="sm_date_to" type="date" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Tipe Mutasi</label>
                    <select wire:model="sm_tipe" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Semua Tipe</option>
                        <option value="MASUK">Masuk</option>
                        <option value="KELUAR">Keluar</option>
                        <option value="TRANSFER">Transfer</option>
                        <option value="OPNAME_ADJUST">Opname Adjust</option>
                    </select>
                </div>
            </div>

            <button wire:click="generateMovement" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-lg">search</span>
                Tampilkan Mutasi
            </button>
        </div>

        @if(!empty($movementData))
            <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4 text-center">Tipe</th>
                                <th class="py-3 px-4 text-center">Qty</th>
                                <th class="py-3 px-4">Asal</th>
                                <th class="py-3 px-4">Tujuan</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($movementData as $m)
                                <tr class="hover:bg-white/5 transition-colors text-on-surface-variant">
                                    <td class="py-2.5 px-4 text-on-surface whitespace-nowrap text-[11px]">{{ \Carbon\Carbon::parse($m['created_at'])->format('d/m/Y H:i') }}</td>
                                    <td class="py-2.5 px-4">
                                        <div class="font-bold text-on-surface">{{ $m['produk']['nama_barang'] ?? '-' }}</div>
                                        <div class="text-[10px] text-primary/80 font-mono mt-0.5">{{ $m['produk']['kode_barang'] ?? '' }}</div>
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        @php
                                            $tipeColors = [
                                                'MASUK' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                                'KELUAR' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                                'TRANSFER' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                                'OPNAME_ADJUST' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-lg text-[9px] font-black border {{ $tipeColors[$m['tipe_mutasi']] ?? '' }}">{{ $m['tipe_mutasi'] }}</span>
                                    </td>
                                    <td class="py-2.5 px-4 text-center font-black text-on-surface">{{ $m['qty'] }}</td>
                                    <td class="py-2.5 px-4 font-mono text-[10px]">{{ $m['lokasi_asal']['kode_lokasi'] ?? '-' }}</td>
                                    <td class="py-2.5 px-4 font-mono text-[10px]">{{ $m['lokasi_tujuan']['kode_lokasi'] ?? '-' }}</td>
                                    <td class="py-2.5 px-4 max-w-[200px] truncate">{{ $m['keterangan'] }}</td>
                                    <td class="py-2.5 px-4 text-on-surface-variant/60">{{ $m['data_dimasukan_oleh'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/5 text-xs text-on-surface-variant flex justify-between items-center">
                    <span>Menampilkan {{ count($movementData) }} record (maks. 200)</span>
                </div>
            </div>
        @endif
    @endif

    {{-- 3. Aging Stock Report --}}
    @if($reportType === 'aging-stock')
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
            <div class="flex justify-between items-start flex-wrap gap-4">
                <div>
                    <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">hourglass_empty</span>
                        Laporan Analisis Usia Stok (Stock Aging Report)
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-1">Mengukur umur barang tersimpan di gudang guna mengidentifikasi Slow Moving & Dead Stock.</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="exportCsv('aging-stock')" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-sm">download</span> Ekspor CSV
                    </button>
                    <a href="{{ route('reports.print') }}?reportType=aging-stock&aging_kategori1={{ $aging_kategori1 }}&aging_kategori2={{ $aging_kategori2 }}&aging_status={{ $aging_status }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-sm">print</span> Cetak PDF
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white/5 p-4 rounded-2xl border border-white/5">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Kategori 1 (Jenis)</label>
                    <select wire:model.live="aging_kategori1" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Semua Kategori</option>
                        <option value="SANDAL">SANDAL</option>
                        <option value="SEPATU">SEPATU</option>
                        <option value="TAS">TAS</option>
                        <option value="APPAREL">APPAREL</option>
                        <option value="AKSESORIS">AKSESORIS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Kategori 2 (Segmen/Status)</label>
                    <select wire:model.live="aging_kategori2" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Semua Segmen</option>
                        <option value="TOP 20">TOP 20 (Fastest)</option>
                        <option value="Produk Berjalan">Produk Berjalan</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Deadstock">Deadstock (Slowest)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Kategori Umur Barang</label>
                    <select wire:model.live="aging_status" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Semua Usia</option>
                        <option value="Fresh Stock">Fresh Stock (< 30 Hari)</option>
                        <option value="Slow Moving">Slow Moving (30-90 Hari)</option>
                        <option value="Dead Stock">Dead Stock (> 90 Hari)</option>
                    </select>
                </div>
            </div>

            <button wire:click="generateAgingStock" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors">
                <span class="material-symbols-outlined text-lg">sync</span>
                Perbarui & Filter Data
            </button>
        </div>

        <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4">Nama Produk</th>
                            <th class="py-3 px-4">Kategori 1</th>
                            <th class="py-3 px-4">Kategori 2</th>
                            <th class="py-3 px-4 text-center">Qty Stok</th>
                            <th class="py-3 px-4 text-right">HPP</th>
                            <th class="py-3 px-4 text-right">Nilai Asset</th>
                            <th class="py-3 px-4">Penerimaan Terakhir</th>
                            <th class="py-3 px-4 text-center">Usia (Hari)</th>
                            <th class="py-3 px-4">Status Usia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($agingData as $item)
                            <tr class="hover:bg-white/5 transition-colors text-on-surface-variant">
                                <td class="py-2.5 px-4 font-mono font-bold text-primary">{{ $item['kode_barang'] }}</td>
                                <td class="py-2.5 px-4 text-on-surface">{{ $item['nama_barang'] }}</td>
                                <td class="py-2.5 px-4 uppercase text-[10px]">{{ $item['kategori'] }}</td>
                                <td class="py-2.5 px-4">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-white/5 border border-white/10">{{ $item['kategori_2'] }}</span>
                                </td>
                                <td class="py-2.5 px-4 text-center font-bold text-on-surface">{{ $item['total_qty'] }}</td>
                                <td class="py-2.5 px-4 text-right font-mono">Rp {{ number_format($item['hpp'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 text-right font-mono font-bold text-on-surface">Rp {{ number_format($item['total_nilai'], 0, ',', '.') }}</td>
                                <td class="py-2.5 px-4 font-mono text-[10px]">{{ $item['tanggal_terakhir'] }}</td>
                                <td class="py-2.5 px-4 text-center font-bold font-mono">{{ $item['usia_hari'] }}</td>
                                <td class="py-2.5 px-4">
                                    @php
                                        $ageColors = [
                                            'Fresh Stock' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                            'Slow Moving' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'Dead Stock' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-black border {{ $ageColors[$item['kategori_usia']] ?? '' }}">{{ $item['kategori_usia'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-6 text-center text-on-surface-variant/40">Tidak ada produk dengan kriteria filter tersebut.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/5 text-xs text-on-surface-variant flex justify-between items-center">
                <span>Total Produk Terdaftar: {{ count($agingData) }}</span>
                <span class="font-bold text-on-surface">Total Nilai Asset: Rp {{ number_format(collect($agingData)->sum(fn($item) => $item['total_nilai']), 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    {{-- 4. Sales Support & Availability Report --}}
    @if($reportType === 'sales-support')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left column: Best Sellers & Categories --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                    <div class="flex justify-between items-start flex-wrap gap-4">
                        <div>
                            <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">emoji_events</span>
                                Produk Terlaris (Best Sellers)
                            </h3>
                            <p class="text-xs text-on-surface-variant mt-1">Daftar produk dengan volume transaksi penjualan tertinggi.</p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="exportCsv('sales-support')" class="flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-xs">download</span> Ekspor CSV
                            </button>
                            <a href="{{ route('reports.print') }}?reportType=sales-support&sales_date_from={{ $sales_date_from }}&sales_date_to={{ $sales_date_to }}&avail_status={{ $avail_status }}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-xs">print</span> Cetak PDF
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/5 p-4 rounded-2xl border border-white/5">
                        <div>
                            <label class="block text-[10px] font-bold text-on-surface-variant mb-1">Mulai Tanggal</label>
                            <input wire:model="sales_date_from" type="date" class="w-full px-3 py-2 rounded-xl bg-surface-container border border-white/10 text-on-surface text-xs focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-on-surface-variant mb-1">Akhir Tanggal</label>
                            <input wire:model="sales_date_to" type="date" class="w-full px-3 py-2 rounded-xl bg-surface-container border border-white/10 text-on-surface text-xs focus:outline-none" />
                        </div>
                    </div>

                    <button wire:click="generateSalesSupport" class="px-4 py-2 rounded-xl text-xs font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors">
                        Terapkan Rentang Tanggal
                    </button>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[9px] font-bold tracking-[0.1em] text-on-surface-variant/60 uppercase">
                                    <th class="py-2 px-3">SKU</th>
                                    <th class="py-2 px-3">Nama Produk</th>
                                    <th class="py-2 px-3">Kategori</th>
                                    <th class="py-2 px-3 text-center">Total Terjual</th>
                                    <th class="py-2 px-3 text-right">Total Nilai Penjualan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($bestSellers as $b)
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="py-2 px-3 font-mono font-bold text-primary">{{ $b['kode_barang'] }}</td>
                                        <td class="py-2 px-3 text-on-surface">{{ $b['nama_barang'] }}</td>
                                        <td class="py-2 px-3 text-[10px] uppercase">{{ $b['kategori'] }}</td>
                                        <td class="py-2 px-3 text-center font-bold text-on-surface">{{ $b['total_qty'] }}</td>
                                        <td class="py-2 px-3 text-right font-mono text-on-surface">Rp {{ number_format($b['total_sales_value'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-on-surface-variant/40">Tidak ada transaksi penjualan di periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kategori Terpopuler --}}
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                    <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">pie_chart</span>
                        Distribusi Kontribusi Kategori Produk
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($topCategories as $tc)
                            <div class="p-4 rounded-2xl bg-white/5 border border-white/5 flex justify-between items-center">
                                <div>
                                    <p class="text-[10px] text-on-surface-variant/60 font-bold uppercase tracking-wider">{{ $tc['kategori'] }}</p>
                                    <p class="text-lg font-black text-on-surface mt-1">{{ $tc['total_qty'] }} <span class="text-xs font-normal text-on-surface-variant">pcs</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-primary/60 font-bold uppercase">Estimasi Omset</p>
                                    <p class="text-sm font-bold text-primary font-mono mt-1">Rp {{ number_format($tc['total_sales_value'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right column: Fulfillment rate & Stock Availability list --}}
            <div class="space-y-6">
                {{-- Fill rates metrics --}}
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">percent</span>
                        Rasio Pemenuhan Order (Fill Rate)
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-white/5 rounded-2xl border border-white/5">
                            <span class="text-[9px] font-bold text-on-surface-variant/60 uppercase">Transfer Order</span>
                            <div class="text-2xl font-black text-on-surface mt-1">{{ $transferFillRate }}%</div>
                        </div>
                        <div class="p-3 bg-white/5 rounded-2xl border border-white/5">
                            <span class="text-[9px] font-bold text-on-surface-variant/60 uppercase">Purchase Order</span>
                            <div class="text-2xl font-black text-on-surface mt-1">{{ $poFillRate }}%</div>
                        </div>
                    </div>
                </div>

                {{-- Stock Health/Availability --}}
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60">Kesehatan Stok (Availability)</h3>
                        <button wire:click="exportCsv('stock-availability')" class="text-primary text-[10px] font-bold flex items-center gap-1 hover:underline">
                            <span class="material-symbols-outlined text-xs">download</span> Ekspor
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant mb-1">Filter Tingkat Stok</label>
                        <select wire:model.live="avail_status" class="w-full px-3 py-2 rounded-xl bg-surface-container border border-white/10 text-on-surface text-xs focus:outline-none">
                            <option value="">Semua Status Kesehatan</option>
                            <option value="Critical">Critical (Di Bawah Minimum)</option>
                            <option value="Warning">Warning (Mendekati Minimum)</option>
                            <option value="Optimal">Optimal (Sesuai Target)</option>
                            <option value="Overstock">Overstock (Stok Berlebih)</option>
                        </select>
                    </div>

                    <div class="space-y-3 max-h-[350px] overflow-y-auto custom-scrollbar pr-1">
                        @forelse($stockAvailability as $s)
                            <div class="p-3 rounded-2xl border bg-white/5 flex flex-col gap-2 {{ $s['status'] === 'Critical' ? 'border-red-500/20 bg-red-500/5' : ($s['status'] === 'Warning' ? 'border-amber-500/20 bg-amber-500/5' : 'border-white/5') }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-on-surface truncate max-w-[150px]">{{ $s['nama_barang'] }}</p>
                                        <p class="text-[9px] font-mono text-primary mt-0.5">{{ $s['kode_barang'] }}</p>
                                    </div>
                                    @php
                                        $statusColors = [
                                            'Critical' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'Warning' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'Optimal' => 'bg-green-500/10 text-green-400 border-green-500/20',
                                            'Overstock' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        ];
                                    @endphp
                                    <span class="px-1.5 py-0.5 rounded-lg text-[8px] font-black border {{ $statusColors[$s['status']] ?? '' }}">{{ $s['status'] }}</span>
                                </div>

                                <div class="grid grid-cols-4 text-center text-[9px] text-on-surface-variant/60 font-bold border-t border-white/5 pt-2">
                                    <div>
                                        <span class="block font-black text-on-surface text-[11px]">{{ $s['total_qty'] }}</span>
                                        Stok
                                    </div>
                                    <div>
                                        <span class="block font-mono">{{ $s['min_stock'] }}</span>
                                        Min
                                    </div>
                                    <div>
                                        <span class="block font-mono">{{ $s['safety_stock'] }}</span>
                                        Safety
                                    </div>
                                    <div>
                                        <span class="block font-mono">{{ $s['max_stock'] }}</span>
                                        Max
                                    </div>
                                </div>

                                @if($s['recomm_qty'] > 0)
                                    <div class="bg-primary-container/10 p-2 rounded-xl text-[9px] text-primary font-bold border border-primary/20 flex justify-between items-center mt-1">
                                        <span>Rekomendasi Isi Ulang:</span>
                                        <span>+{{ $s['recomm_qty'] }} pcs</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-xs text-on-surface-variant/40 py-6">Tidak ada data stok yang cocok.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 5. Warehouse Performance & Accuracy --}}
    @if($reportType === 'warehouse-performance')
        <div class="flex justify-between items-center mb-2 bg-surface-container/10 p-4 rounded-2xl border border-white/5">
            <div>
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">precision_manufacturing</span>
                    Kinerja Gudang (Warehouse Performance)
                </h3>
                <p class="text-xs text-on-surface-variant mt-0.5">Analisis efisiensi receiving, picking, packing, dan akurasi opname.</p>
            </div>
            <a href="{{ route('reports.print') }}?reportType=warehouse-performance" target="_blank" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl border border-primary text-primary hover:bg-primary/10 transition-colors">
                <span class="material-symbols-outlined text-sm">print</span> Cetak PDF
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Receiving card --}}
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-on-surface-variant/60 flex items-center gap-2 border-b border-white/5 pb-3">
                    <span class="material-symbols-outlined text-green-400">download</span>
                    Kinerja Penerimaan (Receiving)
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Total GRN Terbit</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $receivingStats['total_grn'] }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Total Volume Diterima</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ number_format($receivingStats['total_qty'], 0, ',', '.') }} pcs</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">GRN Bulan Ini</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $receivingStats['grn_this_month'] }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Volume Bulan Ini</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ number_format($receivingStats['qty_this_month'], 0, ',', '.') }} pcs</span>
                    </div>
                </div>
                <div class="text-[10px] text-on-surface-variant/40 pt-2 text-center">
                    Data diambil secara real-time dari log Goods Receipt Notes.
                </div>
            </div>

            {{-- Picking & Packing card --}}
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-on-surface-variant/60 flex items-center gap-2 border-b border-white/5 pb-3">
                    <span class="material-symbols-outlined text-amber-400">barcode_scanner</span>
                    Kinerja Picking & Packing
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Total Picking List</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $pickingStats['total_picking'] }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Picking Selesai</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $pickingStats['completed_picking'] }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Rata-rata Waktu Picking</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $pickingStats['avg_picking_duration'] }} mnt</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">QC Passed Rate</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ $pickingStats['qc_passed_rate'] }}%</span>
                    </div>
                </div>
                <div class="text-[10px] text-on-surface-variant/40 pt-2 text-center">
                    Target KPI Waktu Picking adalah &lt; 15 menit per batch.
                </div>
            </div>

            {{-- Accuracy card --}}
            <div class="glass-card rounded-3xl p-6 shadow-2xl space-y-6 bg-primary-container/5 border border-primary/20">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary flex items-center gap-2 border-b border-primary/10 pb-3">
                    <span class="material-symbols-outlined text-primary">fact_check</span>
                    Akurasi Stok (Stock Opname)
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Qty Menurut Sistem</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ number_format($inventoryAccuracy['total_sistem'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Qty Fisik Dihitung</span>
                        <span class="text-base font-black text-on-surface font-mono">{{ number_format($inventoryAccuracy['total_fisik'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-white/5 p-3 rounded-2xl">
                        <span class="text-xs text-on-surface-variant font-bold">Variasi / Selisih Qty</span>
                        <span class="text-base font-black text-red-400 font-mono">{{ number_format($inventoryAccuracy['total_variance'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center bg-primary-container/20 p-3 rounded-2xl border border-primary/20">
                        <span class="text-xs text-primary font-black">Akurasi Inventaris</span>
                        <span class="text-lg font-black text-primary font-mono">{{ $inventoryAccuracy['accuracy_rate'] }}%</span>
                    </div>
                </div>
                <div class="text-[10px] text-on-surface-variant/40 pt-2 text-center">
                    Target KPI WMS Retail Fashion adalah &gt; 99% Akurasi Stok.
                </div>
            </div>

        </div>
    @endif

</div>

