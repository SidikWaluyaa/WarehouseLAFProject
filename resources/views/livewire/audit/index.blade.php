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

                <div class="flex flex-wrap items-center gap-2.5">
                    <button wire:click="backToList" class="px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all">
                        Kembali Ke Daftar
                    </button>

                    @if($selectedSession->status === 'DRAFT')
                        <button wire:click="exportExcel" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all" title="Ekspor Lembar Kerja Excel untuk Perhitungan Fisik">
                            <span class="material-symbols-outlined text-primary text-sm">download</span>
                            Ekspor Excel
                        </button>
                        <button wire:click="openImportModal" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all" title="Impor Hasil Perhitungan Fisik dari Excel">
                            <span class="material-symbols-outlined text-primary text-sm">upload</span>
                            Impor Excel
                        </button>
                        <button wire:click="openDiscrepancyModal" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all" title="Lihat Rangkuman Selisih Saat Ini">
                            <span class="material-symbols-outlined text-primary text-sm">analytics</span>
                            Rangkuman
                        </button>
                        @can('input masuk')
                        <button wire:click="requestCancelSession" class="flex items-center gap-1 px-4 py-2.5 bg-red-500/10 border border-red-500/20 text-error hover:bg-red-500/20 rounded-2xl text-xs font-bold transition-all" title="Batalkan seluruh sesi audit ini">
                            <span class="material-symbols-outlined text-sm">cancel</span>
                            Batalkan
                        </button>
                        <button wire:click="completeSession" class="px-5 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl transition-all hover:shadow-primary/20 active:scale-95 duration-200">
                            Selesaikan Pencatatan
                        </button>
                        @endcan
                    @endif

                    @if($selectedSession->status === 'COMPLETED')
                        <button wire:click="openDiscrepancyModal" class="flex items-center gap-1.5 px-4 py-2.5 bg-tertiary/10 border border-tertiary/20 text-tertiary hover:bg-tertiary/20 rounded-2xl text-xs font-bold transition-all" title="Tinjau Selisih">
                            <span class="material-symbols-outlined text-sm">analytics</span>
                            Review Selisih
                        </button>
                        <a href="{{ route('audit.print', $selectedSession->id) }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all" title="Cetak Berita Acara PDF">
                            <span class="material-symbols-outlined text-primary text-sm">print</span>
                            Cetak PDF
                        </a>
                        @can('input masuk')
                        <button wire:click="reopenSession" class="flex items-center gap-1.5 px-4 py-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 rounded-2xl text-xs font-bold transition-all" title="Kembalikan status sesi ke DRAFT untuk merevisi atau menghitung ulang">
                            <span class="material-symbols-outlined text-sm">undo</span>
                            Kembalikan ke Draft
                        </button>
                        <button wire:click="requestCancelSession" class="px-4 py-2.5 bg-white/5 border border-red-500/20 text-error hover:bg-red-500/10 rounded-2xl text-xs font-bold transition-all" title="Batalkan seluruh sesi audit ini">
                            Batalkan Sesi
                        </button>
                        @endcan
                        @can('manage master')
                        <button wire:click="requestExecuteAdjustment"
                                wire:loading.attr="disabled"
                                wire:target="requestExecuteAdjustment, executeAdjustment"
                                class="px-5 py-2.5 bg-green-600 text-white rounded-2xl font-bold text-xs shadow-xl transition-all hover:bg-green-700 active:scale-95 duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            Sinkronisasi & Terapkan Stok
                        </button>
                        @endcan
                    @endif

                    @if($selectedSession->status === 'ADJUSTED')
                        <button wire:click="openDiscrepancyModal" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all">
                            <span class="material-symbols-outlined text-sm">history</span>
                            Rangkuman Hasil
                        </button>
                        <a href="{{ route('audit.print', $selectedSession->id) }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all" title="Cetak Berita Acara PDF">
                            <span class="material-symbols-outlined text-primary text-sm">print</span>
                            Cetak Berita Acara PDF
                        </a>
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

    <!-- ================= MODAL IMPOR EXCEL ================= -->
    @if ($isImportOpen)
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="glass-card bg-surface-container-high border border-white/10 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-scale-up">
                <div class="flex items-center justify-between pb-3 border-b border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">upload_file</span>
                        <h3 class="text-base font-bold text-on-surface">Impor Lembar Kerja Audit</h3>
                    </div>
                    <button wire:click="closeImportModal" class="p-1 rounded-full text-on-surface-variant/60 hover:text-on-surface hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="space-y-3 text-xs text-on-surface-variant">
                    <p>Unggah file Excel lembar kerja yang telah diisi kolom <strong>Hasil Fisik</strong> untuk memperbarui data opname secara otomatis.</p>
                    <div class="bg-primary/5 border border-primary/20 rounded-2xl p-3 flex items-start gap-2.5 text-[11px] text-primary">
                        <span class="material-symbols-outlined text-base mt-0.5">info</span>
                        <span>Sistem akan mencocokkan baris berdasarkan <strong>Kode SKU</strong>. Pastikan Anda tidak mengubah atau menghapus kode SKU pada file.</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Pilih File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="w-full text-xs text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary file:text-on-primary hover:file:bg-primary/90 file:cursor-pointer bg-surface-container-low border border-white/10 rounded-2xl p-2 cursor-pointer transition-all">
                    <div wire:loading wire:target="importFile" class="text-xs text-primary font-bold mt-2 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                        Sedang mengunggah file...
                    </div>
                    @error('importFile') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                </div>

                @if(!empty($importErrors))
                    <div class="glass-card rounded-2xl p-3.5 border border-error/20 bg-error/5 text-xs text-error space-y-1 max-h-36 overflow-y-auto custom-scrollbar">
                        <p class="font-bold flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">error</span>
                            Kesalahan pada file:
                        </p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                            @foreach($importErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-white/5">
                    <button wire:click="closeImportModal" type="button" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-bold hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="importExcel" wire:loading.attr="disabled" type="button" class="flex items-center gap-1.5 px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl hover:shadow-primary/20 transition-all active:scale-95 disabled:opacity-50">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                        Unggah & Pratinjau
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ================= MODAL PRATINJAU HASIL IMPOR (STAGING PREVIEW) ================= -->
    @if ($isImportPreviewOpen && $selectedSession)
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="glass-card bg-surface-container-high border border-white/10 rounded-3xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden animate-scale-up">
                
                <!-- Modal Header -->
                <div class="p-6 border-b border-white/5 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">table_view</span>
                            <h3 class="text-lg font-bold text-on-surface">Pratinjau Hasil Impor Excel</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                Staging / Belum Disimpan
                            </span>
                        </div>
                        <p class="text-xs text-on-surface-variant/70 mt-1">
                            Sesi: <span class="font-mono text-primary font-bold">{{ $selectedSession->nomor_opname }}</span> &bull; 
                            Tinjau perubahan kuantitas fisik sebelum data diterapkan ke database.
                        </p>
                    </div>
                    <button wire:click="closeImportPreview" class="p-1.5 rounded-full text-on-surface-variant/60 hover:text-on-surface hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
                    
                    <!-- KPI Cards Summary -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="glass-card rounded-2xl p-3.5 border border-white/5 bg-white/5 text-center">
                            <p class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-wider">Total Baris Dibaca</p>
                            <h4 class="text-base font-bold font-mono text-on-surface mt-0.5">{{ number_format($importPreviewSummary['total_rows'] ?? 0) }}</h4>
                            <p class="text-[9px] text-on-surface-variant/40 mt-0.5">Dari file Excel</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-amber-500/20 bg-amber-500/5 text-center">
                            <p class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Perubahan Qty Fisik</p>
                            <h4 class="text-base font-bold font-mono text-amber-400 mt-0.5">{{ number_format($importPreviewSummary['changed_count'] ?? 0) }}</h4>
                            <p class="text-[9px] text-amber-400/60 mt-0.5">Nilai fisik baru</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-green-500/20 bg-green-500/5 text-center">
                            <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider">Sama / Tidak Berubah</p>
                            <h4 class="text-base font-bold font-mono text-green-400 mt-0.5">{{ number_format($importPreviewSummary['unchanged_count'] ?? 0) }}</h4>
                            <p class="text-[9px] text-green-400/60 mt-0.5">Fisik tetap sama</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border {{ !empty($importPreviewSummary['unmatched_skus']) ? 'border-red-500/20 bg-red-500/5' : 'border-white/5 bg-white/5' }} text-center">
                            <p class="text-[10px] font-bold {{ !empty($importPreviewSummary['unmatched_skus']) ? 'text-error' : 'text-on-surface-variant/60' }} uppercase tracking-wider">SKU Dilewati</p>
                            <h4 class="text-base font-bold font-mono {{ !empty($importPreviewSummary['unmatched_skus']) ? 'text-error' : 'text-on-surface' }} mt-0.5">{{ count($importPreviewSummary['unmatched_skus'] ?? []) }}</h4>
                            <p class="text-[9px] text-on-surface-variant/40 mt-0.5">Bukan bagian sesi ini</p>
                        </div>
                    </div>

                    @if(!empty($importPreviewSummary['unmatched_skus']))
                        <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-2xl text-xs text-error flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm mt-0.5">warning</span>
                            <div>
                                <span class="font-bold">Perhatian:</span> Beberapa SKU di Excel tidak ditemukan pada sesi audit ini dan akan dilewati:
                                <span class="font-mono font-bold">{{ implode(', ', $importPreviewSummary['unmatched_skus']) }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Comparison Table -->
                    <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase bg-surface-container-low">
                                    <th class="p-3 text-center">No</th>
                                    <th class="p-3">Produk</th>
                                    <th class="p-3 text-center">Kode SKU</th>
                                    <th class="p-3 text-center">Stok Sistem</th>
                                    <th class="p-3 text-center">Kuantitas Fisik</th>
                                    <th class="p-3 text-center">Selisih Baru</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse ($importPreviewList as $idx => $item)
                                    @php
                                        $diff = (int)$item['selisih_baru'];
                                        $isChanged = $item['is_changed'];
                                    @endphp
                                    <tr class="hover:bg-white/5 transition-colors {{ $isChanged ? 'bg-amber-500/5' : '' }}">
                                        <td class="p-3 text-center text-on-surface-variant/50 font-mono">{{ $idx + 1 }}</td>
                                        <td class="p-3">
                                            <span class="font-bold text-on-surface">{{ $item['nama_barang'] }}</span>
                                        </td>
                                        <td class="p-3 text-center font-mono text-primary/80 font-bold">{{ $item['sku'] }}</td>
                                        <td class="p-3 text-center font-mono text-on-surface-variant">{{ number_format($item['qty_sistem']) }} {{ $item['satuan'] }}</td>
                                        <td class="p-3 text-center font-mono">
                                            @if($isChanged)
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="text-on-surface-variant/50 line-through text-[11px]">{{ $item['qty_fisik_lama'] }}</span>
                                                    <span class="material-symbols-outlined text-[12px] text-amber-400">arrow_forward</span>
                                                    <span class="font-bold text-amber-400 text-xs">{{ $item['qty_fisik_baru'] }} {{ $item['satuan'] }}</span>
                                                </div>
                                            @else
                                                <span class="text-on-surface font-semibold">{{ $item['qty_fisik_baru'] }} {{ $item['satuan'] }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            @if ($diff == 0)
                                                <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/60 rounded-full font-bold font-mono">0</span>
                                            @elseif ($diff > 0)
                                                <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full font-bold font-mono">+{{ $diff }}</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-red-500/10 text-error border border-red-500/20 rounded-full font-bold font-mono">{{ $diff }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            @if($isChanged)
                                                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full font-bold text-[10px]">Update</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/40 rounded-full text-[10px]">Sama</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-on-surface-variant/70">{{ $item['catatan'] ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-6 text-center text-on-surface-variant/60 italic">
                                            Tidak ada data pratinjau yang terbaca.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 bg-surface-container-low">
                    <button wire:click="closeImportPreview" type="button" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-bold hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>

                    <div class="flex items-center gap-2.5">
                        <button wire:click="applyImportPreview" wire:loading.attr="disabled" type="button" class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl hover:shadow-primary/20 transition-all active:scale-95 disabled:opacity-50">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Terapkan Hasil Import ke Sesi Audit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ================= MODAL REVIEW HASIL SELISIH (DISCREPANCY) ================= -->
    @if ($isDiscrepancyModalOpen && $selectedSession)
        @php
            $stats = $this->discrepancyStats;
            $displayedDetails = collect($detailsList);
            if ($discrepancyFilter === 'diff') {
                $displayedDetails = $displayedDetails->filter(fn($i) => (int)$i['selisih'] !== 0);
            }
        @endphp
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="glass-card bg-surface-container-high border border-white/10 rounded-3xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden animate-scale-up">
                
                <!-- Modal Header -->
                <div class="p-6 border-b border-white/5 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">analytics</span>
                            <h3 class="text-lg font-bold text-on-surface">Rangkuman Hasil Audit & Selisih</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $selectedSession->status === 'COMPLETED' ? 'bg-tertiary/10 text-tertiary border border-tertiary/20' : ($selectedSession->status === 'ADJUSTED' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-primary/10 text-primary border border-primary/20') }}">
                                {{ $selectedSession->status }}
                            </span>
                        </div>
                        <p class="text-xs text-on-surface-variant/70 mt-1">
                            Sesi: <span class="font-mono text-primary font-bold">{{ $selectedSession->nomor_opname }}</span> &bull; 
                            Lokasi: <span class="font-bold text-on-surface">{{ $selectedSession->lokasi->kode_lokasi }} ({{ $selectedSession->lokasi->nama_lokasi }})</span>
                        </p>
                    </div>
                    <button wire:click="closeDiscrepancyModal" class="p-1.5 rounded-full text-on-surface-variant/60 hover:text-on-surface hover:bg-white/10 transition-all">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
                    
                    <!-- KPI Cards Summary -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="glass-card rounded-2xl p-3.5 border border-white/5 bg-white/5 text-center">
                            <p class="text-[10px] font-bold text-on-surface-variant/60 uppercase tracking-wider">Total SKU</p>
                            <h4 class="text-base font-bold font-mono text-on-surface mt-0.5">{{ number_format($stats['totalItems']) }}</h4>
                            <p class="text-[9px] text-on-surface-variant/40 mt-0.5">Item diaudit</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-green-500/20 bg-green-500/5 text-center">
                            <p class="text-[10px] font-bold text-green-400 uppercase tracking-wider">Cocok / Sesuai</p>
                            <h4 class="text-base font-bold font-mono text-green-400 mt-0.5">{{ number_format($stats['matched']) }}</h4>
                            <p class="text-[9px] text-green-400/60 mt-0.5">Selisih = 0</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-red-500/20 bg-red-500/5 text-center">
                            <p class="text-[10px] font-bold text-error uppercase tracking-wider">Selisih Kurang (-)</p>
                            <h4 class="text-base font-bold font-mono text-error mt-0.5">{{ number_format($stats['deficit']) }}</h4>
                            <p class="text-[9px] text-error/60 mt-0.5">Fisik &lt; Sistem</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-amber-500/20 bg-amber-500/5 text-center">
                            <p class="text-[10px] font-bold text-primary uppercase tracking-wider">Selisih Lebih (+)</p>
                            <h4 class="text-base font-bold font-mono text-primary mt-0.5">{{ number_format($stats['surplus']) }}</h4>
                            <p class="text-[9px] text-primary/60 mt-0.5">Fisik &gt; Sistem</p>
                        </div>
                        <div class="glass-card rounded-2xl p-3.5 border border-white/10 bg-white/5 text-center col-span-2 sm:col-span-1">
                            <p class="text-[10px] font-bold text-on-surface-variant/70 uppercase tracking-wider">Net Estimasi Nilai</p>
                            <h4 class="text-xs font-bold font-mono text-on-surface mt-1 truncate {{ $stats['netNominal'] < 0 ? 'text-error' : ($stats['netNominal'] > 0 ? 'text-green-400' : '') }}">
                                {{ ($stats['netNominal'] >= 0 ? '+' : '') . 'Rp' . number_format($stats['netNominal'], 0, ',', '.') }}
                            </h4>
                            <p class="text-[9px] text-on-surface-variant/40 mt-0.5">Berdasarkan HPP</p>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex items-center justify-between gap-3 border-b border-white/5 pb-2">
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('discrepancyFilter', 'diff')" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $discrepancyFilter === 'diff' ? 'bg-primary text-on-primary shadow-md' : 'bg-white/5 text-on-surface-variant hover:bg-white/10' }}">
                                Hanya Yang Berselisih ({{ $stats['deficit'] + $stats['surplus'] }})
                            </button>
                            <button wire:click="$set('discrepancyFilter', 'all')" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $discrepancyFilter === 'all' ? 'bg-primary text-on-primary shadow-md' : 'bg-white/5 text-on-surface-variant hover:bg-white/10' }}">
                                Semua Barang ({{ $stats['totalItems'] }})
                            </button>
                        </div>
                        <span class="text-[11px] text-on-surface-variant/60">Menampilkan {{ $displayedDetails->count() }} item</span>
                    </div>

                    <!-- Discrepancy Table -->
                    <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase bg-surface-container-low">
                                    <th class="p-3">Produk</th>
                                    <th class="p-3 text-center">Kode SKU</th>
                                    <th class="p-3 text-center">Sistem</th>
                                    <th class="p-3 text-center">Fisik</th>
                                    <th class="p-3 text-center">Selisih</th>
                                    <th class="p-3 text-right">HPP</th>
                                    <th class="p-3 text-right">Nilai Selisih</th>
                                    <th class="p-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse ($displayedDetails as $item)
                                    @php
                                        $diff = (int)$item['selisih'];
                                        $hpp = isset($item['hpp']) ? (float)$item['hpp'] : 0;
                                        $valDiff = $diff * $hpp;
                                    @endphp
                                    <tr class="hover:bg-white/5 transition-colors {{ $diff < 0 ? 'bg-red-500/5' : ($diff > 0 ? 'bg-green-500/5' : '') }}">
                                        <td class="p-3">
                                            <span class="font-bold text-on-surface">{{ $item['nama_barang'] }}</span>
                                            @if(!empty($item['variasi_barang']))
                                                <span class="block text-[10px] text-on-surface-variant/50">Var: {{ $item['variasi_barang'] }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center font-mono text-primary/80 font-bold">{{ $item['kode_barang'] }}</td>
                                        <td class="p-3 text-center font-mono">{{ number_format($item['qty_sistem']) }} {{ $item['satuan'] }}</td>
                                        <td class="p-3 text-center font-mono font-bold">{{ number_format($item['qty_fisik']) }} {{ $item['satuan'] }}</td>
                                        <td class="p-3 text-center">
                                            @if ($diff == 0)
                                                <span class="px-2.5 py-0.5 bg-white/5 text-on-surface-variant/60 rounded-full font-bold">0</span>
                                            @elseif ($diff > 0)
                                                <span class="px-2.5 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full font-bold font-mono">+{{ $diff }}</span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-red-500/10 text-error border border-red-500/20 rounded-full font-bold font-mono">{{ $diff }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right font-mono text-on-surface-variant">Rp{{ number_format($hpp, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold {{ $valDiff < 0 ? 'text-error' : ($valDiff > 0 ? 'text-green-400' : 'text-on-surface-variant') }}">
                                            {{ ($valDiff >= 0 && $diff > 0 ? '+' : '') . 'Rp' . number_format($valDiff, 0, ',', '.') }}
                                        </td>
                                        <td class="p-3 text-on-surface-variant/70">{{ $item['catatan'] ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-6 text-center text-on-surface-variant/60 italic">
                                            Tidak ada barang yang berselisih. Semua perhitungan fisik sesuai dengan data sistem!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-6 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 bg-surface-container-low">
                    <a href="{{ route('audit.print', $selectedSession->id) }}" target="_blank" class="flex items-center gap-2 px-5 py-2.5 bg-white/5 border border-white/10 hover:bg-white/10 rounded-2xl text-xs font-bold text-on-surface transition-all">
                        <span class="material-symbols-outlined text-primary text-base">print</span>
                        Cetak Berita Acara (PDF)
                    </a>

                    <div class="flex items-center gap-2.5">
                        <button wire:click="closeDiscrepancyModal" type="button" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-bold hover:bg-white/10 transition-all text-on-surface">
                            Tutup
                        </button>
                        
                        @if($selectedSession->status === 'COMPLETED')
                            @can('input masuk')
                            <button wire:click="reopenSession" type="button" class="flex items-center gap-1.5 px-5 py-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 rounded-2xl text-xs font-bold transition-all" title="Kembalikan status sesi ke DRAFT untuk merevisi atau menghitung ulang">
                                <span class="material-symbols-outlined text-base">undo</span>
                                Kembalikan ke Draft
                            </button>
                            @endcan
                            @can('manage master')
                            <button wire:click="requestExecuteAdjustment"
                                    wire:loading.attr="disabled"
                                    wire:target="requestExecuteAdjustment"
                                    type="button"
                                    class="flex items-center gap-1.5 px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-bold text-xs shadow-xl transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-base">sync</span>
                                Terapkan Penyesuaian Stok
                            </button>
                            @endcan
                        @endif
                    </div>
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
