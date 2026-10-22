<div x-init="$dispatch('update-header-title', 'Aturan Pengkodean Barang (SKU)')" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Kategori atau Prefix..." type="text"/>
        </div>
    </template>

    <!-- KPI Summary Section -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
        <!-- KPI Card 1: Total Rules -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 relative overflow-hidden group hover:border-primary/30 transition-all duration-300 amber-glow border border-white/5">
            <div class="absolute -top-3 -right-3 material-symbols-outlined text-primary/10 text-7xl group-hover:scale-110 transition-transform duration-500 select-none pointer-events-none">qr_code_scanner</div>
            <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider mb-1">Total Aturan Pengkodean</p>
            <h3 class="text-2xl sm:text-3xl font-black text-on-surface font-headline-lg">{{ $totalRules }} <span class="text-xs font-medium text-on-surface-variant/60 font-sans">Aturan</span></h3>
            <div class="mt-3 flex items-center text-[11px] font-semibold text-primary">
                <span class="w-1.5 h-1.5 rounded-full bg-primary mr-1.5 animate-pulse"></span>
                <span>Termasuk 1 Default Global Fallback</span>
            </div>
        </div>

        <!-- KPI Card 2: PRD Style Rules -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 relative overflow-hidden group hover:border-primary/30 transition-all duration-300 amber-glow border border-white/5">
            <div class="absolute -top-3 -right-3 material-symbols-outlined text-primary/10 text-7xl group-hover:scale-110 transition-transform duration-500 select-none pointer-events-none">style</div>
            <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider mb-1">Format PRD Style</p>
            <h3 class="text-2xl sm:text-3xl font-black text-on-surface font-headline-lg">{{ $prdCount }} <span class="text-xs font-medium text-on-surface-variant/60 font-sans">Kategori</span></h3>
            <div class="mt-3 flex items-center text-[11px] font-medium text-on-surface-variant/80">
                <span class="material-symbols-outlined text-sm mr-1 text-primary">check_circle</span>
                <span>Prefix-Model-Color-Size</span>
            </div>
        </div>

        <!-- KPI Card 3: Standard Incremental Rules -->
        <div class="glass-card rounded-3xl p-5 sm:p-6 relative overflow-hidden group hover:border-primary/30 transition-all duration-300 amber-glow border border-white/5">
            <div class="absolute -top-3 -right-3 material-symbols-outlined text-primary/10 text-7xl group-hover:scale-110 transition-transform duration-500 select-none pointer-events-none">tag</div>
            <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider mb-1">Standard Incremental</p>
            <h3 class="text-2xl sm:text-3xl font-black text-on-surface font-headline-lg">{{ $standardCount }} <span class="text-xs font-medium text-on-surface-variant/60 font-sans">Aturan</span></h3>
            <div class="mt-3 flex items-center text-[11px] font-medium text-on-surface-variant/80">
                <span class="material-symbols-outlined text-sm mr-1 text-primary">pin</span>
                <span>Prefix + Tokens + Auto Increment</span>
            </div>
        </div>
    </section>

    <!-- Actions Bar -->
    <section class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/60 text-lg">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-11 pr-10 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/40 shadow-inner" placeholder="Cari Kategori atau Prefix Kode..."/>
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant/50 hover:text-on-surface p-0.5 rounded-full transition-colors" title="Hapus pencarian">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            @endif
        </div>

        <!-- Create Button -->
        @can('manage master')
        <button wire:click="create" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong whitespace-nowrap cursor-pointer">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            Tambah Aturan Kategori
        </button>
        @endcan
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden border border-white/10 shadow-2xl mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/10 bg-white/[0.02]">
                        <th wire:click="sortBy('kategori')" class="px-6 py-4.5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/70 uppercase cursor-pointer hover:text-primary transition-colors select-none w-[28%]">
                            <div class="flex items-center gap-1.5">
                                Kategori Produk
                                @if ($sortField === 'kategori')
                                    <span class="text-primary text-xs font-bold">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @else
                                    <span class="text-on-surface-variant/30 text-xs">▲</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('prefix')" class="px-6 py-4.5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/70 uppercase cursor-pointer hover:text-primary transition-colors select-none w-[15%]">
                            <div class="flex items-center gap-1.5">
                                Prefix Kode
                                @if ($sortField === 'prefix')
                                    <span class="text-primary text-xs font-bold">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @else
                                    <span class="text-on-surface-variant/30 text-xs">▲</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4.5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/70 uppercase w-[25%]">Struktur SKU</th>
                        <th class="px-6 py-4.5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/70 uppercase w-[24%]">Contoh Format SKU</th>
                        <th class="px-6 py-4.5 text-[11px] font-bold tracking-[0.15em] text-on-surface-variant/70 uppercase text-center w-[8%]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-xs">
                    @forelse ($rules as $rule)
                        @php
                            $isPrd = ($rule->format_tipe ?? 'INCREMENT') === 'PRD_STYLE';
                            if ($isPrd) {
                                $previewPattern = ($rule->prefix ?: 'SandalSlide') . '-Gorka-FullBlack-36';
                            } else {
                                $parts = [];
                                if (!empty($rule->prefix)) $parts[] = $rule->prefix;
                                if ($rule->include_category_prefix) $parts[] = is_null($rule->kategori) ? 'CAT' : substr(preg_replace('/[^A-Za-z0-9]/', '', $rule->kategori), 0, 3);
                                if ($rule->include_product_initials) $parts[] = 'PRO';
                                if ($rule->include_variant_code) $parts[] = 'VAR';
                                $parts[] = str_repeat('0', max(0, $rule->increment_digits - 1)) . '1';
                                $previewPattern = implode('-', $parts);
                            }
                        @endphp
                        <tr class="hover:bg-white/[0.04] transition-colors group">
                            <!-- Kategori Produk (Sejajar Horizontal) -->
                            <td class="px-6 py-6 align-middle">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    @if (is_null($rule->kategori))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary border border-primary/25 text-[11px] font-extrabold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                            Default Global
                                        </span>
                                    @else
                                        <span class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors tracking-wide">{{ $rule->kategori }}</span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5 text-[10px] px-2.5 py-0.5 rounded-full font-semibold {{ $isPrd ? 'bg-amber-500/10 text-amber-300 border border-amber-500/20' : 'bg-white/5 text-on-surface-variant/70 border border-white/10' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isPrd ? 'bg-amber-400' : 'bg-emerald-400' }}"></span>
                                        {{ $isPrd ? 'PRD Style' : 'Standard' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Prefix Kode -->
                            <td class="px-6 py-6 align-middle">
                                @if(!empty($rule->prefix))
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-surface-container-high border border-white/10 font-mono text-xs font-bold text-primary tracking-wider shadow-sm">
                                        {{ $rule->prefix }}
                                    </span>
                                @else
                                    <span class="text-on-surface-variant/40 font-mono text-xs">-</span>
                                @endif
                            </td>

                            <!-- Struktur SKU (Tokens & Increment) -->
                            <td class="px-6 py-6 align-middle">
                                @if($isPrd)
                                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-amber-500/10 text-amber-300 border border-amber-500/20 text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[15px] text-primary">auto_awesome</span>
                                        Model + Warna + Ukuran
                                    </span>
                                @else
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($rule->include_category_prefix)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">+Kategori</span>
                                        @endif
                                        @if($rule->include_product_initials)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">+Inisial</span>
                                        @endif
                                        @if($rule->include_variant_code)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">+Varian</span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/5 text-on-surface-variant/80 border border-white/10 text-[10px] font-mono font-medium">{{ $rule->increment_digits }} Digit Seri</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Contoh Format SKU with Copy Button -->
                            <td class="px-6 py-6 align-middle">
                                <div x-data="{ copied: false }" class="inline-flex items-center gap-2.5">
                                    <span class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-surface-container-high/90 border border-white/10 text-primary font-mono text-xs font-bold whitespace-nowrap shadow-inner tracking-wide">
                                        <span class="material-symbols-outlined text-[16px] text-primary/70">qr_code_2</span>
                                        {{ $previewPattern }}
                                    </span>
                                    <button 
                                        @click="navigator.clipboard.writeText('{{ $previewPattern }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        type="button" 
                                        class="p-2 rounded-xl bg-white/5 hover:bg-primary/20 text-on-surface-variant hover:text-primary transition-all active:scale-90 cursor-pointer border border-white/5 shadow-sm"
                                        :title="copied ? 'Disalin ke clipboard!' : 'Salin contoh SKU'">
                                        <span class="material-symbols-outlined text-[16px]" x-show="!copied">content_copy</span>
                                        <span class="material-symbols-outlined text-[16px] text-emerald-400" x-show="copied" style="display: none;">check</span>
                                    </button>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-6 align-middle">
                                <div class="flex items-center justify-center gap-2.5">
                                    @can('manage master')
                                    <button wire:click="edit({{ $rule->id }})" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-primary/20 text-on-surface-variant hover:text-primary flex items-center justify-center transition-all active:scale-90 border border-white/5 shadow-sm" title="Ubah Aturan">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button wire:click="destroy({{ $rule->id }})" @disabled(is_null($rule->kategori)) class="w-9 h-9 rounded-xl bg-white/5 hover:bg-error/20 text-on-surface-variant hover:text-error flex items-center justify-center transition-all active:scale-90 disabled:opacity-20 disabled:hover:bg-white/5 disabled:hover:text-on-surface-variant cursor-pointer disabled:cursor-not-allowed border border-white/5 shadow-sm" title="Hapus Aturan">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-14 h-14 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-3 text-on-surface-variant/40">
                                        <span class="material-symbols-outlined text-3xl">rule</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-on-surface mb-1">Tidak Ada Aturan Ditemukan</h4>
                                    <p class="text-xs text-on-surface-variant/60">Tidak ada aturan pengkodean SKU yang sesuai dengan pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div class="px-8 py-5 border-t border-white/10 bg-white/[0.02] flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Left: Result count with indicator dot -->
            <div class="flex items-center gap-3">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <p class="text-xs text-on-surface-variant font-medium">
                    Menampilkan <strong class="text-on-surface font-bold text-sm">{{ $rules->firstItem() ?? 0 }} - {{ $rules->lastItem() ?? 0 }}</strong> dari total <strong class="text-on-surface font-bold text-sm">{{ $rules->total() }}</strong> aturan
                </p>
            </div>

            <!-- Right: Controls -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5 text-xs text-on-surface-variant">
                    <span class="text-on-surface-variant/70 font-medium">Baris per halaman:</span>
                    <select wire:model.live="perPage" class="bg-surface-container border border-white/10 rounded-xl py-1.5 px-3 text-xs text-on-surface font-bold focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer hover:border-white/20">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                @if ($rules->hasPages())
                    <div class="flex items-center gap-1.5 border-l border-white/10 pl-4">
                        {{-- Previous Page Link --}}
                        @if ($rules->onFirstPage())
                            <button class="px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/5 text-xs text-on-surface-variant/40 cursor-not-allowed flex items-center gap-1" disabled>
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                                Sebelumnya
                            </button>
                        @else
                            <button wire:click="previousPage" class="px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all active:scale-95 flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                                Sebelumnya
                            </button>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($rules->getUrlRange(max(1, $rules->currentPage() - 2), min($rules->lastPage(), $rules->currentPage() + 2)) as $page => $url)
                            @if ($page == $rules->currentPage())
                                <button class="w-8 h-8 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold shadow-md flex items-center justify-center">{{ $page }}</button>
                            @else
                                <button wire:click="gotoPage({{ $page }})" class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all flex items-center justify-center cursor-pointer">{{ $page }}</button>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($rules->hasMorePages())
                            <button wire:click="nextPage" class="px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all active:scale-95 flex items-center gap-1 cursor-pointer">
                                Selanjutnya
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </button>
                        @else
                            <button class="px-3.5 py-1.5 rounded-xl bg-white/5 border border-white/5 text-xs text-on-surface-variant/40 cursor-not-allowed flex items-center gap-1" disabled>
                                Selanjutnya
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Aturan CRUD Modal -->
    @if ($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/85 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-lg mx-auto my-6 z-50">
            <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-lg">{{ $isEdit ? 'edit_note' : 'add_circle' }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-on-surface">{{ $isEdit ? 'Ubah Aturan Pengkodean' : 'Tambah Aturan Pengkodean Kategori' }}</h3>
                            <p class="text-[11px] text-on-surface-variant/60">Konfigurasi format dan penomoran otomatis SKU</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <!-- Live SKU Preview Box -->
                    <div class="p-4 rounded-2xl bg-surface-container-high/60 border border-primary/20 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                Live Preview Format SKU
                            </span>
                            <span class="text-[10px] text-on-surface-variant/60 font-medium">Hasil Otomatis</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface-container-lowest border border-white/10 text-primary font-mono text-xs font-bold tracking-wider shadow-sm">
                                <span class="material-symbols-outlined text-sm text-primary/70">qr_code_2</span>
                                {{ $modalPreview }}
                            </span>
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Kategori Produk</label>
                        <input wire:model.live.debounce.250ms="kategori" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: SANDAL (Kosongkan untuk aturan Global Default)" {{ $isEdit && is_null($kategori) ? 'readonly bg-white/5 text-on-surface-variant/50 cursor-not-allowed' : '' }}>
                        <span class="text-[10px] text-on-surface-variant/50 mt-1.5 block">Biarkan kosong untuk menjadikannya template default/fallback bagi kategori yang tidak memiliki aturan khusus.</span>
                        @if($errors->has('kategori')) <span class="text-xs text-error mt-1.5 block font-medium">{{ $errors->first('kategori') }}</span> @endif
                    </div>

                    <!-- Prefix -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Prefix Kode</label>
                        <input wire:model.live.debounce.250ms="prefix" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface font-mono uppercase focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: LAF / GDG / SandalSlide">
                        @if($errors->has('prefix')) <span class="text-xs text-error mt-1.5 block font-medium">{{ $errors->first('prefix') }}</span> @endif
                    </div>

                    <!-- Tipe Format -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tipe Format SKU</label>
                        <select wire:model.live="format_tipe" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="INCREMENT">Standard (Dengan Increment, misal: LAF-CAT-PRO-VAR-001)</option>
                            <option value="PRD_STYLE">PRD Style (Tanpa Increment, misal: SandalSlide-Gorka-FullBlack-36)</option>
                        </select>
                        @if($errors->has('format_tipe')) <span class="text-xs text-error mt-1.5 block font-medium">{{ $errors->first('format_tipe') }}</span> @endif
                    </div>

                    @if ($format_tipe === 'INCREMENT')
                        <!-- Toggles -->
                        <div class="space-y-3 pt-1">
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Token Informasi Tambahan (Disisipkan ke SKU)</label>
                            
                            <!-- Include Category Prefix -->
                            <div class="flex items-center justify-between p-3.5 bg-white/5 rounded-2xl border border-white/5 hover:border-white/10 transition-colors">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Singkatan Kategori</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: SANDAL -> SAN, SEPATU -> SEP</p>
                                </div>
                                <input wire:model.live="include_category_prefix" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer"/>
                            </div>

                            <!-- Include Product Initials -->
                            <div class="flex items-center justify-between p-3.5 bg-white/5 rounded-2xl border border-white/5 hover:border-white/10 transition-colors">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Inisial Nama Produk</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: Sandal Gunung Keren -> SGK</p>
                                </div>
                                <input wire:model.live="include_product_initials" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer"/>
                            </div>

                            <!-- Include Variant Code -->
                            <div class="flex items-center justify-between p-3.5 bg-white/5 rounded-2xl border border-white/5 hover:border-white/10 transition-colors">
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">Sisipkan Kode Varian / Ukuran</p>
                                    <p class="text-[10px] text-on-surface-variant/60">Contoh: Hitam / 42 -> H42</p>
                                </div>
                                <input wire:model.live="include_variant_code" type="checkbox" class="h-5 w-5 rounded bg-surface-container-low border-white/10 text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer"/>
                            </div>
                        </div>

                        <!-- Increment Digits -->
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Jumlah Digit Angka Urut (Increment)</label>
                            <select wire:model.live="increment_digits" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="1">1 digit (Contoh: 1, 2, ...)</option>
                                <option value="2">2 digit (Contoh: 01, 02, ...)</option>
                                <option value="3">3 digit (Contoh: 001, 002, ...)</option>
                                <option value="4">4 digit (Contoh: 0001, 0002, ...)</option>
                                <option value="5">5 digit (Contoh: 00001, 00002, ...)</option>
                            </select>
                            @if($errors->has('increment_digits')) <span class="text-xs text-error mt-1.5 block font-medium">{{ $errors->first('increment_digits') }}</span> @endif
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-on-surface-variant/90 space-y-1">
                            <p class="font-bold text-primary flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">info</span>
                                Format PRD Style Aktif
                            </p>
                            <p class="text-[11px] leading-relaxed">
                                Format PRD Style menyusun SKU secara otomatis dari: <strong class="text-on-surface">{Prefix}-{Model}-{Warna}-{Ukuran}</strong> tanpa menggunakan token terpisah atau digit seri urut.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end px-6 py-4.5 border-t border-white/5 gap-3 bg-surface-container-low/50">
                    <button wire:click="closeModal" type="button" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold hover:bg-white/10 transition-all text-on-surface cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong cursor-pointer">
                        Simpan Aturan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
