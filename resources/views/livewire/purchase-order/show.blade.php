<div x-init="$dispatch('update-header-title', 'Detail Purchase Order')"
     class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    @php
        $statusMap = [
            'terbuka'  => ['Terbuka', 'bg-white/5 border-white/10 text-on-surface-variant', 'radio_button_unchecked'],
            'sebagian' => ['Diterima Sebagian', 'bg-amber-500/10 border-amber-500/30 text-amber-400', 'timelapse'],
            'selesai'  => ['Selesai', 'bg-green-500/10 border-green-500/30 text-green-400', 'task_alt'],
            'ditutup'  => ['Selesai (Ada Penutupan)', 'bg-sky-500/10 border-sky-500/30 text-sky-400', 'lock'],
        ];
        $itemStatusMap = [
            'belum'    => ['Belum Datang', 'bg-white/5 border-white/10 text-on-surface-variant'],
            'sebagian' => ['Sebagian', 'bg-amber-500/10 border-amber-500/20 text-amber-400'],
            'selesai'  => ['Lengkap', 'bg-green-500/10 border-green-500/20 text-green-400'],
            'lebih'    => ['Lebih Kirim', 'bg-sky-500/10 border-sky-500/20 text-sky-400'],
            'ditutup'  => ['Ditutup', 'bg-white/5 border-white/10 text-on-surface-variant/60'],
        ];
        $currentStatus = (string) ($statusPo ?? 'terbuka');
        [$stLabel, $stClass, $stIcon] = $statusMap[$currentStatus] ?? $statusMap['terbuka'];
    @endphp

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-white/5">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-order') }}" class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 flex items-center justify-center text-on-surface transition-all active:scale-95" title="Kembali ke Daftar PO">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-black text-on-surface flex items-center gap-2 flex-wrap">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    <span class="font-mono text-primary">{{ $poHeader->nomor_po ?? 'PO #' . $poHeader->id }}</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $stClass }}">
                        <span class="material-symbols-outlined text-[12px]">{{ $stIcon }}</span>{{ $stLabel }}
                    </span>
                </h1>
                <p class="text-xs text-on-surface-variant/70 mt-0.5">
                    {{ $poHeader->supplier?->nama_supplier ?? '-' }} &bull; {{ $poHeader->tanggal?->format('d/m/Y') }}
                    @if ($poHeader->artikel) &bull; <span class="font-mono text-primary/80">{{ $poHeader->artikel }}</span> @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('purchase-order.print', $poHeader->id) }}" target="_blank"
               class="px-4 py-2.5 bg-primary/10 border border-primary/30 rounded-2xl text-xs font-bold text-primary hover:bg-primary/20 transition-all active:scale-95 flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">print</span>
                Cetak PO (PDF)
            </a>

            @if ($canManage && $totalSisa > 0)
                <button type="button" wire:click="openCloseModal" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold hover:bg-error/10 hover:border-error/30 hover:text-error transition-all text-on-surface flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">block</span>
                    Tutup Semua Sisa PO
                </button>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach ([
            ['Dipesan', number_format($totalDipesan), 'shopping_cart', 'text-on-surface'],
            ['Diterima', number_format($totalDiterima), 'call_received', 'text-green-400'],
            ['Sisa Outstanding', number_format($totalSisa), 'pending', 'text-primary'],
            ['Ditutup', number_format($totalDitutup), 'lock', 'text-sky-400'],
            ['Nilai Sisa (HPP)', 'Rp' . number_format($nilaiSisa, 0, ',', '.'), 'payments', 'text-primary'],
        ] as [$label, $value, $icon, $color])
            <div class="glass-card rounded-3xl p-5 border border-white/10 hover:border-primary/20 transition-colors">
                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/70">
                    <span class="material-symbols-outlined text-sm">{{ $icon }}</span>{{ $label }}
                </div>
                <p class="mt-2 text-xl font-black font-mono {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <!-- Progress -->
    <div class="glass-card rounded-3xl p-5 border border-white/10">
        <div class="flex items-center justify-between text-xs mb-2">
            <span class="font-bold text-on-surface">Progres Penerimaan</span>
            <span class="font-mono font-black text-primary">{{ $progress }}%</span>
        </div>
        <div class="h-2.5 rounded-full bg-white/5 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-primary-container to-[#ff9f00] transition-all duration-700" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <!-- Items Table -->
    <section class="glass-card rounded-3xl border border-white/10 overflow-hidden" x-data>
        <div class="p-5 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xs font-black text-primary uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">format_list_bulleted</span>
                    Item Pesanan ({{ $details->count() }})
                </h2>
                <p class="text-[10px] text-on-surface-variant/60 mt-0.5">Centang item yang datang, sesuaikan qty aktual, lalu klik "Terima Barang Terpilih".</p>
            </div>
            @if ($canReceive && $totalSisa > 0)
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="selectAllOutstanding" class="px-3 py-2 bg-white/5 border border-white/10 rounded-xl text-[11px] font-semibold hover:bg-white/10 text-on-surface">Pilih Semua Sisa</button>
                    <button type="button" wire:click="clearSelection" class="px-3 py-2 bg-white/5 border border-white/10 rounded-xl text-[11px] font-semibold hover:bg-white/10 text-on-surface">Reset</button>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/70 uppercase bg-white/[0.02]">
                        @if ($canReceive) <th class="p-3 w-10 text-center">Pilih</th> @endif
                        <th class="p-3">Produk</th>
                        <th class="p-3 text-center">Dipesan</th>
                        <th class="p-3 text-center">Diterima</th>
                        <th class="p-3 text-center">Ditutup</th>
                        <th class="p-3 text-center">Sisa</th>
                        @if ($canReceive) <th class="p-3 text-center w-32">Qty Datang</th> @endif
                        <th class="p-3 text-right">HPP PO</th>
                        <th class="p-3 text-center">Status</th>
                        @if ($canManage) <th class="p-3 text-center w-16">Aksi</th> @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($details as $d)
                        @php
                            [$isLabel, $isClass] = $itemStatusMap[$d->statusPenerimaan()];
                            $hasSisa = $d->sisa > 0;
                        @endphp
                        <tr wire:key="po-detail-{{ $d->id }}" class="hover:bg-white/5 transition-colors {{ $hasSisa ? '' : 'opacity-70' }}">
                            @if ($canReceive)
                                <td class="p-3 text-center">
                                    @if ($hasSisa)
                                        <input type="checkbox" wire:model="selected.{{ $d->id }}"
                                               class="rounded-lg border-white/20 bg-surface-container text-primary focus:ring-primary w-4 h-4 cursor-pointer">
                                    @else
                                        <span class="material-symbols-outlined text-sm text-green-400/60">check</span>
                                    @endif
                                </td>
                            @endif
                            <td class="p-3 min-w-[200px]">
                                <span class="font-bold text-on-surface block">{{ $d->produk?->nama_barang ?? '-' }}</span>
                                <span class="text-[10px] font-mono text-primary/80">{{ $d->produk?->kode_barang }}</span>
                                @if ($d->ditutup_pada)
                                    <span class="block text-[10px] text-sky-400/80 mt-0.5" title="Ditutup oleh {{ $d->ditutup_oleh }}">
                                        <span class="material-symbols-outlined text-[11px] align-middle">lock</span>
                                        {{ $d->alasan_tutup }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-mono font-semibold text-on-surface">{{ number_format($d->jumlah) }}</td>
                            <td class="p-3 text-center font-mono font-bold text-green-400">{{ number_format($d->barang_masuk) }}</td>
                            <td class="p-3 text-center font-mono text-sky-400/80">{{ $d->qty_ditutup ? number_format($d->qty_ditutup) : '-' }}</td>
                            <td class="p-3 text-center font-mono font-black {{ $hasSisa ? 'text-primary' : 'text-on-surface-variant/30' }}">{{ number_format($d->sisa) }}</td>
                            @if ($canReceive)
                                <td class="p-3 text-center">
                                    @if ($hasSisa)
                                        <input type="number" min="1" wire:model="qtyTerima.{{ $d->id }}"
                                               class="w-20 bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-2 text-xs text-center font-bold font-mono text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                        @error("qtyTerima.{$d->id}") <span class="text-[9px] text-error block mt-0.5">{{ $message }}</span> @enderror
                                    @else
                                        <span class="text-on-surface-variant/30">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="p-3 text-right font-mono text-on-surface">Rp{{ number_format((float) $d->hpp, 0, ',', '.') }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider whitespace-nowrap {{ $isClass }}">{{ $isLabel }}</span>
                            </td>
                            @if ($canManage)
                                <td class="p-3 text-center">
                                    @if ($hasSisa)
                                        <button type="button" wire:click="openCloseModal({{ $d->id }})" title="Tutup sisa item ini"
                                                class="w-7 h-7 rounded-lg inline-flex items-center justify-center hover:bg-error/20 text-on-surface-variant hover:text-error transition-all">
                                            <span class="material-symbols-outlined text-base">block</span>
                                        </button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canReceive && $totalSisa > 0)
            <div class="p-4 border-t border-white/10 bg-white/[0.02] flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-on-surface-variant font-medium">
                    <strong class="text-primary font-bold" x-text="Object.values($wire.selected || {}).filter(Boolean).length">0</strong> item dipilih
                </span>
                <button type="button"
                        wire:click="receiveSelected"
                        wire:loading.attr="disabled"
                        wire:target="receiveSelected"
                        x-bind:disabled="Object.values($wire.selected || {}).filter(Boolean).length === 0"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-400 text-black rounded-2xl font-bold text-xs shadow-lg hover:shadow-green-500/20 transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">call_received</span>
                    Terima Barang Terpilih
                </button>
            </div>
        @endif
    </section>

    <!-- Riwayat Penerimaan & Penutupan -->
    <section class="glass-card rounded-3xl border border-white/10 p-5 space-y-4">
        <h2 class="text-xs font-black text-primary uppercase tracking-wider flex items-center gap-2">
            <span class="material-symbols-outlined text-base">history</span>
            Riwayat Penerimaan &amp; Penyesuaian
        </h2>

        @if ($receipts->isEmpty() && $closures->isEmpty())
            <div class="py-8 text-center text-on-surface-variant space-y-1">
                <span class="material-symbols-outlined text-3xl text-white/20">inventory</span>
                <p class="text-xs font-bold text-on-surface">Belum ada penerimaan barang untuk PO ini</p>
            </div>
        @else
            <ol class="relative border-l border-white/10 ml-2 space-y-5">
                @foreach ($receipts as $headerId => $lines)
                    @php $bm = $lines->first()->barangMasukHeader; @endphp
                    <li class="ml-5">
                        <span class="absolute -left-[9px] w-4 h-4 rounded-full bg-green-500/20 border border-green-500/50 flex items-center justify-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        </span>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-mono font-bold text-on-surface">{{ $bm?->nomor_nota ?? '#' . $headerId }}</span>
                            <span class="text-on-surface-variant/60">&bull; {{ $bm?->tanggal ? \Illuminate\Support\Carbon::parse($bm->tanggal)->format('d/m/Y') : '-' }}</span>
                            <span class="text-on-surface-variant/60">&bull; oleh {{ $bm?->data_dimasukan_oleh ?? '-' }}</span>
                        </div>
                        <div class="mt-2 space-y-1.5">
                            @foreach ($lines as $line)
                                <div class="flex flex-wrap items-center gap-2 text-[11px] bg-white/[0.02] border border-white/5 rounded-xl px-3 py-2">
                                    <span class="font-semibold text-on-surface">{{ $line->produk?->nama_barang }}</span>
                                    <span class="font-mono text-green-400 font-bold">+{{ number_format($line->qty_masuk) }}</span>
                                    @if ($line->sisa_po_sebelum !== null)
                                        <span class="text-on-surface-variant/60">dari sisa {{ number_format($line->sisa_po_sebelum) }}</span>
                                    @endif
                                    @if ($line->selisih_po)
                                        <span class="px-2 py-0.5 rounded-md border text-[10px] font-bold {{ $line->selisih_po < 0 ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' : 'bg-sky-500/10 border-sky-500/20 text-sky-400' }}">
                                            {{ $line->selisih_po < 0 ? 'Kurang ' . abs($line->selisih_po) : 'Lebih ' . $line->selisih_po }}
                                        </span>
                                        @if ($line->alasan_selisih)
                                            <span class="italic text-on-surface-variant">"{{ $line->alasan_selisih }}"</span>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </li>
                @endforeach

                @foreach ($closures as $c)
                    <li class="ml-5">
                        <span class="absolute -left-[9px] w-4 h-4 rounded-full bg-sky-500/20 border border-sky-500/50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[10px] text-sky-400">lock</span>
                        </span>
                        <div class="text-xs flex flex-wrap items-center gap-2">
                            <span class="font-bold text-sky-400">Sisa Ditutup</span>
                            <span class="text-on-surface-variant/60">&bull; {{ $c->ditutup_pada->format('d/m/Y H:i') }} &bull; oleh {{ $c->ditutup_oleh }}</span>
                        </div>
                        <div class="mt-1.5 text-[11px] bg-white/[0.02] border border-white/5 rounded-xl px-3 py-2">
                            <span class="font-semibold text-on-surface">{{ $c->produk?->nama_barang }}</span>
                            <span class="font-mono text-sky-400 font-bold ml-1">{{ number_format($c->qty_ditutup) }} unit</span>
                            <span class="italic text-on-surface-variant ml-1">"{{ $c->alasan_tutup }}"</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <!-- Modal Tutup Sisa -->
    @if ($isCloseModalOpen)
        <div x-data class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-md">
            <div class="glass-card bg-surface-dark border border-white/10 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden" @click.away="$wire.cancelCloseModal()">
                <div class="p-5 border-b border-white/10">
                    <h3 class="text-sm font-black text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-error">block</span>
                        {{ $closingDetailId ? 'Tutup Sisa Item' : 'Tutup Semua Sisa PO' }}
                    </h3>
                    <p class="text-[11px] text-on-surface-variant/70 mt-1">Gunakan jika supplier tidak akan mengirim sisa barang. Sisa akan menjadi 0 dan tercatat di riwayat.</p>
                </div>
                <div class="p-5 space-y-3">
                    <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider">Alasan Penutupan</label>
                    <input type="text" wire:model="alasanTutup" x-ref="alasanTutup" autofocus
                           placeholder="Contoh: Supplier stok habis / dibatalkan"
                           class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (['Supplier stok habis', 'Pesanan dibatalkan', 'Diganti PO baru'] as $preset)
                            <button type="button" @click="$refs.alasanTutup.value = '{{ $preset }}'; $wire.set('alasanTutup', '{{ $preset }}', false)"
                                    class="px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] text-on-surface-variant hover:text-on-surface hover:bg-white/10">{{ $preset }}</button>
                        @endforeach
                    </div>
                    @error('alasanTutup') <span class="text-xs text-error block font-medium">{{ $message }}</span> @enderror
                </div>
                <div class="p-4 border-t border-white/10 bg-white/[0.02] flex justify-end gap-2">
                    <button type="button" wire:click="cancelCloseModal" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-semibold hover:bg-white/10 text-on-surface">Batal</button>
                    <button type="button" wire:click="closeRemaining" wire:loading.attr="disabled" class="px-5 py-2 bg-error/80 hover:bg-error text-white rounded-xl font-bold text-xs transition-all active:scale-95 disabled:opacity-50">Tutup Sisa</button>
                </div>
            </div>
        </div>
    @endif
</div>
