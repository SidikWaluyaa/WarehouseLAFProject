<div x-init="setTimeout(() => { $dispatch('focus-barcode') }, 200); $dispatch('update-header-title', 'Edit Barang Masuk')"
     @click="
         if ($event.target.closest('a, button, input, textarea, select, option')) return;
         setTimeout(() => {
             const barcodeInput = document.getElementById('barcode-scan-input');
             if (!barcodeInput) return;
             const activeEl = document.activeElement;
             if (activeEl && activeEl !== barcodeInput) {
                 const isFormInput = ['INPUT', 'TEXTAREA', 'SELECT', 'A', 'BUTTON', 'OPTION'].includes(activeEl.tagName) || 
                                     activeEl.isContentEditable || 
                                     activeEl.closest('a, button, input, textarea, select, option');
                 if (!isFormInput) {
                     barcodeInput.focus();
                 }
             }
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

    <!-- Error Banner -->
    

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Header Information -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <h3 class="text-sm font-black text-primary uppercase tracking-wider mb-4 pb-2 border-b border-white/5 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-primary">description</span>
                    Informasi Nota
                </h3>

                <div class="space-y-4">
                    <!-- Tanggal -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tanggal Transaksi</label>
                        <input wire:model.live="tanggal" type="date" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        @error('tanggal') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipe Nota -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tipe Nota</label>
                        <select wire:model.live="tipe_nota" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="Pembelian">Pembelian (PO/Restock)</option>
                            <option value="Stock Awal">Stok Awal</option>
                            <option value="Retur Masuk">Retur Masuk</option>
                            <option value="Penyesuaian">Penyesuaian (Opname)</option>
                        </select>
                        @error('tipe_nota') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Supplier -->
                    @if($tipe_nota !== 'Stock Awal')
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Supplier / Pengirim</label>
                        <select wire:model.live="supplier_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Pilih Supplier</option>
                            @foreach ($suppliersList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Nomor Nota -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nomor Nota</label>
                        <input wire:model="nomor_nota" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: INV/2026/012">
                        @error('nomor_nota') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Keterangan Tambahan</label>
                        <textarea wire:model="keterangan_tambahan" rows="3" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Keterangan opsional..."></textarea>
                        @error('keterangan_tambahan') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Back & Submit buttons for Desktop -->
            <div class="hidden lg:flex items-center space-x-3">
                <a href="{{ route('barang-masuk') }}" class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface text-center">
                    Batal / Kembali
                </a>
                <button type="button" wire:click="update" class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                    Perbarui Transaksi
                </button>
            </div>
        </div>

        <!-- Right Column: Barcode Scanning & Items Table -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Barcode Scanner Input -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <h3 class="text-sm font-black text-primary uppercase tracking-wider mb-4 pb-2 border-b border-white/5 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-primary animate-pulse">barcode_scanner</span>
                    Pindai Barcode Produk
                </h3>

                <div class="flex gap-3">
                    <button type="button" onclick="startCameraScanner()" class="px-4 py-3 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface flex items-center justify-center gap-1.5 shadow-sm" title="Pindai dengan Kamera">
                        <span class="material-symbols-outlined text-sm">photo_camera</span>
                        Kamera
                    </button>

                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                        <input wire:model="barcodeScan" x-on:keydown.enter.prevent="$wire.addBarcodeItem($el.value); $el.value = '';" type="text" id="barcode-scan-input" placeholder="Arahkan barcode scanner kemari..." class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold uppercase tracking-wider placeholder:text-on-surface-variant/30">
                    </div>
                    <button type="button" wire:click="addBarcodeItem" class="px-6 py-3 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                        Tambah
                    </button>
                </div>
                @error('barcodeScan') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
            </div>

            <!-- Items Table -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                    <h3 class="text-sm font-black text-primary uppercase tracking-wider flex items-center">
                        <span class="material-symbols-outlined mr-2 text-primary">list_alt</span>
                        Daftar Produk Masuk
                    </h3>
                    <button type="button" wire:click="addItem" class="inline-flex items-center px-4 py-2 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                        <span class="material-symbols-outlined text-sm mr-1">add</span>
                        Tambah Baris
                    </button>
                </div>

                @error('items') <div class="mb-4 text-sm text-error font-bold">{{ $message }}</div> @enderror

                <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center w-20">QTY</th>
                                <th class="px-4 py-3 text-right w-44">Harga Beli (Pcs)</th>
                                <th class="px-4 py-3 text-center w-40">Lokasi Simpan</th>
                                <th class="px-4 py-3 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($items as $index => $item)
                                <tr wire:key="item-row-{{ $index }}" @class([
                                    'hover:bg-white/5 transition-all duration-300',
                                    'animate-scan-flash bg-primary/5 font-semibold' => !empty($item['produk_id']) && $item['produk_id'] == $justScannedProductId
                                ])>
                                    <td class="p-3">
                                        <select wire:model.live="items.{{ $index }}.produk_id" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                            <option value="">-- Pilih Produk --</option>
                                            @foreach ($productsList as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama_barang }} ({{ $p->kode_barang }})</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.produk_id") <span class="text-[10px] text-error block mt-1 font-medium">{{ $message }}</span> @enderror

                                        <!-- PO details link for product -->
                                        @if (!empty($item['produk_id']) && $tipe_nota !== 'Stock Awal')
                                            @php
                                                $poList = $this->getPoDetailsForProduct((int)$item['produk_id']);
                                            @endphp
                                            @if (count($poList) > 0)
                                                <div class="mt-2 p-2 bg-green-500/10 border border-green-500/20 rounded-xl">
                                                    <label class="block text-[9px] font-black text-green-400 uppercase mb-1">Hubungkan ke Purchase Order (PO):</label>
                                                    <select wire:model="items.{{ $index }}.po_detail_id" class="w-full bg-surface-container-low border border-green-500/20 rounded text-[10px] text-green-400 focus:border-green-500 focus:ring-green-500 py-0.5">
                                                        <option value="">-- Jangan hubungkan ke PO --</option>
                                                        @foreach ($poList as $poD)
                                                            <option value="{{ $poD['id'] }}">
                                                                PO Tgl {{ date('d/m/Y', strtotime($poD['po_tanggal'])) }} | Sisa: {{ $poD['sisa'] }} unit
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.qty_masuk" min="1" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 px-3 text-xs text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-bold">
                                        @error("items.{$index}.qty_masuk") <span class="text-[10px] text-error block mt-1 font-medium">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="p-3">
                                        <div class="relative rounded-xl shadow-sm">
                                            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-on-surface-variant/50 text-[10px] font-bold">Rp</span>
                                            <input type="number" wire:model="items.{{ $index }}.hpp" class="pl-8 w-full bg-surface-container-low border border-white/10 rounded-xl py-2 pr-3 text-xs text-right text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold" placeholder="Harga">
                                        </div>
                                        @error("items.{$index}.hpp") <span class="text-[10px] text-error block mt-1 font-medium">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="p-3">
                                        <select wire:model.live="items.{{ $index }}.lokasi_simpan" class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all truncate pr-8">
                                            @foreach ($locationsList as $loc)
                                                <option value="{{ $loc->kode_lokasi }}">{{ $loc->kode_lokasi }} ({{ $loc->tipe_lokasi }})</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.lokasi_simpan") <span class="text-[10px] text-error block mt-1 font-medium">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" title="Hapus Baris">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back & Submit buttons for Mobile -->
            <div class="flex lg:hidden items-center space-x-3 mt-4">
                <a href="{{ route('barang-masuk') }}" class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface text-center">
                    Batal / Kembali
                </a>
                <button type="button" wire:click="update" class="flex-1 inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                    Perbarui Transaksi
                </button>
            </div>
        </div>
    </div>

    <!-- Camera Scanner Modal -->
    <div id="camera-scanner-modal" class="fixed inset-0 z-50 items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none hidden px-4">
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

                    <p id="camera-instruction" class="text-[10px] text-on-surface-variant/60 text-center font-medium">Posisikan barcode Code 128 produk tepat di tengah garis laser merah untuk memindai.</p>

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

        function startCameraScanner() {
            const modal = document.getElementById('camera-scanner-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            switchScannerTab('camera');
            setupFileInputHandlers();
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
                
                stopCameraScannerOnly();
            }
        }

        function initializeCameraScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(initializeCameraScanner, 100);
                return;
            }

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
                        stopCameraScannerOnly().then(() => {
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
        }

        function startScanningWithCamera(cameraId) {
            html5QrcodeScanner = new Html5Qrcode("camera-reader");
            html5QrcodeScanner.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: function(width, height) {
                        return {
                            width: Math.max(Math.min(width * 0.85, 300), 50),
                            height: Math.max(Math.min(height * 0.35, 100), 50)
                        };
                    },
                    formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]
                },
                (decodedText) => {
                    @this.set('barcodeScan', decodedText);
                    @this.call('addBarcodeItem');
                    
                    window.dispatchEvent(new CustomEvent('play-beep'));
                    
                    const viewport = document.getElementById('camera-reader');
                    viewport.classList.add('ring-4', 'ring-green-500');
                    setTimeout(() => viewport.classList.remove('ring-4', 'ring-green-500'), 500);
                },
                (errorMessage) => {}
            ).catch(err => {
                console.error("Gagal menjalankan scanner:", err);
            });
        }

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
                        @this.set('barcodeScan', extractedSku);
                        @this.call('addBarcodeItem');
                        
                        window.dispatchEvent(new CustomEvent('play-beep'));
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
                const html5Qrcode = new Html5Qrcode("camera-reader", {
                    formatsToSupport: [ Html5QrcodeSupportedFormats.CODE_128 ]
                });
                
                html5Qrcode.scanFile(fileToScan, false)
                    .then(decodedText => {
                        @this.set('barcodeScan', decodedText);
                        @this.call('addBarcodeItem');
                        
                        window.dispatchEvent(new CustomEvent('play-beep'));
                        alert('Berhasil memindai barcode: ' + decodedText);
                    })
                    .catch(err => {
                        console.error("Gagal mendeteksi barcode dari file:", err);
                        errorEl.classList.remove('hidden');
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
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                return html5QrcodeScanner.stop();
            }
            return Promise.resolve();
        }

        function closeCameraScanner() {
            stopCameraScannerOnly().then(() => {
                const modal = document.getElementById('camera-scanner-modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                if (html5QrcodeScanner) {
                    html5QrcodeScanner = null;
                }
            }).catch(err => {
                console.error("Gagal menghentikan kamera:", err);
                const modal = document.getElementById('camera-scanner-modal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            });
        }

        window.addEventListener('beforeunload', () => {
            if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
                stopCameraScannerOnly();
            }
        });
    </script>
</div>
