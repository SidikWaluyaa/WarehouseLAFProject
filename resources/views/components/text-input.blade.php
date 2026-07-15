@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white/5 border border-white/10 text-on-surface rounded-xl px-4 py-2.5 focus:border-primary focus:ring-1 focus:ring-primary/30 transition-all outline-none placeholder:text-on-surface-variant/40 disabled:opacity-50']) }}>
