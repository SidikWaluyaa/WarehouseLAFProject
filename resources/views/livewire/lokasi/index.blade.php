<div x-init="$dispatch('update-header-title', 'Manajemen Lokasi')" class="flex-1 overflow-y-auto p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Kode atau Nama Lokasi..." type="text"/>
        </div>
    </template>

    <!-- Actions Bar -->
    <section class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search for mobile -->
            <div class="md:hidden relative w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Cari Lokasi..."/>
            </div>
        </div>
        @can('manage master')
        <button wire:click="create" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
            <span class="material-symbols-outlined">add</span>
            Tambah Lokasi
        </button>
        @endcan
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5">
                        <th wire:click="sortBy('kode_lokasi')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kode Lokasi
                                @if ($sortField === 'kode_lokasi')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('nama_lokasi')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Nama Lokasi
                                @if ($sortField === 'nama_lokasi')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('tipe_lokasi')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Tipe Lokasi
                                @if ($sortField === 'tipe_lokasi')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Unit Disimpan</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Status</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs">
                    @forelse ($locations as $loc)
                        @php
                            $totalQty = $loc->stokLokasi()->sum('qty');
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 font-mono text-primary/80 text-sm font-bold">{{ $loc->kode_lokasi }}</td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-on-surface">{{ $loc->nama_lokasi }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($loc->tipe_lokasi === 'GUDANG')
                                    <span class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">GUDANG</span>
                                @else
                                    <span class="px-3 py-1 bg-tertiary/10 text-tertiary border border-tertiary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">TOKO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black {{ $totalQty > 0 ? 'text-primary' : 'text-on-surface-variant/40' }}">{{ number_format($totalQty) }} unit</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" wire:click="toggleStatus({{ $loc->id }})" @disabled(!auth()->user()->can('manage master') || in_array($loc->kode_lokasi, ['GDG-A01', 'TKO-R01'])) class="inline-flex items-center">
                                    @if ($loc->is_active)
                                        <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-[10px] font-bold uppercase tracking-wider">AKTIF</span>
                                    @else
                                        <span class="px-3 py-1 bg-white/5 text-on-surface-variant/50 border border-white/5 rounded-full text-[10px] font-bold uppercase tracking-wider">NON-AKTIF</span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('lokasi.detail', $loc->id) }}" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Detail Barang">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <button wire:click="showBarcode({{ $loc->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Barcode">
                                        <span class="material-symbols-outlined text-lg">barcode_scanner</span>
                                    </button>
                                    @can('manage master')
                                    <button wire:click="edit({{ $loc->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Ubah">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="destroy({{ $loc->id }})" @disabled(in_array($loc->kode_lokasi, ['GDG-A01', 'TKO-R01'])) class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all disabled:opacity-30 disabled:hover:bg-transparent" title="Hapus">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada data lokasi ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $locations->firstItem() ?? 0 }} s.d. {{ $locations->lastItem() ?? 0 }} dari {{ $locations->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($locations->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($locations->getUrlRange(max(1, $locations->currentPage() - 2), min($locations->lastPage(), $locations->currentPage() + 2)) as $page => $url)
                    @if ($page == $locations->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($locations->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- CRUD Modal -->
    @if ($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-lg mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">{{ $isEdit ? 'Ubah Lokasi' : 'Tambah Lokasi Baru' }}</h3>
                    <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Kode Lokasi -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Kode Lokasi (Unik / Koordinat)</label>
                        <input wire:model="kode_lokasi" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface font-mono focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: GDG-A01" {{ $isEdit && in_array($kode_lokasi, ['GDG-A01', 'TKO-R01']) ? 'readonly bg-white/5 text-on-surface-variant/50' : '' }}>
                        @error('kode_lokasi') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Lokasi -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nama Lokasi / Keterangan Rak</label>
                        <input wire:model="nama_lokasi" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: Gudang Utama - Rak A1">
                        @error('nama_lokasi') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipe Lokasi -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tipe Lokasi</label>
                        <select wire:model="tipe_lokasi" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="GUDANG">GUDANG</option>
                            <option value="TOKO">TOKO</option>
                        </select>
                        @error('tipe_lokasi') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Status Keaktifan</label>
                        <select wire:model="is_active" @disabled($isEdit && in_array($kode_lokasi, ['GDG-A01', 'TKO-R01'])) class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="1">Aktif (Dapat digunakan transaksi)</option>
                            <option value="0">Non-Aktif (Terkunci)</option>
                        </select>
                        @error('is_active') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" wire:loading.attr="disabled" wire:target="store">
                        <div class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-on-primary-container border-t-transparent" wire:loading wire:target="store"></div>
                        <span wire:loading.remove wire:target="store">Simpan</span>
                        <span wire:loading wire:target="store">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Barcode Modal -->
    @if ($isBarcodeOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeBarcodeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-sm mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">Label Barcode Lokasi</h3>
                    <button wire:click="closeBarcodeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 flex flex-col items-center justify-center text-center space-y-4" id="barcode-print-area">
                    <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-inner flex flex-col items-center justify-center max-w-full overflow-hidden">
                        {!! \App\Helpers\Barcode128::getSvg($barcodeLocationCode, 80, 2) !!}
                        <span class="mt-3 font-mono text-xs font-bold text-black tracking-widest">{{ $barcodeLocationCode }}</span>
                        <span class="mt-1 text-[10px] text-gray-500 font-semibold uppercase">{{ $barcodeLocationName }}</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end px-6 py-5 border-t border-white/5 gap-2 bg-surface-container-low/50">
                    <button wire:click="closeBarcodeModal" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Tutup
                    </button>
                    <button onclick="printLocationBarcode('{{ $barcodeLocationCode }}')" type="button" class="px-4 py-2 bg-primary-container text-on-primary-container text-sm font-bold rounded-2xl hover:bg-primary-container/80 transition-all flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Cetak
                    </button>
                    <button wire:click="downloadBarcode('{{ $barcodeLocationCode }}')" type="button" class="px-4 py-2 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">download</span>
                        SVG
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function printLocationBarcode(code) {
            const svgElement = document.querySelector('#barcode-print-area svg');
            if (!svgElement) return;
            
            const svgHtml = svgElement.outerHTML;
            const printWindow = window.open('', '_blank', 'width=650,height=420');
            
            let html = '<html><head><title>Print Lokasi - ' + code + '</title>';
            html += '<style>';
            html += '@page { size: 80mm 50mm; margin: 0; }';
            html += 'html, body { margin: 0; padding: 0; width: 80mm; height: 50mm; display: flex; align-items: center; justify-content: center; background: #fff; }';
            html += 'svg { width: 80mm; height: 50mm; }';
            html += '</style></' + 'head>';
            html += '<body>';
            html += svgHtml;
            html += '<script>window.onload = function() { window.print(); window.close(); };</' + 'script>';
            html += '</' + 'body></' + 'html>';
            
            printWindow.document.write(html);
            printWindow.document.close();
        }
    </script>
</div>
