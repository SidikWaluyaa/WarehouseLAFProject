<div x-init="$dispatch('update-header-title', 'Log Perubahan & Riwayat Aktivitas')" class="flex-1 overflow-y-auto p-6 lg:p-margin-desktop space-y-stack-lg custom-scrollbar">
    
    <!-- Actions Bar & Filters -->
    <section class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full sm:w-80">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface focus:ring-1 focus:ring-primary focus:border-primary transition-all placeholder:text-on-surface-variant/40" placeholder="Cari nama, IP, aksi, entitas..." type="text"/>
            </div>

            <!-- Action Filter -->
            <div class="relative w-full sm:w-48">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">filter_alt</span>
                <select wire:model.live="actionFilter" class="w-full bg-surface-container-low border border-white/10 rounded-2xl py-3 pl-12 pr-4 text-sm text-on-surface appearance-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    <option value="">Semua Aksi</option>
                    <option value="CREATE">CREATE (Baru)</option>
                    <option value="UPDATE">UPDATE (Ubah)</option>
                    <option value="DELETE">DELETE (Hapus)</option>
                </select>
            </div>
            
            @if($search || $actionFilter)
                <button wire:click="clearFilters" class="px-4 py-2 text-xs font-semibold text-primary hover:underline self-center">
                    Reset Filter
                </button>
            @endif
        </div>
    </section>

    <!-- Data Table Container -->
    <section class="glass-card rounded-3xl overflow-hidden mb-8">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] font-bold tracking-[0.2em] text-on-surface-variant/60 uppercase">
                        <th class="px-6 py-5 w-10"></th>
                        <th class="px-6 py-5">Waktu</th>
                        <th class="px-6 py-5">Operator</th>
                        <th class="px-6 py-5">Aksi</th>
                        <th class="px-6 py-5">Entitas</th>
                        <th class="px-6 py-5">ID Ref</th>
                        <th class="px-6 py-5">Alamat IP</th>
                        <th class="px-6 py-5">User Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-xs text-on-surface-variant" x-data="{ expandedId: null }">
                    @forelse ($logs as $log)
                        <!-- Main Header Row -->
                        <tr class="hover:bg-white/5 cursor-pointer transition-colors" @click="expandedId = (expandedId === {{ $log->id }}) ? null : {{ $log->id }}">
                            <td class="px-6 py-4 text-center">
                                <span class="material-symbols-outlined text-sm text-on-surface-variant/60 transform transition-transform duration-200 block" :class="{ 'rotate-90 text-primary': expandedId === {{ $log->id }} }">
                                    chevron_right
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-on-surface">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-on-surface">{{ $log->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider
                                    {{ $log->action === 'CREATE' ? 'bg-green-500/10 text-green-400 border-green-500/20' : '' }}
                                    {{ $log->action === 'UPDATE' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : '' }}
                                    {{ $log->action === 'DELETE' ? 'bg-red-500/10 text-red-400 border-red-500/20' : '' }}
                                ">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-primary/80">{{ class_basename($log->auditable_type) }}</td>
                            <td class="px-6 py-4 font-mono font-bold">#{{ $log->auditable_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-on-surface">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-6 py-4 max-w-xs truncate text-[10px] text-on-surface-variant/70" title="{{ $log->user_agent }}">{{ \App\Models\AuditLog::parseUserAgent($log->user_agent) }}</td>
                        </tr>
                        
                        <!-- Detail Collapsible Row -->
                        <tr x-show="expandedId === {{ $log->id }}" x-cloak class="bg-white/[0.01]">
                            <td colspan="8" class="px-6 py-4">
                                <div class="glass-card rounded-2xl p-5 space-y-4 bg-surface-dark/40 border-white/5">
                                    <div class="text-xs font-black text-primary uppercase tracking-wider border-b border-white/5 pb-2">
                                        Perbandingan Perubahan Kolom & Nilai
                                    </div>
                                    
                                    <div class="text-xs space-y-2">
                                        @php
                                            $oldVal = $log->old_values;
                                            $newVal = $log->new_values;
                                            $diffs = [];

                                            if (is_array($oldVal) && is_array($newVal)) {
                                                // Check for header-detail structure
                                                if (isset($oldVal['header']) || isset($newVal['header'])) {
                                                    $oldH = $oldVal['header'] ?? [];
                                                    $newH = $newVal['header'] ?? [];
                                                    
                                                    $headerFields = [
                                                        'tanggal' => 'Tanggal',
                                                        'tipe_nota' => 'Tipe Nota',
                                                        'nomor_nota' => 'Nomor Nota',
                                                        'keterangan_tambahan' => 'Keterangan'
                                                    ];
                                                    foreach ($headerFields as $f => $lbl) {
                                                        $o = $oldH[$f] ?? '-';
                                                        $n = $newH[$f] ?? '-';
                                                        if ($o != $n) {
                                                            if ($f === 'tanggal') {
                                                                $o = date('d/m/Y', strtotime($o));
                                                                $n = date('d/m/Y', strtotime($n));
                                                            }
                                                            $diffs[] = "<strong>Header $lbl</strong>: <span class='line-through text-error'>$o</span> &rarr; <span class='text-green-500 font-bold'>$n</span>";
                                                        }
                                                    }

                                                    $oldD = $oldVal['details'] ?? [];
                                                    $newD = $newVal['details'] ?? [];
                                                    if (count($oldD) !== count($newD)) {
                                                        $diffs[] = "<strong>Jumlah Detail Item</strong>: <span class='line-through text-error'>" . count($oldD) . " item</span> &rarr; <span class='text-green-500 font-bold'>" . count($newD) . " item</span>";
                                                    } else {
                                                        $changed = false;
                                                        foreach ($newD as $i => $nDet) {
                                                            $oDet = $oldD[$i] ?? [];
                                                            if (($oDet['qty_masuk'] ?? 0) != ($nDet['qty_masuk'] ?? 0) || ($oDet['hpp'] ?? 0) != ($nDet['hpp'] ?? 0) || ($oDet['lokasi_simpan'] ?? '') != ($nDet['lokasi_simpan'] ?? '')) {
                                                                $changed = true;
                                                                break;
                                                            }
                                                        }
                                                        if ($changed) {
                                                            $diffs[] = "<strong>Rincian Item Detail</strong>: Perubahan terdeteksi pada kuantitas, harga, atau lokasi simpan.";
                                                        }
                                                    }
                                                } else {
                                                    // Flat model diff
                                                    $keys = array_unique(array_merge(array_keys($oldVal), array_keys($newVal)));
                                                    $ignored = ['created_at', 'updated_at', 'id', 'password', 'remember_token'];
                                                    foreach ($keys as $k) {
                                                        if (in_array($k, $ignored)) continue;
                                                        $o = $oldVal[$k] ?? '-';
                                                        $n = $newVal[$k] ?? '-';
                                                        if ($o != $n) {
                                                            $label = ucwords(str_replace('_', ' ', $k));
                                                            $diffs[] = "<strong>$label</strong>: <span class='line-through text-error'>$o</span> &rarr; <span class='text-green-500 font-bold'>$n</span>";
                                                        }
                                                    }
                                                }
                                            } elseif ($log->action === 'CREATE' && is_array($newVal)) {
                                                // Create details
                                                $keys = array_keys($newVal);
                                                $ignored = ['created_at', 'updated_at', 'id', 'password', 'remember_token'];
                                                foreach ($keys as $k) {
                                                    if (in_array($k, $ignored)) continue;
                                                    $n = $newVal[$k] ?? '-';
                                                    if (!empty($n) && $n !== '-') {
                                                        $label = ucwords(str_replace('_', ' ', $k));
                                                        $diffs[] = "<strong>$label</strong>: <span class='text-green-500 font-bold'>$n</span>";
                                                    }
                                                }
                                            } elseif ($log->action === 'DELETE' && is_array($oldVal)) {
                                                // Delete details
                                                if (isset($oldVal['header']) || isset($oldVal['details'])) {
                                                    $oldH = $oldVal['header'] ?? [];
                                                    $diffs[] = "<strong>Nomor Nota Dihapus</strong>: <span class='text-error font-bold'>" . ($oldH['nomor_nota'] ?? '-') . "</span>";
                                                    $diffs[] = "<strong>Tipe Transaksi</strong>: " . ($oldH['tipe_nota'] ?? '-');
                                                    $diffs[] = "<strong>Jumlah Item</strong>: " . count($oldVal['details'] ?? []) . " item";
                                                } else {
                                                    $keys = array_keys($oldVal);
                                                    $ignored = ['created_at', 'updated_at', 'id', 'password', 'remember_token'];
                                                    foreach ($keys as $k) {
                                                        if (in_array($k, $ignored)) continue;
                                                        $o = $oldVal[$k] ?? '-';
                                                        if (!empty($o) && $o !== '-') {
                                                            $label = ucwords(str_replace('_', ' ', $k));
                                                            $diffs[] = "<strong>$label</strong>: <span class='text-error font-bold'>$o</span>";
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @if (count($diffs) > 0)
                                            <ul class="list-disc pl-5 space-y-1 text-on-surface-variant">
                                                @foreach ($diffs as $diff)
                                                    <li>{!! $diff !!}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-on-surface-variant/50">Tidak ada detail kolom yang tercatat atau berubah.</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">Tidak ada log aktivitas sistem ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Styled Pagination -->
        <div class="px-6 py-4 border-t border-white/5 flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan {{ $logs->firstItem() ?? 0 }} s.d. {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} hasil
            </p>
            <div class="flex gap-2">
                @if ($logs->onFirstPage())
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Sebelumnya</button>
                @else
                    <button wire:click="previousPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Sebelumnya</button>
                @endif

                @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                    @if ($page == $logs->currentPage())
                        <button class="px-4 py-2 rounded-xl bg-primary-container text-on-primary-container text-xs font-bold">{{ $page }}</button>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">{{ $page }}</button>
                    @endif
                @endforeach

                @if ($logs->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface hover:bg-white/10 transition-all">Selanjutnya</button>
                @else
                    <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-on-surface-variant/50 cursor-not-allowed" disabled>Selanjutnya</button>
                @endif
            </div>
        </div>
    </section>
</div>
