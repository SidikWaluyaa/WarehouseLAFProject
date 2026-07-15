<div x-init="$dispatch('update-header-title', 'Buat Transfer Order')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">


    <form wire:submit="store" class="space-y-6">
        {{-- Header --}}
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-6">
            <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">swap_horiz</span>
                Informasi Transfer
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Tanggal *</label>
                    <input wire:model="tanggal" type="date" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                    @error('tanggal') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Lokasi Asal *</label>
                    <select wire:model="lokasi_asal_id" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Pilih Lokasi Asal</option>
                        @foreach($locationsList as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->kode_lokasi }} — {{ $loc->nama_lokasi }}</option>
                        @endforeach
                    </select>
                    @error('lokasi_asal_id') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Lokasi Tujuan *</label>
                    <select wire:model="lokasi_tujuan_id" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Pilih Lokasi Tujuan</option>
                        @foreach($locationsList as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->kode_lokasi }} — {{ $loc->nama_lokasi }}</option>
                        @endforeach
                    </select>
                    @error('lokasi_tujuan_id') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Keterangan</label>
                    <input wire:model="keterangan" type="text" placeholder="Opsional" class="w-full px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40" />
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                    Daftar Barang Transfer
                </h3>
                <button type="button" wire:click="addItem" class="flex items-center gap-1 px-4 py-2 rounded-xl bg-primary/10 text-primary text-xs font-bold hover:bg-primary/20 transition-colors border border-primary/20">
                    <span class="material-symbols-outlined text-sm">add</span> Tambah Baris
                </button>
            </div>

            @error('items') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror

            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="flex flex-col md:flex-row gap-3 items-end p-4 rounded-2xl bg-surface-container/50 border border-white/5">
                        <div class="flex-1 space-y-2">
                            <label class="block text-[10px] font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Produk *</label>
                            
                            <!-- Search input for lazy loading -->
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-xs">search</span>
                                <input wire:model.live.debounce.300ms="items.{{ $index }}.search_query" type="text" placeholder="Ketik SKU / nama produk..." class="w-full bg-surface-container border border-white/10 rounded-xl py-1.5 pl-8 pr-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30">
                            </div>

                            <!-- Dropdown select populated dynamically -->
                            @php
                                $options = $this->getProductsForSearch($item['search_query'] ?? '', $item['produk_id'] ?? null);
                            @endphp
                            <select wire:model="items.{{ $index }}.produk_id" class="w-full px-3 py-2.5 rounded-xl bg-surface-container border border-white/10 text-on-surface text-xs focus:outline-none focus:ring-2 focus:ring-primary/40">
                                <option value="">Pilih Produk (Hasil Pencarian)</option>
                                @foreach($options as $p)
                                    <option value="{{ $p->id }}">{{ $p->kode_barang }} — {{ $p->nama_barang }}</option>
                                @endforeach
                            </select>
                            @error("items.{$index}.produk_id") <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-32">
                            <label class="block text-[10px] font-bold text-on-surface-variant mb-1 uppercase tracking-wider">Kuantitas *</label>
                            <input wire:model="items.{{ $index }}.qty_requested" type="number" min="1" class="w-full px-3 py-2.5 rounded-xl bg-surface-container border border-white/10 text-on-surface text-xs text-center focus:outline-none focus:ring-2 focus:ring-primary/40" />
                            @error("items.{$index}.qty_requested") <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <button type="button" wire:click="removeItem({{ $index }})" class="p-2 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors border border-red-500/20">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('transfer') }}" wire:navigate class="px-6 py-3 rounded-2xl text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors">
                &larr; Kembali
            </a>
            <button type="submit" class="flex items-center gap-2 px-8 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-lg">
                <span class="material-symbols-outlined text-lg">save</span>
                Simpan Transfer Order
            </button>
        </div>
    </form>
</div>
