<div x-init="$dispatch('update-header-title', 'Replenishment Toko')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-on-surface">Rekomendasi Pengisian Stok Toko</h2>
            <p class="text-xs text-on-surface-variant mt-1">Produk yang stok tokonya di bawah batas minimum akan ditampilkan di sini.</p>
        </div>
        <select wire:model.live="selectedTokoId" class="px-4 py-3 rounded-2xl bg-surface-container border border-white/10 text-on-surface text-sm focus:outline-none focus:ring-2 focus:ring-primary/40 min-w-[200px]">
            <option value="">Semua Toko</option>
            @foreach($tokoLocations as $toko)
                <option value="{{ $toko->id }}">{{ $toko->kode_lokasi }} — {{ $toko->nama_lokasi }}</option>
            @endforeach
        </select>
    </div>

    {{-- Recommendations Table --}}
    <div class="glass-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-[10px] font-bold tracking-[0.15em] text-on-surface-variant/60 uppercase">
                        <th class="py-4 px-4 w-10">
                            <span class="material-symbols-outlined text-sm text-on-surface-variant/40">check_box_outline_blank</span>
                        </th>
                        <th class="py-4 px-4">Produk</th>
                        <th class="py-4 px-4">Lokasi Toko</th>
                        <th class="py-4 px-4 text-center">Stok Toko</th>
                        <th class="py-4 px-4 text-center">Min Stock</th>
                        <th class="py-4 px-4 text-center">Max Stock</th>
                        <th class="py-4 px-4 text-center">Stok Gudang</th>
                        <th class="py-4 px-4 text-center">Rekomendasi Kirim</th>
                        <th class="py-4 px-4 text-center">Urgensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recommendations as $rec)
                        @php
                            $key = $rec->produk_id . '-' . $rec->lokasi_toko_id;
                            $isSelected = isset($selectedItems[$key]);
                            $urgencyColors = [
                                'KRITIS' => 'bg-red-500/10 text-red-400 border-red-500/20 animate-pulse',
                                'TINGGI' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'SEDANG' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                            ];
                        @endphp
                        <tr class="hover:bg-white/5 transition-colors text-on-surface-variant cursor-pointer {{ $isSelected ? 'bg-primary/5' : '' }}"
                            wire:click="toggleItem({{ $rec->produk_id }}, {{ $rec->lokasi_toko_id }}, {{ $rec->feasible_qty }})">
                            <td class="py-3.5 px-4">
                                @if($isSelected)
                                    <span class="material-symbols-outlined text-primary text-lg">check_box</span>
                                @else
                                    <span class="material-symbols-outlined text-on-surface-variant/30 text-lg">check_box_outline_blank</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-on-surface">{{ $rec->produk->nama_barang }}</div>
                                <div class="text-[10px] text-primary/80 font-mono">{{ $rec->produk->kode_barang }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] bg-green-500/10 text-green-400 border border-green-500/20 font-bold">{{ $rec->lokasi_toko->kode_lokasi }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-red-400">{{ $rec->current_stock }}</td>
                            <td class="py-3.5 px-4 text-center text-on-surface">{{ $rec->minimal_stock }}</td>
                            <td class="py-3.5 px-4 text-center text-on-surface">{{ $rec->maximum_stock }}</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $rec->gudang_stock_available > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $rec->gudang_stock_available }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-primary/10 text-primary border border-primary/20">+{{ $rec->feasible_qty }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border {{ $urgencyColors[$rec->urgency] ?? '' }}">{{ $rec->urgency }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-green-500/30 block mb-2">verified</span>
                                Semua stok toko dalam kondisi aman. Tidak ada rekomendasi replenishment saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Action Bar --}}
    @if($recommendations->isNotEmpty())
        <div class="flex items-center justify-between glass-card rounded-3xl p-4 border border-white/10">
            <p class="text-sm text-on-surface-variant">
                <span class="font-bold text-primary">{{ count($selectedItems) }}</span> produk dipilih untuk di-replenish.
            </p>
            <button wire:click="createTransferOrder"
                    @if(empty($selectedItems)) disabled @endif
                    class="flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold bg-primary text-on-primary hover:bg-primary/90 transition-colors shadow-lg disabled:opacity-30 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-lg">send</span>
                Buat Transfer Order
            </button>
        </div>
    @endif
</div>
