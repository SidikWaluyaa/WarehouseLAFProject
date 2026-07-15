<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-white/5 border border-white/10 rounded-xl font-bold text-xs text-on-surface uppercase tracking-widest hover:bg-white/10 active:scale-95 transition-all duration-150 cursor-pointer disabled:opacity-25 focus:outline-none']) }}>
    {{ $slot }}
</button>
