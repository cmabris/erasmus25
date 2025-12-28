<?php

namespace App\Livewire\Admin\Programs;

use App\Http\Requests\StoreProgramRequest;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

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
     * Program image.
     */
    public ?UploadedFile $image = null;

    /**
     * Temporary image preview URL.
     */
    public ?string $imagePreview = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Program::class);
    }

    /**
     * Generate slug when name changes.
     */
    public function updatedName(): void
    {
        if (empty($this->slug)) {
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
        } else {
            $this->imagePreview = null;
        }
    }

    /**
     * Remove image.
     */
    public function removeImage(): void
    {
        $this->image = null;
        $this->imagePreview = null;
    }

    /**
     * Store the program.
     */
    public function store(): void
    {
        $validated = $this->validate((new StoreProgramRequest)->rules());

        // Remove image from validated data as it's handled separately
        unset($validated['image']);

        $program = Program::create($validated);

        // Handle image upload
        if ($this->image) {
            $program->addMedia($this->image->getRealPath())
                ->usingName($program->name)
                ->usingFileName($this->image->getClientOriginalName())
                ->toMediaCollection('image');
        }

        $this->dispatch('program-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.programs.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.programs.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Programa'),
            ]);
    }
}
