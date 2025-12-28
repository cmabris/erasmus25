<?php

namespace App\Livewire\Admin\AcademicYears;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * Academic year (format: YYYY-YYYY).
     */
    public string $year = '';

    /**
     * Start date.
     */
    public string $start_date = '';

    /**
     * End date.
     */
    public string $end_date = '';

    /**
     * Whether this is the current academic year.
     */
    public bool $is_current = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', AcademicYear::class);
    }

    /**
     * Validate year format when it changes.
     */
    public function updatedYear(): void
    {
        $this->validateOnly('year', [
            'year' => ['regex:/^\d{4}-\d{4}$/'],
        ]);
    }

    /**
     * Validate dates when start_date changes.
     */
    public function updatedStartDate(): void
    {
        if ($this->start_date && $this->end_date) {
            $this->validateOnly('start_date', [
                'start_date' => ['date', 'before:end_date'],
            ]);
        }
    }

    /**
     * Validate dates when end_date changes.
     */
    public function updatedEndDate(): void
    {
        if ($this->start_date && $this->end_date) {
            $this->validateOnly('end_date', [
                'end_date' => ['date', 'after:start_date'],
            ]);
        }
    }

    /**
     * Handle is_current toggle - if set to true, unset other current years.
     */
    public function updatedIsCurrent(): void
    {
        if ($this->is_current) {
            // Unset other current academic years
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
        }
    }

    /**
     * Store the academic year.
     */
    public function store(): void
    {
        $validated = $this->validate((new StoreAcademicYearRequest)->rules());

        // If marking as current, unset other current academic years first
        if ($validated['is_current'] ?? false) {
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create($validated);

        $this->dispatch('academic-year-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.academic-years.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.academic-years.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Año Académico'),
            ]);
    }
}
