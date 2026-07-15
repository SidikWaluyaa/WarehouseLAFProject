<header x-data="{
            title: 'WMS',
            titleMap: {
                '/dashboard': 'Dashboard',
                '/stock-master': 'Master Stok',
                '/lokasi': 'Manajemen Lokasi',
                '/pengkodean': 'Aturan Pengkodean',
                '/audit': 'Audit Stok (Opname)',
                '/supplier': 'Supplier',
                '/pelanggan': 'Pelanggan',
                '/barang-masuk': 'Barang Masuk',
                '/barang-keluar': 'Barang Keluar',
                '/purchase-order': 'Purchase Order',
                '/analytics': 'Forecast & Analisis',
                '/profile': 'Profil Saya'
            },
            updateTitle() {
                let path = window.location.pathname;
                let found = Object.keys(this.titleMap).find(k => path.startsWith(k));
                this.title = found ? this.titleMap[found] : 'LAF Project';
                document.title = this.title + ' - WMS LAF PROJECT';
            }
        }"
        x-init="updateTitle()"
        x-on:livewire:navigated.window="updateTitle()"
        class="sticky top-0 z-40 flex justify-between items-center px-6 lg:px-margin-desktop w-full h-16 bg-surface-dark/95 border-b border-white/10 shadow-lg backdrop-blur-md shadow-black/30 flex-shrink-0">
    
    <div class="flex items-center gap-4">
        <!-- Hamburger Menu Button for Mobile -->
        <button @click="$dispatch('open-mobile-sidebar')" class="lg:hidden w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/5 transition-all duration-200 text-on-surface flex-shrink-0 active:scale-95">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight font-bold transition-all duration-300" x-text="title">Dashboard</h2>
        <div id="header-search-placeholder" class="flex items-center"></div>
    </div>

    <div class="flex items-center gap-4">
        <!-- Notifications Dropdown -->
        <div x-data="{
                open: false,
                dismissed: [],
                activeTab: 'all',
                init() {
                    this.dismissed = JSON.parse(window.safeStorage.getItem('dismissed_notifications') || '[]');
                },
                dismiss(id) {
                    if (!this.dismissed.includes(id)) {
                        this.dismissed.push(id);
                        window.safeStorage.setItem('dismissed_notifications', JSON.stringify(this.dismissed));
                    }
                },
                dismissAll(notifications) {
                    notifications.forEach(n => {
                        if (!this.dismissed.includes(n.id)) {
                            this.dismissed.push(n.id);
                        }
                    });
                    window.safeStorage.setItem('dismissed_notifications', JSON.stringify(this.dismissed));
                },
                isDismissed(id) {
                    return this.dismissed.includes(id);
                },
                getVisible(notifications) {
                    return notifications.filter(n => !this.isDismissed(n.id));
                },
                getLowStock(notifications) {
                    return this.getVisible(notifications).filter(n => n.type === 'low_stock');
                }
            }" 
            class="relative">
            
            <button @click="open = !open" 
                    class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/5 transition-all duration-200 relative active:scale-95 focus:outline-none"
                    :class="open ? 'bg-white/5' : ''">
                <span class="material-symbols-outlined text-on-surface-variant hover:text-on-surface transition-colors">notifications</span>
                
                <!-- Dynamic Badge -->
                <template x-if="getVisible(@js($notifications)).length > 0">
                    <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-primary rounded-full animate-ping shadow-lg shadow-accent-glow"></span>
                </template>
                <template x-if="getVisible(@js($notifications)).length > 0">
                    <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-primary rounded-full shadow-lg shadow-accent-glow"></span>
                </template>
            </button>

            <!-- Floating Card Dropdown -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 @click.outside="open = false"
                 class="absolute right-0 mt-3 w-80 sm:w-96 rounded-2xl border border-white/10 bg-surface-dark/95 backdrop-blur-xl shadow-2xl overflow-hidden z-50"
                 style="display: none;">
                
                <!-- Dropdown Header -->
                <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-on-surface">Notifikasi</h3>
                        <p class="text-xs text-on-surface-variant mt-0.5" x-text="getVisible(@js($notifications)).length + ' Baru'"></p>
                    </div>
                    <template x-if="getVisible(@js($notifications)).length > 0">
                        <button @click="dismissAll(@js($notifications))" class="text-xs text-primary hover:underline font-semibold focus:outline-none">
                            Tandai semua dibaca
                        </button>
                    </template>
                </div>

                <!-- Dropdown Tabs -->
                <div class="flex border-b border-white/10 px-3 py-1 bg-white/2">
                    <button @click="activeTab = 'all'" 
                            :class="activeTab === 'all' ? 'text-primary border-primary font-bold' : 'text-on-surface-variant hover:text-on-surface border-transparent font-medium'"
                            class="flex-1 text-center py-2 text-xs border-b-2 transition-all duration-200 focus:outline-none">
                        Semua
                    </button>
                    <button @click="activeTab = 'low_stock'" 
                            :class="activeTab === 'low_stock' ? 'text-primary border-primary font-bold' : 'text-on-surface-variant hover:text-on-surface border-transparent font-medium'"
                            class="flex-1 text-center py-2 text-xs border-b-2 transition-all duration-200 focus:outline-none">
                        Stok Menipis
                        <template x-if="getLowStock(@js($notifications)).length > 0">
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full bg-primary/20 text-primary text-[10px] font-bold" x-text="getLowStock(@js($notifications)).length"></span>
                        </template>
                    </button>
                </div>

                <!-- Dropdown Content (Notification List) -->
                <div class="max-h-72 overflow-y-auto custom-sidebar-nav-scrollbar divide-y divide-white/5">
                    <!-- Tab: Semua -->
                    <template x-if="activeTab === 'all'">
                        <div>
                            <template x-for="item in getVisible(@js($notifications))" :key="item.id">
                                <div class="px-5 py-4 hover:bg-white/5 transition-all duration-200 group flex items-start gap-3 relative">
                                    <!-- Icon Type Wrapper -->
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                         :class="item.type === 'low_stock' ? 'bg-red-500/10 text-red-400' : 'bg-primary/10 text-primary'">
                                        <span class="material-symbols-outlined text-lg" x-text="item.type === 'low_stock' ? 'warning' : 'receipt_long'"></span>
                                    </div>
                                    
                                    <!-- Body -->
                                    <div class="flex-1 min-w-0 pr-4">
                                        <div class="flex justify-between items-start">
                                            <span class="text-xs font-bold text-on-surface" x-text="item.title"></span>
                                            <span class="text-[10px] text-on-surface-variant" x-text="item.time"></span>
                                        </div>
                                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed" x-text="item.message"></p>
                                        <a :href="item.link" wire:navigate class="text-[11px] text-primary hover:underline font-semibold mt-2 inline-block">Lihat Detail &rarr;</a>
                                    </div>

                                    <!-- Dismiss Button -->
                                    <button @click="dismiss(item.id)" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </template>
                            
                            <template x-if="getVisible(@js($notifications)).length === 0">
                                <div class="px-5 py-8 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-4xl text-white/10 mb-2">done_all</span>
                                    <p class="text-xs">Semua beres! Tidak ada notifikasi baru.</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Tab: Stok Menipis -->
                    <template x-if="activeTab === 'low_stock'">
                        <div>
                            <template x-for="item in getLowStock(@js($notifications))" :key="item.id">
                                <div class="px-5 py-4 hover:bg-white/5 transition-all duration-200 group flex items-start gap-3 relative">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-lg">warning</span>
                                    </div>
                                    <div class="flex-1 min-w-0 pr-4">
                                        <div class="flex justify-between items-start">
                                            <span class="text-xs font-bold text-on-surface" x-text="item.title"></span>
                                            <span class="text-[10px] text-on-surface-variant" x-text="item.time"></span>
                                        </div>
                                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed" x-text="item.message"></p>
                                        <a :href="item.link" wire:navigate class="text-[11px] text-primary hover:underline font-semibold mt-2 inline-block">Lihat Detail &rarr;</a>
                                    </div>
                                    <button @click="dismiss(item.id)" class="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="getLowStock(@js($notifications)).length === 0">
                                <div class="px-5 py-8 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-4xl text-white/10 mb-2">inventory_2</span>
                                    <p class="text-xs">Stok barang aman! Tidak ada stok kritis.</p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                
                <!-- View All Audit Link -->
                <div class="px-5 py-3.5 bg-white/2 border-t border-white/10 text-center">
                    <a href="{{ route('audit') }}" wire:navigate class="text-xs font-bold text-primary hover:underline">
                        Buka Manajemen Audit Stok &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Slide-over Trigger -->
        <button @click="$dispatch('open-settings')" 
                title="Pengaturan Sistem"
                class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/5 transition-all duration-200 active:scale-95 focus:outline-none">
            <span class="material-symbols-outlined text-on-surface-variant hover:text-on-surface transition-colors">settings</span>
        </button>

        <div class="h-8 w-[1px] bg-white/10 mx-1"></div>

        <!-- Profile Dropdown Component -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                    class="flex items-center gap-2 hover:bg-white/5 p-1 rounded-xl transition-all duration-200 focus:outline-none active:scale-95"
                    :class="open ? 'bg-white/5' : ''">
                <!-- User Avatar -->
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-primary/20 border border-primary/40 flex items-center justify-center">
                    @if(auth()->check())
                        <!-- Use initials from user name if no image uploaded -->
                        <span class="font-bold text-xs text-primary">
                            {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                        </span>
                    @else
                        <span class="material-symbols-outlined text-primary text-sm">person</span>
                    @endif
                </div>
            </button>

            <!-- Profile Dropdown Menu Card -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 @click.outside="open = false"
                 class="absolute right-0 mt-3 w-64 rounded-2xl border border-white/10 bg-surface-dark/95 backdrop-blur-xl shadow-2xl overflow-hidden z-50 p-2"
                 style="display: none;">
                
                @if(auth()->check())
                    <!-- User Header Info -->
                    <div class="px-4 py-3 border-b border-white/5 flex flex-col gap-1">
                        <p class="text-sm font-bold text-on-surface truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-on-surface-variant truncate">{{ auth()->user()->email }}</p>
                        
                        <!-- Role Badge (Gradient neon borders per Role) -->
                        <div class="mt-2.5">
                            @php
                                $role = auth()->user()->roles->first()?->name ?? 'guest';
                                $badgeStyle = match($role) {
                                    'super_admin' => 'bg-violet-500/10 text-violet-400 border-violet-500/30 ring-1 ring-violet-500/15',
                                    'admin' => 'bg-amber-500/10 text-amber-400 border-amber-500/30 ring-1 ring-amber-500/15',
                                    'operator_gudang' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30 ring-1 ring-cyan-500/15',
                                    'sales' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 ring-1 ring-emerald-500/15',
                                    'finance' => 'bg-rose-500/10 text-rose-400 border-rose-500/30 ring-1 ring-rose-500/15',
                                    default => 'bg-neutral-500/10 text-neutral-400 border-neutral-500/30 ring-1 ring-neutral-500/15'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold tracking-widest uppercase border {{ $badgeStyle }}">
                                {{ $role === 'super_admin' ? 'SUPER ADMIN' : ($role === 'operator_gudang' ? 'OPERATOR GUDANG' : strtoupper(str_replace('_', ' ', $role))) }}
                            </span>
                        </div>
                    </div>

                    <!-- Links Group -->
                    <div class="py-1">
                        <!-- Profil Link -->
                        <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all duration-200">
                            <span class="material-symbols-outlined text-base">manage_accounts</span>
                            Profil Saya
                        </a>
                        
                        <!-- Opname/Audit Link -->
                        <a href="{{ route('audit') }}" wire:navigate class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-on-surface-variant hover:text-on-surface hover:bg-white/5 transition-all duration-200">
                            <span class="material-symbols-outlined text-base">fact_check</span>
                            Riwayat Audit
                        </a>
                    </div>

                    <!-- Logout Form (POST via form wrapper) -->
                    <div class="border-t border-white/5 pt-1 mt-1">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-all duration-200 text-left focus:outline-none">
                                <span class="material-symbols-outlined text-base">logout</span>
                                Keluar Aplikasi
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
