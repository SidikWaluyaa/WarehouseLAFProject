<?php

namespace App\Livewire\Calendar;

use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\PoHeader;
use App\Traits\HasAlerts;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    use HasAlerts;

    #[Url(as: 'month')]
    public int $currentMonth;

    #[Url(as: 'year')]
    public int $currentYear;

    #[Url(as: 'date')]
    public string $selectedDate;

    // Filter Kategori: 'all', 'inbound', 'outbound', 'po'
    #[Url(as: 'category')]
    public string $filterCategory = 'all';

    public array $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function mount(?string $date = null): void
    {
        Carbon::setLocale('id');
        $parsed = $date ? Carbon::parse($date) : now();
        $this->selectedDate = $parsed->format('Y-m-d');
        $this->currentMonth = (int) $parsed->month;
        $this->currentYear = (int) $parsed->year;
    }

    public function updatedCurrentMonth(int|string|null $value): void
    {
        $this->currentMonth = max(1, min(12, (int) $value));
        $this->syncSelectedDate();
    }

    public function updatedCurrentYear(int|string|null $value): void
    {
        $this->currentYear = (int) $value;
        $this->syncSelectedDate();
    }

    protected function syncSelectedDate(): void
    {
        $currentDay = (int) Carbon::parse($this->selectedDate)->day;
        $maxDays = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->daysInMonth;
        $clampedDay = min($currentDay, $maxDays);

        $this->selectedDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $clampedDay)->format('Y-m-d');
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $parsed = Carbon::parse($date);
        $this->currentMonth = (int) $parsed->month;
        $this->currentYear = (int) $parsed->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = (int) $date->month;
        $this->currentYear = (int) $date->year;
        $this->syncSelectedDate();
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = (int) $date->month;
        $this->currentYear = (int) $date->year;
        $this->syncSelectedDate();
    }

    public function goToToday(): void
    {
        $today = now();
        $this->currentMonth = (int) $today->month;
        $this->currentYear = (int) $today->year;
        $this->selectedDate = $today->format('Y-m-d');
    }

    public function setFilterCategory(string $category): void
    {
        $this->filterCategory = in_array($category, ['all', 'inbound', 'outbound', 'po']) ? $category : 'all';
    }

    public function getYearsList(): array
    {
        $nowYear = (int) now()->year;
        $minYear = $nowYear - 5;
        $maxYear = $nowYear + 2;

        try {
            $bmMin = BarangMasuk::min('tanggal');
            $bkMin = BarangKeluar::min('tanggal');
            $poMin = PoHeader::min('tanggal');
            $earliestDate = collect([$bmMin, $bkMin, $poMin])->filter()->min();
            if ($earliestDate) {
                $earliestYear = (int) Carbon::parse($earliestDate)->year;
                if ($earliestYear < $minYear) {
                    $minYear = $earliestYear;
                }
            }
        } catch (\Throwable $e) {
            // fallback gracefully
        }

        // Pastikan currentYear juga masuk jika di luar rentang
        if ($this->currentYear < $minYear) {
            $minYear = $this->currentYear;
        }
        if ($this->currentYear > $maxYear) {
            $maxYear = $this->currentYear;
        }

        return range($minYear, $maxYear);
    }

    /**
     * Bangun susunan array hari dalam grid kalender bulanan 7 kolom
     */
    protected function buildCalendarDays(): array
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startDayOfWeek = $startOfMonth->dayOfWeek;

        $calendarDays = [];
        for ($i = $startDayOfWeek - 1; $i >= 0; $i--) {
            $prevDate = $startOfMonth->copy()->subDays($i + 1);
            $calendarDays[] = [
                'date' => $prevDate->format('Y-m-d'),
                'day' => $prevDate->day,
                'isCurrentMonth' => false,
            ];
        }

        for ($d = 1; $d <= $endOfMonth->day; $d++) {
            $currentDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $d);
            $calendarDays[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $d,
                'isCurrentMonth' => true,
            ];
        }

        $totalDays = count($calendarDays);
        $remaining = (7 - ($totalDays % 7)) % 7;
        for ($i = 1; $i <= $remaining; $i++) {
            $nextDate = $endOfMonth->copy()->addDays($i);
            $calendarDays[] = [
                'date' => $nextDate->format('Y-m-d'),
                'day' => $nextDate->day,
                'isCurrentMonth' => false,
            ];
        }

        return $calendarDays;
    }

    /**
     * Hitung ringkasan transaksi per tanggal untuk grid kalender
     */
    protected function buildEventsMap(array $calendarDays): array
    {
        if (empty($calendarDays)) {
            return [];
        }

        $minDate = $calendarDays[0]['date'];
        $maxDate = end($calendarDays)['date'];

        $inboundList = BarangMasuk::withSum('details as total_qty', 'qty_masuk')
            ->whereBetween('tanggal', [$minDate, $maxDate])
            ->get()
            ->groupBy(fn($m) => Carbon::parse($m->tanggal)->format('Y-m-d'));

        $outboundList = BarangKeluar::withSum('details as total_qty', 'qty_keluar')
            ->whereBetween('tanggal', [$minDate, $maxDate])
            ->get()
            ->groupBy(fn($k) => Carbon::parse($k->tanggal)->format('Y-m-d'));

        $poList = PoHeader::withSum('poDetails as total_qty', 'jumlah')
            ->whereBetween('tanggal', [$minDate, $maxDate])
            ->get()
            ->groupBy(fn($p) => Carbon::parse($p->tanggal)->format('Y-m-d'));

        $eventsMap = [];
        foreach ($calendarDays as $cDay) {
            $dStr = $cDay['date'];
            $inboundItems = $inboundList->get($dStr);
            $outboundItems = $outboundList->get($dStr);
            $poItems = $poList->get($dStr);

            $eventsMap[$dStr] = [
                'masuk_count' => $inboundItems ? $inboundItems->count() : 0,
                'masuk_qty' => (int) ($inboundItems ? $inboundItems->sum('total_qty') : 0),
                'keluar_count' => $outboundItems ? $outboundItems->count() : 0,
                'keluar_qty' => (int) ($outboundItems ? $outboundItems->sum('total_qty') : 0),
                'po_count' => $poItems ? $poItems->count() : 0,
                'po_qty' => (int) ($poItems ? $poItems->sum('total_qty') : 0),
            ];
        }

        return $eventsMap;
    }

    /**
     * Ambil rincian transaksi untuk tanggal aktif (selectedDate)
     */
    protected function getSelectedDateDetails(): array
    {
        $selectedMasuk = BarangMasuk::with(['supplier', 'details.produk'])
            ->whereDate('tanggal', $this->selectedDate)
            ->latest('id')
            ->get();

        $selectedKeluar = BarangKeluar::with(['pelanggan', 'details.produk'])
            ->whereDate('tanggal', $this->selectedDate)
            ->latest('id')
            ->get();

        $selectedPo = PoHeader::with(['supplier', 'poDetails.produk'])
            ->whereDate('tanggal', $this->selectedDate)
            ->latest('id')
            ->get();

        $dailyStats = [
            'masuk_count' => $selectedMasuk->count(),
            'masuk_qty' => (int) $selectedMasuk->sum(fn($m) => $m->details->sum('qty_masuk')),
            'keluar_count' => $selectedKeluar->count(),
            'keluar_qty' => (int) $selectedKeluar->sum(fn($k) => $k->details->sum('qty_keluar')),
            'po_count' => $selectedPo->count(),
            'po_qty' => (int) $selectedPo->sum(fn($p) => $p->poDetails->sum('jumlah')),
        ];

        return [
            'selectedMasuk' => $selectedMasuk,
            'selectedKeluar' => $selectedKeluar,
            'selectedPo' => $selectedPo,
            'dailyStats' => $dailyStats,
        ];
    }

    public function render(): View
    {
        $calendarDays = $this->buildCalendarDays();
        $eventsMap = $this->buildEventsMap($calendarDays);
        $dateDetails = $this->getSelectedDateDetails();

        $monthTitle = ($this->monthNames[$this->currentMonth] ?? Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->locale('id')->translatedFormat('F')) . ' ' . $this->currentYear;
        $selectedDateTitle = Carbon::parse($this->selectedDate)->locale('id')->translatedFormat('l, d F Y');

        return view('livewire.calendar.index', array_merge($dateDetails, [
            'calendarDays' => $calendarDays,
            'eventsMap' => $eventsMap,
            'monthTitle' => $monthTitle,
            'selectedDateTitle' => $selectedDateTitle,
            'todayDate' => now()->format('Y-m-d'),
            'monthNames' => $this->monthNames,
            'yearsList' => $this->getYearsList(),
        ]));
    }
}
