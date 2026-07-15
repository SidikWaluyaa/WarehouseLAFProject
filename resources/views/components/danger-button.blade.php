<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-red-500/10 border border-red-500/30 rounded-xl font-bold text-xs text-red-400 uppercase tracking-widest hover:bg-red-500/20 active:scale-95 transition-all duration-150 cursor-pointer focus:outline-none']) }}>
    {{ $slot }}
</button>
