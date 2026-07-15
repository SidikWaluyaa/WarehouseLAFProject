@props([
    'wireModel' => 'barcodeScan',
    'wireAction' => 'addBarcodeItem'
])

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
                <button type="button" class="text-on-surface-variant hover:text-on-surface transition-all" onclick="closeCameraScanner()">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="relative p-6 flex-auto flex flex-col items-center space-y-6">
                <!-- Tab Headers -->
                <div class="flex w-full text-center border-b border-white/5 text-xs font-bold uppercase tracking-wider">
                    <button type="button" id="tab-camera" onclick="switchScannerTab('camera')" class="flex-1 pb-2 border-b-2 border-primary text-primary transition-all">Kamera</button>
                    <button type="button" id="tab-file" onclick="switchScannerTab('file')" class="flex-1 pb-2 text-on-surface-variant/40 hover:text-on-surface-variant border-b-2 border-transparent transition-all">Unggah Gambar</button>
                </div>

                <!-- Camera Selection -->
                <div class="w-full space-y-2">
                    <label class="text-[10px] font-bold text-on-surface-variant/60 tracking-wider uppercase block">Pilih Kamera</label>
                    <select id="camera-select" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Mengakses kamera...</option>
                    </select>
                </div>

                <!-- Camera Viewport Container -->
                <div class="w-full overflow-hidden rounded-2xl bg-black/40 border border-white/5 relative aspect-square flex items-center justify-center">
                    <div id="camera-reader" class="w-full h-full object-cover"></div>
                </div>

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
    let html5QrcodeScanner = null;
    let activeTab = 'camera';

    function startCameraScanner() {
        // Tampilkan modal
        const modal = document.getElementById('camera-scanner-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Reset tab ke default (Kamera)
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
                @this.set('{{ $wireModel }}', decodedText);
                @this.call('{{ $wireAction }}');
                
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

    // File scanning function
    function scanBarcodeFile(file) {
        const errorEl = document.getElementById('file-scan-result-error');
        errorEl.classList.add('hidden');

        if (typeof Html5Qrcode === 'undefined') {
            alert('Library scanner belum siap.');
            return;
        }

        const html5QrCode = new Html5Qrcode("camera-reader");
        html5QrCode.scanFile(file, true)
            .then(decodedText => {
                @this.set('{{ $wireModel }}', decodedText);
                @this.call('{{ $wireAction }}');
                
                window.dispatchEvent(new CustomEvent('play-beep'));
                closeCameraScanner();
            })
            .catch(err => {
                console.error("Gagal scan file:", err);
                errorEl.classList.remove('hidden');
            });
    }

    function setupFileInputHandlers() {
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('barcode-file-input');

        if (!dropZone || !fileInput) return;

        dropZone.onclick = () => fileInput.click();

        fileInput.onchange = (e) => {
            if (e.target.files && e.target.files[0]) {
                scanBarcodeFile(e.target.files[0]);
            }
        };

        dropZone.ondragover = (e) => {
            e.preventDefault();
            dropZone.classList.add('bg-white/15');
        };

        dropZone.ondragleave = () => {
            dropZone.classList.remove('bg-white/15');
        };

        dropZone.ondrop = (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-white/15');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                scanBarcodeFile(e.dataTransfer.files[0]);
            }
        };
    }

    function stopCameraScannerOnly() {
        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            return html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner = null;
            });
        }
        return Promise.resolve();
    }

    function closeCameraScanner() {
        stopCameraScannerOnly().then(() => {
            const modal = document.getElementById('camera-scanner-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        });
    }
</script>
