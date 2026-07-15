<div x-init="
        $dispatch('update-header-title', 'Audit Stok (Stock Opname)');
        setTimeout(() => { $dispatch('focus-barcode') }, 200);
        window.addEventListener('play-beep', () => { playSynthTone(600, 'sine', 0.15); });
        window.addEventListener('play-error-buzzer', () => { playSynthTone(220, 'sawtooth', 0.35); });
     "
     @click="
        if ($event.target.closest('a, button, input, textarea, select, option')) return;
        setTimeout(() => {
            const barcodeInput = document.getElementById('barcode-scan-input');
            if (barcodeInput) barcodeInput.focus();
        }, 50);
     "
     class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    <style>
        @keyframes scan-flash {
            0% { background-color: rgba(255, 178, 44, 0.15); }
            100% { background-color: transparent; }
        }
        .animate-scan-flash {
            animation: scan-flash 1.5s ease-out;
        }
    </style>

    @if (!$selectedSession && !$isCreatingSession)
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Sesi Opname..." type="text"/>
        </div>
    </template>
    @endif

    @if (!$selectedSession)
        <!-- ================= LIST SESSIONS ================= -->
        @if ($isCreatingSession)
            <!-- Form Buat Sesi Baru -->
            <div class="max-w-xl mx-auto glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
                <div class="pb-2 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">Buat Sesi Opname Baru</h3>
                    <p class="text-xs text-on-surface-variant/60 mt-1">Pilih lokasi gudang/toko yang akan diaudit untuk menarik data stok saat ini.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tanggal Sesi</label>
                        <input wire:model="new_tanggal" type="date" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        @error('new_tanggal') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Pilih Lokasi Audit</label>
                        <select wire:model="new_lokasi_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($locationsList as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->kode_lokasi }} ({{ $loc->nama_lokasi }})</option>
                            @endforeach
                        </select>
                        @error('new_lokasi_id') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Keterangan / Memo Audit</label>
                        <textarea wire:model="new_keterangan" rows="3" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: Audit Tahunan Q2 Gudang Utama"></textarea>
                        @error('new_keterangan') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/5">
                    <button wire:click="cancelCreateSession" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="createSession" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        Mulai Audit
                    </button>
                </div>
            </div>
        @else
            <!-- Dashboard List Sessions -->
            <section class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center w-full md:w-auto">
                    <!-- Scan to Quick-Load location or session -->
                    <div class="relative flex-1 md:w-80">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">barcode_scanner</span>
                        <input wire:model="barcodeScan" wire:keydown.enter.prevent="processBarcodeScan" id="barcode-scan-input" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-mono uppercase placeholder:text-on-surface-variant/30" placeholder="Pindai Barcode Lokasi..."/>
                    </div>
                </div>
                @can('input masuk')
                <button wire:click="startNewSession" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                    <span class="material-symbols-outlined">add</span>
                    Mulai Audit Baru
                </button>
                @endcan
            </section>

            @error('barcodeScan')
                <div class="glass-card rounded-2xl p-4 border border-error/20 bg-error/5 text-xs text-error font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">error</span>
                    {{ $message }}
                </div>
            @enderror

            <!-- Sessions Table -->
            <section class="glass-card rounded-3xl overflow-hidden mb-8">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Nomor Opname</th>
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Tanggal</th>
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Lokasi</th>
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Status</th>
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Operator</th>
                                <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs">
                            @forelse ($sessions as $session)
                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 font-mono text-primary/80 text-sm font-bold">{{ $session->nomor_opname }}</td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $session->tanggal->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-surface-container-high border border-white/5 rounded-full font-bold uppercase text-[10px] text-on-surface">{{ $session->lokasi->kode_lokasi }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($session->status === 'DRAFT')
                                            <span class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">DRAFT</span>
                                        @elseif($session->status === 'COMPLETED')
                                            <span class="px-3 py-1 bg-tertiary/10 text-tertiary border border-tertiary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">COMPLETED</span>
                                        @elseif($session->status === 'ADJUSTED')
                                            <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[10px] font-bold uppercase tracking-wider">ADJUSTED</span>
                                        @else
                                            <span class="px-3 py-1 bg-white/5 text-on-surface-variant/40 border border-white/5 rounded-full text-[10px] font-bold uppercase tracking-wider">CANCELLED</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant">{{ $session->operator_name }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <button wire:click="loadSession({{ $session->id }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all font-semibold" title="Buka Detail">
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada sesi stock opname ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
                    <p class="text-xs text-on-surface-variant">
                        Menampilkan {{ $sessions->firstItem() ?? 0 }} s.d. {{ $sessions->lastItem() ?? 0 }} dari {{ $sessions->total() }} hasil
                    </p>
                    <div class="flex gap-2">
                        {{ $sessions->links() }}
                    </div>
                </div>
            </section>
        @endif
    @else
        <!-- ================= ACTIVE SESSION DETAILS ================= -->
        <div class="space-y-6">
            <!-- Header Information Card -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono text-sm text-primary/80 font-bold">{{ $selectedSession->nomor_opname }}</span>
                        @if($selectedSession->status === 'DRAFT')
                            <span class="px-3 py-0.5 bg-primary/10 text-primary border border-primary/20 rounded-full text-[9px] font-black tracking-widest uppercase">DRAFT</span>
                        @elseif($selectedSession->status === 'COMPLETED')
                            <span class="px-3 py-0.5 bg-tertiary/10 text-tertiary border border-tertiary/20 rounded-full text-[9px] font-black tracking-widest uppercase">COMPLETED</span>
                        @elseif($selectedSession->status === 'ADJUSTED')
                            <span class="px-3 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[9px] font-black tracking-widest uppercase">ADJUSTED</span>
                        @else
                            <span class="px-3 py-0.5 bg-white/5 text-on-surface-variant/40 border border-white/5 rounded-full text-[9px] font-black tracking-widest uppercase">CANCELLED</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-on-surface">Audit Lokasi: {{ $selectedSession->lokasi->kode_lokasi }}</h3>
                    <p class="text-xs text-on-surface-variant/80">{{ $selectedSession->lokasi->nama_lokasi }}</p>
                    <p class="text-[10px] text-on-surface-variant/50">Diopname pada: {{ $selectedSession->tanggal->format('d M Y') }} | Oleh: {{ $selectedSession->operator_name }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="backToList" class="px-5 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all">
                        Kembali Ke Daftar
                    </button>

                    @if($selectedSession->status === 'DRAFT')
                        @can('input masuk')
                        <button wire:click="cancelSession" class="px-5 py-2.5 bg-white/5 border border-red-500/20 text-error hover:bg-red-500/5 rounded-2xl text-xs font-bold transition-all">
                            Batalkan Sesi
                        </button>
                        <button wire:click="completeSession" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl transition-all hover:shadow-primary/20 active:scale-95 duration-200">
                            Selesaikan Pencatatan
                        </button>
                        @endcan
                    @endif

                    @if($selectedSession->status === 'COMPLETED')
                        @can('manage master')
                        <button wire:click="executeAdjustment" class="px-6 py-2.5 bg-green-600 text-white rounded-2xl font-bold text-xs shadow-xl transition-all hover:bg-green-700 active:scale-95 duration-200">
                            Sinkronisasi & Terapkan Stok
                        </button>
                        @endcan
                    @endif
                </div>
            </div>

            @if($selectedSession->status === 'DRAFT')
                <!-- Scanner Input Card -->
                <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                    <h3 class="text-xs font-black text-primary uppercase tracking-wider mb-3 pb-2 border-b border-white/5 flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary animate-pulse text-base">barcode_scanner</span>
                        Alur Verifikasi: Pindai Barcode Lokasi lalu Pindai SKU Produk
                    </h3>

                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                            <input wire:model="barcodeScan" wire:keydown.enter.prevent="processBarcodeScan" type="text" id="barcode-scan-input" placeholder="Arahkan barcode scanner kemari untuk memindai SKU..." class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold uppercase tracking-wider placeholder:text-on-surface-variant/30">
                        </div>
                        <button type="button" wire:click="processBarcodeScan" class="px-6 py-3 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                            Tambah Scan
                        </button>
                    </div>
                    @error('barcodeScan') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Audit Items Table Card -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                    <h3 class="text-xs font-black text-primary uppercase tracking-wider flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary text-base">analytics</span>
                        Detail Hasil Audit Barang
                    </h3>
                </div>

                <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center w-24">Kode SKU</th>
                                <th class="px-4 py-3 text-center w-28">Stok Sistem</th>
                                <th class="px-4 py-3 text-center w-36">Hasil Fisik</th>
                                <th class="px-4 py-3 text-center w-28">Selisih (Discrepancy)</th>
                                <th class="px-4 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($detailsList as $item)
                                <tr class="hover:bg-white/5 transition-all">
                                    <td class="p-3">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-on-surface">{{ $item['nama_barang'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center font-mono text-primary/80">{{ $item['kode_barang'] }}</td>
                                    <td class="p-3 text-center font-bold text-on-surface-variant/80">{{ number_format($item['qty_sistem']) }} {{ $item['satuan'] }}</td>
                                    <td class="p-3 text-center">
                                        @if($selectedSession->status === 'DRAFT')
                                            <input type="number" 
                                                   value="{{ $item['qty_fisik'] }}" 
                                                   onchange="@this.call('updateQtyFisik', {{ $item['id'] }}, this.value)" 
                                                   min="0" 
                                                   class="w-20 bg-surface-container-low border border-white/10 rounded-xl py-1 px-2 text-xs text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-bold">
                                        @else
                                            <span class="font-bold text-on-surface">{{ number_format($item['qty_fisik']) }} {{ $item['satuan'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        @if ($item['selisih'] == 0)
                                            <span class="px-3 py-1 bg-white/5 text-on-surface-variant/60 rounded-full font-bold">0</span>
                                        @elseif ($item['selisih'] > 0)
                                            <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full font-bold">+{{ $item['selisih'] }}</span>
                                        @else
                                            <span class="px-3 py-1 bg-red-500/10 text-error border border-red-500/20 rounded-full font-bold">{{ $item['selisih'] }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($selectedSession->status === 'DRAFT')
                                            <input type="text" 
                                                   value="{{ $item['catatan'] }}" 
                                                   onchange="@this.call('updateCatatan', {{ $item['id'] }}, this.value)" 
                                                   class="w-full bg-surface-container-low border border-white/10 rounded-xl py-1 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/20" 
                                                   placeholder="Tambahkan catatan audit...">
                                        @else
                                            <span class="text-on-surface-variant/80">{{ $item['catatan'] ?: '-' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Audio Synthesizer Script for barcode beep -->
    <script>
        function playSynthTone(frequency, type, duration) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (!audioCtx) return;
                
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = type;
                oscillator.frequency.value = frequency;
                gainNode.gain.setValueAtTime(0.12, audioCtx.currentTime); // volume

                // Fade out smoothly to avoid clicks
                gainNode.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + duration);
            } catch (e) {
                console.error("Audio Context error:", e);
            }
        }
    </script>
</div>
