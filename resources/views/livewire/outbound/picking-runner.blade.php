<div x-init="$dispatch('update-header-title', 'Eksekusi Picking')"
     x-on:focus-product.window="$nextTick(() => { const el = document.getElementById('scan-product-input'); if (el) { el.focus(); el.select(); } })"
     class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar pb-16">
    <!-- Top Action / Breadcrumbs Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('picking') }}" wire:navigate class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-on-surface hover:bg-white/10 hover:border-primary/40 hover:text-primary transition-all shadow-lg shadow-black/20" title="Kembali ke Daftar Picking">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-on-surface font-mono tracking-tight">{{ $picking->nomor_picking }}</h1>
                    @if ($picking->status === 'COMPLETED')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border bg-green-500/10 text-green-400 border-green-500/20">COMPLETED</span>
                    @elseif ($picking->status === 'PICKING')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border bg-primary/10 text-primary border-primary/20 animate-pulse">PICKING IN PROGRESS</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border bg-amber-500/10 text-amber-400 border-amber-500/20">PENDING</span>
                    @endif
                </div>
                <p class="text-xs text-on-surface-variant/70 mt-0.5 flex items-center gap-2">
                    <span>Gudang: <strong class="text-on-surface">{{ $picking->lokasi->nama_lokasi ?? 'Semua Gudang' }}</strong></span>
                    <span class="text-white/10">|</span>
                    <span>Tipe: <strong class="text-primary">{{ $picking->referensi_tipe === 'BarangKeluar' ? 'Penjualan / Keluar' : 'Transfer Antar Gudang' }}</strong></span>
                </p>
            </div>
        </div>

        <!-- Quick Status Card -->
        <div class="flex items-center gap-3">
            <div class="glass-card px-4 py-2.5 rounded-2xl border border-white/10 flex items-center gap-3 bg-surface-dark/80">
                <div class="w-8 h-8 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-base">person</span>
                </div>
                <div>
                    <span class="text-[9px] uppercase font-bold text-on-surface-variant/60 block">Petugas PIC</span>
                    <span class="text-xs font-bold text-on-surface">{{ $picking->picker_name ?: 'Belum Ditugaskan' }}</span>
                </div>
            </div>

            @if ($picking->started_at && $picking->completed_at)
                <div class="glass-card px-4 py-2.5 rounded-2xl border border-white/10 flex items-center gap-3 bg-surface-dark/80">
                    <div class="w-8 h-8 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400">
                        <span class="material-symbols-outlined text-base">timer</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-on-surface-variant/60 block">Durasi Selesai</span>
                        <span class="text-xs font-bold text-green-400">{{ $picking->started_at->diffForHumans($picking->completed_at, true) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert / Toast Messages -->
    @if ($errorMessage)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-center justify-between animate-in fade-in">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-lg text-rose-400">error</span>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="clearAlerts" class="text-rose-400 hover:text-rose-200">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <!-- Progress Card -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/80">Progress Pengambilan Barang</h3>
                <p class="text-sm font-black text-on-surface mt-0.5 font-mono">
                    <span class="text-primary">{{ $totalQtyPicked }}</span> / {{ $totalQtyToPick }} Unit Terambil
                    <span class="text-xs font-normal text-on-surface-variant/70 ml-2">({{ $verifiedItems }} dari {{ $totalItems }} Rak Selesai)</span>
                </p>
            </div>
            <div class="text-right">
                <span class="text-2xl font-black font-mono {{ $progressPct === 100 ? 'text-green-400' : 'text-primary' }}">{{ $progressPct }}%</span>
            </div>
        </div>
        <div class="w-full h-3 rounded-full bg-white/5 border border-white/10 overflow-hidden p-0.5">
            <div class="h-full rounded-full transition-all duration-500 {{ $progressPct === 100 ? 'bg-gradient-to-r from-green-500 to-emerald-400 shadow-[0_0_12px_rgba(34,197,94,0.5)]' : 'bg-gradient-to-r from-primary to-amber-400 shadow-[0_0_12px_rgba(245,158,11,0.5)]' }}" style="width: {{ $progressPct }}%"></div>
        </div>
    </div>

    @if ($picking->status !== 'COMPLETED')
        <!-- Focused Barcode Scanner Card -->
        <div class="glass-card rounded-3xl p-6 border border-primary/30 bg-primary/[0.03] shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">qr_code_scanner</span>
                    Pindai Barcode / SKU Produk (Fokus Scanner Aktif)
                </h4>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="startCameraScanner('product')" class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl text-xs font-semibold text-on-surface hover:bg-white/10 transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        <span>Buka Kamera</span>
                    </button>
                </div>
            </div>

            <form wire:submit.prevent="scanBarcode" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Scan Barcode / SKU Produk</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 material-symbols-outlined text-sm text-primary">barcode_scanner</span>
                        <input wire:model="scanProduct" id="scan-product-input" type="text" placeholder="Arahkan scanner ke barcode produk..." autofocus
                            class="w-full bg-surface-container-low border border-white/15 rounded-2xl py-3 pl-10 pr-4 text-xs text-on-surface font-mono placeholder:text-on-surface-variant/40 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all shadow-inner" />
                    </div>
                </div>

                <div class="w-full sm:w-28">
                    <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase mb-1.5">Qty Ambil</label>
                    <input wire:model="scanQty" type="number" min="1" class="w-full bg-surface-container-low border border-white/15 rounded-2xl py-3 px-3.5 text-xs text-on-surface font-mono text-center focus:outline-none focus:ring-2 focus:ring-primary" />
                </div>

                <div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary text-on-primary font-bold text-xs rounded-2xl hover:bg-primary/90 transition-all flex items-center justify-center gap-1.5 shadow-lg shadow-primary/25 h-[42px]">
                        <span class="material-symbols-outlined text-sm">check</span>
                        <span>Verifikasi</span>
                    </button>
                </div>
            </form>
            <p class="text-[10px] text-on-surface-variant/60">Gunakan scanner barcode USB/Bluetooth atau ketik SKU produk manual lalu tekan Enter.</p>
        </div>
    @endif

    <!-- FIFO Items Checklist Table -->
    <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <div>
                <h3 class="text-sm font-bold text-on-surface">Daftar Rekomendasi Rak FIFO</h3>
                <p class="text-[11px] text-on-surface-variant/60">Ambil barang sesuai prioritas batch tanggal masuk paling awal (FIFO)</p>
            </div>
            @if ($picking->status === 'PICKING' && $progressPct === 100)
                <button wire:click="completePicking" wire:loading.attr="disabled"
                        class="px-6 py-2.5 bg-gradient-to-r from-primary to-amber-500 text-on-primary font-black text-xs rounded-2xl shadow-xl hover:shadow-primary/30 transition-all flex items-center gap-2 amber-glow-strong">
                    <span wire:loading.remove wire:target="completePicking" class="material-symbols-outlined text-base">task_alt</span>
                    <span wire:loading wire:target="completePicking" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                    Selesaikan Picking & Update Stok
                </button>
            @endif
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase">
                        <th class="pb-3 pl-2">No.</th>
                        <th class="pb-3">Produk</th>
                        <th class="pb-3">Rak Rekomendasi (FIFO)</th>
                        <th class="pb-3 text-center">Permintaan</th>
                        <th class="pb-3 text-center">Telah Diambil</th>
                        <th class="pb-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($picking->details as $index => $d)
                        <tr class="hover:bg-white/5 transition-colors {{ $d->is_verified ? 'bg-green-500/[0.04]' : '' }}">
                            <td class="py-3.5 pl-2 font-mono text-[11px] text-on-surface-variant/60">{{ $index + 1 }}</td>
                            <td class="py-3.5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center font-mono text-[10px] text-primary">
                                    {{ substr($d->produk->kode_barang, 0, 3) }}
                                </div>
                                <div>
                                    <p class="font-bold text-on-surface">{{ $d->produk->nama_barang }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[9px] text-on-surface-variant/70 font-mono">{{ $d->produk->kode_barang }}</span>
                                        <button type="button" wire:click="showBarcodeModal('{{ $d->produk->kode_barang }}', 'sku')" class="text-primary hover:text-primary/80 transition-colors" title="Lihat Barcode SKU">
                                            <span class="material-symbols-outlined text-[13px]">barcode_scanner</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-lg font-mono font-bold text-xs">
                                        {{ $d->lokasiRak->kode_lokasi }}
                                    </span>
                                    <button type="button" wire:click="showBarcodeModal('{{ $d->lokasiRak->kode_lokasi }}', 'location')" class="text-primary hover:text-primary/80 transition-colors" title="Lihat Barcode Rak">
                                        <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 text-center font-bold text-on-surface font-mono">{{ $d->qty_to_pick }}</td>
                            <td class="py-3.5 text-center">
                                <span class="text-sm font-black font-mono {{ $d->qty_picked >= $d->qty_to_pick ? 'text-green-400' : 'text-amber-400' }}">
                                    {{ $d->qty_picked }}
                                </span>
                            </td>
                            <td class="py-3.5 text-center">
                                @if($d->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[10px] font-black uppercase">
                                        <span class="material-symbols-outlined text-xs">check_circle</span> VERIFIED
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-500/10 text-slate-400 border border-slate-500/20 rounded-full text-[10px] font-black uppercase">
                                        <span class="material-symbols-outlined text-xs">schedule</span> BELUM
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($picking->status === 'PICKING' && $progressPct === 100)
            <div class="flex justify-end pt-4 border-t border-white/5">
                <button wire:click="completePicking" wire:loading.attr="disabled"
                        class="px-8 py-3 bg-gradient-to-r from-primary to-amber-500 text-on-primary font-black text-xs rounded-2xl shadow-xl hover:shadow-primary/30 transition-all flex items-center gap-2 amber-glow-strong">
                    <span wire:loading.remove wire:target="completePicking" class="material-symbols-outlined text-base">task_alt</span>
                    <span wire:loading wire:target="completePicking" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                    Selesaikan Picking & Update Stok
                </button>
            </div>
        @endif
    </div>

    <!-- Barcode View / Simulation Modal -->
    @if ($isBarcodeModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeBarcodeModal"></div>
        <div class="relative w-full max-w-sm mx-auto my-6 z-50 animate-in fade-in zoom-in duration-200">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface">{{ $barcodeModalTitle }}</h3>
                    <button wire:click="closeBarcodeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                <div class="p-6 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-inner flex flex-col items-center justify-center max-w-full overflow-hidden">
                        {!! \App\Helpers\Barcode128::getSvg($barcodeModalValue, 80, 2) !!}
                        <span class="mt-3 font-mono text-xs font-bold text-black tracking-widest">{{ $barcodeModalValue }}</span>
                    </div>
                    <p class="text-[10px] text-on-surface-variant/60">Arahkan scanner hardware Anda ke barcode di atas untuk memindai.</p>
                </div>
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50 gap-2">
                    <button wire:click="closeBarcodeModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                    @if ($barcodeModalType === 'sku')
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
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" onclick="closeCameraScanner()"></div>
        <div class="relative w-full max-w-sm mx-auto my-6 z-50">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-primary">photo_camera</span>
                        Pindai Barcode Produk
                    </h3>
                    <button type="button" onclick="closeCameraScanner()" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
                <div class="p-6 flex flex-col items-center justify-center space-y-4">
                    <div class="w-full">
                        <label for="camera-select" class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Pilih Kamera</label>
                        <select id="camera-select" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Mengakses kamera...</option>
                        </select>
                    </div>
                    <div class="w-full overflow-hidden rounded-2xl bg-white/5 border border-white/10 aspect-[4/3] relative flex items-center justify-center">
                        <div id="camera-reader" class="w-full h-full"></div>
                        <div class="absolute inset-x-0 top-1/2 h-0.5 bg-red-500 animate-bounce opacity-70 pointer-events-none shadow-[0_0_8px_rgba(239,68,68,0.8)]"></div>
                    </div>
                    <p class="text-[10px] text-on-surface-variant/60 text-center font-medium">Posisikan barcode Code 128 tepat di tengah garis laser merah.</p>
                </div>
                <div class="flex items-center justify-end px-6 py-4 border-t border-white/5 bg-surface-container-low/50">
                    <button type="button" onclick="closeCameraScanner()" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-medium hover:bg-white/10 transition-all text-on-surface">
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

        function startCameraScanner() {
            const modal = document.getElementById('camera-scanner-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            initializeCameraScanner();
        }

        function cleanupExistingScanner() {
            if (!html5QrcodeScanner) return Promise.resolve();
            let promise = Promise.resolve();
            if (html5QrcodeScanner.isScanning) {
                promise = html5QrcodeScanner.stop();
            }
            return promise.then(() => {
                try { html5QrcodeScanner.clear(); } catch (e) {}
                html5QrcodeScanner = null;
            });
        }

        function closeCameraScanner() {
            cleanupExistingScanner().then(() => {
                const modal = document.getElementById('camera-scanner-modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            });
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
                        startScanningWithCamera(cameraId);
                        select.onchange = () => {
                            cleanupExistingScanner().then(() => startScanningWithCamera(select.value));
                        };
                    } else {
                        alert('Tidak ada kamera yang terdeteksi.');
                        closeCameraScanner();
                    }
                }).catch(err => {
                    alert('Gagal mengakses kamera: ' + err);
                    closeCameraScanner();
                });
            });
        }

        function startScanningWithCamera(cameraId) {
            html5QrcodeScanner = new Html5Qrcode("camera-reader");
            const config = { fps: 10, qrbox: { width: 250, height: 150 } };
            html5QrcodeScanner.start(
                cameraId,
                config,
                (decodedText) => {
                    @this.set('scanProduct', decodedText);
                    closeCameraScanner();
                    @this.scanBarcode();
                },
                (errorMessage) => {}
            ).catch(err => console.error("Start scanning error:", err));
        }

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
