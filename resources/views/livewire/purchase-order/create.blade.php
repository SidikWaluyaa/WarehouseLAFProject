<div x-init="$dispatch('update-header-title', 'Terbitkan Purchase Order Baru')"
     class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    <!-- Header Actions & Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-white/5">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-order') }}" class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 flex items-center justify-center text-on-surface transition-all active:scale-95" title="Kembali ke Daftar PO">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <h2 class="text-xl font-black text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">assignment_add</span>
                    Terbitkan Purchase Order Baru
                </h2>
                <p class="text-xs text-on-surface-variant/70">Buat pesanan pengadaan barang ke supplier menggunakan katalog produk, barcode scanner, atau pencarian SKU.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('purchase-order') }}" class="flex-1 sm:flex-initial px-5 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-xs font-semibold hover:bg-white/10 transition-all text-on-surface text-center">
                Batal
            </a>
            <button type="button" wire:click="store" class="flex-1 sm:flex-initial px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong flex items-center justify-center gap-2" wire:loading.attr="disabled">
                <span class="material-symbols-outlined text-sm" wire:loading.remove>check_circle</span>
                <div class="animate-spin rounded-full h-3.5 w-3.5 border-2 border-on-primary-container border-t-transparent" wire:loading></div>
                <span>Terbitkan PO</span>
            </button>
        </div>
    </div>

    <!-- Main Form Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: PO Header Details -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                <h3 class="text-xs font-black text-primary uppercase tracking-wider pb-3 border-b border-white/5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">receipt_long</span>
                    Informasi Purchase Order
                </h3>

                <!-- Nomor PO (Otomatis & Locked) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider">Nomor PO (Sistem)</label>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-[10px] font-black tracking-wider uppercase">
                            <span class="material-symbols-outlined text-[12px]">lock</span>
                            Otomatis
                        </span>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-primary text-base">tag</span>
                        <input wire:model="nomor_po" 
                               value="{{ $nomor_po }}" 
                               type="text" 
                               readonly 
                               class="w-full bg-surface-container-highest/60 border border-primary/30 rounded-2xl py-2.5 pl-10 pr-4 text-xs font-mono font-bold text-primary focus:outline-none cursor-not-allowed select-all shadow-inner"
                               title="Nomor PO di-generate otomatis oleh sistem">
                    </div>
                    <p class="text-[10px] text-on-surface-variant/60 mt-1.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs text-primary/70">verified</span>
                        Nomor PO unik diterbitkan otomatis berdasarkan tanggal & counter sistem.
                    </p>
                    @error('nomor_po') <span class="text-xs text-error mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <!-- Tanggal PO (Interactive Picker with Calendar Icon) -->
                <div x-data>
                    <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tanggal PO</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-on-surface-variant text-base pointer-events-none">calendar_month</span>
                        <input x-ref="poDateInput"
                               wire:model.live="tanggal" 
                               value="{{ $tanggal }}" 
                               type="date" 
                               class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-10 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all cursor-pointer font-medium">
                        <button type="button" 
                                @click="if ($refs.poDateInput.showPicker) { $refs.poDateInput.showPicker(); } else { $refs.poDateInput.focus(); }"
                                class="absolute right-2.5 p-1 rounded-xl hover:bg-white/10 text-on-surface-variant hover:text-primary transition-colors"
                                title="Buka Kalender Pemilih Tanggal">
                            <span class="material-symbols-outlined text-base">event</span>
                        </button>
                    </div>
                    @error('tanggal') <span class="text-xs text-error mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Supplier / Vendor Tujuan</label>
                    <div class="relative">
                        <select wire:model.live="supplier_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliersList as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('supplier_id') <span class="text-xs text-error mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <!-- Nomor Artikel / Keterangan PO -->
                <div>
                    <label class="block text-[11px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nomor Artikel / Keterangan PO</label>
                    <textarea wire:model="artikel" rows="3" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: ART-JULY-SNDL atau Pesanan Produksi Batch 3"></textarea>
                    @error('artikel') <span class="text-xs text-error mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Ringkasan Nilai PO Card -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-3 bg-gradient-to-br from-surface-container-low/80 to-surface-container/60">
                <h4 class="text-xs font-black text-on-surface uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-base">payments</span>
                    Ringkasan Nilai Pesanan
                </h4>
                
                <div class="space-y-2 pt-2 border-t border-white/5 text-xs">
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Total Jenis Item (SKU)</span>
                        <span class="font-bold text-on-surface font-mono">{{ $totalItems }} SKU</span>
                    </div>
                    <div class="flex justify-between text-on-surface-variant">
                        <span>Total Kuantitas Pesanan</span>
                        <span class="font-bold text-on-surface font-mono">{{ number_format($totalQty, 0, ',', '.') }} unit</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-white/5">
                        <span class="text-xs font-bold uppercase text-primary">Estimasi Total Nilai:</span>
                        <span class="text-base font-black text-primary font-mono">Rp{{ number_format($grandTotalHpp, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Barcode Scanner, Search & Ordered Items Table -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Quick Add Panel: Barcode Scanner & Search Autocomplete -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-primary uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg animate-pulse">barcode_scanner</span>
                        Pindai Barcode / Tambah Barang Pesanan
                    </h3>
                    @if(count($items) > 0)
                        <button type="button" wire:click="clearAllItems" class="text-[10px] text-error hover:underline flex items-center gap-1 font-bold">
                            <span class="material-symbols-outlined text-xs">delete_sweep</span>
                            Kosongkan Daftar
                        </button>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- 1. Barcode Scanner Input -->
                    <div>
                        <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Scan Barcode / SKU</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">barcode_reader</span>
                            <input wire:model="barcodeScan" 
                                   x-on:keydown.enter.prevent="$wire.addBarcodeItem($el.value); $el.value = '';" 
                                   type="text" 
                                   id="po-barcode-scan-input" 
                                   placeholder="Scan barcode lalu tekan Enter..." 
                                   class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-mono font-bold uppercase placeholder:text-on-surface-variant/30">
                        </div>
                        @error('barcodeScan') <span class="text-xs text-error mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- 2. Autocomplete Search Input -->
                    <div class="relative" x-data="{ open: false, query: '', results: [] }" @click.away="open = false">
                        <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1.5">Cari Berdasarkan Nama / SKU</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">search</span>
                            <input type="text" 
                                   x-model="query" 
                                   @input.debounce.300ms="
                                        if (query.trim().length >= 2) {
                                            $wire.getProductsForSearch(query).then(res => { results = res; open = true; });
                                        } else {
                                            results = [];
                                            open = false;
                                        }
                                   "
                                   @focus="if (results.length > 0) open = true"
                                   placeholder="Ketik minimal 2 huruf produk..." 
                                   class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30">
                        </div>

                        <!-- Dropdown Search Results -->
                        <div x-show="open && results.length > 0" 
                             x-cloak 
                             class="absolute left-0 right-0 mt-1 bg-surface-dark border border-white/10 rounded-2xl shadow-2xl z-50 max-h-56 overflow-y-auto custom-scrollbar p-1.5 space-y-1">
                            <template x-for="p in results" :key="p.id">
                                <button type="button" 
                                        @click="$wire.addProductById(p.id); query = ''; open = false;" 
                                        class="w-full p-2.5 rounded-xl hover:bg-white/5 flex items-center justify-between text-left transition-all group">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <p class="text-xs font-bold text-on-surface group-hover:text-primary transition-colors truncate" x-text="p.nama_barang"></p>
                                        <p class="text-[10px] text-on-surface-variant/60 font-mono" x-text="p.kode_barang"></p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-mono font-bold text-primary" x-text="'Rp' + new Intl.NumberFormat('id-ID').format(p.hpp)"></span>
                                        <span class="block text-[9px] text-on-surface-variant/50" x-text="'Stok: ' + p.total_stok + ' ' + (p.satuan || 'pcs')"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ordered Items Table -->
            <div class="glass-card rounded-3xl p-6 border border-white/10 shadow-2xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/5">
                    <div>
                        <h3 class="text-xs font-black text-primary uppercase tracking-wider flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base">format_list_bulleted</span>
                            Daftar Barang Pesanan PO ({{ count($items) }})
                        </h3>
                        <p class="text-[10px] text-on-surface-variant/60 mt-0.5">Tentukan kuantitas dan estimasi HPP per item pesanan.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                wire:click="openCatalogModal" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary/10 border border-primary/30 text-primary hover:bg-primary/20 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm">
                            <span class="material-symbols-outlined text-sm">menu_book</span>
                            Pilih dari Katalog
                        </button>
                        <button type="button" 
                                wire:click="addItemRow" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm">
                            <span class="material-symbols-outlined text-sm">add_circle</span>
                            Tambah Baris
                        </button>
                    </div>
                </div>

                @error('items') <span class="text-xs text-error block font-medium">{{ $message }}</span> @enderror

                @if(count($items) > 0)
                    <div class="overflow-x-auto custom-scrollbar border border-white/5 rounded-2xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/70 uppercase bg-white/[0.02]">
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-3 py-3 text-center w-28">Kuantitas</th>
                                    <th class="px-3 py-3 text-right w-40">Estimasi HPP (Rp)</th>
                                    <th class="px-3 py-3 text-right w-36">Subtotal</th>
                                    <th class="px-3 py-3 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ($items as $index => $item)
                                    <tr wire:key="po-item-row-{{ $index }}" class="hover:bg-white/5 transition-colors {{ $justScannedProductId === ($item['produk_id'] ?? null) ? 'bg-primary/10 animate-pulse' : '' }}">
                                        <!-- Produk Info / Row Search -->
                                        <td class="px-4 py-3 min-w-[220px]">
                                            @if (!empty($item['produk_id']))
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold text-on-surface">{{ $item['nama_barang'] }}</span>
                                                    <span class="text-[10px] text-primary/80 font-mono mt-0.5">{{ $item['kode_barang'] }} &bull; {{ $item['satuan'] ?? 'pcs' }}</span>
                                                </div>
                                            @else
                                                <!-- Row Product Search Dropdown -->
                                                <div class="relative" x-data="{ open: false, query: '', results: [] }" @click.away="open = false">
                                                    <div class="relative">
                                                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant/40 text-xs">search</span>
                                                        <input type="text" 
                                                               x-model="query"
                                                               @input.debounce.300ms="
                                                                   if (query.trim().length >= 2) {
                                                                       $wire.getProductsForSearch(query).then(res => { results = res; open = true; });
                                                                   } else {
                                                                       results = [];
                                                                       open = false;
                                                                   }
                                                               "
                                                               @focus="if (results.length > 0) open = true"
                                                               placeholder="Ketik nama / SKU barang..." 
                                                               class="w-full bg-surface-container-low border border-primary/40 rounded-xl py-1.5 pl-7 pr-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/40 font-medium">
                                                    </div>
                                                    
                                                    <!-- Results Dropdown -->
                                                    <div x-show="open && results.length > 0" 
                                                         x-cloak 
                                                         class="absolute left-0 right-0 mt-1 bg-neutral-900 border border-white/10 rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto custom-scrollbar p-1 space-y-1">
                                                        <template x-for="p in results" :key="p.id">
                                                            <button type="button" 
                                                                    @click="$wire.selectProductForRow({{ $index }}, p.id); query = ''; open = false;" 
                                                                    class="w-full p-2 rounded-lg hover:bg-white/5 flex items-center justify-between text-left transition-all">
                                                                <div class="min-w-0 flex-1 pr-2">
                                                                    <p class="text-xs font-bold text-on-surface truncate" x-text="p.nama_barang"></p>
                                                                    <p class="text-[9px] text-on-surface-variant/60 font-mono" x-text="p.kode_barang"></p>
                                                                </div>
                                                                <div class="text-right shrink-0">
                                                                    <span class="text-xs font-mono font-bold text-primary" x-text="'Rp' + new Intl.NumberFormat('id-ID').format(p.hpp)"></span>
                                                                </div>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            @endif
                                            @error("items.{$index}.produk_id") <span class="text-[9px] text-error block mt-0.5">{{ $message }}</span> @enderror
                                        </td>

                                        <!-- Kuantitas Input -->
                                        <td class="px-3 py-3 text-center">
                                            <div class="relative flex items-center justify-center">
                                                <input type="number" 
                                                       min="1" 
                                                       wire:model.live.debounce.300ms="items.{{ $index }}.jumlah" 
                                                       class="w-20 bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-2 text-xs text-center font-bold font-mono text-on-surface focus:ring-1 focus:ring-primary focus:border-primary">
                                            </div>
                                            @error("items.{$index}.jumlah") <span class="text-[9px] text-error block mt-0.5">{{ $message }}</span> @enderror
                                        </td>

                                        <!-- Estimasi HPP Input -->
                                        <td class="px-3 py-3 text-right">
                                            <div class="relative">
                                                <input type="number" 
                                                       min="0" 
                                                       wire:model.live.debounce.300ms="items.{{ $index }}.hpp" 
                                                       class="w-32 bg-surface-container-low border border-white/10 rounded-xl py-1.5 px-2.5 text-xs text-right font-mono font-semibold text-on-surface focus:ring-1 focus:ring-primary focus:border-primary ml-auto">
                                            </div>
                                            @error("items.{$index}.hpp") <span class="text-[9px] text-error block mt-0.5">{{ $message }}</span> @enderror
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-3 py-3 text-right font-mono font-bold text-primary">
                                            Rp{{ number_format(((int)($item['jumlah'] ?? 0)) * ((float)($item['hpp'] ?? 0)), 0, ',', '.') }}
                                        </td>

                                        <!-- Hapus Row -->
                                        <td class="px-3 py-3 text-center">
                                            <button type="button" 
                                                    wire:click="removeItem({{ $index }})" 
                                                    class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" 
                                                    title="Hapus Barang">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 border border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center text-center space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-3xl">inventory_2</span>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-on-surface">Belum ada barang dalam daftar pesanan PO</p>
                            <p class="text-xs text-on-surface-variant/60 max-w-sm">Pilih produk langsung dari katalog master stok atau tambahkan baris secara manual.</p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                            <button type="button" 
                                    wire:click="openCatalogModal" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-xs shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                                <span class="material-symbols-outlined text-sm">menu_book</span>
                                Pilih dari Katalog Produk
                            </button>
                            <button type="button" 
                                    wire:click="addItemRow" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/5 border border-white/10 text-on-surface hover:bg-white/10 rounded-2xl font-bold text-xs transition-all active:scale-95">
                                <span class="material-symbols-outlined text-sm">add_circle</span>
                                Tambah Baris Manual
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Katalog Produk (Multi-Select) -->
    @if($isCatalogModalOpen)
        <div x-data class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-md">
            <div class="glass-card bg-surface-dark border border-white/10 rounded-3xl w-full max-w-4xl shadow-2xl flex flex-col max-h-[85vh] overflow-hidden"
                 @click.away="$wire.closeCatalogModal()">
                
                <!-- Modal Header -->
                <div class="p-6 border-b border-white/10 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">menu_book</span>
                            Katalog Produk & Master Stok
                        </h3>
                        <p class="text-xs text-on-surface-variant/70 mt-0.5">Centang satu atau beberapa produk untuk dimasukkan ke pesanan PO sekaligus.</p>
                    </div>
                    <button type="button" wire:click="closeCatalogModal" class="w-8 h-8 rounded-xl hover:bg-white/10 text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>

                <!-- Modal Filter & Search Bar -->
                <div class="p-4 bg-white/[0.02] border-b border-white/5 flex flex-col sm:flex-row gap-3 items-center">
                    <div class="relative flex-1 w-full">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">search</span>
                        <input wire:model.live.debounce.300ms="catalogSearch" 
                               type="text" 
                               placeholder="Cari berdasarkan nama produk, SKU, atau variasi..." 
                               class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 pl-10 pr-4 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    <div class="w-full sm:w-56 shrink-0">
                        <select wire:model.live="catalogCategory" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2.5 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Semua Kategori</option>
                            @foreach ($catalogCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Product List Table -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                    @if(count($catalogProducts) > 0)
                        <div class="overflow-x-auto border border-white/5 rounded-2xl">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-white/5 text-[10px] font-bold tracking-wider text-on-surface-variant/70 uppercase bg-white/[0.02]">
                                        <th class="p-3 w-10 text-center">Pilih</th>
                                        <th class="p-3">Nama Produk</th>
                                        <th class="p-3">SKU</th>
                                        <th class="p-3">Kategori</th>
                                        <th class="p-3 text-center">Stok</th>
                                        <th class="p-3 text-right">Estimasi HPP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @foreach($catalogProducts as $prod)
                                        <tr wire:key="catalog-p-{{ $prod->id }}" class="hover:bg-white/5 transition-colors cursor-pointer" onclick="this.querySelector('input[type=checkbox]').click()">
                                            <td class="p-3 text-center" onclick="event.stopPropagation()">
                                                <input type="checkbox" 
                                                       wire:model="selectedCatalogProducts" 
                                                       value="{{ $prod->id }}" 
                                                       class="rounded-lg border-white/20 bg-surface-container text-primary focus:ring-primary w-4 h-4 cursor-pointer">
                                            </td>
                                            <td class="p-3">
                                                <span class="font-bold text-on-surface">{{ $prod->nama_barang }}</span>
                                                @if($prod->variasi_barang)
                                                    <span class="text-[10px] text-on-surface-variant/60 block">{{ $prod->variasi_barang }}</span>
                                                @endif
                                            </td>
                                            <td class="p-3 font-mono font-bold text-primary/80">{{ $prod->kode_barang }}</td>
                                            <td class="p-3 text-on-surface-variant">{{ $prod->kategori ?: '-' }}</td>
                                            <td class="p-3 text-center font-mono">{{ $prod->total_stok }} {{ $prod->satuan }}</td>
                                            <td class="p-3 text-right font-mono font-bold text-on-surface">Rp{{ number_format($prod->hpp, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center text-on-surface-variant space-y-2">
                            <span class="material-symbols-outlined text-4xl text-white/20">search_off</span>
                            <p class="text-xs font-bold text-on-surface">Tidak ada produk yang sesuai kriteria pencarian</p>
                            <p class="text-[11px] text-on-surface-variant/60">Coba kata kunci pencarian lain atau pilih kategori berbeda.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-white/10 bg-white/[0.02] flex items-center justify-between">
                    <span class="text-xs text-on-surface-variant font-medium">
                        <strong class="text-primary font-bold" x-text="($wire.selectedCatalogProducts || []).length">{{ count($selectedCatalogProducts) }}</strong> produk dipilih
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeCatalogModal" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-semibold hover:bg-white/10 text-on-surface">
                            Batal
                        </button>
                        {{-- Counter & disabled dibaca dari state klien ($wire) karena wire:model bersifat deferred di Livewire 3 --}}
                        <button type="button" 
                                wire:click="addSelectedCatalogProducts" 
                                wire:loading.attr="disabled"
                                wire:target="addSelectedCatalogProducts"
                                class="px-5 py-2 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-xl font-bold text-xs shadow-lg hover:shadow-primary/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                                x-bind:disabled="($wire.selectedCatalogProducts || []).length === 0">
                            Tambahkan (<span x-text="($wire.selectedCatalogProducts || []).length">{{ count($selectedCatalogProducts) }}</span>) ke PO
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- Web Audio Synthesizer for Scanners -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

            Livewire.on('play-beep', () => {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1200, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.1);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.1);
            });

            Livewire.on('play-error-buzzer', () => {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(200, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
            });

            Livewire.on('focus-barcode', () => {
                const el = document.getElementById('po-barcode-scan-input');
                if (el) el.focus();
            });
        });
    </script>
</div>
