<?php

namespace App\Livewire\Public\Documents;

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
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
     * Search query for filtering documents.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by category.
     */
    #[Url(as: 'categoria')]
    public string $category = '';

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
     * Filter by document type.
     */
    #[Url(as: 'tipo')]
    public string $documentType = '';

    /**
     * Available categories for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, DocumentCategory>
     */
    #[Computed]
    public function availableCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return DocumentCategory::orderBy('order')
            ->orderBy('name')
            ->get();
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
     * Available document types for filtering.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function availableDocumentTypes(): array
    {
        return [
            'convocatoria' => __('Convocatoria'),
            'modelo' => __('Modelo'),
            'seguro' => __('Seguro'),
            'consentimiento' => __('Consentimiento'),
            'guia' => __('Guía'),
            'faq' => __('FAQ'),
            'otro' => __('Otro'),
        ];
    }

    /**
     * Statistics for the documents section.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = Document::where('is_active', true);

        $totalDownloads = (clone $baseQuery)->sum('download_count');

        return [
            'total' => (clone $baseQuery)->count(),
            'categories' => DocumentCategory::has('documents')->count(),
            'total_downloads' => $totalDownloads,
        ];
    }

    /**
     * Get paginated and filtered documents.
     */
    #[Computed]
    public function documents(): LengthAwarePaginator
    {
        return Document::query()
            ->with(['category', 'program', 'academicYear', 'creator'])
            ->where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->program, fn ($query) => $query->where('program_id', $this->program))
            ->when($this->academicYear, fn ($query) => $query->where('academic_year_id', $this->academicYear))
            ->when($this->documentType, fn ($query) => $query->where('document_type', $this->documentType))
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'program', 'academicYear', 'documentType']);
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
    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function updatedAcademicYear(): void
    {
        $this->resetPage();
    }

    public function updatedDocumentType(): void
    {
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.documents.index')
            ->layout('components.layouts.public', [
                'title' => __('Documentos Erasmus+ - Centro de Movilidad Internacional'),
                'description' => __('Accede a todos los documentos oficiales, guías, modelos y formularios relacionados con los programas Erasmus+.'),
            ]);
    }
}

