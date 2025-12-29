<?php

namespace App\Livewire\Admin\Calls;

use App\Http\Requests\StoreCallRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * Program ID.
     */
    public int $program_id = 0;

    /**
     * Academic year ID.
     */
    public int $academic_year_id = 0;

    /**
     * Title.
     */
    public string $title = '';

    /**
     * Slug (auto-generated from title).
     */
    public string $slug = '';

    /**
     * Type (alumnado/personal).
     */
    public string $type = 'alumnado';

    /**
     * Modality (corta/larga).
     */
    public string $modality = 'corta';

    /**
     * Number of places.
     */
    public int $number_of_places = 1;

    /**
     * Destinations array.
     *
     * @var array<int, string>
     */
    public array $destinations = [];

    /**
     * New destination input (temporary).
     */
    public string $newDestination = '';

    /**
     * Estimated start date.
     */
    public string $estimated_start_date = '';

    /**
     * Estimated end date.
     */
    public string $estimated_end_date = '';

    /**
     * Requirements.
     */
    public string $requirements = '';

    /**
     * Documentation.
     */
    public string $documentation = '';

    /**
     * Selection criteria.
     */
    public string $selection_criteria = '';

    /**
     * Scoring table items.
     *
     * @var array<int, array{concept: string, max_points: int, description: string}>
     */
    public array $scoringTable = [];

    /**
     * New scoring item (temporary).
     */
    public array $newScoringItem = [
        'concept' => '',
        'max_points' => 0,
        'description' => '',
    ];

    /**
     * Status.
     */
    public string $status = 'borrador';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Call::class);

        // Initialize with at least one empty destination
        if (empty($this->destinations)) {
            $this->destinations = [''];
        }

        // Initialize with at least one empty scoring item
        if (empty($this->scoringTable)) {
            $this->scoringTable = [
                [
                    'concept' => '',
                    'max_points' => 0,
                    'description' => '',
                ],
            ];
        }
    }

    /**
     * Get all programs for dropdown.
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all academic years for dropdown.
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::query()
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Generate slug from title when title changes.
     */
    public function updatedTitle(): void
    {
        if ($this->title && ! $this->slug) {
            $this->slug = Str::slug($this->title);
        }

        // Validate title in real-time
        $this->validateOnly('title', [
            'title' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        if ($this->slug) {
            $this->validateOnly('slug', [
                'slug' => ['nullable', 'string', 'max:255', 'unique:calls,slug'],
            ]);
        }
    }

    /**
     * Validate estimated start date when it changes.
     */
    public function updatedEstimatedStartDate(): void
    {
        if ($this->estimated_start_date && $this->estimated_end_date) {
            $this->validateOnly('estimated_start_date', [
                'estimated_start_date' => ['nullable', 'date', 'before:estimated_end_date'],
            ]);
        } else {
            $this->validateOnly('estimated_start_date', [
                'estimated_start_date' => ['nullable', 'date'],
            ]);
        }
    }

    /**
     * Validate estimated end date when it changes.
     */
    public function updatedEstimatedEndDate(): void
    {
        if ($this->estimated_start_date && $this->estimated_end_date) {
            $this->validateOnly('estimated_end_date', [
                'estimated_end_date' => ['nullable', 'date', 'after:estimated_start_date'],
            ]);
        } else {
            $this->validateOnly('estimated_end_date', [
                'estimated_end_date' => ['nullable', 'date'],
            ]);
        }
    }

    /**
     * Validate program when it changes.
     */
    public function updatedProgramId(): void
    {
        $this->validateOnly('program_id', [
            'program_id' => ['required', 'exists:programs,id'],
        ]);
    }

    /**
     * Validate academic year when it changes.
     */
    public function updatedAcademicYearId(): void
    {
        $this->validateOnly('academic_year_id', [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);
    }

    /**
     * Validate number of places when it changes.
     */
    public function updatedNumberOfPlaces(): void
    {
        $this->validateOnly('number_of_places', [
            'number_of_places' => ['required', 'integer', 'min:1'],
        ]);
    }

    /**
     * Add a new destination.
     */
    public function addDestination(): void
    {
        if ($this->newDestination) {
            // If there's an empty destination at the end, replace it
            $lastIndex = count($this->destinations) - 1;
            if ($lastIndex >= 0 && empty(trim($this->destinations[$lastIndex] ?? ''))) {
                $this->destinations[$lastIndex] = $this->newDestination;
            } else {
                $this->destinations[] = $this->newDestination;
            }
            $this->newDestination = '';
        } else {
            // Add empty destination if newDestination is empty and there's no empty one at the end
            $lastIndex = count($this->destinations) - 1;
            if ($lastIndex < 0 || ! empty(trim($this->destinations[$lastIndex] ?? ''))) {
                $this->destinations[] = '';
            }
        }
    }

    /**
     * Remove a destination by index.
     */
    public function removeDestination(int $index): void
    {
        if (count($this->destinations) > 1) {
            unset($this->destinations[$index]);
            $this->destinations = array_values($this->destinations); // Re-index array
        }
    }

    /**
     * Update destination at index.
     */
    public function updateDestination(int $index, string $value): void
    {
        if (isset($this->destinations[$index])) {
            $this->destinations[$index] = $value;
        }
    }

    /**
     * Add a new scoring item.
     */
    public function addScoringItem(): void
    {
        $this->scoringTable[] = [
            'concept' => $this->newScoringItem['concept'] ?? '',
            'max_points' => (int) ($this->newScoringItem['max_points'] ?? 0),
            'description' => $this->newScoringItem['description'] ?? '',
        ];

        // Reset new scoring item
        $this->newScoringItem = [
            'concept' => '',
            'max_points' => 0,
            'description' => '',
        ];
    }

    /**
     * Remove a scoring item by index.
     */
    public function removeScoringItem(int $index): void
    {
        if (count($this->scoringTable) > 1) {
            unset($this->scoringTable[$index]);
            $this->scoringTable = array_values($this->scoringTable); // Re-index array
        }
    }

    /**
     * Update scoring item at index.
     */
    public function updateScoringItem(int $index, string $field, mixed $value): void
    {
        if (isset($this->scoringTable[$index])) {
            if ($field === 'max_points') {
                $this->scoringTable[$index][$field] = (int) $value;
            } else {
                $this->scoringTable[$index][$field] = $value;
            }
        }
    }

    /**
     * Store the call.
     */
    public function store(): void
    {
        // Filter empty destinations
        $filteredDestinations = array_filter($this->destinations, fn ($dest) => ! empty(trim($dest)));

        // Filter empty scoring items
        $filteredScoringTable = array_filter($this->scoringTable, function ($item) {
            return ! empty(trim($item['concept'] ?? '')) || ! empty(trim($item['description'] ?? ''));
        });

        // Prepare data for validation - merge with current component state
        $this->destinations = array_values($filteredDestinations);
        $this->scoringTable = array_values($filteredScoringTable);
        $this->slug = $this->slug ?: Str::slug($this->title);

        // Prepare data array for validation (map camelCase to snake_case for validation)
        $data = [
            'program_id' => $this->program_id,
            'academic_year_id' => $this->academic_year_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'modality' => $this->modality,
            'number_of_places' => $this->number_of_places,
            'destinations' => $this->destinations,
            'estimated_start_date' => $this->estimated_start_date ?: null,
            'estimated_end_date' => $this->estimated_end_date ?: null,
            'requirements' => $this->requirements ?: null,
            'documentation' => $this->documentation ?: null,
            'selection_criteria' => $this->selection_criteria ?: null,
            'scoring_table' => ! empty($this->scoringTable) ? $this->scoringTable : null,
            'status' => $this->status,
        ];

        // Validate using FormRequest rules with Validator
        $rules = (new StoreCallRequest)->rules();
        $messages = (new StoreCallRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // Add created_by
        $validated['created_by'] = auth()->id();

        // Create the call
        $call = Call::create($validated);

        $this->dispatch('call-created', [
            'message' => __('Convocatoria creada correctamente. Puedes editarla o publicarla cuando esté lista.'),
            'title' => __('Convocatoria creada'),
        ]);

        $this->redirect(route('admin.calls.show', $call), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Convocatoria'),
            ]);
    }
}
