<?php

namespace App\Livewire\Admin\AcademicYears;

use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The academic year being edited.
     */
    public AcademicYear $academicYear;

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
    public function mount(AcademicYear $academic_year): void
    {
        $this->authorize('update', $academic_year);

        $this->academicYear = $academic_year;

        // Load academic year data
        $this->year = $academic_year->year;
        $this->start_date = $academic_year->start_date->format('Y-m-d');
        $this->end_date = $academic_year->end_date->format('Y-m-d');
        $this->is_current = $academic_year->is_current;
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
            // Unset other current academic years (excluding the current one being edited)
            AcademicYear::where('is_current', true)
                ->where('id', '!=', $this->academicYear->id)
                ->update(['is_current' => false]);
        }
    }

    /**
     * Update the academic year.
     */
    public function update(): void
    {
        $validated = $this->validate([
            'year' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/', Rule::unique('academic_years', 'year')->ignore($this->academicYear->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        // If marking as current, unset other current academic years first (excluding this one)
        if ($validated['is_current'] ?? false) {
            AcademicYear::where('is_current', true)
                ->where('id', '!=', $this->academicYear->id)
                ->update(['is_current' => false]);
        }

        $this->academicYear->update($validated);

        $this->dispatch('academic-year-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.academic-years.show', $this->academicYear), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.academic-years.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Año Académico'),
            ]);
    }
}
