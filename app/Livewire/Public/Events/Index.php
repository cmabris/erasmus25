<?php

namespace App\Livewire\Public\Events;

use App\Models\AcademicYear;
use App\Models\ErasmusEvent;
use App\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * Search query for filtering events.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public string $program = '';

    /**
     * Filter by event type.
     */
    #[Url(as: 'tipo')]
    public string $eventType = '';

    /**
     * Filter date from.
     */
    #[Url(as: 'desde')]
    public string $dateFrom = '';

    /**
     * Filter date to.
     */
    #[Url(as: 'hasta')]
    public string $dateTo = '';

    /**
     * Show past events.
     */
    #[Url(as: 'pasados')]
    public bool $showPast = false;

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
     * Statistics for the events section.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = ErasmusEvent::public();

        return [
            'total' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)
                ->whereMonth('start_date', now()->month)
                ->whereYear('start_date', now()->year)
                ->count(),
            'upcoming' => (clone $baseQuery)->upcoming()->count(),
        ];
    }

    /**
     * Get paginated and filtered events.
     */
    #[Computed]
    public function events(): LengthAwarePaginator
    {
        return ErasmusEvent::query()
            ->with(['program', 'call'])
            ->public()
            ->when(! $this->showPast, fn ($query) => $query->upcoming())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->program, fn ($query) => $query->forProgram((int) $this->program))
            ->when($this->eventType, fn ($query) => $query->byType($this->eventType))
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('start_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('start_date', '<=', $this->dateTo);
            })
            ->orderBy('start_date', $this->showPast ? 'desc' : 'asc')
            ->paginate(12);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'program', 'eventType', 'dateFrom', 'dateTo', 'showPast']);
        $this->resetPage();
    }

    /**
     * Toggle showing past events.
     */
    public function togglePastEvents(): void
    {
        $this->showPast = ! $this->showPast;
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
    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function updatedEventType(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.events.index')
            ->layout('components.layouts.public', [
                'title' => __('Eventos Erasmus+ - Centro de Movilidad Internacional'),
                'description' => __('Consulta el calendario de eventos Erasmus+. Reuniones informativas, aperturas y cierres de convocatorias, entrevistas y publicaciones de resultados.'),
            ]);
    }
}

