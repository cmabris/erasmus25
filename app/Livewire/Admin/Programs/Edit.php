<?php

namespace App\Livewire\Admin\Programs;

use App\Http\Requests\UpdateProgramRequest;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    /**
     * The program being edited.
     */
    public Program $program;

    /**
     * Program code.
     */
    public string $code = '';

    /**
     * Program name.
     */
    public string $name = '';

    /**
     * Program slug.
     */
    public string $slug = '';

    /**
     * Program description.
     */
    public string $description = '';

    /**
     * Whether the program is active.
     */
    public bool $is_active = true;

    /**
     * Program order.
     */
    public int $order = 0;

    /**
     * Program image (new upload).
     */
    public ?UploadedFile $image = null;

    /**
     * Temporary image preview URL (for new upload).
     */
    public ?string $imagePreview = null;

    /**
     * Whether to remove existing image.
     */
    public bool $removeExistingImage = false;

    /**
     * Mount the component.
     */
    public function mount(Program $program): void
    {
        $this->authorize('update', $program);

        $this->program = $program;

        // Load program data
        $this->code = $program->code;
        $this->name = $program->name;
        $this->slug = $program->slug ?? '';
        $this->description = $program->description ?? '';
        $this->is_active = $program->is_active;
        $this->order = $program->order;
    }

    /**
     * Generate slug when name changes.
     */
    public function updatedName(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->program->name)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Update image preview when image changes.
     */
    public function updatedImage(): void
    {
        if ($this->image) {
            $this->validate([
                'image' => ['image', 'max:5120'], // 5MB max
            ]);

            $this->imagePreview = $this->image->temporaryUrl();
            $this->removeExistingImage = false; // If uploading new image, don't remove existing
        } else {
            $this->imagePreview = null;
        }
    }

    /**
     * Remove new image upload.
     */
    public function removeImage(): void
    {
        $this->image = null;
        $this->imagePreview = null;
    }

    /**
     * Toggle removal of existing image.
     */
    public function toggleRemoveExistingImage(): void
    {
        $this->removeExistingImage = ! $this->removeExistingImage;
        if ($this->removeExistingImage) {
            $this->image = null;
            $this->imagePreview = null;
        }
    }

    /**
     * Get current image URL.
     */
    public function getCurrentImageUrl(): ?string
    {
        return $this->program->getFirstMediaUrl('image');
    }

    /**
     * Check if program has existing image.
     */
    public function hasExistingImage(): bool
    {
        return $this->program->hasMedia('image');
    }

    /**
     * Update the program.
     */
    public function update(): void
    {
        $validated = $this->validate((new UpdateProgramRequest)->rules());

        // Remove image from validated data as it's handled separately
        unset($validated['image']);

        $this->program->update($validated);

        // Handle image removal
        if ($this->removeExistingImage && $this->program->hasMedia('image')) {
            $this->program->clearMediaCollection('image');
        }

        // Handle new image upload
        if ($this->image) {
            // Remove existing image if uploading new one
            if ($this->program->hasMedia('image')) {
                $this->program->clearMediaCollection('image');
            }

            $this->program->addMedia($this->image->getRealPath())
                ->usingName($this->program->name)
                ->usingFileName($this->image->getClientOriginalName())
                ->toMediaCollection('image');
        }

        $this->dispatch('program-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.programs.show', $this->program), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.programs.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Programa'),
            ]);
    }
}
