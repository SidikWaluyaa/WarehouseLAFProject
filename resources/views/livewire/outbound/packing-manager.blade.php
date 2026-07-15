<div x-init="$dispatch('update-header-title', 'Outbound QC & Packing')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    
    {{-- Alerts --}}
    
    

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: Transactions Ready to Pack --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <h3 class="text-base font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Outbound Orders
                </h3>
                
                {{-- Tabs --}}
                <div class="flex border-b border-white/5 mb-4">
                    <button wire:click="$set('activeTab', 'ready')" class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider {{ $activeTab === 'ready' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant/60 hover:text-on-surface' }}">
                        Siap Packing (QC)
                    </button>
                    <button wire:click="$set('activeTab', 'completed')" class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider {{ $activeTab === 'completed' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant/60 hover:text-on-surface' }}">
                        Riwayat Kirim
                    </button>
                </div>

                {{-- List --}}
                <div class="space-y-3 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    @if($activeTab === 'ready')
                        @forelse($readyTransactions as $t)
                            <div wire:click="selectTransaction({{ $t->id }}, '{{ $t->type }}')" class="p-4 rounded-2xl border transition-all cursor-pointer {{ $activeTransactionId === $t->id && $activeTransactionType === $t->type ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-on-surface font-mono">{{ $t->nomor_nota }}</span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black border bg-blue-500/10 text-blue-400 border-blue-500/20">{{ $t->type === 'BarangKeluar' ? 'PENJUALAN' : 'TRANSFER' }}</span>
                                </div>
                                <div class="text-[10px] text-on-surface-variant/70 space-y-1">
                                    <p>Tujuan: <strong class="text-on-surface">{{ $t->destinasi }}</strong></p>
                                    <p>Total Qty: {{ $t->items_count }} pcs</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-on-surface-variant/50 py-6">Tidak ada order siap packing.</p>
                        @endforelse
                    @else
                        @forelse($completedTransactions as $t)
                            <div wire:click="selectTransaction({{ $t->id }}, '{{ $t->type }}')" class="p-4 rounded-2xl border transition-all cursor-pointer {{ $activeTransactionId === $t->id && $activeTransactionType === $t->type ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }} space-y-2">
                                <div class="flex justify-between items-start">
                                    <span class="text-xs font-bold text-on-surface font-mono">{{ $t->nomor_nota }}</span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black border bg-green-500/10 text-green-400 border-green-500/20">SHIPPED</span>
                                </div>
                                <div class="text-[10px] text-on-surface-variant/70 space-y-1">
                                    <p>Tujuan: <strong class="text-on-surface">{{ $t->destinasi }}</strong></p>
                                    <p>Kurir / Resi: <span class="text-primary font-mono font-bold">{{ $t->kurir }} ({{ $t->resi_ekspedisi }})</span></p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-on-surface-variant/50 py-6">Tidak ada riwayat pengiriman.</p>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: QC and Shipping Info --}}
        <div class="lg:col-span-2">
            @if($activeTransaction)
                @php
                    $isCompletedTransaction = false;
                    if ($activeTransactionType === 'BarangKeluar') {
                        $isCompletedTransaction = (bool)$activeTransaction->is_qc_passed;
                    } else {
                        $isCompletedTransaction = $activeTransaction->status !== 'PICKING';
                    }
                @endphp
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                        <div>
                            <h2 class="text-lg font-bold text-on-surface font-mono">
                                {{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->nomor_nota ?: 'Barang Keluar #'.$activeTransaction->id) : $activeTransaction->nomor_transfer }}
                            </h2>
                            <p class="text-xs text-on-surface-variant">Tujuan: 
                                <strong class="text-primary">
                                    {{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->pelanggan->nama_pelanggan ?? '-') : ($activeTransaction->lokasiTujuan->nama_lokasi ?? '-') }}
                                </strong>
                            </p>
                        </div>
                        <button wire:click="closeTransaction" class="px-4 py-2 bg-white/5 border border-white/10 text-xs font-semibold rounded-xl text-on-surface hover:bg-white/10 transition-all">
                            Tutup
                        </button>
                    </div>

                    {{-- QC Checklist --}}
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">fact_check</span>
                            Quality Control Checklist (Verifikasi Fisik & Kemasan)
                        </h4>
                        
                        <div class="divide-y divide-white/5 border border-white/5 rounded-2xl overflow-hidden bg-white/5">
                            @foreach($activeTransaction->details as $d)
                                @php
                                    $qty = $activeTransactionType === 'BarangKeluar' ? $d->qty_keluar : $d->qty_requested;
                                    $isPassed = $qcItems[$d->id] ?? false;
                                @endphp
                                <div class="p-4 flex items-center justify-between hover:bg-white/5 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                            {{ substr($d->produk->kode_barang, 0, 3) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-on-surface">{{ $d->produk->nama_barang }}</p>
                                            <p class="text-[9px] text-on-surface-variant/60 font-mono">{{ $d->produk->kode_barang }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <p class="text-xs text-on-surface-variant">Jumlah</p>
                                            <p class="text-sm font-black text-primary">{{ $qty }} {{ $d->satuan }}</p>
                                        </div>
                                        @if($activeTransactionType === 'BarangKeluar')
                                            @if($isCompletedTransaction)
                                                <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[10px] font-bold uppercase bg-green-500/10 text-green-400 border-green-500/30">
                                                    <span class="material-symbols-outlined text-xs">check</span>
                                                    QC: PASSED
                                                </span>
                                            @else
                                                <button wire:click="toggleQc({{ $d->id }})" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[10px] font-bold uppercase transition-all {{ $isPassed ? 'bg-green-500/10 text-green-400 border-green-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30' }}">
                                                    <span class="material-symbols-outlined text-xs">{{ $isPassed ? 'check' : 'close' }}</span>
                                                    QC: {{ $isPassed ? 'PASSED' : 'PENDING' }}
                                                </button>
                                            @endif
                                        @else
                                            <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-xl text-[10px] font-bold uppercase">
                                                {{ $isCompletedTransaction ? 'Shipped' : 'Ready' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Shipping Label & Courier details --}}
                    @if($activeTransactionType === 'BarangKeluar')
                        <div class="p-6 rounded-2xl bg-white/5 border border-white/5 space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">local_shipping</span>
                                Informasi Pengiriman & Ekspedisi
                            </h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Pilih Kurir / Ekspedisi</label>
                                    <select wire:model="kurir" {{ $isCompletedTransaction ? 'disabled' : '' }} class="w-full bg-surface-container border border-white/10 rounded-xl py-3 px-4 text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary {{ $isCompletedTransaction ? 'opacity-60 cursor-not-allowed' : '' }}">
                                        <option value="JNE">JNE Express</option>
                                        <option value="J&T">J&T Express</option>
                                        <option value="Sicepat">Sicepat</option>
                                        <option value="Anteraja">Anteraja</option>
                                        <option value="Wahana">Wahana</option>
                                        <option value="POS">POS Indonesia</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Nomor Resi Pengiriman</label>
                                    <input wire:model="resi_ekspedisi" type="text" placeholder="Masukkan Nomor Resi" {{ $isCompletedTransaction ? 'disabled' : '' }}
                                        class="w-full bg-surface-container border border-white/10 rounded-xl py-3 px-4 text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary {{ $isCompletedTransaction ? 'opacity-60 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Action buttons --}}
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-white/5">
                        <button onclick="printShippingLabel()" type="button" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-on-surface hover:bg-white/10 transition-all flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">print</span> Cetak Label Alamat
                        </button>
                        
                        @if($isCompletedTransaction)
                            <span class="px-6 py-2.5 bg-green-500/10 text-green-400 border border-green-500/20 text-xs font-bold rounded-xl flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">local_shipping</span> Sudah Dikirim
                            </span>
                        @else
                            <button wire:click="submitPacking" class="px-8 py-3 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container font-black text-xs rounded-xl shadow-xl hover:shadow-primary/20 transition-all duration-200 amber-glow-strong">
                                {{ $activeTransactionType === 'BarangKeluar' ? 'Konfirmasi QC & Kirim Barang' : 'Kirim Transfer Gudang' }}
                            </button>
                        @endif
                    </div>

                    {{-- Print Preview script template --}}
                    <div class="hidden" id="print-label-template">
                        <div style="font-family: sans-serif; padding: 20px; border: 2px solid #000; width: 100mm; max-width: 100%;">
                            <h3 style="margin: 0 0 10px 0; border-bottom: 2px solid #000; padding-bottom: 5px; text-transform: uppercase; font-size: 14px; letter-spacing: 1px;">WMS SHIPPING LABEL</h3>
                            <div style="font-size: 11px; margin-bottom: 10px;">
                                <p style="margin: 2px 0;"><strong>No. Order:</strong> {{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->nomor_nota ?: 'BK/'.$activeTransaction->id) : ($activeTransaction->nomor_transfer ?? '-') }}</p>
                                <p style="margin: 2px 0;"><strong>Tanggal:</strong> {{ $activeTransaction->tanggal ? \Carbon\Carbon::parse($activeTransaction->tanggal)->format('d M Y') : '-' }}</p>
                            </div>
                            <div style="border-top: 1px dashed #000; padding-top: 10px; font-size: 12px;">
                                <p style="margin: 2px 0;"><strong>Penerima:</strong></p>
                                <p style="margin: 2px 0; font-weight: bold; font-size: 14px;">{{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->pelanggan->nama_pelanggan ?? '-') : ($activeTransaction->lokasiTujuan->nama_lokasi ?? '-') }}</p>
                                <p style="margin: 2px 0; color: #555;">{{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->pelanggan->alamat ?? '-') : ($activeTransaction->lokasiTujuan->nama_lokasi ?? '-') }}</p>
                                <p style="margin: 2px 0; color: #555;">Tlp: {{ $activeTransactionType === 'BarangKeluar' ? ($activeTransaction->pelanggan->kontak ?? '-') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="glass-card rounded-3xl p-12 border border-white/10 shadow-2xl flex flex-col items-center justify-center text-center space-y-4">
                    <span class="material-symbols-outlined text-6xl text-primary/30">inventory_2</span>
                    <div>
                        <h3 class="text-base font-bold text-on-surface">Pilih Order untuk QC & Packing</h3>
                        <p class="text-xs text-on-surface-variant max-w-sm mt-1">Pilih transaksi yang telah selesai di-pick di sebelah kiri untuk mengisi kurir, nomor resi, serta melakukan pemeriksaan QC detail barang sebelum dikirim.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function printShippingLabel() {
            const labelHtml = document.getElementById('print-label-template').innerHTML;
            
            // Use iframe to bypass popup blocker
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
            
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write('<html><head><title>Shipping Label</title><style>body { margin: 0; padding: 0; }</style></head><body>' + labelHtml + '</body></html>');
            doc.close();
            
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 250);
        }
    </script>
</div>
