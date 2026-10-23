<div x-init="$dispatch('update-header-title', 'Detail Transfer Order')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    {{-- Header Info Card --}}
    <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-black text-on-surface font-mono">{{ $transfer->nomor_transfer }}</h3>
                <p class="text-xs text-on-surface-variant mt-1">Dibuat oleh <span class="font-bold text-primary">{{ $transfer->created_by }}</span> pada {{ $transfer->created_at->format('d/m/Y H:i') }} WIB</p>
            </div>
            <x-status-badge :status="$transfer->status" class="text-sm font-black px-4 py-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold mb-1">Tanggal</p>
                <p class="font-bold text-on-surface">{{ $transfer->tanggal->format('d/m/Y') }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold mb-1">Lokasi Asal</p>
                <p class="font-bold text-on-surface">{{ $transfer->lokasiAsal->kode_lokasi }} — {{ $transfer->lokasiAsal->nama_lokasi }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold mb-1">Lokasi Tujuan</p>
                <p class="font-bold text-on-surface">{{ $transfer->lokasiTujuan->kode_lokasi }} — {{ $transfer->lokasiTujuan->nama_lokasi }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                <p class="text-[10px] uppercase tracking-wider text-on-surface-variant/60 font-bold mb-1">Keterangan</p>
                <p class="font-medium text-on-surface-variant">{{ $transfer->keterangan ?: '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Status Progress Pipeline --}}
    <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
        @php
            $steps = ['DRAFT', 'PICKING', 'IN_TRANSIT', 'RECEIVED', 'CLOSED'];
            $currentIndex = array_search($transfer->status, $steps);
            if ($transfer->status === 'CANCELLED') $currentIndex = -1;
        @endphp
        <div class="flex items-center justify-between">
            @foreach($steps as $i => $step)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black border-2 transition-all duration-500 {{ $i <= $currentIndex ? 'bg-primary border-primary text-on-primary' : 'bg-surface-container border-white/10 text-on-surface-variant/50' }}">
                        @if($i < $currentIndex)
                            <span class="material-symbols-outlined text-sm">check</span>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="text-[9px] mt-2 font-bold uppercase tracking-wider {{ $i <= $currentIndex ? 'text-primary' : 'text-on-surface-variant/40' }}">{{ str_replace('_', ' ', $step) }}</span>
                </div>
                @if(!$loop->last)
                    <div class="h-0.5 flex-1 mx-1 rounded {{ $i < $currentIndex ? 'bg-primary' : 'bg-white/10' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Items Table --}}
    <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
        <h4 class="text-base font-bold text-on-surface flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-primary">inventory_2</span>
            Daftar Barang
        </h4>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4 text-center">Qty Diminta</th>
                        <th class="py-3 px-4 text-center">Qty Dikirim</th>
                        <th class="py-3 px-4 text-center">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($transfer->details as $detail)
                        <tr class="hover:bg-white/5 transition-colors text-on-surface-variant">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-on-surface">{{ $detail->produk->nama_barang }}</div>
                                <div class="text-[10px] text-primary/80 font-mono">{{ $detail->produk->kode_barang }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-on-surface">{{ $detail->qty_requested }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $detail->qty_sent > 0 ? 'text-amber-400' : 'text-on-surface-variant/40' }}">{{ $detail->qty_sent }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $detail->qty_received > 0 ? 'text-green-400' : 'text-on-surface-variant/40' }}">{{ $detail->qty_received }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <a href="{{ route('transfer') }}" class="px-6 py-3 rounded-2xl text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors">
            &larr; Kembali ke Daftar
        </a>

        <div class="flex flex-wrap gap-3">
            @if($transfer->status === 'DRAFT')
                <button wire:click="requestProcessShipment" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-colors border border-amber-500/20">
                    <span class="material-symbols-outlined text-lg">local_shipping</span>
                    Proses Pengiriman
                </button>
                <button wire:click="requestCancelTransfer" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors border border-red-500/20">
                    <span class="material-symbols-outlined text-lg">cancel</span>
                    Batalkan
                </button>
            @endif

            @if($transfer->status === 'IN_TRANSIT')
                <button wire:click="requestProcessReceive" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-green-500/10 text-green-400 hover:bg-green-500/20 transition-colors border border-green-500/20">
                    <span class="material-symbols-outlined text-lg">fact_check</span>
                    Konfirmasi Penerimaan
                </button>
            @endif

            @if($transfer->status === 'RECEIVED')
                <button wire:click="requestCloseTransfer" class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-lg">
                    <span class="material-symbols-outlined text-lg">task_alt</span>
                    Selesaikan Transfer
                </button>
            @endif
        </div>
    </div>

    @if($transfer->status === 'RECEIVED' && $transfer->received_by)
        <div class="p-4 rounded-2xl bg-green-500/5 border border-green-500/20 text-sm text-green-400">
            <span class="material-symbols-outlined text-sm mr-1">verified</span>
            Diterima oleh <span class="font-bold">{{ $transfer->received_by }}</span> pada {{ $transfer->received_at?->format('d/m/Y H:i') }}.
        </div>
    @endif
</div>
