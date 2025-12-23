<?php

namespace App\Livewire\Public\Events;

use App\Models\ErasmusEvent;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Calendar extends Component
{
    /**
     * Current date for the calendar view.
     */
    #[Url(as: 'fecha')]
    public string $currentDate = '';

    /**
     * View mode: month, week, or day.
     */
    #[Url(as: 'vista')]
    public string $viewMode = 'month';

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public string $selectedProgram = '';

    /**
     * Filter by event type.
     */
    #[Url(as: 'tipo')]
    public string $selectedEventType = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        if (empty($this->currentDate)) {
            $this->currentDate = now()->format('Y-m-d');
        }
    }

    /**
     * Get the current date as Carbon instance.
     */
    #[Computed]
    public function currentDateCarbon(): Carbon
    {
        return Carbon::parse($this->currentDate);
    }

    /**
     * Available programs for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Available event types.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function eventTypes(): array
    {
        return [
            'apertura' => __('Apertura'),
            'cierre' => __('Cierre'),
            'entrevista' => __('Entrevistas'),
            'publicacion_provisional' => __('Listado provisional'),
            'publicacion_definitivo' => __('Listado definitivo'),
            'reunion_informativa' => __('Reunión informativa'),
            'otro' => __('Otro'),
        ];
    }

    /**
     * Get events for the current month/week/day.
     *
     * @return Collection<int, ErasmusEvent>
     */
    #[Computed]
    public function calendarEvents(): Collection
    {
        $query = ErasmusEvent::query()
            ->with(['program', 'call'])
            ->public();

        // Apply filters
        if ($this->selectedProgram) {
            $query->forProgram((int) $this->selectedProgram);
        }

        if ($this->selectedEventType) {
            $query->byType($this->selectedEventType);
        }

        // Filter by date range based on view mode
        $date = $this->currentDateCarbon;

        return match ($this->viewMode) {
            'week' => $query->inDateRange(
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek()
            )->orderBy('start_date')->get(),
            'day' => $query->forDate($date)->orderBy('start_date')->get(),
            default => $query->forMonth($date->year, $date->month)
                ->orderBy('start_date')
                ->get(),
        };
    }

    /**
     * Get events grouped by date.
     *
     * @return array<string, \Illuminate\Support\Collection<int, ErasmusEvent>>
     */
    #[Computed]
    public function eventsByDate(): array
    {
        $grouped = $this->calendarEvents->groupBy(fn ($event) => $event->start_date->format('Y-m-d'));
        
        // Convert to array but keep Collections as values
        $result = [];
        foreach ($grouped as $date => $events) {
            $result[$date] = $events; // Keep as Collection
        }
        
        return $result;
    }

    /**
     * Get calendar days for month view.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function calendarDays(): array
    {
        $date = $this->currentDateCarbon;
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();

        $days = [];
        $currentDay = $startOfCalendar->copy();

        while ($currentDay->lte($endOfCalendar)) {
            $dayKey = $currentDay->format('Y-m-d');
            $events = $this->eventsByDate[$dayKey] ?? collect();
            // Keep as Collection to preserve object properties
            $eventsCollection = $events instanceof \Illuminate\Support\Collection ? $events : collect($events);

            $days[] = [
                'date' => $currentDay->copy(),
                'isCurrentMonth' => $currentDay->month === $date->month,
                'isToday' => $currentDay->isToday(),
                'events' => $eventsCollection,
                'eventsCount' => $eventsCollection->count(),
            ];

            $currentDay->addDay();
        }

        return $days;
    }

    /**
     * Get week days for week view.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function weekDays(): array
    {
        $date = $this->currentDateCarbon;
        $startOfWeek = $date->copy()->startOfWeek();
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayKey = $day->format('Y-m-d');
            $events = $this->eventsByDate[$dayKey] ?? [];
            // Ensure events is a Collection for week view
            $eventsCollection = is_array($events) ? collect($events) : (is_a($events, \Illuminate\Support\Collection::class) ? $events : collect());

            $days[] = [
                'date' => $day,
                'isToday' => $day->isToday(),
                'events' => $eventsCollection,
            ];
        }

        return $days;
    }

    /**
     * Statistics for the calendar.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $date = $this->currentDateCarbon;

        return [
            'this_month' => ErasmusEvent::public()
                ->forMonth($date->year, $date->month)
                ->count(),
            'upcoming' => ErasmusEvent::public()->upcoming()->count(),
        ];
    }

    /**
     * Navigate to previous month/week/day.
     */
    public function previous(): void
    {
        $date = $this->currentDateCarbon;

        $this->currentDate = match ($this->viewMode) {
            'week' => $date->copy()->subWeek()->format('Y-m-d'),
            'day' => $date->copy()->subDay()->format('Y-m-d'),
            default => $date->copy()->subMonth()->format('Y-m-d'),
        };
    }

    /**
     * Navigate to next month/week/day.
     */
    public function next(): void
    {
        $date = $this->currentDateCarbon;

        $this->currentDate = match ($this->viewMode) {
            'week' => $date->copy()->addWeek()->format('Y-m-d'),
            'day' => $date->copy()->addDay()->format('Y-m-d'),
            default => $date->copy()->addMonth()->format('Y-m-d'),
        };
    }

    /**
     * Go to today's date.
     */
    public function goToToday(): void
    {
        $this->currentDate = now()->format('Y-m-d');
    }

    /**
     * Go to a specific date.
     */
    public function goToDate(string $date): void
    {
        $this->currentDate = $date;
    }

    /**
     * Change view mode.
     */
    public function changeView(string $view): void
    {
        $this->viewMode = $view;
    }

    /**
     * Reset filters.
     */
    public function resetFilters(): void
    {
        $this->reset(['selectedProgram', 'selectedEventType']);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.events.calendar')
            ->layout('components.layouts.public', [
                'title' => __('Calendario de Eventos Erasmus+ - Centro de Movilidad Internacional'),
                'description' => __('Consulta el calendario completo de eventos Erasmus+. Navega por meses, semanas o días para ver todas las fechas importantes.'),
            ]);
    }
}

