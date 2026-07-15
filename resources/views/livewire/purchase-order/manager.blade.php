<div x-init="$dispatch('update-header-title', 'Purchase Order (PO)')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    <template x-teleport="#header-search-placeholder">
        <div class="hidden md:flex items-center bg-surface-container-low rounded-full px-4 py-1.5 border border-white/5 ml-4">
            <span class="material-symbols-outlined text-on-surface-variant text-sm mr-2">search</span>
            <input wire:model.live.debounce.300ms="search" class="bg-transparent border-none text-sm focus:ring-0 text-on-surface w-64 placeholder:text-on-surface-variant/50" placeholder="Cari vendor, artikel, atau produk..." type="text"/>
        </div>
    </template>

    <!-- Tab Bar -->
    <div class="glass-card rounded-3xl p-2 mb-6 border border-white/5 flex gap-2">
        <button wire:click="switchTab('po_list')" 
                class="flex-1 py-3 px-4 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ $activeTab === 'po_list' ? 'bg-primary text-on-primary-fixed shadow-md active-nav-shadow font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            Daftar & Buat PO (PO A)
        </button>
        <button wire:click="switchTab('po_tracking')" 
                class="flex-1 py-3 px-4 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ $activeTab === 'po_tracking' ? 'bg-primary text-on-primary-fixed shadow-md active-nav-shadow font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            Tracking Sisa PO (PO B)
        </button>
        <button wire:click="switchTab('po_hpp_recap')" 
                class="flex-1 py-3 px-4 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all duration-200 {{ $activeTab === 'po_hpp_recap' ? 'bg-primary text-on-primary-fixed shadow-md active-nav-shadow font-black' : 'text-on-surface-variant/70 hover:text-on-surface hover:bg-white/5' }}">
            Rekap HPP Sisa PO (PO C)
        </button>
    </div>

    <!-- Alert Toast -->
    

    

    <!-- Controls Row & Actions -->
    <section class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        <!-- Mobile Search Bar (Only visible on screens < md) -->
        <div class="md:hidden relative w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Cari vendor, artikel, atau produk..." type="text"/>
        </div>

        <div class="flex-1"></div>

        <div class="flex flex-col sm:flex-row gap-3">
            @if ($activeTab === 'po_hpp_recap')
                <button wire:click="exportExcel" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white/5 border border-white/10 text-on-surface rounded-2xl font-bold text-sm shadow-xl hover:bg-white/10 transition-all active:scale-95 duration-200">
                    <span class="material-symbols-outlined text-sm">download</span>
                    Ekspor Rekap HPP
                </button>
            @endif
            @if ($activeTab === 'po_list')
                @can('manage po')
                    <button wire:click="openModal" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                        <span class="material-symbols-outlined">add</span>
                        Buat Purchase Order
                    </button>
                @endcan
            @endif
        </div>
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <!-- Tab 1: Daftar PO (PO A) -->
        @if ($activeTab === 'po_list')
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="px-6 py-5">Tanggal PO</th>
                            <th class="px-6 py-5">Supplier / Vendor</th>
                            <th class="px-6 py-5">Artikel</th>
                            <th class="px-6 py-5 text-center">Total Item</th>
                            <th class="px-6 py-5">Detail Item</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                        @forelse ($dataList as $po)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-on-surface">{{ $po->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ $po->supplier->nama_supplier }}</td>
                                <td class="px-6 py-4 font-mono text-primary/80 font-bold">{{ $po->artikel ?? '-' }}</td>
                                <td class="px-6 py-4 text-center font-black text-on-surface text-sm">{{ number_format($po->jumlah) }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($po->poDetails as $detail)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-white/5 border border-white/5 text-on-surface-variant">
                                                {{ $detail->produk->nama_barang }} ({{ number_format($detail->jumlah) }} unit)
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada data Purchase Order ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- Tab 2: Tracking Sisa PO (PO B) -->
        @elseif ($activeTab === 'po_tracking')
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="px-6 py-5">Tanggal PO</th>
                            <th class="px-6 py-5">Supplier / Vendor</th>
                            <th class="px-6 py-5">Artikel</th>
                            <th class="px-6 py-5">Produk</th>
                            <th class="px-6 py-5 text-center">Jumlah Awal</th>
                            <th class="px-6 py-5 text-center">Barang Masuk</th>
                            <th class="px-6 py-5 text-center">Sisa PO</th>
                            <th class="px-6 py-5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                        @forelse ($dataList as $row)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-on-surface">{{ $row->poHeader->tanggal->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ $row->poHeader->supplier->nama_supplier }}</td>
                                <td class="px-6 py-4 font-mono text-primary/80 font-bold">{{ $row->poHeader->artikel ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-on-surface">{{ $row->produk->nama_barang }}</div>
                                    <div class="text-[9px] text-primary/80 font-mono">{{ $row->produk->kode_barang }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold">{{ number_format($row->jumlah) }}</td>
                                <td class="px-6 py-4 text-center font-bold text-green-400">{{ number_format($row->barang_masuk) }}</td>
                                <td class="px-6 py-4 text-center font-black {{ $row->sisa > 0 ? 'text-primary' : 'text-on-surface-variant/30' }}">{{ number_format($row->sisa) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($row->sisa === 0)
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border border-green-500/20 bg-green-500/10 text-green-400 uppercase tracking-wider">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border border-primary/20 bg-primary/10 text-primary uppercase tracking-wider">
                                            Outstanding
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada tracking PO ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- Tab 3: Rekap HPP Sisa PO (PO C) -->
        @elseif ($activeTab === 'po_hpp_recap')
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                            <th class="px-6 py-5">Supplier / Vendor</th>
                            <th class="px-6 py-5">Artikel</th>
                            <th class="px-6 py-5">Produk</th>
                            <th class="px-6 py-5 text-center">Kuantitas PO</th>
                            <th class="px-6 py-5 text-center">Barang Masuk</th>
                            <th class="px-6 py-5 text-center">Sisa PO</th>
                            <th class="px-6 py-5 text-right">HPP PO</th>
                            <th class="px-6 py-5 text-right">Total Nilai HPP Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant">
                        @php $sumHppSisa = 0; @endphp
                        @forelse ($dataList as $row)
                            @php $sumHppSisa += ($row->sisa * $row->hpp); @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-on-surface font-semibold">{{ $row->poHeader->supplier->nama_supplier }}</td>
                                <td class="px-6 py-4 font-mono text-primary/80 font-bold">{{ $row->poHeader->artikel ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-on-surface">{{ $row->produk->nama_barang }}</div>
                                    <div class="text-[9px] text-primary/80 font-mono">{{ $row->produk->kode_barang }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">{{ number_format($row->jumlah) }}</td>
                                <td class="px-6 py-4 text-center text-green-400">{{ number_format($row->barang_masuk) }}</td>
                                <td class="px-6 py-4 text-center font-bold text-on-surface">{{ number_format($row->sisa) }}</td>
                                <td class="px-6 py-4 text-right">Rp{{ number_format($row->hpp, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-black text-primary">Rp{{ number_format($row->sisa * $row->hpp, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada rekap HPP sisa PO</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($dataList) > 0)
                        <tfoot class="bg-white/[0.02] font-black border-t border-white/10">
                            <tr>
                                <td colspan="7" class="px-6 py-5 text-right text-xs uppercase tracking-wider text-on-surface-variant/80">Total HPP Outstanding:</td>
                                <td class="px-6 py-5 text-right text-sm text-primary font-black">Rp{{ number_format($sumHppSisa, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $dataList->firstItem() ?? 0 }} s.d. {{ $dataList->lastItem() ?? 0 }} dari {{ $dataList->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($dataList->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($dataList->getUrlRange(max(1, $dataList->currentPage() - 2), min($dataList->lastPage(), $dataList->currentPage() + 2)) as $page => $url)
                    @if ($page == $dataList->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($dataList->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>

    <!-- Create PO Modal -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-background/80 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-2xl mx-auto my-6 z-50">
                <div class="relative flex flex-col w-full glass-card rounded-3xl shadow-2xl border border-white/10 outline-none focus:outline-none overflow-hidden bg-surface-dark/95">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-white/5">
                        <h3 class="text-base font-bold text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined">assignment</span>
                            Terbitkan Purchase Order Baru
                        </h3>
                        <button wire:click="closeModal" class="text-on-surface-variant hover:text-on-surface">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto custom-scrollbar">
                        <!-- Tanggal & Supplier -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Tanggal PO</label>
                                <input wire:model="tanggal" type="date" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                @error('tanggal') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Supplier / Vendor</label>
                                <select wire:model="supplier_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($suppliersList as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_supplier }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Artikel -->
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant/80 uppercase tracking-wider mb-2">Nomor Artikel PO / Keterangan PO</label>
                            <input wire:model="artikel" type="text" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/30" placeholder="Contoh: ART-JULY-SNDL">
                            @error('artikel') <span class="text-xs text-error mt-1.5 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Dynamic Items Subform -->
                        <div class="border border-white/5 rounded-2xl p-4 bg-white/[0.02] space-y-4">
                            <div class="flex items-center justify-between border-b border-white/5 pb-2">
                                <h4 class="text-xs font-black text-primary uppercase tracking-wider">Daftar Barang Pesanan</h4>
                                <button type="button" wire:click="addPoItemRow" class="inline-flex items-center px-4 py-1.5 bg-white/5 border border-white/10 text-xs font-bold rounded-2xl hover:bg-white/10 transition-all text-on-surface shadow-sm">
                                    + Tambah Baris
                                </button>
                            </div>

                            <!-- List of Item Rows -->
                            <div class="space-y-4">
                                @foreach ($poItems as $index => $item)
                                    <div class="grid grid-cols-12 gap-3 items-start" wire:key="po-item-row-{{ $index }}">
                                        <!-- Product select (col-5) -->
                                        <div class="col-span-12 md:col-span-5">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Produk</label>
                                            <select wire:model="poItems.{{ $index }}.produk_id" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                                <option value="">Pilih Produk</option>
                                                @foreach ($productsList as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nama_barang }} ({{ $p->variasi_barang }})</option>
                                                @endforeach
                                            </select>
                                            @error("poItems.{$index}.produk_id") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Quantity (col-3) -->
                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">QTY</label>
                                            <input wire:model="poItems.{{ $index }}.jumlah" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3 text-xs text-center text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold" placeholder="Qty">
                                            @error("poItems.{$index}.jumlah") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Price / HPP (col-3) -->
                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-[10px] font-bold text-on-surface-variant/80 uppercase tracking-wider mb-1">Harga HPP PO</label>
                                            <input wire:model="poItems.{{ $index }}.hpp" type="number" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-2 px-3 text-xs text-right text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all font-semibold" placeholder="Harga per pcs">
                                            @error("poItems.{$index}.hpp") <span class="text-[10px] text-error mt-1 block font-semibold">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Delete row (col-1) -->
                                        <div class="col-span-12 md:col-span-1 text-center md:pt-6">
                                            <button type="button" wire:click="removePoItemRow({{ $index }})" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-error/20 text-error transition-all" title="Hapus Baris">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end px-6 py-5 border-t border-white/5 bg-surface-container-low/50 space-x-3">
                        <button wire:click="closeModal" type="button" class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-2xl text-sm font-medium hover:bg-white/10 transition-all text-on-surface">
                            Batal
                        </button>
                        <button wire:click="store" type="button" class="px-6 py-2.5 bg-gradient-to-r from-primary-container to-[#ff9f00] text-on-primary-container rounded-2xl font-bold text-sm shadow-xl hover:shadow-primary/20 transition-all active:scale-95 duration-200 amber-glow-strong">
                            Terbitkan PO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
