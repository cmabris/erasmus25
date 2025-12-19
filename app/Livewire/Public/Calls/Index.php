<?php

namespace App\Livewire\Public\Calls;

use App\Models\AcademicYear;
use App\Models\Call;
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
     * Search query for filtering calls.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public string $program = '';

    /**
     * Filter by academic year.
     */
    #[Url(as: 'ano')]
    public string $academicYear = '';

    /**
     * Filter by call type (alumnado, personal).
     */
    #[Url(as: 'tipo')]
    public string $type = '';

    /**
     * Filter by modality (corta, larga).
     */
    #[Url(as: 'modalidad')]
    public string $modality = '';

    /**
     * Filter by status (abierta, cerrada).
     */
    #[Url(as: 'estado')]
    public string $status = '';

    /**
     * Available program types for filtering.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function programTypes(): array
    {
        return [
            '' => __('Todos los tipos'),
            'alumnado' => __('Alumnado'),
            'personal' => __('Personal'),
        ];
    }

    /**
     * Available modalities for filtering.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function modalities(): array
    {
        return [
            '' => __('Todas las modalidades'),
            'corta' => __('Corta duración'),
            'larga' => __('Larga duración'),
        ];
    }

    /**
     * Available statuses for filtering.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function statuses(): array
    {
        return [
            '' => __('Todos los estados'),
            'abierta' => __('Abierta'),
            'cerrada' => __('Cerrada'),
        ];
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
     * Available academic years for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, AcademicYear>
     */
    #[Computed]
    public function availableAcademicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::orderBy('year', 'desc')
            ->get();
    }

    /**
     * Statistics for the calls section.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = Call::whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at');

        return [
            'total' => (clone $baseQuery)->count(),
            'abierta' => (clone $baseQuery)->where('status', 'abierta')->count(),
            'cerrada' => (clone $baseQuery)->where('status', 'cerrada')->count(),
        ];
    }

    /**
     * Get paginated and filtered calls.
     */
    #[Computed]
    public function calls(): LengthAwarePaginator
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('requirements', 'like', "%{$this->search}%")
                        ->orWhere('documentation', 'like', "%{$this->search}%");
                });
            })
            ->when($this->program, fn ($query) => $query->where('program_id', $this->program))
            ->when($this->academicYear, fn ($query) => $query->where('academic_year_id', $this->academicYear))
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->when($this->modality, fn ($query) => $query->where('modality', $this->modality))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->paginate(12);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'program', 'academicYear', 'type', 'modality', 'status']);
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

    public function updatedAcademicYear(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedModality(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.calls.index')
            ->layout('components.layouts.public', [
                'title' => __('Convocatorias Erasmus+ - Movilidad Internacional'),
                'description' => __('Consulta las convocatorias abiertas y cerradas de movilidad internacional. Encuentra la oportunidad perfecta para tu formación y desarrollo profesional.'),
            ]);
    }
}
