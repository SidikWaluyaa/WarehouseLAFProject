@props([
    'icon' => 'inbox',
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada data untuk ditampilkan saat ini.',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-16 px-6 text-center rounded-3xl border border-white/5 bg-white/[0.02] backdrop-blur-md relative overflow-hidden']) }}>
    <!-- Decorative background glow -->
    <div class="absolute -inset-24 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative flex flex-col items-center max-w-sm space-y-4">
        <!-- Icon Container -->
        <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-primary shadow-lg transition-transform hover:scale-105 duration-300">
            <span class="material-symbols-outlined text-3xl">{{ $icon }}</span>
        </div>

        <!-- Text content -->
        <div class="space-y-1">
            <h3 class="text-sm font-bold text-on-surface tracking-wide">{{ $title }}</h3>
            <p class="text-xs text-on-surface-variant/70 leading-relaxed">{{ $description }}</p>
        </div>

        <!-- Optional action/content slot -->
        @if ($slot->isNotEmpty())
            <div class="pt-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
