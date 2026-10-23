<div x-init="$dispatch('update-header-title', 'Master Pelanggan')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari Pelanggan..." type="text"/>
        </div>
    </template>

    <!-- Tab Navigation -->
    <div class="flex border-b border-white/10 mb-6 space-x-8">
        <a href="{{ route('pelanggan') }}" class="relative pb-4 text-sm font-bold text-rose-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg text-rose-400">list_alt</span>
            Daftar Pelanggan
            <div class="absolute bottom-0 left-0 right-0 h-[2px] bg-rose-500 rounded-full shadow-[0_0_12px_#f43f5e]"></div>
        </a>
        <a href="{{ route('pelanggan.riwayat') }}" class="pb-4 text-sm font-semibold text-on-surface-variant hover:text-on-surface flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-lg">history</span>
            Riwayat Pelanggan
        </a>
    </div>
    <section class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="w-full sm:w-auto flex-1 max-w-md">
            <!-- Mobile Search Bar (Only visible on screens < md) -->
            <div class="md:hidden relative w-full">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Cari Pelanggan..." type="text"/>
            </div>
        </div>
        @can('manage master')
        <button wire:click="create" class="flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong w-full sm:w-auto justify-center">
            <span class="material-symbols-outlined">add</span>
            Tambah Pelanggan
        </button>
        @endcan
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5">
                        <th wire:click="sortBy('nama_pelanggan')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Nama Pelanggan
                                @if ($sortField === 'nama_pelanggan')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sortBy('kontak')" class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase cursor-pointer hover:text-on-surface select-none">
                            <div class="flex items-center gap-1">
                                Kontak
                                @if ($sortField === 'kontak')
                                    <span class="text-primary text-xs">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">Alamat</th>
                        @can('manage master')
                        <th class="px-6 py-5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase text-center w-32">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                    @forelse ($pelanggans as $p)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 font-bold text-on-surface text-sm">{{ $p->nama_pelanggan }}</td>
                            <td class="px-6 py-4 font-mono font-semibold">{{ $p->kontak ?? '-' }}</td>
                            <td class="px-6 py-4 max-w-xs truncate">{{ $p->alamat ?? '-' }}</td>
                            @can('manage master')
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="edit({{ $p->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-primary/20 text-primary transition-all" title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button wire:click="destroy({{ $p->id }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" title="Hapus">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8">
                                <x-empty-state icon="person" title="Tidak Ada Pelanggan" description="Belum ada pelanggan yang ditambahkan ke sistem." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $pelanggans->firstItem() ?? 0 }} s.d. {{ $pelanggans->lastItem() ?? 0 }} dari {{ $pelanggans->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($pelanggans->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($pelanggans->getUrlRange(max(1, $pelanggans->currentPage() - 2), min($pelanggans->lastPage(), $pelanggans->currentPage() + 2)) as $page => $url)
                    @if ($page == $pelanggans->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($pelanggans->hasMorePages())
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
                    <h3 class="text-base font-bold text-primary">{{ $isEdit ? 'Ubah Pelanggan' : 'Tambah Pelanggan Baru' }}</h3>
                    <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <!-- Nama Pelanggan -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nama Pelanggan</label>
                        <input wire:model="nama_pelanggan" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: Toko Berkah Sepatu">
                        @error('nama_pelanggan') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Kontak -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Kontak / No. Telepon</label>
                        <input wire:model="kontak" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: 08567890123">
                        @error('kontak') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Alamat</label>
                        <textarea wire:model="alamat" rows="3" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Alamat lengkap..."></textarea>
                        @error('alamat') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
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
</div>
