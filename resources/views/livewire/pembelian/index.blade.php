<div x-init="$dispatch('update-header-title', 'Barang Masuk')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari nota, produk, supplier..." type="text"/>
        </div>
    </template>

    <!-- Tab Navigation -->
    <div class="flex border-b border-white/10 mb-6 space-x-8">
        <a href="{{ route('barang-masuk') }}" class="relative pb-4 text-sm font-bold text-purple-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg text-purple-400">receipt_long</span>
            Daftar Transaksi (Header)
            <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-purple-500 rounded-full shadow-[0_0_12px_#a855f7]"></div>
        </a>
        <a href="{{ route('barang-masuk.riwayat') }}" class="pb-4 text-sm font-semibold text-on-surface-variant hover:text-on-surface flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-lg">history</span>
            Riwayat Detail Barang (Itemized)
        </a>
        <a href="{{ route('barang-masuk.riwayat-tanggal') }}" class="pb-4 text-sm font-semibold text-on-surface-variant hover:text-on-surface flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-lg">payments</span>
            Riwayat Masuk / Tanggal (Laporan Nilai)
        </a>
    </div>

    <!-- Alert Toast -->
    

    

    <!-- Actions Bar & Filters -->
    <section class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <!-- Mobile Search Bar (Only visible on screens < md) -->
            <div class="md:hidden relative w-full">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Cari nota, produk, supplier..." type="text"/>
            </div>

            <div class="relative w-full sm:w-48">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">filter_alt</span>
                <select wire:model.live="tipeFilter" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    <option value="">Semua Tipe Nota</option>
                    <option value="Purchase Order">Purchase Order (PO)</option>
                    <option value="Stock Awal">Stok Awal</option>
                    <option value="Pembelian">Pembelian</option>
                    <option value="Retur Masuk">Retur Masuk</option>
                    <option value="Penyesuaian">Penyesuaian</option>
                </select>
            </div>

            <!-- Date Range Picker (Flatpickr) -->
            <div class="relative w-full sm:w-64"
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
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg z-10 pointer-events-none">date_range</span>
                <input x-ref="dateInput" type="text" readonly
                       class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-9 text-sm text-on-surface cursor-pointer focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/50"
                       placeholder="Filter tanggal...">
                <template x-if="$wire.tanggalStart">
                    <button @click.stop="fp.clear(); $wire.setDateRange(null, null);" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant/50 hover:text-on-surface transition-colors z-10">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </template>
            </div>
        </div>

        <a href="{{ route('barang-masuk.create') }}" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong justify-center">
            <span class="material-symbols-outlined">add</span>
            Input Barang Masuk
        </a>
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                        <th class="px-6 py-5 w-10"></th>
                        <th class="px-6 py-5">Tanggal</th>
                        <th class="px-6 py-5">Tipe</th>
                        <th class="px-6 py-5">No. Nota</th>
                        <th class="px-6 py-5">Supplier / Pengirim</th>
                        <th class="px-6 py-5 text-center">Total Item</th>
                        <th class="px-6 py-5 text-center">Total Qty</th>
                        <th class="px-6 py-5 text-right">Total Nilai</th>
                        <th class="px-6 py-5">Diinput Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant" x-data="{ expandedId: null }">
                    @forelse ($transactions as $t)
                        <!-- Main Header Row -->
                        <tr class="hover:bg-white/5 cursor-pointer transition-colors" @click="expandedId = (expandedId === {{ $t->id }}) ? null : {{ $t->id }}">
                            <td class="px-6 py-4 text-center">
                                <span class="material-symbols-outlined text-sm text-on-surface-variant/60 transform transition-transform duration-200 block" :class="{ 'rotate-90 text-primary': expandedId === {{ $t->id }} }">
                                    chevron_right
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-on-surface">{{ $t->tanggal->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider
                                    {{ $t->tipe_nota === 'Purchase Order' ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : '' }}
                                    {{ $t->tipe_nota === 'Pembelian' ? 'bg-green-500/10 text-green-400 border-green-500/20' : '' }}
                                    {{ $t->tipe_nota === 'Stock Awal' ? 'bg-primary/10 text-primary border border-primary/20' : '' }}
                                    {{ $t->tipe_nota === 'Retur Masuk' ? 'bg-tertiary/10 text-tertiary border border-tertiary/20' : '' }}
                                    {{ $t->tipe_nota === 'Penyesuaian' ? 'bg-error/10 text-error border border-error/20' : '' }}
                                ">
                                    {{ $t->tipe_nota }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-primary/80 font-bold">
                                <div>{{ $t->nomor_nota ?? '-' }}</div>
                                @if($t->po_header_id && $t->poHeader)
                                    <div class="flex items-center gap-1 mt-1 font-sans">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20" title="Terhubung ke Purchase Order">
                                            <span class="material-symbols-outlined text-[10px]">receipt_long</span>
                                            {{ $t->poHeader->nomor_po ?? 'PO #'.$t->poHeader->id }}
                                        </span>
                                        @if($t->poHeader->artikel)
                                            <span class="text-[9px] text-on-surface-variant/60 font-mono">({{ $t->poHeader->artikel }})</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface font-semibold">{{ $t->supplier->nama_supplier ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">{{ $t->details->count() }} item</td>
                            <td class="px-6 py-4 text-center font-bold text-on-surface">{{ number_format($t->details->sum('qty_masuk')) }} pasang</td>
                            <td class="px-6 py-4 text-right font-black text-primary text-sm">
                                Rp{{ number_format($t->details->sum(function($d) { return $d->qty_masuk * $d->hpp; }), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-white/5 border border-white/5 rounded-lg text-[10px] text-on-surface-variant">{{ $t->data_dimasukan_oleh }}</span>
                            </td>
                        </tr>
                        
                        <!-- Detail Collapsible Row -->
                        <tr x-show="expandedId === {{ $t->id }}" x-cloak class="bg-white/[0.01]">
                            <td colspan="9" class="px-6 py-4">
                                <div class="glass-card rounded-2xl p-5 space-y-4 bg-surface-dark/40 border-white/5">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-white/5 pb-2 gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="text-xs font-black text-primary uppercase tracking-wider">Rincian Item Nota: {{ $t->nomor_nota ?? '-' }}</div>
                                            <a href="{{ route('barang-masuk.print', $t->id) }}" target="_blank" class="flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-lg border border-primary text-primary hover:bg-primary/10 transition-colors" title="Cetak PDF / Nota">
                                                <span class="material-symbols-outlined text-[12px]">print</span>
                                                Cetak PDF
                                            </a>
                                            @if (auth()->user()->hasPermissionTo('manage master') || auth()->user()->hasPermissionTo('input masuk'))
                                                <a href="{{ route('barang-masuk.edit', $t->id) }}" class="flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-lg border border-amber-500 text-amber-500 hover:bg-amber-500/10 transition-colors" title="Ubah Transaksi">
                                                    <span class="material-symbols-outlined text-[12px]">edit</span>
                                                    Edit
                                                </a>
                                                <button type="button" wire:click.stop="destroy({{ $t->id }})" class="flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold rounded-lg border border-red-500 text-red-500 hover:bg-red-500/10 transition-colors" title="Hapus Transaksi">
                                                    <span class="material-symbols-outlined text-[12px]">delete</span>
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                        @if ($t->keterangan_tambahan)
                                            <div class="text-xs text-on-surface-variant"><span class="font-bold text-on-surface">Keterangan:</span> {{ $t->keterangan_tambahan }}</div>
                                        @endif
                                    </div>
                                    <div class="overflow-x-auto custom-scrollbar">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">
                                                    <th class="px-4 py-2">Produk</th>
                                                    <th class="px-4 py-2 text-center w-24">QTY</th>
                                                    <th class="px-4 py-2 text-right w-36">Harga Beli</th>
                                                    <th class="px-4 py-2 text-right w-36">Total</th>
                                                    <th class="px-4 py-2 text-center w-28">Lokasi Simpan</th>
                                                    <th class="px-4 py-2 w-32">Keterangan PO</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/5 text-on-surface-variant">
                                                @foreach ($t->details as $d)
                                                    <tr class="hover:bg-white/5 transition-colors">
                                                        <td class="px-4 py-2">
                                                            <div class="font-bold text-on-surface">{{ $d->produk->nama_barang }}</div>
                                                            <div class="text-[9px] text-primary/80 font-mono">{{ $d->produk->kode_barang }} - {{ $d->produk->variasi_barang }}</div>
                                                        </td>
                                                        <td class="px-4 py-2 text-center font-bold text-on-surface">{{ number_format($d->qty_masuk) }} {{ $d->satuan }}</td>
                                                        <td class="px-4 py-2 text-right">Rp{{ number_format($d->hpp, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-right font-bold text-primary">Rp{{ number_format($d->qty_masuk * $d->hpp, 0, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-center">
                                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border {{ $d->lokasi_simpan === 'GUDANG' ? 'bg-primary/10 text-primary border-primary/20' : 'bg-tertiary/10 text-tertiary border-tertiary/20' }}">
                                                                {{ $d->lokasi_simpan }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            @if ($d->po_detail_id)
                                                                <span class="text-[9px] text-green-400 font-bold uppercase tracking-wider flex items-center gap-0.5">
                                                                    <span class="material-symbols-outlined text-xs">done</span>
                                                                    PO Linked
                                                                </span>
                                                            @else
                                                                <span class="text-on-surface-variant/40">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Audit Log Section -->
                                    @if ($t->auditLogs->count() > 0)
                                        <div class="mt-4 border-t border-white/5 pt-3">
                                            <div class="text-[10px] font-black text-amber-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">history</span>
                                                Riwayat Perubahan (Audit Log)
                                            </div>
                                            <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar pr-2">
                                                @foreach ($t->auditLogs as $log)
                                                    <div class="p-3 bg-white/5 border border-white/5 rounded-xl text-[11px] space-y-1.5">
                                                        <div class="flex items-center justify-between text-on-surface-variant/60 font-medium">
                                                            <div>
                                                                Oleh: <span class="text-on-surface font-bold">{{ $log->username }}</span> 
                                                                | IP: <span class="font-mono text-on-surface">{{ $log->ip_address ?? '-' }}</span>
                                                            </div>
                                                            <div class="font-mono text-[10px]">{{ $log->created_at->format('d/m/Y H:i:s') }} WIB</div>
                                                        </div>
                                                        <div class="text-on-surface-variant leading-relaxed">
                                                            @if (!empty($log->old_values) && !empty($log->new_values))
                                                                @php
                                                                    $oldH = $log->old_values['header'] ?? [];
                                                                    $newH = $log->new_values['header'] ?? [];
                                                                    $changes = [];
                                                                    
                                                                    $fields = [
                                                                        'tanggal' => 'Tanggal',
                                                                        'tipe_nota' => 'Tipe Nota',
                                                                        'nomor_nota' => 'Nomor Nota',
                                                                        'keterangan_tambahan' => 'Keterangan'
                                                                    ];
                                                                    foreach ($fields as $field => $label) {
                                                                        $oldVal = $oldH[$field] ?? '-';
                                                                        $newVal = $newH[$field] ?? '-';
                                                                        if ($oldVal != $newVal) {
                                                                            if ($field === 'tanggal') {
                                                                                $oldVal = date('d/m/Y', strtotime($oldVal));
                                                                                $newVal = date('d/m/Y', strtotime($newVal));
                                                                            }
                                                                            $changes[] = "$label: <span class='line-through text-error'>$oldVal</span> &rarr; <span class='text-green-500 font-bold'>$newVal</span>";
                                                                        }
                                                                    }
                                                                    
                                                                    $oldD = $log->old_values['details'] ?? [];
                                                                    $newD = $log->new_values['details'] ?? [];
                                                                    if (count($oldD) !== count($newD)) {
                                                                        $changes[] = "Jumlah item detail berubah: <span class='line-through text-error'>" . count($oldD) . " item</span> &rarr; <span class='text-green-500 font-bold'>" . count($newD) . " item</span>";
                                                                    } else {
                                                                        $detailChanged = false;
                                                                        foreach ($newD as $idx => $newDet) {
                                                                            $oldDet = $oldD[$idx] ?? [];
                                                                            if (($oldDet['qty_masuk'] ?? 0) != ($newDet['qty_masuk'] ?? 0) || ($oldDet['hpp'] ?? 0) != ($newDet['hpp'] ?? 0) || ($oldDet['lokasi_simpan'] ?? '') != ($newDet['lokasi_simpan'] ?? '')) {
                                                                                $detailChanged = true;
                                                                                break;
                                                                            }
                                                                        }
                                                                        if ($detailChanged) {
                                                                            $changes[] = "Rincian kuantitas, harga, atau lokasi barang masuk diperbarui.";
                                                                        }
                                                                    }
                                                                @endphp
                                                                
                                                                <ul class="list-disc pl-4 space-y-0.5">
                                                                    @foreach ($changes as $change)
                                                                        <li>{!! $change !!}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                Transaksi dibuat atau diperbarui.
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada transaksi barang masuk ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $transactions->firstItem() ?? 0 }} s.d. {{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($transactions->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
                    @if ($page == $transactions->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($transactions->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>
</div>
