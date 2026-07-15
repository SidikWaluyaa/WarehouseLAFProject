<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>WMS LAF PROJECT</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}"/>

    <!-- Immediate Theme Application to prevent FOUC -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('system_theme_mode') || 'dark';
                if (theme === 'light') {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.remove('light');
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>



    <!-- Flatpickr Date Range Picker (CSS only — JS loaded after Livewire/Alpine) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">

    <!-- Scripts and CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-background: 0, 0, 0;
            --color-on-surface: 247, 247, 247;
            --color-surface-dark: 18, 18, 18;
            --color-surface-container: 26, 26, 26;
            --color-surface-container-low: 18, 18, 18;
            --color-surface-container-high: 34, 34, 34;
            --color-surface-container-highest: 44, 44, 44;
            --color-on-surface-variant: 172, 176, 181; /* Medium Grey */
            
            --color-glass-border: rgba(255, 255, 255, 0.1);
            --color-glass-bg: rgba(255, 255, 255, 0.03);
            --color-border-subtle: rgba(255, 255, 255, 0.1);
            --color-scrollbar-thumb: rgba(255, 255, 255, 0.12);
            --color-active-nav-bg: rgba(255, 221, 50, 0.1); /* Gold Yellow accent */
            
            --color-on-primary-container: #000000; /* black text for Gold Yellow */
            --color-primary: #FFDD32;
            --color-secondary: #EDEDED; /* Light Grey */
        }

        html.light {
            --color-background: 255, 255, 255; /* #FFFFFF (Off-White) */
            --color-on-surface: 0, 0, 0; /* #000000 */
            --color-surface-dark: 237, 237, 237; /* #EDEDED (Light Grey) */
            --color-surface-container: 255, 255, 255; /* #FFFFFF */
            --color-surface-container-low: 237, 237, 237; /* #EDEDED (Light Grey) */
            --color-surface-container-high: 226, 226, 226; /* #E2E2E2 */
            --color-surface-container-highest: 211, 211, 211; /* #D3D3D3 */
            --color-on-surface-variant: 51, 51, 51; /* #333333 */
            
            --color-glass-border: rgba(0, 0, 0, 0.08);
            --color-glass-bg: rgba(255, 255, 255, 0.85);
            --color-border-subtle: rgba(0, 0, 0, 0.08);
            --color-scrollbar-thumb: rgba(0, 0, 0, 0.15);
            --color-active-nav-bg: rgba(255, 221, 50, 0.12); /* Gold Yellow active nav */
            
            --color-on-primary-container: #000000; /* black text on Gold Yellow */
            --color-primary: #FFDD32; /* Gold Yellow */
            --color-secondary: #000000; /* Black */
            --accent-rgb: 255, 221, 50; /* Gold Yellow */
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: rgb(var(--color-background));
            color: rgb(var(--color-on-surface));
        }

        .glass-card {
            background: var(--color-glass-bg);
            backdrop-filter: blur(24px);
            border: 1px solid var(--color-glass-border);
        }

        /* Light Mode CSS Helper Overrides for Dark-First HTML structures */
        html.light .border-white\/10 {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        html.light .border-white\/5 {
            border-color: rgba(0, 0, 0, 0.05) !important;
        }
        html.light .border-white\/20 {
            border-color: rgba(0, 0, 0, 0.15) !important;
        }
        html.light .divide-white\/5 > * + * {
            border-color: rgba(0, 0, 0, 0.05) !important;
        }
        html.light .bg-white\/5 {
            background-color: rgba(255, 221, 50, 0.08) !important;
        }
        html.light .bg-white\/2 {
            background-color: rgba(255, 221, 50, 0.03) !important;
        }
        html.light .hover:bg-white\/5:hover {
            background-color: rgba(255, 221, 50, 0.12) !important;
        }
        html.light .bg-surface-dark\/95 {
            background-color: rgba(255, 255, 255, 0.95) !important;
        }
        
        html.light .text-slate-400 {
            color: #555555 !important;
        }
        html.light .text-slate-300 {
            color: #333333 !important;
        }
        html.light .text-slate-200 {
            color: #111111 !important;
        }
        html.light .text-slate-500 {
            color: #666666 !important;
        }
        html.light .text-on-surface-variant\/60 {
            color: rgba(0, 0, 0, 0.5) !important;
        }
        html.light .text-white\/10 {
            color: rgba(0, 0, 0, 0.1) !important;
        }
        html.light .bg-white\/10 {
            background-color: rgba(0, 0, 0, 0.08) !important;
        }
        html.light .bg-white\/20 {
            background-color: rgba(0, 0, 0, 0.15) !important;
        }

        .amber-glow {
            box-shadow: 0 0 20px rgba(var(--accent-rgb), 0.15);
        }

        .amber-glow-strong {
            box-shadow: 0 0 30px rgba(var(--accent-rgb), 0.3);
        }

        /* Modern Scrollbar Design System (Big Tech Standard) */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Scrollbars for Webkit (Chrome, Safari, Edge, Opera, Brave) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background-color: var(--color-scrollbar-thumb);
            border: 2px solid transparent;
            background-clip: padding-box;
            border-radius: 9999px;
            transition: background-color 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(var(--accent-rgb), 0.45); /* Elegant accent hover glow */
        }

        ::-webkit-scrollbar-corner {
            background: transparent;
        }

        /* Firefox support */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--color-scrollbar-thumb) transparent;
        }

        /* Thin, floating custom scrollbar for sidebar nav menu to prevent layout squeeze */
        .custom-sidebar-nav-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-sidebar-nav-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-sidebar-nav-scrollbar::-webkit-scrollbar-thumb {
            background-color: var(--color-scrollbar-thumb);
            border-radius: 9999px;
        }

        .custom-sidebar-nav-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(var(--accent-rgb), 0.4);
        }

        /* Enable modern independent scrolling on page content */
        main > div {
            width: 100%;
        }

        .active-nav-shadow {
            box-shadow: 0 0 15px rgba(var(--accent-rgb), 0.2);
        }

        /* Hide Livewire navigation progress bar globally */
        .livewire-progress-bar {
            display: none !important;
        }

        /* Custom Flatpickr Dark Theme Override — Matches App Design System */
        .flatpickr-calendar {
            background: #1a1a1a !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 1rem !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 40px rgba(168,85,247,0.08) !important;
            font-family: 'Inter', sans-serif !important;
            overflow: hidden;
        }
        .flatpickr-calendar.arrowTop::before,
        .flatpickr-calendar.arrowTop::after,
        .flatpickr-calendar.arrowBottom::before,
        .flatpickr-calendar.arrowBottom::after {
            display: none !important;
        }
        .flatpickr-months {
            background: rgba(255,255,255,0.03) !important;
            border-bottom: 1px solid rgba(255,255,255,0.08) !important;
            padding: 4px 0 !important;
        }
        .flatpickr-months .flatpickr-month {
            color: #e2e2e2 !important;
            fill: #e2e2e2 !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            color: #e2e2e2 !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months option {
            background: #1a1a1a !important;
            color: #e2e2e2 !important;
        }
        .flatpickr-months .flatpickr-prev-month svg,
        .flatpickr-months .flatpickr-next-month svg {
            fill: rgba(255,255,255,0.5) !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover svg,
        .flatpickr-months .flatpickr-next-month:hover svg {
            fill: #a855f7 !important;
        }
        span.flatpickr-weekday {
            color: rgba(255,255,255,0.35) !important;
            font-weight: 700 !important;
            font-size: 0.65rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
        }
        .flatpickr-day {
            color: #d1d5db !important;
            border-radius: 0.5rem !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            border: none !important;
        }
        .flatpickr-day:hover {
            background: rgba(168,85,247,0.15) !important;
            border-color: transparent !important;
        }
        .flatpickr-day.today {
            border: 1px solid rgba(168,85,247,0.5) !important;
            color: #a855f7 !important;
            font-weight: 700 !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #a855f7 !important;
            border-color: #a855f7 !important;
            color: #fff !important;
            font-weight: 700 !important;
            box-shadow: 0 0 12px rgba(168,85,247,0.4) !important;
        }
        .flatpickr-day.inRange {
            background: rgba(168,85,247,0.15) !important;
            border-color: transparent !important;
            box-shadow: -5px 0 0 rgba(168,85,247,0.15), 5px 0 0 rgba(168,85,247,0.15) !important;
        }
        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: rgba(255,255,255,0.15) !important;
        }
        /* Rose accent variant for Barang Keluar pages */
        .flatpickr-rose .flatpickr-day.today {
            border-color: rgba(244,63,94,0.5) !important;
            color: #f43f5e !important;
        }
        .flatpickr-rose .flatpickr-day.selected,
        .flatpickr-rose .flatpickr-day.startRange,
        .flatpickr-rose .flatpickr-day.endRange {
            background: #f43f5e !important;
            border-color: #f43f5e !important;
            box-shadow: 0 0 12px rgba(244,63,94,0.4) !important;
        }
        .flatpickr-rose .flatpickr-day.inRange {
            background: rgba(244,63,94,0.15) !important;
            box-shadow: -5px 0 0 rgba(244,63,94,0.15), 5px 0 0 rgba(244,63,94,0.15) !important;
        }
        .flatpickr-rose .flatpickr-day:hover {
            background: rgba(244,63,94,0.15) !important;
        }
        .flatpickr-rose .flatpickr-months .flatpickr-prev-month:hover svg,
        .flatpickr-rose .flatpickr-months .flatpickr-next-month:hover svg {
            fill: #f43f5e !important;
        }

        /* Custom Flatpickr Light Theme overrides */
        html.light .flatpickr-calendar {
            background: #FFFFFF !important;
            border: 1px solid rgba(0,0,0,0.08) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06), 0 0 20px rgba(133,72,54,0.05) !important;
        }
        html.light .flatpickr-months {
            background: rgba(0,0,0,0.02) !important;
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }
        html.light .flatpickr-months .flatpickr-month {
            color: #000000 !important;
            fill: #000000 !important;
        }
        html.light .flatpickr-current-month .flatpickr-monthDropdown-months,
        html.light .flatpickr-current-month input.cur-year {
            color: #000000 !important;
        }
        html.light .flatpickr-current-month .flatpickr-monthDropdown-months option {
            background: #FFFFFF !important;
            color: #000000 !important;
        }
        html.light .flatpickr-months .flatpickr-prev-month svg,
        html.light .flatpickr-months .flatpickr-next-month svg {
            fill: rgba(0,0,0,0.5) !important;
        }
        html.light span.flatpickr-weekday {
            color: rgba(0,0,0,0.4) !important;
        }
        html.light .flatpickr-day {
            color: #222222 !important;
        }
        html.light .flatpickr-day.prevMonthDay,
        html.light .flatpickr-day.nextMonthDay {
            color: rgba(0,0,0,0.2) !important;
        }
        html.light .flatpickr-day:hover {
            background: rgba(255,221,50,0.15) !important;
        }
        html.light .flatpickr-day.today {
            border: 1px solid rgba(255,221,50,0.5) !important;
            color: #000000 !important;
            font-weight: bold;
        }
        html.light .flatpickr-day.selected,
        html.light .flatpickr-day.startRange,
        html.light .flatpickr-day.endRange {
            background: #FFDD32 !important;
            border-color: #FFDD32 !important;
            color: #000000 !important;
            box-shadow: 0 0 12px rgba(255,221,50,0.4) !important;
        }
        html.light .flatpickr-day.inRange {
            background: rgba(255,221,50,0.1) !important;
            box-shadow: -5px 0 0 rgba(255,221,50,0.1), 5px 0 0 rgba(255,221,50,0.1) !important;
        }

        /* Dynamic Theme Aksen (CSS variables based on selected theme) */
        :root {
            --accent-rgb: 255, 221, 50; /* LAF Gold Yellow */
        }
        .theme-amber {
            --accent-rgb: 255, 221, 50;
        }
        .theme-purple {
            --accent-rgb: 168, 85, 247;
        }
        .theme-emerald {
            --accent-rgb: 16, 185, 129;
        }
    </style>

    <!-- Safe LocalStorage Helper for Sandboxed environments -->
    <script>
        window.safeStorage = {
            getItem(key, fallback = null) {
                try {
                    const value = localStorage.getItem(key);
                    return value !== null ? value : fallback;
                } catch (e) {
                    return fallback;
                }
            },
            setItem(key, value) {
                try {
                    localStorage.setItem(key, value);
                } catch (e) {}
            }
        };
    </script>
    @livewireStyles
</head>
<body x-data="{ accentTheme: 'amber' }" x-init="accentTheme = window.safeStorage ? window.safeStorage.getItem('system_accent_theme') || 'amber' : 'amber'" @accent-changed.window="accentTheme = $event.detail" :class="'theme-' + accentTheme" class="bg-background text-on-surface font-body-md h-screen overflow-hidden">
    <div class="h-screen w-full max-w-full lg:flex lg:flex-row overflow-hidden">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Main Canvas -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden relative">
            <!-- TopNavBar (Sticky header with scroll shadow and border transition) -->
            <livewire:topbar />

            <!-- Content Area -->
            {{ $slot }}

            <!-- Atmospheric Glow Background (Absolute & Clipped) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
                <div class="absolute -bottom-64 -right-64 w-[800px] h-[800px] bg-primary/5 rounded-full blur-[150px]"></div>
            </div>
        </main>
    </div>

    <x-sweetalert />

    <!-- Settings Drawer (System Settings) -->
    <div x-data="{
            drawerOpen: false,
            localSoundEnabled: true,
            localAccentTheme: 'amber',
            localThemeMode: 'dark',
            init() {
                this.localSoundEnabled = window.safeStorage.getItem('system_sound_enabled') !== 'false';
                this.localAccentTheme = window.safeStorage.getItem('system_accent_theme') || 'amber';
                this.localThemeMode = window.safeStorage.getItem('system_theme_mode') || 'dark';
                this.applyTheme(this.localAccentTheme);
                this.applyThemeMode(this.localThemeMode);
            },
            toggleSound() {
                this.localSoundEnabled = !this.localSoundEnabled;
                window.safeStorage.setItem('system_sound_enabled', this.localSoundEnabled ? 'true' : 'false');
                window.dispatchEvent(new CustomEvent('play-beep'));
            },
            setAccent(theme) {
                this.localAccentTheme = theme;
                window.safeStorage.setItem('system_accent_theme', theme);
                this.applyTheme(theme);
            },
            applyTheme(theme) {
                const el = document.body;
                if (el) {
                    el.classList.remove('theme-amber', 'theme-purple', 'theme-emerald');
                    el.classList.add('theme-' + theme);
                }
                window.dispatchEvent(new CustomEvent('accent-changed', { detail: theme }));
            },
            setThemeMode(mode) {
                this.localThemeMode = mode;
                window.safeStorage.setItem('system_theme_mode', mode);
                this.applyThemeMode(mode);
                window.dispatchEvent(new CustomEvent('play-beep'));
            },
            applyThemeMode(mode) {
                const el = document.documentElement;
                if (el) {
                    if (mode === 'light') {
                        el.classList.remove('dark');
                        el.classList.add('light');
                    } else {
                        el.classList.remove('light');
                        el.classList.add('dark');
                    }
                }
                window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: mode }));
            }
        }"
        @open-settings.window="drawerOpen = true"
        style="display: none;"
        x-show="drawerOpen">
        <!-- Backdrop -->
        <div x-show="drawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="drawerOpen = false"
             class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50"></div>

        <!-- Panel Content -->
        <div x-show="drawerOpen" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 right-0 w-80 sm:w-96 bg-surface-dark border-l border-white/10 shadow-2xl z-50 p-6 flex flex-col backdrop-blur-xl">
             
            <!-- Drawer Header -->
            <div class="flex items-center justify-between pb-5 border-b border-white/10">
                <h3 class="font-bold text-lg text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">tune</span>
                    Pengaturan Sistem
                </h3>
                <button @click="drawerOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5 text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Settings Body -->
            <div class="flex-1 overflow-y-auto py-6 space-y-8 custom-sidebar-nav-scrollbar pr-1">
                
                <!-- Sound Effects Toggles -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-widest text-on-surface-variant uppercase">Preferensi Audio</h4>
                    <div class="flex items-center justify-between p-4 bg-white/5 border border-white/10 rounded-2xl">
                        <div>
                            <p class="text-sm font-semibold text-on-surface">Efek Suara</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Mainkan bel & buzzer pada aksi scanner</p>
                        </div>
                        <button @click="toggleSound()" 
                                class="w-12 h-6 rounded-full p-1 transition-all duration-300 focus:outline-none cursor-pointer"
                                :class="localSoundEnabled ? 'bg-primary' : 'bg-neutral-800'">
                            <div class="w-4 h-4 rounded-full bg-background shadow-md transform transition-all duration-300"
                                 :class="localSoundEnabled ? 'translate-x-6' : 'translate-x-0'"></div>
                        </button>
                    </div>
                </div>

                <!-- Theme Accent Glow -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-widest text-on-surface-variant uppercase">Tema & Aksen</h4>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Pilih warna aksen pendaran (glow effect) untuk interface sistem:</p>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Amber -->
                        <button @click="setAccent('amber')" 
                                :class="localAccentTheme === 'amber' ? 'border-primary ring-2 ring-primary/20 bg-primary/5 font-bold' : 'border-white/5 hover:bg-white/5 font-medium'"
                                class="flex flex-col items-center gap-2 p-3 border rounded-xl transition-all duration-200 text-xs focus:outline-none cursor-pointer">
                            <span class="w-5 h-5 rounded-full bg-[#FFDD32] shadow-lg shadow-[#FFDD32]/30"></span>
                            <span class="text-on-surface">LAF Gold</span>
                        </button>
                        <!-- Purple -->
                        <button @click="setAccent('purple')" 
                                :class="localAccentTheme === 'purple' ? 'border-primary ring-2 ring-primary/20 bg-primary/5 font-bold' : 'border-white/5 hover:bg-white/5 font-medium'"
                                class="flex flex-col items-center gap-2 p-3 border rounded-xl transition-all duration-200 text-xs focus:outline-none cursor-pointer">
                            <span class="w-5 h-5 rounded-full bg-[#a855f7] shadow-lg shadow-[#a855f7]/30"></span>
                            <span class="text-on-surface">Purple</span>
                        </button>
                        <!-- Emerald -->
                        <button @click="setAccent('emerald')" 
                                :class="localAccentTheme === 'emerald' ? 'border-primary ring-2 ring-primary/20 bg-primary/5 font-bold' : 'border-white/5 hover:bg-white/5 font-medium'"
                                class="flex flex-col items-center gap-2 p-3 border rounded-xl transition-all duration-200 text-xs focus:outline-none cursor-pointer">
                            <span class="w-5 h-5 rounded-full bg-[#10b981] shadow-lg shadow-[#10b981]/30"></span>
                            <span class="text-on-surface">Emerald</span>
                        </button>
                    </div>
                </div>

                <!-- Mode Tampilan (Dark / Light) Segmented Control -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-widest text-on-surface-variant uppercase">Mode Tampilan</h4>
                    <div class="grid grid-cols-2 gap-2.5 p-1.5 bg-white/5 border border-white/10 rounded-2xl font-outfit">
                        <!-- Dark Option -->
                        <button @click="setThemeMode('dark')" 
                                :class="localThemeMode === 'dark' ? 'bg-primary text-on-primary-container font-bold shadow-md' : 'text-on-surface-variant hover:text-on-surface font-medium'"
                                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs transition-all duration-200 focus:outline-none cursor-pointer">
                            <span class="material-symbols-outlined text-base">dark_mode</span>
                            <span>Dark Mode</span>
                        </button>
                        <!-- Light Option -->
                        <button @click="setThemeMode('light')" 
                                :class="localThemeMode === 'light' ? 'bg-primary text-on-primary-container font-bold shadow-md' : 'text-on-surface-variant hover:text-on-surface font-medium'"
                                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs transition-all duration-200 focus:outline-none cursor-pointer">
                            <span class="material-symbols-outlined text-base">light_mode</span>
                            <span>Light Mode</span>
                        </button>
                    </div>
                </div>

                <!-- Sidebar Compact Setting -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold tracking-widest text-on-surface-variant uppercase">Menu Sidebar</h4>
                    <div class="flex items-center justify-between p-4 bg-white/5 border border-white/10 rounded-2xl">
                        <div>
                            <p class="text-sm font-semibold text-on-surface">Sembunyikan Sidebar</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Toggle default compact layout menu</p>
                        </div>
                        <button @click="$dispatch('toggle-sidebar')" 
                                class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-white/5 transition-all duration-200 active:scale-95 focus:outline-none cursor-pointer">
                            <span class="material-symbols-outlined text-on-surface-variant">menu_open</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer info -->
            <div class="pt-4 border-t border-white/10 text-center text-[10px] text-on-surface-variant">
                WMS LAF Project v2.1.0 &bull; Powered by Antigravity
            </div>
        </div>
    </div>

    @livewireScripts
    <!-- Flatpickr JS — must load AFTER Alpine/Livewire -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Micro-interactions and simple state management
        document.querySelectorAll('.active\\:scale-95').forEach(button => {
            button.addEventListener('mousedown', () => {
                button.style.transform = 'scale(0.95)';
            });
            button.addEventListener('mouseup', () => {
                button.style.transform = 'scale(1)';
            });
            button.addEventListener('mouseleave', () => {
                button.style.transform = 'scale(1)';
            });
        });

        // Beeper sound events support
        window.addEventListener('play-beep', () => {
            try {
                if (window.safeStorage && window.safeStorage.getItem('system_sound_enabled') === 'false') {
                    return;
                }
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            } catch (e) {
                console.error('Failed to play sound:', e);
            }
        });

        window.addEventListener('play-error-buzzer', () => {
            try {
                if (window.safeStorage && window.safeStorage.getItem('system_sound_enabled') === 'false') {
                    return;
                }
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(120, ctx.currentTime);
                gain.gain.setValueAtTime(0.25, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch (e) {
                console.error('Failed to play sound:', e);
            }
        });

        // Focus barcode input helper
        window.addEventListener('focus-barcode', () => {
            setTimeout(() => {
                const el = document.getElementById('barcode-scan-input');
                if (el) el.focus();
            }, 100);
        });
    </script>
</body>
</html>
