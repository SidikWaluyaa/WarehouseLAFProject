@props([
    'start',
    'end',
    'theme' => 'primary',
    'placeholder' => 'Pilih rentang tanggal...'
])

<div class="glass-card rounded-2xl p-4 flex flex-col justify-between space-y-2"
     wire:ignore
     x-data="{
         fp: null,
         init() {
             this.$nextTick(() => {
                 this.fp = flatpickr(this.$refs.dateInput, {
                     mode: 'range',
                     dateFormat: 'Y-m-d',
                     altInput: true,
                     altFormat: 'd M Y',
                     allowInput: false,
                     showMonths: 1,
                     defaultDate: [
                         $wire.get('{{ $start }}') || null,
                         $wire.get('{{ $end }}') || null
                     ].filter(d => d),
                     locale: {
                         rangeSeparator: '  →  ',
                         firstDayOfWeek: 1,
                     },
                     onReady: (selectedDates, dateStr, instance) => {
                         instance.calendarContainer.classList.add('flatpickr-{{ $theme }}');
                     },
                     onChange: (selectedDates, dateStr, instance) => {
                         if (selectedDates.length === 2) {
                             const start = instance.formatDate(selectedDates[0], 'Y-m-d');
                             const end = instance.formatDate(selectedDates[1], 'Y-m-d');
                             $wire.setDateRange(start, end);
                         } else if (selectedDates.length === 0) {
                             $wire.setDateRange(null, null);
                         }
                     }
                 });
             });

             this.$watch('$wire.{{ $start }}', value => {
                 if (!value && this.fp) {
                     this.fp.clear();
                 }
             });
         },
         destroy() {
             if (this.fp) this.fp.destroy();
         }
     }">
    <span class="text-[10px] font-bold tracking-wider text-on-surface-variant/60 uppercase flex items-center gap-1.5">
        <span class="material-symbols-outlined text-{{ $theme }}-400 text-sm">date_range</span>
        Filter Tanggal
    </span>
    <div class="relative">
        <input x-ref="dateInput" type="text" readonly
               class="w-full bg-surface-container-low border border-white/10 rounded-xl py-2.5 px-3 pr-9 text-xs text-on-surface cursor-pointer focus:ring-1 focus:ring-{{ $theme }}-500 focus:border-{{ $theme }}-500 transition-all placeholder:text-on-surface-variant/40"
               placeholder="{{ $placeholder }}">
        <template x-if="$wire.get('{{ $start }}')">
            <button @click="fp.clear(); $wire.setDateRange(null, null);" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </template>
    </div>
</div>
