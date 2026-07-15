<div x-data="{ 
         collapsed: false, 
         mobileOpen: false,
         currentPath: window.location.pathname,
         activeDropdown: null,
         isActive(path) {
             if (path === '/') {
                 return this.currentPath === '/' || this.currentPath.startsWith('/dashboard');
             }
             return this.currentPath.startsWith(path);
         },
         isDropdownActive(name) {
             if (name === 'master') {
                 return this.isActive('/stock-master') || this.isActive('/lokasi') || this.isActive('/pengkodean') || this.isActive('/supplier') || this.isActive('/pelanggan');
             }
             if (name === 'transaksi') {
                 return this.isActive('/barang-masuk') || this.isActive('/barang-keluar') || this.isActive('/purchase-order');
             }
             return false;
         },
         initDropdowns() {
             if (this.isDropdownActive('master')) {
                 this.activeDropdown = 'master';
             } else if (this.isDropdownActive('transaksi')) {
                 this.activeDropdown = 'transaksi';
             } else {
                 this.activeDropdown = null;
             }
         },
         toggleDropdown(name) {
             if (this.collapsed) {
                 this.collapsed = false;
                 this.activeDropdown = name;
             } else {
                 this.activeDropdown = this.activeDropdown === name ? null : name;
             }
         }
     }"
     x-init="
         collapsed = (window.safeStorage ? window.safeStorage.getItem('sidebar_collapsed') === 'true' : false);
         initDropdowns();
     "
     x-effect="
         document.body.style.overflow = mobileOpen ? 'hidden' : '';
         if (window.safeStorage) {
             window.safeStorage.setItem('sidebar_collapsed', collapsed);
         }
     "
     x-on:livewire:navigated.window="currentPath = window.location.pathname; mobileOpen = false; initDropdowns()"
     @toggle-sidebar.window="collapsed = !collapsed"
     @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
     @open-mobile-sidebar.window="mobileOpen = true"
     @close-mobile-sidebar.window="mobileOpen = false"
     class="flex-shrink-0 transition-all duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:self-start lg:z-40"
     :class="collapsed ? 'lg:w-20' : 'lg:w-64'">

    <!-- Mobile Drawer Backdrop -->
    <div x-show="mobileOpen"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 lg:hidden"
         @click="mobileOpen = false"
         style="display: none;"></div>

    <!-- Mobile Drawer Content -->
    <aside x-show="mobileOpen"
           x-transition:enter="transition transform ease-in-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform ease-in-out duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 w-64 bg-surface-dark border-r border-white/10 z-50 p-5 flex flex-col lg:hidden shadow-2xl shadow-accent-glow"
           style="display: none;">
        
        <!-- Mobile Header (Logo & Close) -->
        <div class="flex items-center justify-between mb-8 px-2">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" class="h-8 w-auto rounded-lg" alt="LAF PROJECT Logo">
                <span class="font-label-caps text-label-caps tracking-[0.2em] text-primary font-bold text-sm">
                    LAF PROJECT
                </span>
            </a>
            <button @click="mobileOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5 text-on-surface-variant hover:text-on-surface transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <nav class="flex-1 space-y-1.5 overflow-y-auto custom-scrollbar pr-1">
            <!-- Dashboard -->
            <a wire:key="sidebar-mobile-dashboard"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('dashboard') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>
            <!-- Master Data Group -->
            <div class="space-y-1">
                <button @click="toggleDropdown('master')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm font-medium focus:outline-none"
                        :class="isDropdownActive('master') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Master Data</span>
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200"
                         :class="activeDropdown === 'master' ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="activeDropdown === 'master'"
                     x-transition:enter="transition-all ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                     x-transition:enter-end="opacity-100 max-h-[400px] overflow-hidden"
                     x-transition:leave="transition-all ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-[400px] overflow-hidden"
                     x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                     class="pl-4 ml-6 border-l border-white/5 space-y-1">
                    <!-- Master Stok -->
                    <a wire:key="sidebar-mobile-stock-master"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/stock-master') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('stock-master') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Master Stok</span>
                    </a>
                    <!-- Lokasi -->
                    <a wire:key="sidebar-mobile-lokasi"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/lokasi') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('lokasi') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Manajemen Lokasi</span>
                    </a>
                    <!-- Pengkodean Barang -->
                    @can('manage master')
                    <a wire:key="sidebar-mobile-pengkodean"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/pengkodean') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('pengkodean') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 16h6M9 12h6" />
                        </svg>
                        <span>Aturan Pengkodean</span>
                    </a>
                    @endcan
                    <!-- Supplier -->
                    <a wire:key="sidebar-mobile-supplier"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/supplier') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('supplier') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        <span>Supplier</span>
                    </a>
                    <!-- Pelanggan -->
                    <a wire:key="sidebar-mobile-pelanggan"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/pelanggan') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('pelanggan') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Pelanggan</span>
                    </a>
                </div>
            </div>

            <!-- Audit Stok -->
            <a wire:key="sidebar-mobile-audit"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/audit') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('audit') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Audit Stok (Opname)</span>
            </a>

            <!-- Manajemen Pengguna & Akses -->
            @can('manage system')
            <a wire:key="sidebar-mobile-user-management"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/user-management') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('user-management') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Manajemen Akses</span>
            </a>
            @endcan

            <!-- Transaksi Gudang Group -->
            <div class="space-y-1">
                <button @click="toggleDropdown('transaksi')"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm font-medium focus:outline-none"
                        :class="isDropdownActive('transaksi') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5'">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Transaksi Gudang</span>
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200"
                         :class="activeDropdown === 'transaksi' ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="activeDropdown === 'transaksi'"
                     x-transition:enter="transition-all ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                     x-transition:enter-end="opacity-100 max-h-[300px] overflow-hidden"
                     x-transition:leave="transition-all ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-[300px] overflow-hidden"
                     x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                     class="pl-4 ml-6 border-l border-white/5 space-y-1">
                    <!-- Barang Masuk -->
                    <a wire:key="sidebar-mobile-barang-masuk"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/barang-masuk') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('barang-masuk') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Barang Masuk</span>
                    </a>
                    <!-- Barang Keluar -->
                    <a wire:key="sidebar-mobile-barang-keluar"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/barang-keluar') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('barang-keluar') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Barang Keluar</span>
                    </a>
                    <!-- Purchase Order -->
                    <a wire:key="sidebar-mobile-purchase-order"
                       wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
                       :class="isActive('/purchase-order') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('purchase-order') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Purchase Order</span>
                    </a>
                </div>
            </div>

            <!-- Forecast & Analisis -->
            <a wire:key="sidebar-mobile-analytics"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/analytics') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('analytics') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <span>Forecast & Analisis</span>
            </a>

            <!-- Operasi Gudang Group Header -->
            <div class="pt-4 pb-1 px-4">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Operasi Gudang</p>
            </div>

            <!-- Transfer Gudang -->
            <a wire:key="sidebar-mobile-transfer"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/transfer') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('transfer') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Transfer Gudang</span>
            </a>

            <!-- Replenishment Toko -->
            <a wire:key="sidebar-mobile-replenishment"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/replenishment') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('replenishment') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1" />
                </svg>
                <span>Replenishment Toko</span>
            </a>

            <!-- Picking Barang -->
            <a wire:key="sidebar-mobile-picking"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/picking') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('picking') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5.4 20h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Picking Barang</span>
            </a>

            <!-- Packing & QC -->
            <a wire:key="sidebar-mobile-packing"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/packing') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('packing') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Packing & QC</span>
            </a>

            <!-- Laporan Group Header -->
            <div class="pt-4 pb-1 px-4">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Laporan</p>
            </div>

            <!-- Laporan & Analitik -->
            <a wire:key="sidebar-mobile-reports"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/reports') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('reports') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Laporan & Analitik</span>
            </a>

            @can('manage master')
            <!-- Log Perubahan -->
            <a wire:key="sidebar-mobile-audit-log"
               wire:navigate
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="isActive('/audit-log') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
               href="{{ route('audit-log') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Log Perubahan</span>
            </a>
            @endcan
        </nav>

        <!-- Mobile Profile & Footer -->
        <div class="mt-auto pt-6 border-t border-white/5 space-y-4">
            @if(auth()->check())
                <div class="flex items-center gap-3 px-3 py-2 bg-white/5 rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-primary-container/20 flex items-center justify-center border border-primary/30 flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-on-surface truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] text-primary tracking-widest uppercase font-bold truncate">
                            @php
                                $firstRole = auth()->user()->roles->first();
                                $roleName = $firstRole ? $firstRole->name : null;
                            @endphp
                            {{ $roleName === 'super_admin' ? 'SUPER ADMIN' : ($roleName === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper($roleName ?? 'TAMU')) }}
                        </p>
                    </div>
                </div>
            @endif
            <a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:text-on-surface transition-all font-medium text-sm" href="#">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Bantuan</span>
            </a>
            @if(auth()->check())
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-error hover:text-error/80 transition-all font-medium text-left text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            @endif
        </div>
    </aside>

    <!-- Desktop Sidebar (Sticky, always visible on >= lg) -->
    <aside class="hidden lg:flex lg:flex-col bg-surface-dark border-r border-white/10 shadow-2xl shadow-accent-glow h-full transition-all duration-300 w-full"
           :class="collapsed ? 'p-2' : 'px-3.5 py-5'">
        
        <!-- Desktop Header (Logo & Toggle) -->
        <div class="flex items-center justify-between mb-6" :class="collapsed ? 'justify-center py-2' : 'px-3 py-2'">
            <!-- Expanded Logo -->
            <a href="{{ route('dashboard') }}" x-show="!collapsed" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" class="h-8 w-auto rounded-lg" alt="LAF PROJECT Logo">
                <span class="font-label-caps text-label-caps tracking-[0.2em] text-primary font-bold text-sm">
                    LAF PROJECT
                </span>
            </a>
            <!-- Collapsed Logo -->
            <a href="{{ route('dashboard') }}" x-show="collapsed" class="flex items-center justify-center">
                <img src="{{ asset('logo.png') }}" class="h-7 w-7 object-cover rounded-lg" alt="Logo">
            </a>
        </div>

        <!-- Desktop Navigation Menu -->
        <!-- Thin scrollbar configuration with pr-1.5 to leave gap for custom scrollbar -->
        <nav class="flex-1 space-y-1.5 overflow-y-auto pr-1.5 pl-1 custom-sidebar-nav-scrollbar" :class="collapsed ? 'px-0' : ''">
            <!-- Dashboard -->
            <a wire:key="sidebar-desktop-dashboard"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('dashboard') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Dashboard</span>
                
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Dashboard
                </span>
            </a>

            <!-- Master Data Group -->
            <div class="space-y-1">
                <button @click="toggleDropdown('master')"
                        class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm w-full text-left font-medium focus:outline-none"
                        :class="[
                            isDropdownActive('master') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5',
                            collapsed ? 'justify-center' : ''
                        ]">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span x-show="!collapsed" class="whitespace-nowrap">Master Data</span>
                    <svg x-show="!collapsed" 
                         class="w-4 h-4 ml-auto transition-transform duration-200"
                         :class="activeDropdown === 'master' ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>

                    <!-- Tooltip -->
                    <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                        Master Data
                    </span>
                </button>
                <div x-show="!collapsed && activeDropdown === 'master'"
                     x-transition:enter="transition-all ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                     x-transition:enter-end="opacity-100 max-h-[400px] overflow-hidden"
                     x-transition:leave="transition-all ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-[400px] overflow-hidden"
                     x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                     class="pl-4 ml-5 border-l border-white/5 space-y-1">
                    <!-- Master Stok -->
                    <a wire:key="sidebar-desktop-stock-master"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/stock-master') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('stock-master') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Master Stok</span>
                    </a>
                    <!-- Lokasi -->
                    <a wire:key="sidebar-desktop-lokasi"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/lokasi') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('lokasi') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Manajemen Lokasi</span>
                    </a>
                    <!-- Pengkodean Barang -->
                    @can('manage master')
                    <a wire:key="sidebar-desktop-pengkodean"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/pengkodean') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('pengkodean') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 16h6M9 12h6" />
                        </svg>
                        <span>Aturan Pengkodean</span>
                    </a>
                    @endcan
                    <!-- Supplier -->
                    <a wire:key="sidebar-desktop-supplier"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/supplier') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('supplier') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        <span>Supplier</span>
                    </a>
                    <!-- Pelanggan -->
                    <a wire:key="sidebar-desktop-pelanggan"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/pelanggan') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('pelanggan') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Pelanggan</span>
                    </a>
                </div>
            </div>

            <!-- Audit Stok -->
            <a wire:key="sidebar-desktop-audit"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/audit') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('audit') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 112-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Audit Stok (Opname)</span>
                
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Audit Stok (Opname)
                </span>
            </a>

            <!-- Transaksi Gudang Group -->
            <div class="space-y-1">
                <button @click="toggleDropdown('transaksi')"
                        class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm w-full text-left font-medium focus:outline-none"
                        :class="[
                            isDropdownActive('transaksi') ? 'text-primary' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5',
                            collapsed ? 'justify-center' : ''
                        ]">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span x-show="!collapsed" class="whitespace-nowrap">Transaksi Gudang</span>
                    <svg x-show="!collapsed" 
                         class="w-4 h-4 ml-auto transition-transform duration-200"
                         :class="activeDropdown === 'transaksi' ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>

                    <!-- Tooltip -->
                    <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                        Transaksi Gudang
                    </span>
                </button>
                <div x-show="!collapsed && activeDropdown === 'transaksi'"
                     x-transition:enter="transition-all ease-out duration-300"
                     x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                     x-transition:enter-end="opacity-100 max-h-[300px] overflow-hidden"
                     x-transition:leave="transition-all ease-in duration-200"
                     x-transition:leave-start="opacity-100 max-h-[300px] overflow-hidden"
                     x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                     class="pl-4 ml-5 border-l border-white/5 space-y-1">
                    <!-- Barang Masuk -->
                    <a wire:key="sidebar-desktop-barang-masuk"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/barang-masuk') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('barang-masuk') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Barang Masuk</span>
                    </a>
                    <!-- Barang Keluar -->
                    <a wire:key="sidebar-desktop-barang-keluar"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/barang-keluar') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('barang-keluar') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Barang Keluar</span>
                    </a>
                    <!-- Purchase Order -->
                    <a wire:key="sidebar-desktop-purchase-order"
                       wire:navigate
                       class="flex items-center gap-2 px-3 py-2 rounded-xl active:scale-[0.98] transition-all duration-200 text-xs"
                       :class="isActive('/purchase-order') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium'"
                       href="{{ route('purchase-order') }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Purchase Order</span>
                    </a>
                </div>
            </div>

            <!-- Forecast & Analisis -->
            <a wire:key="sidebar-desktop-analytics"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/analytics') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('analytics') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Forecast & Analisis</span>
                
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Forecast & Analisis
                </span>
            </a>

            <!-- Operasi Gudang Group Header -->
            <div x-show="!collapsed" class="pt-4 pb-1 px-3">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Operasi Gudang</p>
            </div>
            <div x-show="collapsed" class="w-full border-t border-white/5 my-3"></div>

            <!-- Transfer Gudang -->
            <a wire:key="sidebar-desktop-transfer"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/transfer') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('transfer') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Transfer Gudang</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Transfer Gudang
                </span>
            </a>

            <!-- Replenishment Toko -->
            <a wire:key="sidebar-desktop-replenishment"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/replenishment') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('replenishment') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Replenishment Toko</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Replenishment Toko
                </span>
            </a>

            <!-- Picking Barang -->
            <a wire:key="sidebar-desktop-picking"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/picking') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('picking') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5.4 20h2a1 1 0 001-1v-2a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Picking Barang</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Picking Barang
                </span>
            </a>

            <!-- Packing & QC -->
            <a wire:key="sidebar-desktop-packing"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/packing') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('packing') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Packing & QC</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Packing & QC
                </span>
            </a>

            <!-- Laporan Group Header -->
            <div x-show="!collapsed" class="pt-4 pb-1 px-3">
                <p class="text-[9px] font-bold tracking-[0.2em] text-on-surface-variant/40 uppercase">Laporan</p>
            </div>
            <div x-show="collapsed" class="w-full border-t border-white/5 my-3"></div>

            <!-- Laporan & Analitik -->
            <a wire:key="sidebar-desktop-reports"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/reports') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('reports') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Laporan & Analitik</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Laporan & Analitik
                </span>
            </a>

            @can('manage master')
            <!-- Log Perubahan -->
            <a wire:key="sidebar-desktop-audit-log"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/audit-log') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('audit-log') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Log Perubahan</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Log Perubahan
                </span>
            </a>
            @endcan

            <!-- Manajemen Akses -->
            @can('manage system')
            <a wire:key="sidebar-desktop-user-management"
               wire:navigate
               class="group relative flex items-center gap-2.5 px-3 py-2.5 rounded-xl active:scale-[0.98] transition-all duration-200 text-sm"
               :class="[
                   isActive('/user-management') ? 'bg-primary-container text-on-primary-container shadow-accent-glow font-bold' : 'text-on-surface-variant hover:text-on-surface hover:bg-white/5 font-medium',
                   collapsed ? 'justify-center' : ''
               ]"
               href="{{ route('user-management') }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span x-show="!collapsed" class="whitespace-nowrap">Manajemen Akses</span>
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Manajemen Akses
                </span>
            </a>
            @endcan
        </nav>

        <!-- Desktop Profile & Footer actions -->
        <div class="mt-auto pt-4 border-t border-white/5 space-y-3">
            
            <!-- User Profile card -->
            @if(auth()->check())
                <div class="group relative flex items-center gap-2.5 rounded-xl transition-all duration-200"
                     :class="collapsed ? 'justify-center py-2' : 'px-3 py-2 bg-white/5'">
                    <div class="w-9 h-9 rounded-xl bg-primary-container/20 flex items-center justify-center border border-primary/30 flex-shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="min-w-0" x-show="!collapsed">
                        <p class="text-sm font-bold text-on-surface truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] text-primary tracking-widest uppercase font-bold truncate">
                            @php
                                $firstRole = auth()->user()->roles->first();
                                $roleName = $firstRole ? $firstRole->name : null;
                            @endphp
                            {{ $roleName === 'super_admin' ? 'SUPER ADMIN' : ($roleName === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper($roleName ?? 'TAMU')) }}
                        </p>
                    </div>
                    
                    <!-- Tooltip -->
                    <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                        {{ auth()->user()->name }} ({{ $roleName === 'super_admin' ? 'SUPER ADMIN' : ($roleName === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper($roleName ?? 'TAMU')) }})
                    </span>
                </div>
            @endif

            <!-- Help / Bantuan -->
            <a class="group relative flex items-center gap-2.5 px-3 py-2 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all text-left text-sm"
               href="#"
               :class="collapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-show="!collapsed" class="text-sm font-medium">Bantuan</span>
                
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Bantuan
                </span>
            </a>

            <!-- Expand / Collapse Sidebar Toggle Button -->
            <button @click="collapsed = !collapsed"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all text-left group relative text-sm"
                    :class="collapsed ? 'justify-center' : ''">
                <svg class="w-5 h-5 transition-transform duration-300 ease-in-out"
                     :class="collapsed ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <span x-show="!collapsed" class="text-sm font-medium">Sembunyikan Menu</span>
                
                <!-- Tooltip -->
                <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-white rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    Perluas Menu
                </span>
            </button>

            <!-- Logout / Keluar -->
            @if(auth()->check())
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-error hover:text-error/80 hover:bg-red-500/5 transition-all text-left group relative text-sm"
                            :class="collapsed ? 'justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span x-show="!collapsed" class="text-sm font-medium">Keluar</span>
                        
                        <!-- Tooltip -->
                        <span x-show="collapsed" class="absolute left-full ml-4 px-3 py-1.5 bg-neutral-900 border border-neutral-800 text-xs text-red-400 rounded-lg shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                            Keluar
                        </span>
                    </button>
                </form>
            @endif
        </div>
    </aside>
</div>
