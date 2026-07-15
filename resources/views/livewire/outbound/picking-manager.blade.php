<div x-init="$dispatch('update-header-title', 'Outbound Picking Manager')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    
    {{-- Alerts --}}
    
    

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: List of Picking Orders --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <h3 class="text-base font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">format_list_bulleted</span>
                    Picking Orders
                </h3>
                
                {{-- Tabs --}}
                <div class="flex border-b border-white/5 mb-4">
                    <button wire:click="$set('activeTab', 'pending')" class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider {{ $activeTab === 'pending' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant/60 hover:text-on-surface' }}">
                        Aktif / Pending
                    </button>
                    <button wire:click="$set('activeTab', 'history')" class="flex-1 pb-3 text-xs font-bold uppercase tracking-wider {{ $activeTab === 'history' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant/60 hover:text-on-surface' }}">
                        Riwayat (Selesai)
                    </button>
                </div>

                {{-- List --}}
                <div class="space-y-3 max-h-[50vh] overflow-y-auto custom-scrollbar">
                    @if($activeTab === 'pending')
                        @forelse($pendingLists as $p)
                            <div wire:click="selectPicking({{ $p->id }})" class="p-4 rounded-2xl border transition-all cursor-pointer {{ $activePickingId === $p->id ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-on-surface font-mono">{{ $p->nomor_picking }}</span>
                                    @php
                                        $badgeColor = $p->status === 'PICKING' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 animate-pulse' : 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black border {{ $badgeColor }}">{{ $p->status }}</span>
                                </div>
                                <div class="text-[10px] text-on-surface-variant/70 space-y-1">
                                    <p>Tipe: <strong class="text-on-surface">{{ $p->referensi_tipe === 'TransferHeader' ? 'Transfer' : 'Penjualan' }}</strong></p>
                                    <p>Lokasi: <span class="text-primary font-bold">{{ $p->lokasi->kode_lokasi }}</span></p>
                                    <p>Item: {{ $p->details->count() }} SKUs</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-on-surface-variant/50 py-6">Tidak ada order picking aktif.</p>
                        @endforelse
                    @else
                        @forelse($completedLists as $p)
                            <div wire:click="selectPicking({{ $p->id }})" class="p-4 rounded-2xl border transition-all cursor-pointer {{ $activePickingId === $p->id ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-on-surface font-mono">{{ $p->nomor_picking }}</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black border bg-green-500/10 text-green-400 border-green-500/20">COMPLETED</span>
                                </div>
                                <div class="text-[10px] text-on-surface-variant/70 space-y-1">
                                    <p>Picker: <strong class="text-on-surface">{{ $p->picker_name }}</strong></p>
                                    <p>Selesai: {{ $p->completed_at ? $p->completed_at->format('d/m H:i') : '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs text-on-surface-variant/50 py-6">Tidak ada riwayat picking.</p>
                        @endforelse
                    @endif
                </div>
            </div>

            {{-- Generating Picking List for pending transactions --}}
            @if($activeTab === 'pending')
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <h3 class="text-base font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">pending_actions</span>
                    Pending Outbound
                </h3>
                <div class="space-y-3 max-h-[30vh] overflow-y-auto custom-scrollbar">
                    @forelse($bkWithNoPicking as $bk)
                        <div wire:click="selectPendingOutbound({{ $bk->id }}, 'BarangKeluar')" class="p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between {{ $activePendingOutboundId === $bk->id && $activePendingOutboundType === 'BarangKeluar' ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }}">
                            <div class="text-xs">
                                <p class="font-bold text-on-surface">{{ $bk->nomor_nota ?: 'BK-'.$bk->id }}</p>
                                <p class="text-[10px] text-on-surface-variant/60">{{ $bk->pelanggan->nama_pelanggan }}</p>
                            </div>
                            <button wire:click.stop="generatePickingList('BarangKeluar', {{ $bk->id }})" class="px-3 py-1 bg-primary text-on-primary text-[10px] font-bold rounded-lg hover:bg-primary/95 transition-all">
                                Pick
                            </button>
                        </div>
                    @empty
                    @endforelse

                    @forelse($trfWithNoPicking as $trf)
                        <div wire:click="selectPendingOutbound({{ $trf->id }}, 'TransferHeader')" class="p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between {{ $activePendingOutboundId === $trf->id && $activePendingOutboundType === 'TransferHeader' ? 'bg-primary/10 border-primary' : 'bg-white/5 border-white/5 hover:bg-white/10' }}">
                            <div class="text-xs">
                                <p class="font-bold text-on-surface">{{ $trf->nomor_transfer }}</p>
                                <p class="text-[10px] text-on-surface-variant/60">{{ $trf->lokasiAsal->kode_lokasi }} &rarr; {{ $trf->lokasiTujuan->kode_lokasi }}</p>
                            </div>
                            <button wire:click.stop="generatePickingList('TransferHeader', {{ $trf->id }})" class="px-3 py-1 bg-primary text-on-primary text-[10px] font-bold rounded-lg hover:bg-primary/95 transition-all">
                                Pick
                            </button>
                        </div>
                    @empty
                    @endforelse

                    @if($bkWithNoPicking->isEmpty() && $trfWithNoPicking->isEmpty())
                        <p class="text-center text-[10px] text-on-surface-variant/40 py-4">Semua transaksi outbound telah dibuatkan Picking List.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Picking Work Area (Scanners & Verification) --}}
        <div class="lg:col-span-2">
            @if($activePicking)
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-bold text-on-surface font-mono">{{ $activePicking->nomor_picking }}</h2>
                                @php
                                    $statusColor = $activePicking->status === 'COMPLETED' ? 'bg-green-500/10 text-green-400 border-green-500/20' : 
                                                  ($activePicking->status === 'PICKING' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 animate-pulse' : 'bg-slate-500/10 text-slate-400 border-slate-500/20');
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[9px] font-black border {{ $statusColor }}">{{ $activePicking->status }}</span>
                            </div>
                            <p class="text-xs text-on-surface-variant">
                                Lokasi Asal: <strong class="text-primary">{{ $activePicking->lokasi->kode_lokasi }}</strong>
                                @if($activePicking->status === 'COMPLETED')
                                    <span class="mx-2 text-white/10">|</span>
                                    Picker: <strong class="text-on-surface">{{ $activePicking->picker_name }}</strong>
                                    <span class="mx-2 text-white/10">|</span>
                                    Selesai: <strong class="text-on-surface">{{ $activePicking->completed_at ? $activePicking->completed_at->format('d M Y H:i') : '-' }}</strong>
                                @endif
                            </p>
                        </div>
                        <div class="flex gap-2">
                            @if($activePicking->status === 'PENDING')
                                <button wire:click="startPicking({{ $activePicking->id }})" class="px-5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">play_arrow</span> Mulai Picking
                                </button>
                            @endif
                            <button wire:click="closePicking" class="px-4 py-2 bg-white/5 border border-white/10 text-xs font-semibold rounded-xl text-on-surface hover:bg-white/10 transition-all">
                                Tutup
                            </button>
                        </div>
                    </div>

                    @if($activePicking->status === 'PICKING')
                        {{-- Barcode Scan Form --}}
                        <div class="p-6 rounded-2xl bg-primary/5 border border-primary/20 space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">qr_code_scanner</span>
                                Scan Barcode Konfirmasi (Fokus Scanner)
                            </h4>
                            
                            <form wire:submit.prevent="scanBarcode" class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Scan SKU Produk</label>
                                    <div class="flex gap-2">
                                        <input wire:model="scanProduct" id="scan-product-input" type="text" placeholder="Scan SKU Produk" autofocus
                                            class="flex-1 bg-surface-container border border-white/10 rounded-2xl py-2 px-3 text-xs text-on-surface font-mono focus:outline-none focus:ring-1 focus:ring-primary" />
                                        <button type="button" onclick="startCameraScanner('product')" class="px-3 bg-white/5 border border-white/10 text-xs font-bold rounded-xl hover:bg-white/10 transition-all text-on-surface flex items-center justify-center shadow-sm" title="Pindai SKU dengan Kamera/File">
                                            <span class="material-symbols-outlined text-sm">photo_camera</span>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="py-2 px-6 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/95 transition-all h-[38px]">
                                        Input / Verify
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- Items Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase">
                                    <th class="pb-3">Produk</th>
                                    <th class="pb-3">Rak Rekomendasi (FIFO)</th>
                                    <th class="pb-3 text-center">Permintaan</th>
                                    <th class="pb-3 text-center">Telah Diambil</th>
                                    <th class="pb-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($activePicking->details as $d)
                                    <tr class="hover:bg-white/5 transition-colors {{ $activePicking->status !== 'COMPLETED' && $d->is_verified ? 'opacity-50' : '' }}">
                                        <td class="py-3 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                {{ substr($d->produk->kode_barang, 0, 3) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-on-surface">{{ $d->produk->nama_barang }}</p>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[9px] text-on-surface-variant/60 font-mono">{{ $d->produk->kode_barang }}</span>
                                                    <button type="button" wire:click="showBarcodeModal('{{ $d->produk->kode_barang }}', 'sku')" class="text-primary hover:text-primary/80 transition-colors flex items-center" title="Tampilkan Barcode SKU">
                                                        <span class="material-symbols-outlined text-[12px]">barcode_scanner</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded font-mono font-bold text-[10px]">
                                                    {{ $d->lokasiRak->kode_lokasi }}
                                                </span>
                                                <button type="button" wire:click="showBarcodeModal('{{ $d->lokasiRak->kode_lokasi }}', 'location')" class="text-primary hover:text-primary/80 transition-colors flex items-center" title="Tampilkan Barcode Rak">
                                                    <span class="material-symbols-outlined text-sm">barcode_scanner</span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center font-bold text-on-surface">{{ $d->qty_to_pick }}</td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm font-black {{ $d->qty_picked >= $d->qty_to_pick ? 'text-green-400' : 'text-amber-400' }}">{{ $d->qty_picked }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($d->is_verified)
                                                <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded text-[9px] font-bold uppercase">VERIFIED</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-slate-500/10 text-slate-400 border border-slate-500/20 rounded text-[9px] font-bold uppercase">BELUM</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($activePicking->status === 'PICKING')
                        <div class="flex justify-end pt-4 border-t border-white/5">
                            <button wire:click="completePicking" class="px-6 py-3 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container font-black text-xs rounded-xl shadow-xl hover:shadow-primary/20 transition-all duration-200 amber-glow-strong">
                                Selesaikan Picking & Update Stok
                            </button>
                        </div>
                    @endif
                </div>
            @elseif($activePendingOutbound)
                {{-- Pending Outbound Detail Panel --}}
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-lg font-bold text-on-surface font-mono">
                                    {{ $activePendingOutboundType === 'BarangKeluar' ? ($activePendingOutbound->nomor_nota ?: 'Barang Keluar #'.$activePendingOutbound->id) : $activePendingOutbound->nomor_transfer }}
                                </h2>
                                <span class="px-2 py-0.5 rounded text-[9px] font-black border bg-amber-500/10 text-amber-400 border-amber-500/20">PENDING OUTBOUND</span>
                            </div>
                            <p class="text-xs text-on-surface-variant">
                                @if($activePendingOutboundType === 'BarangKeluar')
                                    Tujuan: <strong class="text-primary">{{ $activePendingOutbound->pelanggan->nama_pelanggan ?? '-' }}</strong>
                                @else
                                    Tujuan: <strong class="text-primary">{{ $activePendingOutbound->lokasiTujuan->nama_lokasi ?? '-' }}</strong>
                                    <span class="mx-2 text-white/10">|</span>
                                    Asal: <strong class="text-on-surface">{{ $activePendingOutbound->lokasiAsal->nama_lokasi ?? '-' }}</strong>
                                @endif
                                <span class="mx-2 text-white/10">|</span>
                                Tanggal: <strong class="text-on-surface">{{ $activePendingOutbound->tanggal ? \Carbon\Carbon::parse($activePendingOutbound->tanggal)->format('d M Y') : '-' }}</strong>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="generatePickingList('{{ $activePendingOutboundType }}', {{ $activePendingOutbound->id }})" class="px-5 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">build</span> Buat Picking List
                            </button>
                            <button wire:click="$set('activePendingOutboundId', null)" class="px-4 py-2 bg-white/5 border border-white/10 text-xs font-semibold rounded-xl text-on-surface hover:bg-white/10 transition-all">
                                Tutup
                            </button>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase">
                                    <th class="pb-3">Produk</th>
                                    <th class="pb-3 text-center">Jumlah Permintaan</th>
                                    <th class="pb-3 text-center">Satuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($activePendingOutbound->details as $d)
                                    @php
                                        $qty = $activePendingOutboundType === 'BarangKeluar' ? $d->qty_keluar : $d->qty_requested;
                                    @endphp
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="py-3 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                                {{ substr($d->produk->kode_barang, 0, 3) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-on-surface">{{ $d->produk->nama_barang }}</p>
                                                <p class="text-[9px] text-on-surface-variant/60 font-mono mt-0.5">{{ $d->produk->kode_barang }}</p>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center font-bold text-on-surface">{{ $qty }}</td>
                                        <td class="py-3 text-center text-on-surface-variant/80">{{ $d->satuan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="glass-card rounded-3xl p-12 border border-white/10 shadow-2xl flex flex-col items-center justify-center text-center space-y-4">
                    <span class="material-symbols-outlined text-6xl text-primary/30">barcode_scanner</span>
                    <div>
                        <h3 class="text-base font-bold text-on-surface">Pilih Picking Order untuk Memulai</h3>
                        <p class="text-xs text-on-surface-variant max-w-sm mt-1">Pilih order aktif di panel sebelah kiri untuk memulai pemindaian barcode rak dan produk FIFO.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Barcode Viewer Modal --}}
    @if ($isBarcodeModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeBarcodeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-sm mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-sm font-bold text-primary">{{ $barcodeModalTitle }}</h3>
                    <button wire:click="closeBarcodeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-inner flex flex-col items-center justify-center max-w-full overflow-hidden">
                        {!! \App\Helpers\Barcode128::getSvg($barcodeModalValue, 80, 2) !!}
                        <span class="mt-3 font-mono text-xs font-bold text-black tracking-widest">{{ $barcodeModalValue }}</span>
                    </div>
                    <p class="text-[10px] text-on-surface-variant/60">Arahkan scanner hardware/aplikasi HP Anda ke barcode di atas untuk melakukan simulasi scan.</p>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50 gap-2">
                    <button wire:click="closeBarcodeModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                    @if ($barcodeModalType === 'sku')
                    <!-- Quick Simulate Scan Button for testing without hardware scanner -->
                    <button type="button" 
                            x-on:click="
                                $wire.set('scanProduct', '{{ $barcodeModalValue }}');
                                $wire.closeBarcodeModal();
                                $wire.scanBarcode();
                            "
                            class="px-4 py-2 bg-primary text-on-primary font-bold text-xs rounded-xl hover:bg-primary/90 transition-all">
                        Simulasi Scan
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Camera Scanner Modal -->
    <div id="camera-scanner-modal" wire:ignore class="fixed inset-0 z-50 items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none hidden px-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" onclick="closeCameraScanner()"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-sm mx-auto my-6 z-50">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined animate-pulse text-primary">barcode_scanner</span>
                        Kamera Pemindai Barcode
                    </h3>
                    <button type="button" onclick="closeCameraScanner()" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col items-center justify-center space-y-4">
                    <!-- Pilihan Mode Tab -->
                    <div class="flex border-b border-white/5 w-full mb-2 text-xs font-bold uppercase tracking-wider text-center">
                        <button type="button" id="tab-camera" onclick="switchScannerTab('camera')" class="flex-1 pb-2 border-b-2 border-primary text-primary transition-all">
                            Kamera
                        </button>
                        <button type="button" id="tab-file" onclick="switchScannerTab('file')" class="flex-1 pb-2 text-on-surface-variant/40 hover:text-on-surface-variant border-b-2 border-transparent transition-all">
                            Unggah Gambar
                        </button>
                    </div>

                    <!-- Dropdown Pilihan Kamera -->
                    <div class="w-full">
                        <label for="camera-select" class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Pilih Kamera</label>
                        <select id="camera-select" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Mengakses kamera...</option>
                        </select>
                    </div>

                    <!-- Viewport Scanner -->
                    <div class="w-full overflow-hidden rounded-2xl bg-white/5 border border-white/10 aspect-[4/3] relative flex items-center justify-center">
                        <div id="camera-reader" class="w-full h-full"></div>
                        <div class="absolute inset-x-0 top-1/2 h-0.5 bg-red-500 animate-bounce opacity-70 pointer-events-none shadow-[0_0_8px_rgba(239,68,68,0.8)]"></div>
                    </div>

                    <p id="camera-instruction" class="text-[10px] text-on-surface-variant/60 text-center font-medium">Posisikan barcode Code 128 tepat di tengah garis laser merah untuk memindai.</p>

                    <!-- File Scanner Area -->
                    <div id="file-scanner-area" class="w-full space-y-3 hidden">
                        <div class="flex flex-col items-center justify-center border-2 border-dashed border-white/10 rounded-2xl p-6 bg-white/5 hover:bg-white/10 transition duration-150 cursor-pointer relative" id="drop-zone">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/50 mb-2">upload_file</span>
                            <span class="text-xs text-on-surface-variant font-semibold">Tarik & lepas gambar barcode di sini atau</span>
                            <span class="text-xs text-primary font-bold mt-1">Pilih File Gambar</span>
                            <input type="file" id="barcode-file-input" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        </div>
                        <div id="file-scan-result-error" class="text-xs text-error text-center font-semibold hidden">
                            Gagal mendeteksi barcode. Pastikan gambar barcode terlihat jelas.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 bg-surface-container-low/50">
                    <button type="button" onclick="closeCameraScanner()" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Html5-Qrcode CDN -->
    <script src="https://unpkg.com/html5-qrcode" defer data-navigate-once></script>

    <!-- Camera Scanner Handler -->
    <script data-navigate-once>
        var html5QrcodeScanner = html5QrcodeScanner || null;
        var activeTab = activeTab || 'camera';
        var scannerTarget = scannerTarget || 'rak'; // 'rak' or 'product'

        function startCameraScanner(target) {
            scannerTarget = target;
            
            // Tampilkan modal
            const modal = document.getElementById('camera-scanner-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Reset tab ke default (Kamera)
            switchScannerTab('camera');
            setupFileInputHandlers();
        }

        function cleanupExistingScanner() {
            if (!html5QrcodeScanner) {
                return Promise.resolve();
            }

            let promise = Promise.resolve();
            if (html5QrcodeScanner.isScanning) {
                promise = html5QrcodeScanner.stop();
            }
            
            return promise.then(() => {
                try {
                    html5QrcodeScanner.clear();
                } catch (e) {
                    console.warn("Error clearing scanner:", e);
                }
                html5QrcodeScanner = null;
            });
        }

        function switchScannerTab(tab) {
            activeTab = tab;
            const tabCamera = document.getElementById('tab-camera');
            const tabFile = document.getElementById('tab-file');
            const cameraSection = document.getElementById('camera-select').closest('div.w-full');
            const cameraViewport = document.getElementById('camera-reader').closest('div.w-full');
            const fileSection = document.getElementById('file-scanner-area');

            if (tab === 'camera') {
                tabCamera.className = "flex-1 pb-2 border-b-2 border-primary text-primary transition-all";
                tabFile.className = "flex-1 pb-2 text-on-surface-variant/40 hover:text-on-surface-variant border-b-2 border-transparent transition-all";
                cameraSection.classList.remove('hidden');
                cameraViewport.classList.remove('hidden');
                fileSection.classList.add('hidden');
                
                initializeCameraScanner();
            } else {
                tabFile.className = "flex-1 pb-2 border-b-2 border-primary text-primary transition-all";
                tabCamera.className = "flex-1 pb-2 text-on-surface-variant/40 hover:text-on-surface-variant border-b-2 border-transparent transition-all";
                cameraSection.classList.add('hidden');
                cameraViewport.classList.add('hidden');
                fileSection.classList.remove('hidden');
                document.getElementById('file-scan-result-error').classList.add('hidden');
                
                cleanupExistingScanner();
            }
        }

        function initializeCameraScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(initializeCameraScanner, 100);
                return;
            }

            cleanupExistingScanner().then(() => {
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length) {
                        const select = document.getElementById('camera-select');
                        select.innerHTML = '';
                        devices.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Kamera ${select.childNodes.length + 1}`;
                            select.appendChild(option);
                        });

                        let cameraId = devices[0].id;
                        const backCamera = devices.find(device => 
                            device.label.toLowerCase().includes('back') || 
                            device.label.toLowerCase().includes('environment') ||
                            device.label.toLowerCase().includes('rear')
                        );
                        if (backCamera) {
                            cameraId = backCamera.id;
                            select.value = cameraId;
                        }

                        startScanningWithCamera(cameraId);

                        select.onchange = () => {
                            cleanupExistingScanner().then(() => {
                                startScanningWithCamera(select.value);
                            });
                        };
                    } else {
                        alert('Tidak ada kamera yang terdeteksi.');
                        closeCameraScanner();
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Gagal mengakses izin kamera: ' + err);
                    closeCameraScanner();
                });
            });
        }

        function startScanningWithCamera(cameraId) {
            html5QrcodeScanner = new Html5Qrcode("camera-reader", {
                formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]
            });
            html5QrcodeScanner.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: function(width, height) {
                        return {
                            width: Math.max(Math.min(width * 0.85, 300), 50),
                            height: Math.max(Math.min(height * 0.35, 100), 50)
                        };
                    }
                },
                (decodedText) => {
                    handleScanSuccess(decodedText);
                },
                (errorMessage) => {}
            ).catch(err => {
                console.error("Gagal menjalankan scanner:", err);
            });
        }

        function handleScanSuccess(decodedText) {
            if (scannerTarget === 'rak') {
                @this.set('scanRak', decodedText);
            } else {
                @this.set('scanProduct', decodedText);
            }
            
            // Auto submit / verify scan
            @this.call('scanBarcode');
            
            window.dispatchEvent(new CustomEvent('play-beep'));
            
            const viewport = document.getElementById('camera-reader');
            if (viewport) {
                viewport.classList.add('ring-4', 'ring-green-500');
                setTimeout(() => viewport.classList.remove('ring-4', 'ring-green-500'), 500);
            }
            
            closeCameraScanner();
        }

        // File scanning function
        function scanBarcodeFile(file) {
            const errorEl = document.getElementById('file-scan-result-error');
            errorEl.classList.add('hidden');

            if (typeof Html5Qrcode === 'undefined') {
                alert('Pustaka scanner belum termuat sepenuhnya.');
                return;
            }

            function extractSkuFromSvgText(svgText) {
                try {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(svgText, "image/svg+xml");
                    const textNodes = doc.querySelectorAll('text');
                    for (const node of textNodes) {
                        const fontFamily = node.getAttribute('font-family') || '';
                        const y = node.getAttribute('y') || '';
                        if (fontFamily.includes('Courier New') || y === '162') {
                            return node.textContent.trim();
                        }
                    }
                    if (textNodes.length > 0) {
                        const lastText = textNodes[textNodes.length - 1].textContent.trim();
                        if (lastText && /^[A-Z0-9\-_]+$/i.test(lastText)) {
                            return lastText;
                        }
                    }
                } catch (e) {
                    console.error("Gagal mengekstrak SKU dari XML SVG:", e);
                }
                return null;
            }

            const isSvg = file.type === 'image/svg+xml' || file.name.toLowerCase().endsWith('.svg');

            if (isSvg) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const svgText = e.target.result;
                    const extractedSku = extractSkuFromSvgText(svgText);
                    if (extractedSku) {
                        handleScanSuccess(extractedSku);
                        alert('Berhasil memindai barcode: ' + extractedSku);
                        return;
                    }
                    rasterizeAndScanSvg(file);
                };
                reader.onerror = function(err) {
                    console.error("Gagal membaca teks SVG, mencoba rasterisasi:", err);
                    rasterizeAndScanSvg(file);
                };
                reader.readAsText(file);
            } else {
                executeScan(file);
            }

            function rasterizeAndScanSvg(svgFile) {
                const url = URL.createObjectURL(svgFile);
                const img = new Image();
                img.onload = function() {
                    try {
                        const canvas = document.createElement('canvas');
                        const baseWidth = img.naturalWidth || img.width || 320;
                        const baseHeight = img.naturalHeight || img.height || 190;
                        
                        const scale = 3;
                        canvas.width = baseWidth * scale;
                        canvas.height = baseHeight * scale;
                        
                        const ctx = canvas.getContext('2d');
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                        
                        canvas.toBlob(function(blob) {
                            URL.revokeObjectURL(url);
                            if (blob) {
                                const convertedFile = new File([blob], svgFile.name.replace(/\.[^/.]+$/, "") + ".png", { type: 'image/png' });
                                executeScan(convertedFile);
                            } else {
                                console.error("Gagal membuat PNG Blob dari SVG.");
                                errorEl.classList.remove('hidden');
                            }
                        }, 'image/png');
                    } catch (err) {
                        URL.revokeObjectURL(url);
                        console.error("Gagal memproses gambar SVG:", err);
                        errorEl.classList.remove('hidden');
                    }
                };
                img.onerror = function(err) {
                    URL.revokeObjectURL(url);
                    console.error("Gagal memuat file SVG sebagai gambar:", err);
                    errorEl.classList.remove('hidden');
                };
                img.src = url;
            }

            function executeScan(fileToScan) {
                cleanupExistingScanner().then(() => {
                    const html5Qrcode = new Html5Qrcode("camera-reader", {
                        formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]
                    });
                    
                    html5QrcodeScanner = html5Qrcode;
                    
                    html5Qrcode.scanFile(fileToScan, false)
                        .then(decodedText => {
                            handleScanSuccess(decodedText);
                            alert('Berhasil memindai barcode: ' + decodedText);
                        })
                        .catch(err => {
                            console.error("Gagal mendeteksi barcode dari file:", err);
                            errorEl.classList.remove('hidden');
                        });
                });
            }
        }

        function setupFileInputHandlers() {
            const fileInput = document.getElementById('barcode-file-input');
            if (fileInput) {
                fileInput.onchange = (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    scanBarcodeFile(file);
                    fileInput.value = ''; // Reset value to allow uploading and scanning same file again
                };
            }

            const dropZone = document.getElementById('drop-zone');
            if (dropZone) {
                dropZone.ondragover = (e) => {
                    e.preventDefault();
                    dropZone.classList.add('bg-white/10', 'border-primary');
                };
                dropZone.ondragleave = () => {
                    dropZone.classList.remove('bg-white/10', 'border-primary');
                };
                dropZone.ondrop = (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('bg-white/10', 'border-primary');
                    if (e.dataTransfer.files && e.dataTransfer.files.length) {
                        scanBarcodeFile(e.dataTransfer.files[0]);
                    }
                };
            }
        }

        function stopCameraScannerOnly() {
            return cleanupExistingScanner();
        }

        function closeCameraScanner() {
            cleanupExistingScanner().then(() => {
                const modal = document.getElementById('camera-scanner-modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }).catch(err => {
                console.error("Gagal menghentikan kamera:", err);
                const modal = document.getElementById('camera-scanner-modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            });
        }

        function handleNavigation(event) {
            const targetUrl = event.detail.url;
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning && targetUrl) {
                event.preventDefault();
                cleanupExistingScanner().then(() => {
                    document.removeEventListener('livewire:navigate', handleNavigation);
                    Livewire.navigate(targetUrl);
                }).catch(err => {
                    console.error("Gagal menghentikan kamera saat navigasi:", err);
                    document.removeEventListener('livewire:navigate', handleNavigation);
                    Livewire.navigate(targetUrl);
                });
            } else {
                document.removeEventListener('livewire:navigate', handleNavigation);
            }
        }
        document.addEventListener('livewire:navigate', handleNavigation);

        // Autofocus shifts for barcode scanner optimization
        window.addEventListener('focus-rak', () => {
            setTimeout(() => {
                const el = document.getElementById('scan-rak-input');
                if (el) {
                    el.focus();
                    el.select();
                }
            }, 80);
        });

        window.addEventListener('focus-product', () => {
            setTimeout(() => {
                const el = document.getElementById('scan-product-input');
                if (el) {
                    el.focus();
                    el.select();
                }
            }, 80);
        });
    </script>
</div>
