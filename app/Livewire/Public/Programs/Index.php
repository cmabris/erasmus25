<?php

namespace App\Livewire\Public\Programs;

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
     * Search query for filtering programs.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by program type (KA1, KA2, etc.).
     */
    #[Url(as: 'tipo')]
    public string $type = '';

    /**
     * Filter to show only active programs.
     */
    #[Url(as: 'activos')]
    public bool $onlyActive = true;

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
            'KA1' => __('KA1 - Movilidad'),
            'KA2' => __('KA2 - Cooperación'),
            'JM' => __('Jean Monnet'),
            'DISCOVER' => __('DiscoverEU'),
        ];
    }

    /**
     * Statistics for the programs section.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'total' => Program::count(),
            'active' => Program::where('is_active', true)->count(),
            'mobility' => Program::where('code', 'like', 'KA1%')->where('is_active', true)->count(),
            'cooperation' => Program::where('code', 'like', 'KA2%')->where('is_active', true)->count(),
        ];
    }

    /**
     * Get paginated and filtered programs.
     */
    #[Computed]
    public function programs(): LengthAwarePaginator
    {
        return Program::query()
            ->when($this->onlyActive, fn ($query) => $query->where('is_active', true))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->type, function ($query) {
                $query->where('code', 'like', "{$this->type}%");
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(9);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'type']);
        $this->onlyActive = true;
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
     * Handle type filter changes.
     */
    public function updatedType(): void
    {
        $this->resetPage();
    }

    /**
     * Handle active filter changes.
     */
    public function updatedOnlyActive(): void
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.programs.index')
            ->layout('components.layouts.public', [
                'title' => __('Programas Erasmus+ - Movilidad Internacional'),
                'description' => __('Descubre todos los programas Erasmus+ disponibles: movilidad de estudiantes, formación profesional, cooperación entre centros educativos y más.'),
            ]);
    }
}
