<?php

namespace App\Livewire\Admin\Events;

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * View mode: 'list' or 'calendar'.
     */
    #[Url(as: 'vista')]
    public string $viewMode = 'list';

    /**
     * Search query for filtering events.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'start_date';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'desc';

    /**
     * Filter to show deleted events.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public ?int $programFilter = null;

    /**
     * Filter by call.
     */
    #[Url(as: 'convocatoria')]
    public ?int $callFilter = null;

    /**
     * Filter by event type.
     */
    #[Url(as: 'tipo')]
    public string $eventTypeFilter = '';

    /**
     * Filter by date.
     */
    #[Url(as: 'fecha')]
    public string $dateFilter = '';

    /**
     * Number of items per page.
     */
    #[Url(as: 'por-pagina')]
    public int $perPage = 15;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Event ID to delete (for confirmation).
     */
    public ?int $eventToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Event ID to restore (for confirmation).
     */
    public ?int $eventToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Event ID to force delete (for confirmation).
     */
    public ?int $eventToForceDelete = null;

    /**
     * Current date for the calendar view.
     */
    #[Url(as: 'fecha-calendario')]
    public string $currentDate = '';

    /**
     * Calendar view mode: 'month', 'week', or 'day'.
     */
    #[Url(as: 'vista-calendario')]
    public string $calendarView = 'month';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', ErasmusEvent::class);

        if (empty($this->currentDate)) {
            $this->currentDate = now()->format('Y-m-d');
        }
    }

    /**
     * Get paginated and filtered events (for list view).
     */
    #[Computed]
    public function events(): LengthAwarePaginator
    {
        return ErasmusEvent::query()
            ->with(['program', 'call', 'creator', 'media'])
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->programFilter, fn ($query) => $query->forProgram($this->programFilter))
            ->when($this->callFilter, fn ($query) => $query->forCall($this->callFilter))
            ->when($this->eventTypeFilter, fn ($query) => $query->byType($this->eventTypeFilter))
            ->when($this->dateFilter, function ($query) {
                $date = Carbon::parse($this->dateFilter);
                $query->forDate($date);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('start_date', 'desc')
            ->paginate($this->perPage);
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
     * Get events for the calendar view.
     *
     * @return Collection<int, ErasmusEvent>
     */
    #[Computed]
    public function calendarEvents(): Collection
    {
        $query = ErasmusEvent::query()
            ->with(['program', 'call', 'creator', 'media']);

        // Apply filters
        if ($this->showDeleted === '0') {
            $query->whereNull('deleted_at');
        } elseif ($this->showDeleted === '1') {
            $query->onlyTrashed();
        }

        if ($this->programFilter) {
            $query->forProgram($this->programFilter);
        }

        if ($this->callFilter) {
            $query->forCall($this->callFilter);
        }

        if ($this->eventTypeFilter) {
            $query->byType($this->eventTypeFilter);
        }

        // Filter by date range based on view mode
        $date = $this->currentDateCarbon;

        return match ($this->calendarView) {
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
     * @return array<string, Collection<int, ErasmusEvent>>
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
     * Get events for day view.
     *
     * @return Collection<int, ErasmusEvent>
     */
    #[Computed]
    public function dayEvents(): Collection
    {
        $date = $this->currentDateCarbon;
        $dayKey = $date->format('Y-m-d');

        return $this->eventsByDate[$dayKey] ?? collect();
    }

    /**
     * Available programs for filtering (cached).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::getCachedActive();
    }

    /**
     * Available calls for filtering (filtered by program if selected).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Call>
     */
    #[Computed]
    public function availableCalls(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Call::query();

        if ($this->programFilter) {
            $query->where('program_id', $this->programFilter);
        }

        return $query->orderBy('title')->get();
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
     * Sort by field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'showDeleted', 'programFilter', 'callFilter', 'eventTypeFilter', 'dateFilter']);
        $this->showDeleted = '0'; // Reset to '0' (no)
        $this->resetPage();
    }

    /**
     * Handle search input changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle filter changes.
     */
    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Handle program filter changes.
     */
    public function updatedProgramFilter(): void
    {
        // Reset call filter when program changes
        $this->callFilter = null;
        $this->resetPage();
    }

    /**
     * Check if user can create events.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', ErasmusEvent::class) ?? false;
    }

    /**
     * Check if user can view deleted events.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', ErasmusEvent::class) ?? false;
    }

    /**
     * Get event type badge configuration.
     *
     * @return array<string, mixed>
     */
    public function getEventTypeConfig(string $eventType): array
    {
        return match ($eventType) {
            'apertura' => ['variant' => 'success', 'icon' => 'play-circle', 'label' => __('Apertura')],
            'cierre' => ['variant' => 'danger', 'icon' => 'stop-circle', 'label' => __('Cierre')],
            'entrevista' => ['variant' => 'info', 'icon' => 'chat-bubble-left-right', 'label' => __('Entrevistas')],
            'publicacion_provisional' => ['variant' => 'warning', 'icon' => 'document-text', 'label' => __('Listado provisional')],
            'publicacion_definitivo' => ['variant' => 'success', 'icon' => 'document-check', 'label' => __('Listado definitivo')],
            'reunion_informativa' => ['variant' => 'primary', 'icon' => 'user-group', 'label' => __('Reunión informativa')],
            default => ['variant' => 'neutral', 'icon' => 'calendar', 'label' => __('Otro')],
        };
    }

    /**
     * Get event status badge configuration.
     *
     * @return array<string, mixed>
     */
    public function getEventStatusConfig(ErasmusEvent $event): array
    {
        if ($event->trashed()) {
            return ['variant' => 'danger', 'label' => __('Eliminado')];
        }

        if ($event->isUpcoming()) {
            return ['variant' => 'success', 'label' => __('Próximo')];
        }

        if ($event->isToday()) {
            return ['variant' => 'info', 'label' => __('Hoy')];
        }

        return ['variant' => 'neutral', 'label' => __('Pasado')];
    }

    // ============================================
    // DELETION METHODS
    // ============================================

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $eventId): void
    {
        $this->eventToDelete = $eventId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete an event (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->eventToDelete) {
            return;
        }

        $event = ErasmusEvent::with(['program', 'call'])->findOrFail($this->eventToDelete);

        $this->authorize('delete', $event);

        $event->delete();

        $this->eventToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('event-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $eventId): void
    {
        $this->eventToRestore = $eventId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted event.
     */
    public function restore(): void
    {
        if (! $this->eventToRestore) {
            return;
        }

        $event = ErasmusEvent::onlyTrashed()
            ->with(['program', 'call'])
            ->findOrFail($this->eventToRestore);

        $this->authorize('restore', $event);

        $event->restore();

        $this->eventToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('event-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $eventId): void
    {
        $this->eventToForceDelete = $eventId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete an event (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->eventToForceDelete) {
            return;
        }

        $event = ErasmusEvent::onlyTrashed()
            ->with(['program', 'call'])
            ->findOrFail($this->eventToForceDelete);

        $this->authorize('forceDelete', $event);

        $event->forceDelete();

        $this->eventToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('event-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    // ============================================
    // CALENDAR NAVIGATION METHODS
    // ============================================

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
     * Navigate to previous month.
     */
    public function previousMonth(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->subMonth()->format('Y-m-d');
    }

    /**
     * Navigate to next month.
     */
    public function nextMonth(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->addMonth()->format('Y-m-d');
    }

    /**
     * Navigate to previous week.
     */
    public function previousWeek(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->subWeek()->format('Y-m-d');
    }

    /**
     * Navigate to next week.
     */
    public function nextWeek(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->addWeek()->format('Y-m-d');
    }

    /**
     * Navigate to previous day.
     */
    public function previousDay(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->subDay()->format('Y-m-d');
    }

    /**
     * Navigate to next day.
     */
    public function nextDay(): void
    {
        $date = $this->currentDateCarbon;
        $this->currentDate = $date->copy()->addDay()->format('Y-m-d');
    }

    /**
     * Change calendar view (month, week, day).
     */
    public function changeCalendarView(string $view): void
    {
        $this->calendarView = $view;
    }

    /**
     * Change view mode (list or calendar).
     */
    public function changeViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    /**
     * Navigate to previous period (month/week/day based on calendar view).
     */
    public function previous(): void
    {
        $date = $this->currentDateCarbon;

        $this->currentDate = match ($this->calendarView) {
            'week' => $date->copy()->subWeek()->format('Y-m-d'),
            'day' => $date->copy()->subDay()->format('Y-m-d'),
            default => $date->copy()->subMonth()->format('Y-m-d'),
        };
    }

    /**
     * Navigate to next period (month/week/day based on calendar view).
     */
    public function next(): void
    {
        $date = $this->currentDateCarbon;

        $this->currentDate = match ($this->calendarView) {
            'week' => $date->copy()->addWeek()->format('Y-m-d'),
            'day' => $date->copy()->addDay()->format('Y-m-d'),
            default => $date->copy()->addMonth()->format('Y-m-d'),
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.events.index')
            ->layout('components.layouts.app', [
                'title' => __('Eventos Erasmus+'),
            ]);
    }
}
