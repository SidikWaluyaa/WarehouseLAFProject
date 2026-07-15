<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-primary text-on-primary border border-transparent rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-opacity-95 shadow-lg shadow-accent-glow active:scale-95 transition-all duration-150 cursor-pointer focus:outline-none']) }}>
    {{ $slot }}
</button>
