<?php

namespace App\Livewire\Admin\Events;

use App\Http\Requests\UpdateErasmusEventRequest;
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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

    /**
     * The event being edited.
     */
    public ErasmusEvent $event;

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
     * New images to upload (Filepond handles this).
     *
     * @var array<int, UploadedFile>
     */
    public array $images = [];

    /**
     * IDs of existing images to delete.
     *
     * @var array<int>
     */
    public array $imagesToDelete = [];

    /**
     * Media ID to delete (for confirmation modal).
     */
    public ?int $imageToDelete = null;

    /**
     * Show delete image confirmation modal.
     */
    public bool $showDeleteImageModal = false;

    /**
     * Media ID to force delete (for confirmation modal).
     */
    public ?int $imageToForceDelete = null;

    /**
     * Show force delete image confirmation modal.
     */
    public bool $showForceDeleteImageModal = false;

    /**
     * Mount the component.
     */
    public function mount(ErasmusEvent $event): void
    {
        $this->authorize('update', $event);

        $this->event = $event;

        // Load event data
        $this->program_id = $event->program_id;
        $this->call_id = $event->call_id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->event_type = $event->event_type;
        $this->start_date = $event->start_date->format('Y-m-d\TH:i');
        $this->end_date = $event->end_date ? $event->end_date->format('Y-m-d\TH:i') : '';
        $this->location = $event->location ?? '';
        $this->is_public = $event->is_public;
        $this->is_all_day = $event->isAllDay();
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
     * Get existing images from the event (excluding soft-deleted ones).
     *
     * @return \Illuminate\Support\Collection<int, Media>
     */
    #[Computed]
    public function existingImages(): \Illuminate\Support\Collection
    {
        return $this->event->getMedia('images');
    }

    /**
     * Get deleted images from the event.
     *
     * @return \Illuminate\Support\Collection<int, Media>
     */
    #[Computed]
    public function deletedImages(): \Illuminate\Support\Collection
    {
        return $this->event->getSoftDeletedImages();
    }

    /**
     * Handle program ID changes.
     */
    public function updatedProgramId(): void
    {
        // Reset call_id when program changes if it doesn't belong to new program
        if ($this->call_id) {
            $call = Call::find($this->call_id);
            if ($call && $call->program_id !== $this->program_id) {
                $this->call_id = null;
            }
        }

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
     * Get validation rules filtered to only include component properties.
     *
     * @return array<string, mixed>
     */
    protected function getComponentRules(): array
    {
        $allRules = (new UpdateErasmusEventRequest)->rules();

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
     * Confirm deletion of an existing image.
     */
    public function confirmDeleteImage(int $mediaId): void
    {
        $this->imageToDelete = $mediaId;
        $this->showDeleteImageModal = true;
    }

    /**
     * Delete an existing image (soft delete).
     */
    public function deleteImage(): void
    {
        if (! $this->imageToDelete) {
            return;
        }

        if ($this->event->softDeleteMediaById($this->imageToDelete)) {
            $this->imageToDelete = null;
            $this->showDeleteImageModal = false;

            $this->dispatch('image-deleted', [
                'message' => __('Imagen eliminada correctamente.'),
            ]);
        }
    }

    /**
     * Restore a deleted image.
     */
    public function restoreImage(int $mediaId): void
    {
        if ($this->event->restoreMediaById($mediaId)) {
            $this->dispatch('image-restored', [
                'message' => __('Imagen restaurada correctamente.'),
            ]);
        }
    }

    /**
     * Confirm force delete of an image.
     */
    public function confirmForceDeleteImage(int $imageId): void
    {
        $this->imageToForceDelete = $imageId;
        $this->showForceDeleteImageModal = true;
    }

    /**
     * Force delete an image (permanent deletion).
     */
    public function forceDeleteImage(): void
    {
        if (! $this->imageToForceDelete) {
            return;
        }

        $this->authorize('forceDelete', $this->event);

        if ($this->event->forceDeleteMediaById($this->imageToForceDelete)) {
            $this->imageToForceDelete = null;
            $this->showForceDeleteImageModal = false;

            $this->dispatch('image-force-deleted', [
                'message' => __('Imagen eliminada permanentemente.'),
            ]);
        }
    }

    /**
     * Update the event.
     */
    public function update(): void
    {
        // If all day is checked, adjust dates before validation
        if ($this->is_all_day) {
            if ($this->start_date) {
                $date = Carbon::parse($this->start_date);
                $this->start_date = $date->format('Y-m-d').'T00:00';
            }

            if ($this->end_date) {
                $date = Carbon::parse($this->end_date);
                // For all-day events, if end_date is same day as start_date, set it to next day at 00:00
                if ($this->start_date) {
                    $startDate = Carbon::parse($this->start_date);
                    $endDate = Carbon::parse($this->end_date);
                    if ($endDate->isSameDay($startDate)) {
                        $this->end_date = $endDate->copy()->addDay()->format('Y-m-d').'T00:00';
                    } else {
                        $this->end_date = $endDate->format('Y-m-d').'T00:00';
                    }
                } else {
                    $this->end_date = $date->format('Y-m-d').'T00:00';
                }
            }
        }

        // Use filtered rules that only include component properties
        $validated = $this->validate($this->getComponentRules());

        // Remove images from validated data as they're handled separately
        unset($validated['images']);

        // Convert datetime-local strings to proper datetime format
        if ($validated['start_date']) {
            $validated['start_date'] = Carbon::parse($validated['start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($validated['end_date']) && $validated['end_date']) {
            $validated['end_date'] = Carbon::parse($validated['end_date'])->format('Y-m-d H:i:s');
        } else {
            $validated['end_date'] = null;
        }

        // Add is_all_day to validated data
        $validated['is_all_day'] = $this->is_all_day;

        // Update the event
        $this->event->update($validated);

        // Handle new image uploads
        if (! empty($this->images)) {
            foreach ($this->images as $image) {
                if ($image instanceof UploadedFile) {
                    $this->event->addMedia($image->getRealPath())
                        ->usingName($this->event->title)
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('images');
                }
            }
        }

        $this->dispatch('event-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.events.show', $this->event), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.events.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Evento'),
            ]);
    }
}
