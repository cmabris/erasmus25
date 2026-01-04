<?php

namespace App\Livewire\Admin\Events;

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class Create extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

    /**
     * Program ID (optional).
     */
    public ?int $program_id = null;

    /**
     * Call ID (optional).
     */
    public ?int $call_id = null;

    /**
     * Event title.
     */
    public string $title = '';

    /**
     * Event description.
     */
    public string $description = '';

    /**
     * Event type.
     */
    public string $event_type = '';

    /**
     * Start date (datetime-local format).
     */
    public string $start_date = '';

    /**
     * End date (datetime-local format, optional).
     */
    public string $end_date = '';

    /**
     * Location.
     */
    public string $location = '';

    /**
     * Whether the event is public.
     */
    public bool $is_public = true;

    /**
     * Whether the event is all day.
     */
    public bool $is_all_day = false;

    /**
     * Images to upload (array of UploadedFile).
     *
     * @var array<int, UploadedFile>
     */
    public array $images = [];

    /**
     * Temporary image preview URLs.
     *
     * @var array<int, string>
     */

    /**
     * Mount the component.
     */
    public function mount(?int $program_id = null, ?int $call_id = null): void
    {
        $this->authorize('create', ErasmusEvent::class);

        if ($program_id) {
            $this->program_id = $program_id;
        }

        if ($call_id) {
            $this->call_id = $call_id;
        }

        // Set default start date to now
        if (empty($this->start_date)) {
            $this->start_date = now()->format('Y-m-d\TH:i');
        }
    }

    /**
     * Get all programs for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available calls for dropdown (filtered by program if selected).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Call>
     */
    #[Computed]
    public function availableCalls(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Call::query();

        if ($this->program_id) {
            $query->where('program_id', $this->program_id);
        }

        return $query->orderBy('title')->get();
    }

    /**
     * Get available event types.
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
     * Get validation rules filtered to only include component properties.
     *
     * @return array<string, mixed>
     */
    protected function getComponentRules(): array
    {
        $allRules = (new \App\Http\Requests\StoreErasmusEventRequest)->rules();

        // Only include rules for properties that exist in this component
        $componentProperties = [
            'program_id',
            'call_id',
            'title',
            'description',
            'event_type',
            'start_date',
            'end_date',
            'location',
            'is_public',
            'images',
        ];

        return array_intersect_key($allRules, array_flip($componentProperties));
    }

    /**
     * Handle program ID changes.
     */
    public function updatedProgramId(): void
    {
        // Reset call_id when program changes
        $this->call_id = null;

        // Validate program_id in real-time
        $this->validateOnly('program_id', $this->getComponentRules());
    }

    /**
     * Handle start date changes.
     */
    public function updatedStartDate(): void
    {
        // Validate start_date in real-time
        $this->validateOnly('start_date', $this->getComponentRules());

        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

            if ($end->lte($start)) {
                // Auto-adjust end date to be after start date
                $this->end_date = $start->copy()->addHour()->format('Y-m-d\TH:i');
                $this->resetErrorBag('end_date');
            }
        }

        // If all day is checked, set times to 00:00
        if ($this->is_all_day && $this->start_date) {
            $date = Carbon::parse($this->start_date);
            $this->start_date = $date->format('Y-m-d').'T00:00';
        }
    }

    /**
     * Handle end date changes.
     */
    public function updatedEndDate(): void
    {
        // Validate end_date in real-time
        $this->validateOnly('end_date', $this->getComponentRules());

        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

            if ($end->lte($start)) {
                $this->addError('end_date', __('La fecha de fin debe ser posterior a la fecha de inicio.'));
            } else {
                $this->resetErrorBag('end_date');
            }
        }

        // If all day is checked, set times to 00:00
        if ($this->is_all_day && $this->end_date) {
            $date = Carbon::parse($this->end_date);
            $this->end_date = $date->format('Y-m-d').'T00:00';
        }
    }

    /**
     * Handle all day checkbox changes.
     */
    public function updatedIsAllDay(): void
    {
        if ($this->is_all_day) {
            // Set times to 00:00
            if ($this->start_date) {
                $date = Carbon::parse($this->start_date);
                $this->start_date = $date->format('Y-m-d').'T00:00';
            }

            if ($this->end_date) {
                $date = Carbon::parse($this->end_date);
                $this->end_date = $date->format('Y-m-d').'T00:00';
            }
        }
    }

    /**
     * Validate uploaded file (called by Filepond).
     * The $response parameter is the temporary path returned by Livewire's upload() method.
     */
    public function validateUploadedFile(string $response): bool
    {
        $rules = $this->getComponentRules();
        $imageRules = $rules['images.*'] ?? ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'];

        // Find the uploaded file in the images array by comparing the temporary path
        foreach ($this->images as $image) {
            if ($image instanceof UploadedFile) {
                // Compare the temporary path (response) with the file's real path
                if ($image->getRealPath() === $response || $image->getPathname() === $response) {
                    $validator = \Illuminate\Support\Facades\Validator::make(
                        ['images' => [$image]],
                        ['images.*' => $imageRules]
                    );

                    return ! $validator->fails();
                }
            }
        }

        // If not found by path, try to validate the last uploaded file
        // This handles cases where the path comparison might not match exactly
        if (! empty($this->images)) {
            $lastImage = end($this->images);
            if ($lastImage instanceof UploadedFile) {
                $validator = \Illuminate\Support\Facades\Validator::make(
                    ['images' => [$lastImage]],
                    ['images.*' => $imageRules]
                );

                return ! $validator->fails();
            }
        }

        return false;
    }

    /**
     * Handle title changes with real-time validation.
     */
    public function updatedTitle(): void
    {
        $this->validateOnly('title', $this->getComponentRules());
    }

    /**
     * Handle event type changes with real-time validation.
     */
    public function updatedEventType(): void
    {
        $this->validateOnly('event_type', $this->getComponentRules());
    }

    /**
     * Handle call ID changes with real-time validation.
     */
    public function updatedCallId(): void
    {
        $this->validateOnly('call_id', $this->getComponentRules());
    }

    /**
     * Store the event.
     */
    public function store(): void
    {
        // Use filtered rules that only include component properties
        $validated = $this->validate($this->getComponentRules());

        // Remove images from validated data as they're handled separately
        unset($validated['images']);

        // Set created_by to current user
        $validated['created_by'] = auth()->id();

        // Convert datetime-local strings to proper datetime format
        if ($validated['start_date']) {
            $validated['start_date'] = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($validated['end_date']) && $validated['end_date']) {
            $validated['end_date'] = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');
        }

        // Create the event
        $event = ErasmusEvent::create($validated);

        // Handle image uploads
        if (! empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof UploadedFile) {
                    $event->addMedia($image->getRealPath())
                        ->usingName($event->title)
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('images');
                }
            }
        }

        $this->dispatch('event-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.events.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.events.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Evento'),
            ]);
    }
}
