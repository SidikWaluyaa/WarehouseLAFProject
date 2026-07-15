<div x-init="$dispatch('update-header-title', 'Aturan Pengkodean Barang (SKU)')" class="flex-1 overflow-y-auto p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Kategori atau Prefix..." type="text"/>
        </div>
    </template>

    <!-- Actions Bar -->
    <section class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Search for mobile -->
            <div class="md:hidden relative w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Cari..."/>
            </div>
        </div>
        @can('manage master')
        <button wire:click="create" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
            <span class="material-symbols-outlined">add</span>
            Tambah Aturan Kategori
        </button>
        @endcan
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5">
                        <th wire:click="sortBy('kategori')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kategori Produk
                                @if ($sortField === 'kategori')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('prefix')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Prefix Kode
                                @if ($sortField === 'prefix')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Token Kategori</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Token Initials</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Token Varian</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Digit Seri</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Contoh Format</th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs">
                    @forelse ($rules as $rule)
                        @php
                            // Build a visual preview pattern
                            if (($rule->format_tipe ?? 'INCREMENT') === 'PRD_STYLE') {
                                $previewPattern = ($rule->prefix ?: 'SandalSlide') . '-Gorka-FullBlack-36';
                            } else {
                                $parts = [];
                                if (!empty($rule->prefix)) $parts[] = $rule->prefix;
                                if ($rule->include_category_prefix) $parts[] = is_null($rule->kategori) ? 'CAT' : substr(preg_replace('/[^A-Za-z0-9]/', '', $rule->kategori), 0, 3);
                                if ($rule->include_product_initials) $parts[] = 'PRO';
                                if ($rule->include_variant_code) $parts[] = 'VAR';
                                $parts[] = str_repeat('0', $rule->increment_digits - 1) . '1';
                                $previewPattern = implode('-', $parts);
                            }
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4">
                                @if (is_null($rule->kategori))
                                    <span class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-[10px] font-bold uppercase tracking-wider">Default Global</span>
                                @else
                                    <span class="text-sm font-semibold text-on-surface">{{ $rule->kategori }}</span>
                                @endif
                                <div class="text-[9px] text-on-surface-variant/40 mt-0.5">
                                    Format: <span class="text-primary font-semibold">{{ ($rule->format_tipe ?? 'INCREMENT') === 'PRD_STYLE' ? 'PRD Style' : 'Standard' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-primary/80 font-bold">{{ $rule->prefix ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($rule->include_category_prefix)
                                    <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded text-[10px] font-bold">YES</span>
                                @else
                                    <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/40 rounded text-[10px]">NO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($rule->include_product_initials)
                                    <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded text-[10px] font-bold">YES</span>
                                @else
                                    <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/40 rounded text-[10px]">NO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($rule->include_variant_code)
                                    <span class="px-2 py-0.5 bg-green-500/10 text-green-400 border border-green-500/20 rounded text-[10px] font-bold">YES</span>
                                @else
                                    <span class="px-2 py-0.5 bg-white/5 text-on-surface-variant/40 rounded text-[10px]">NO</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-on-surface-variant">{{ $rule->increment_digits }} digit</td>
                            <td class="px-6 py-4 font-mono text-xs text-on-surface-variant/80">
                                <span class="bg-surface-container rounded-lg px-2 py-1 border border-white/5 text-primary">{{ $previewPattern }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    @can('manage master')
                                    <button wire:click="edit({{ $rule->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Ubah">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="destroy({{ $rule->id }})" @disabled(is_null($rule->kategori)) class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all disabled:opacity-30 disabled:hover:bg-transparent" title="Hapus">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada aturan pengkodean ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $rules->firstItem() ?? 0 }} s.d. {{ $rules->lastItem() ?? 0 }} dari {{ $rules->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($rules->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($rules->getUrlRange(max(1, $rules->currentPage() - 2), min($rules->lastPage(), $rules->currentPage() + 2)) as $page => $url)
                    @if ($page == $rules->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($rules->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- Aturan CRUD Modal -->
    @if ($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-lg mx-auto my-6 z-50 px-4">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <h3 class="text-base font-bold text-primary">{{ $isEdit ? 'Ubah Aturan Pengkodean' : 'Tambah Aturan Pengkodean Kategori' }}</h3>
                    <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Kategori -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Kategori Produk</label>
                        <input wire:model="kategori" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: SANDAL (Kosongkan untuk aturan Global Default)" {{ $isEdit && is_null($kategori) ? 'readonly bg-white/5 text-on-surface-variant/50' : '' }}>
                        <span class="text-[10px] text-on-surface-variant/40 mt-1 block">Biarkan kosong untuk menjadikannya template default/fallback bagi kategori yang tidak memiliki aturan khusus.</span>
                        @error('kategori') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Prefix -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Prefix Kode</label>
                        <input wire:model="prefix" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface font-mono focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: LAF / GDG">
                        @error('prefix') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipe Format -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tipe Format SKU</label>
                        <select wire:model.live="format_tipe" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="INCREMENT">Standard (Dengan Increment, misal: LAF-SAN-GORKA-H36-001)</option>
                            <option value="PRD_STYLE">PRD Style (Tanpa Increment, misal: SandalSlide-Gorka-FullBlack-36)</option>
                        </select>
                        @error('format_tipe') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    @if ($format_tipe === 'INCREMENT')
                        <!-- Toggles -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Token Informasi Tambahan (Disisipkan ke SKU)</label>
                            
                            <!-- Include Category Prefix -->
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-2xl border border-white/5">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Singkatan Kategori</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: SANDAL -> SAN, SEPATU -> SEP</p>
                                </div>
                                <input wire:model="include_category_prefix" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0"/>
                            </div>

                            <!-- Include Product Initials -->
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-2xl border border-white/5">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Inisial Nama Produk</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: Sandal Gunung Keren -> SGK</p>
                                </div>
                                <input wire:model="include_product_initials" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0"/>
                            </div>

                            <!-- Include Variant Code -->
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-2xl border border-white/5">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Kode Varian / Ukuran</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: Hitam / 42 -> H42</p>
                                </div>
                                <input wire:model="include_variant_code" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0"/>
                            </div>
                        </div>

                        <!-- Increment Digits -->
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Jumlah Digit Angka Urut (Increment)</label>
                            <select wire:model="increment_digits" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="1">1 digit (Contoh: 1, 2)</option>
                                <option value="2">2 digit (Contoh: 01, 02)</option>
                                <option value="3">3 digit (Contoh: 001, 002)</option>
                                <option value="4">4 digit (Contoh: 0001, 0002)</option>
                                <option value="5">5 digit (Contoh: 00001, 00002)</option>
                            </select>
                            @error('increment_digits') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                        Batal
                    </button>
                    <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
