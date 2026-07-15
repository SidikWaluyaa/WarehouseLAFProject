<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    /**
     * Initialize the component and load the remembered email if it exists.
     */
    public function mount(): void
    {
        $this->form->email = request()->cookie('remember_email', '');
        $this->form->remember = !empty($this->form->email);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        // Remember email cookie configuration
        if ($this->form->remember) {
            cookie()->queue('remember_email', $this->form->email, 60 * 24 * 30); // 30 days
        } else {
            cookie()->queue('remember_email', '', -1);
        }

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col lg:flex-row w-full bg-[rgb(var(--color-background))] overflow-x-hidden transition-all duration-300">
    <!-- Custom CSS for Animations & UI Styling -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
        
        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(1deg); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(-1deg); }
        }
        @keyframes float-delay {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(0.5deg); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.15; transform: scale(1); }
            50% { opacity: 0.35; transform: scale(1.08); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-slow {
            animation: float-slow 8s ease-in-out infinite;
            animation-delay: 1.5s;
        }
        .animate-float-delay {
            animation: float-delay 7s ease-in-out infinite;
            animation-delay: 3s;
        }
        .animate-pulse-glow {
            animation: pulse-glow 5s ease-in-out infinite;
        }
        .bg-brand-left-col {
            background: linear-gradient(to bottom right, #111111, #000000, #1a1a1a);
        }
        html.light .bg-brand-left-col {
            background: linear-gradient(to bottom right, #fcfaf9, #eeeeee, #f5f1f0);
            border-right-color: rgba(255, 221, 50, 0.15) !important;
        }
        html.light .brand-logo-text {
            background: linear-gradient(to right, #000000, #333333);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        html.light .floating-card {
            background-color: rgba(255, 255, 255, 0.7) !important;
            border-color: rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }
        html.light .floating-card:hover {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: rgba(255, 221, 50, 0.4) !important;
        }
        html.light .floating-card p {
            color: #000000 !important;
        }
        html.light .floating-card span.text-slate-400 {
            color: #555555 !important;
        }
        html.light .relative input::placeholder {
            color: #888888 !important;
        }
        .submit-btn-theme {
            background: linear-gradient(to right, #FFDD32, #cca900);
            color: #000000;
            box-shadow: 0 0 20px rgba(255, 221, 50, 0.35);
            transition: all 0.3s ease;
        }
        .submit-btn-theme:hover {
            box-shadow: 0 0 35px rgba(255, 221, 50, 0.5);
            transform: translateY(-1px);
        }
        html.light .submit-btn-theme {
            background: linear-gradient(to right, #000000, #222222);
            color: #FFFFFF;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.25);
        }
        html.light .submit-btn-theme:hover {
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.4);
        }
        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255,255,255,0.015) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.015) 1px, transparent 1px);
        }
        html.light .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255, 221, 50, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 221, 50, 0.05) 1px, transparent 1px);
        }
    </style>

    <!-- Left Column: Visual Brand Area -->
    <div class="hidden lg:flex flex-col justify-between w-7/12 p-12 bg-brand-left-col border-r border-[var(--color-border)] relative overflow-hidden select-none transition-all duration-300">
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-grid-pattern bg-[size:40px_40px] pointer-events-none"></div>
        
        <!-- Background Ambient Glows -->
        <div class="absolute top-[20%] left-[20%] w-[350px] h-[350px] bg-[#FFDD32]/10 rounded-full blur-[120px] pointer-events-none animate-pulse-glow"></div>
        <div class="absolute bottom-[20%] right-[10%] w-[400px] h-[400px] bg-[#FFDD32]/8 rounded-full blur-[140px] pointer-events-none animate-pulse-glow" style="animation-delay: 2.5s;"></div>

        <!-- Top Header: Logo & Brand -->
        <div class="flex items-center gap-3.5 relative z-20">
            <div class="w-11 h-11 rounded-2xl bg-[#FFDD32]/10 border border-[#FFDD32]/20 flex items-center justify-center shadow-inner">
                <img src="{{ asset('logo.png') }}" class="w-7 h-7 object-contain drop-shadow-[0_0_8px_rgba(255,221,50,0.5)]" alt="Logo">
            </div>
            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-[#FFDD32] to-[#F7F7F7] bg-clip-text text-transparent font-outfit brand-logo-text">LAF PROJECT WMS</span>
        </div>

        <!-- Center Section: Sandal & Floating WMS Cards -->
        <div class="flex-1 flex items-center justify-center relative min-h-[450px]">
            <!-- Pulse Glow behind Sandal -->
            <div class="absolute w-[450px] h-[450px] bg-[#FFDD32]/10 rounded-full blur-[100px] pointer-events-none animate-pulse-glow"></div>

            <!-- Floating Card 1 (Barang Masuk) - Widen to w-[340px] to prevent wrap -->
            <div class="absolute top-[12%] left-[8%] z-20 animate-float backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-5 shadow-2xl w-[340px] transition-all hover:bg-white/10 hover:border-white/20 floating-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[9px] font-black text-[#FFDD32] uppercase tracking-widest px-2.5 py-0.5 bg-[#FFDD32]/10 rounded-md">Masuk</span>
                    <span class="text-[10px] text-slate-400 font-medium">2 jam lalu</span>
                </div>
                <p class="text-xs font-semibold text-slate-200 leading-relaxed font-outfit">#BM-20260601 PO Restock dari Supplier Utama</p>
                <div class="mt-3.5 flex items-center gap-1.5 text-[10px] text-[#FFDD32] font-bold bg-[#FFDD32]/10 py-1 px-2.5 rounded-full w-max">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FFDD32] animate-ping"></span>
                    Selesai Diterima
                </div>
            </div>

            <!-- Floating Card 2 (Barang Keluar) -->
            <div class="absolute top-[28%] right-[6%] z-20 animate-float-delay backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-5 shadow-2xl w-[340px] transition-all hover:bg-white/10 hover:border-white/20 floating-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[9px] font-black text-[#FFDD32] uppercase tracking-widest px-2.5 py-0.5 bg-[#FFDD32]/10 rounded-md font-outfit">Keluar</span>
                    <span class="text-[10px] text-slate-400 font-medium font-outfit">5 jam lalu</span>
                </div>
                <p class="text-xs font-semibold text-slate-200 leading-relaxed font-outfit">#BK-20260602 Pengiriman 15 unit Sandal LAF</p>
                <div class="mt-3.5 flex items-center gap-1.5 text-[10px] text-[#FFDD32] font-bold bg-[#FFDD32]/10 py-1 px-2.5 rounded-full w-max">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FFDD32]"></span>
                    Sedang Diproses
                </div>
            </div>

            <!-- Floating Card 3 (Audit Opname) -->
            <div class="absolute bottom-[18%] left-[10%] z-20 animate-float-slow backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl p-5 shadow-2xl w-[340px] transition-all hover:bg-white/10 hover:border-white/20 floating-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[9px] font-black text-[#F7F7F7] uppercase tracking-widest px-2.5 py-0.5 bg-white/10 rounded-md font-outfit">Opname</span>
                    <span class="text-[10px] text-slate-400 font-medium font-outfit">1 hari lalu</span>
                </div>
                <p class="text-xs font-semibold text-slate-200 leading-relaxed font-outfit">#OPN-0620 Sesi Audit Lokasi GDG-A01</p>
                <div class="mt-3.5 flex items-center gap-1.5 text-[10px] text-slate-300 font-bold bg-white/10 py-1 px-2.5 rounded-full w-max font-outfit">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    DRAFT
                </div>
            </div>

            <!-- Glowing Sandal Image -->
            <img src="{{ asset('images/glowing_sandal.png') }}" class="w-[480px] object-contain drop-shadow-[0_25px_60px_rgba(255,221,50,0.22)] relative z-10 animate-float pointer-events-none select-none" alt="Glowing Sandal LAF">
        </div>

        <!-- Bottom Footer Info -->
        <div class="max-w-[480px] relative z-20 space-y-3 pl-2 transition-all duration-300">
            <h1 class="text-2xl font-black text-[rgb(var(--color-primary-text))] font-outfit leading-tight tracking-tight">Satu tempat untuk mengelola inventaris & pergerakan barang.</h1>
            <p class="text-sm font-medium text-[rgba(var(--color-primary-text),0.6)] leading-relaxed font-outfit">Sistem Manajemen Gudang terintegrasi untuk optimalisasi penyimpanan, mutasi lokasi, dan pelacakan stok secara real-time.</p>
        </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-12 md:p-20 bg-[rgb(var(--color-background))] relative min-h-screen transition-all duration-300">
        <!-- Floating Theme Toggle at Login Page -->
        <div x-data="{
                themeMode: 'dark',
                init() {
                    this.themeMode = localStorage.getItem('system_theme_mode') || 'dark';
                    this.apply(this.themeMode);
                },
                toggle() {
                    this.themeMode = this.themeMode === 'light' ? 'dark' : 'light';
                    localStorage.setItem('system_theme_mode', this.themeMode);
                    this.apply(this.themeMode);
                },
                apply(mode) {
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
                }
            }" 
            class="absolute top-6 right-6 z-20">
            <button @click="toggle()" class="w-10 h-10 rounded-full bg-[rgba(var(--color-surface-dark),0.5)] border border-[var(--color-border)] flex items-center justify-center text-[rgb(var(--color-on-surface))] hover:bg-[rgb(var(--color-surface-dark))] transition-all duration-200 active:scale-95 focus:outline-none shadow-lg">
                <span class="material-symbols-outlined text-lg" x-text="themeMode === 'light' ? 'dark_mode' : 'light_mode'"></span>
            </button>
        </div>

        <!-- Ambient background glows -->
        <div class="absolute top-[10%] right-[10%] w-[300px] h-[300px] bg-[#FFDD32]/5 rounded-full blur-[100px] pointer-events-none animate-pulse-glow"></div>
        <div class="absolute bottom-[10%] left-[10%] w-[300px] h-[300px] bg-[#FFDD32]/3 rounded-full blur-[100px] pointer-events-none animate-pulse-glow" style="animation-delay: 2.5s;"></div>

        <!-- Card Container -->
        <div class="w-full max-w-[440px] bg-[rgba(var(--color-surface-dark),0.35)] border border-[var(--color-border)] backdrop-blur-2xl rounded-3xl p-8 sm:p-10 shadow-2xl flex flex-col space-y-8 relative z-10 transition-all duration-300">
            <!-- Form Header -->
            <div class="space-y-2">
                <h2 class="text-2xl sm:text-3xl font-black text-[rgb(var(--color-primary-text))] font-outfit tracking-tight">Selamat datang kembali</h2>
                <p class="text-sm text-[rgba(var(--color-primary-text),0.6)] leading-relaxed font-medium font-outfit">Masuk untuk mengelola inventaris gudang dan melacak transaksi WMS.</p>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Login Form -->
            <form wire:submit="login" class="space-y-6">
                <!-- Email Address -->
                <div class="space-y-2.5">
                    <label for="email" class="block text-xs font-bold text-[rgba(var(--color-primary-text),0.6)] uppercase tracking-widest pl-1 font-outfit">Alamat Email Perusahaan</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-[rgb(var(--color-primary))] transition-colors duration-200">alternate_email</span>
                        <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="nama@wmslaf.com" class="w-full bg-[rgb(var(--color-input-bg))] border border-[var(--color-border)] rounded-2xl py-3.5 pl-12 pr-4 text-sm text-[rgb(var(--color-primary-text))] placeholder:text-slate-600 focus:outline-none focus:border-[rgba(var(--color-primary),0.5)] focus:ring-1 focus:ring-[rgba(var(--color-primary),0.5)] transition-all font-semibold">
                    </div>
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1.5 text-xs text-rose-500 font-medium pl-1" />
                </div>

                <!-- Password -->
                <div class="space-y-2.5" x-data="{ showPassword: false }">
                    <label for="password" class="block text-xs font-bold text-[rgba(var(--color-primary-text),0.6)] uppercase tracking-widest pl-1 font-outfit">Kata Sandi Aman</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-[rgb(var(--color-primary))] transition-colors duration-200">lock</span>
                        <input wire:model="form.password" id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full bg-[rgb(var(--color-input-bg))] border border-[var(--color-border)] rounded-2xl py-3.5 pl-12 pr-12 text-sm text-[rgb(var(--color-primary-text))] placeholder:text-slate-600 focus:outline-none focus:border-[rgba(var(--color-primary),0.5)] focus:ring-1 focus:ring-[rgba(var(--color-primary),0.5)] transition-all font-semibold tracking-widest">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors duration-200 focus:outline-none flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-1.5 text-xs text-rose-500 font-medium pl-1" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember" class="inline-flex items-center cursor-pointer select-none">
                        <input wire:model="form.remember" id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-[var(--color-border)] bg-[rgb(var(--color-input-bg))] text-[rgb(var(--color-primary))] focus:ring-[rgba(var(--color-primary),0.2)] focus:ring-offset-[rgb(var(--color-background))] transition-all cursor-pointer">
                        <span class="ms-2.5 text-xs text-[rgba(var(--color-primary-text),0.6)] font-semibold hover:text-[rgb(var(--color-primary-text))] transition-colors font-outfit">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-xs text-[rgb(var(--color-primary))] font-bold hover:text-[rgba(var(--color-primary),0.8)] transition-colors duration-200 font-outfit" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-4 font-outfit">
                    <button type="submit" class="w-full text-sm font-extrabold py-3.5 px-6 rounded-2xl flex justify-center items-center gap-2 submit-btn-theme active:scale-[0.98] transition-all duration-200 animate-btn">
                        <span>Masuk ke Portal</span>
                        <span class="material-symbols-outlined text-lg font-bold">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
