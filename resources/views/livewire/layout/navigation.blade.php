<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div x-data="{ open: false }" class="lg:w-64 flex-shrink-0">
    <!-- Mobile Header (Only visible on screens < lg) -->
    <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-surface-dark border-b border-white/10 z-40 flex justify-between items-center px-6">
        <h1 class="font-label-caps text-sm tracking-[0.2em] text-primary">LAF PROJECT</h1>
        <button @click="open = true" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/5 transition-colors">
            <span class="material-symbols-outlined text-on-surface">menu</span>
        </button>
    </header>
    
    <!-- Spacer for Mobile Header so content below doesn't get covered -->
    <div class="h-16 lg:hidden"></div>

    <!-- Mobile Drawer Sidebar (Overlay) -->
    <div x-show="open" class="fixed inset-0 z-50 lg:hidden flex" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="open = false"></div>
        
        <!-- Drawer Content -->
        <aside class="relative flex flex-col p-stack-md bg-surface-dark w-64 h-full border-r border-white/10 shadow-2xl shadow-accent-glow">
            <!-- Close Button -->
            <button @click="open = false" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
            
            <div class="mb-10 px-4 py-2">
                <h1 class="font-label-caps text-label-caps tracking-[0.4em] text-primary">LAF PROJECT</h1>
            </div>
            
            <!-- Side Navigation Menu Links (Indonesian) -->
            <nav class="flex-1 space-y-2 overflow-y-auto custom-scrollbar px-2">
                <!-- Dashboard -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('dashboard') }}" wire:navigate>
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <!-- Stock Master -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('stock-master*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('stock-master') }}" wire:navigate>
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                    <span>Master Stok</span>
                </a>
                <!-- Supplier -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('supplier*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('supplier') }}" wire:navigate>
                    <span class="material-symbols-outlined">conveyor_belt</span>
                    <span>Supplier</span>
                </a>
                <!-- Customers (Pelanggan) -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('pelanggan*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('pelanggan') }}" wire:navigate>
                    <span class="material-symbols-outlined">group</span>
                    <span>Pelanggan</span>
                </a>
                <!-- Incoming Goods (Barang Masuk) -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('barang-masuk*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('barang-masuk') }}" wire:navigate>
                    <span class="material-symbols-outlined">input</span>
                    <span>Barang Masuk</span>
                </a>
                <!-- Outgoing Goods (Barang Keluar) -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('barang-keluar*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('barang-keluar') }}" wire:navigate>
                    <span class="material-symbols-outlined">output</span>
                    <span>Barang Keluar</span>
                </a>
                <!-- Purchase Orders -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('purchase-order*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('purchase-order') }}" wire:navigate>
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span>Purchase Order</span>
                </a>
                <!-- Analytics (Forecast & Analisis) -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('analytics*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('analytics') }}" wire:navigate>
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Forecast & Analisis</span>
                </a>

                <!-- Divider: Warehouse Operations -->
                <div class="pt-4 pb-1 px-4">
                    <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Operasi Gudang</p>
                </div>

                <!-- Transfer Gudang -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('transfer*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('transfer') }}" wire:navigate>
                    <span class="material-symbols-outlined">swap_horiz</span>
                    <span>Transfer Gudang</span>
                </a>
                <!-- Replenishment Toko -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('replenishment*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('replenishment') }}" wire:navigate>
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span>Replenishment</span>
                </a>
                <!-- Picking -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('picking*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('picking') }}" wire:navigate>
                    <span class="material-symbols-outlined">barcode_scanner</span>
                    <span>Picking Barang</span>
                </a>
                <!-- Packing -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('packing*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('packing') }}" wire:navigate>
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>Packing & QC</span>
                </a>
                <!-- Lokasi -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('lokasi*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('lokasi') }}" wire:navigate>
                    <span class="material-symbols-outlined">location_on</span>
                    <span>Manajemen Lokasi</span>
                </a>
                <!-- Audit Stok -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('audit*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('audit') }}" wire:navigate>
                    <span class="material-symbols-outlined">fact_check</span>
                    <span>Audit Stok</span>
                </a>

                <!-- Divider: Reporting -->
                <div class="pt-4 pb-1 px-4">
                    <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Laporan</p>
                </div>

                <!-- Reports -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('reports*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('reports') }}" wire:navigate>
                    <span class="material-symbols-outlined">summarize</span>
                    <span>Laporan & Analitik</span>
                </a>
                @can('manage master')
                <!-- Log Perubahan -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('audit-log*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('audit-log') }}" wire:navigate>
                    <span class="material-symbols-outlined">history</span>
                    <span>Log Perubahan</span>
                </a>
                @endcan
                <!-- Pengkodean -->
                <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('pengkodean*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('pengkodean') }}" wire:navigate>
                    <span class="material-symbols-outlined">qr_code</span>
                    <span>Aturan Pengkodean</span>
                </a>
            </nav>
            
            <div class="mt-auto pt-6 border-t border-white/5 space-y-4">
                @if(auth()->check())
                    <div class="flex items-center gap-3 px-4 py-4 mb-2">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center border border-primary/30">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-on-surface">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-primary tracking-widest uppercase">
                                @php
                                    $firstRole = auth()->user()->roles->first();
                                    $roleName = $firstRole ? $firstRole->name : null;
                                @endphp
                                {{ $roleName === 'super_admin' ? 'SUPER ADMIN' : ($roleName === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper($roleName ?? 'TAMU')) }}
                            </p>
                        </div>
                    </div>
                @endif
                <a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-on-surface transition-all" href="#">
                    <span class="material-symbols-outlined">help</span>
                    <span>Bantuan</span>
                </a>
                @if(auth()->check())
                    <button wire:click="logout" class="w-full flex items-center gap-3 px-4 py-2 text-error hover:text-error/80 transition-all text-left">
                        <span class="material-symbols-outlined">logout</span>
                        <span>Keluar</span>
                    </button>
                @endif
            </div>
        </aside>
    </div>

    <!-- Desktop Sidebar (Always visible on screens >= lg) -->
    <aside class="hidden lg:flex lg:flex-col p-stack-md bg-surface-dark w-64 border-r border-white/10 shadow-2xl shadow-accent-glow h-screen sticky top-0 z-40">
        <div class="mb-10 px-4 py-6">
            <h1 class="font-label-caps text-label-caps tracking-[0.4em] text-primary">LAF PROJECT</h1>
        </div>
        
        <nav class="flex-1 space-y-2 overflow-y-auto custom-scrollbar px-2">
            <!-- Dashboard -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('dashboard') }}" wire:navigate>
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <!-- Stock Master -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('stock-master*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('stock-master') }}" wire:navigate>
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                <span>Master Stok</span>
            </a>
            <!-- Supplier -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('supplier*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('supplier') }}" wire:navigate>
                <span class="material-symbols-outlined">conveyor_belt</span>
                <span>Supplier</span>
            </a>
            <!-- Customers (Pelanggan) -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('pelanggan*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('pelanggan') }}" wire:navigate>
                <span class="material-symbols-outlined">group</span>
                <span>Pelanggan</span>
            </a>
            <!-- Incoming Goods (Barang Masuk) -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('barang-masuk*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('barang-masuk') }}" wire:navigate>
                <span class="material-symbols-outlined">input</span>
                <span>Barang Masuk</span>
            </a>
            <!-- Outgoing Goods (Barang Keluar) -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('barang-keluar*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('barang-keluar') }}" wire:navigate>
                <span class="material-symbols-outlined">output</span>
                <span>Barang Keluar</span>
            </a>
            <!-- Purchase Orders -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('purchase-order*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('purchase-order') }}" wire:navigate>
                <span class="material-symbols-outlined">shopping_cart</span>
                <span>Purchase Order</span>
            </a>
            <!-- Analytics (Forecast & Analisis) -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('analytics*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('analytics') }}" wire:navigate>
                <span class="material-symbols-outlined">analytics</span>
                <span>Forecast & Analisis</span>
            </a>

            <!-- Divider: Warehouse Operations -->
            <div class="pt-4 pb-1 px-4">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Operasi Gudang</p>
            </div>

            <!-- Transfer Gudang -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('transfer*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('transfer') }}" wire:navigate>
                <span class="material-symbols-outlined">swap_horiz</span>
                <span>Transfer Gudang</span>
            </a>
            <!-- Replenishment Toko -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('replenishment*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('replenishment') }}" wire:navigate>
                <span class="material-symbols-outlined">local_shipping</span>
                <span>Replenishment</span>
            </a>
            <!-- Picking -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('picking*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('picking') }}" wire:navigate>
                <span class="material-symbols-outlined">barcode_scanner</span>
                <span>Picking Barang</span>
            </a>
            <!-- Packing -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('packing*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('packing') }}" wire:navigate>
                <span class="material-symbols-outlined">inventory_2</span>
                <span>Packing & QC</span>
            </a>
            <!-- Lokasi -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('lokasi*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('lokasi') }}" wire:navigate>
                <span class="material-symbols-outlined">location_on</span>
                <span>Manajemen Lokasi</span>
            </a>
            <!-- Audit Stok -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('audit*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('audit') }}" wire:navigate>
                <span class="material-symbols-outlined">fact_check</span>
                <span>Audit Stok</span>
            </a>

            <!-- Divider: Reporting -->
            <div class="pt-4 pb-1 px-4">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Laporan</p>
            </div>

            <!-- Reports -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('reports*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('reports') }}" wire:navigate>
                <span class="material-symbols-outlined">summarize</span>
                <span>Laporan & Analitik</span>
            </a>
            @can('manage master')
            <!-- Log Perubahan -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('audit-log*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('audit-log') }}" wire:navigate>
                <span class="material-symbols-outlined">history</span>
                <span>Log Perubahan</span>
            </a>
            @endcan
            <!-- Pengkodean -->
            <a class="flex items-center gap-3 px-4 py-3 rounded-xl active:scale-[0.98] transition-all duration-200 {{ request()->routeIs('pengkodean*') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium' }}" href="{{ route('pengkodean') }}" wire:navigate>
                <span class="material-symbols-outlined">qr_code</span>
                <span>Aturan Pengkodean</span>
            </a>
        </nav>
        
        <div class="mt-auto pt-6 border-t border-white/5 space-y-4">
            @if(auth()->check())
                <div class="flex items-center gap-3 px-4 py-4 mb-2">
                    <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center border border-primary/30">
                        <span class="material-symbols-outlined text-primary">person</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-on-surface">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-primary tracking-widest uppercase">
                            @php
                                $firstRole = auth()->user()->roles->first();
                                $roleName = $firstRole ? $firstRole->name : null;
                            @endphp
                            {{ $roleName === 'super_admin' ? 'SUPER ADMIN' : ($roleName === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper($roleName ?? 'TAMU')) }}
                        </p>
                    </div>
                </div>
            @endif
            <a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-on-surface transition-all" href="#">
                <span class="material-symbols-outlined">help</span>
                <span>Bantuan</span>
            </a>
            @if(auth()->check())
                <button wire:click="logout" class="w-full flex items-center gap-3 px-4 py-2 text-error hover:text-error/80 transition-all text-left">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Keluar</span>
                </button>
            @endif
        </div>
    </aside>
</div>
