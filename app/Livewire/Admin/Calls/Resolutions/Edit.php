<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Http\Requests\UpdateResolutionRequest;
use App\Models\Call;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\LivewireFilepond\WithFilePond;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;
    use WithFileUploads;

    /**
     * The call that owns this resolution.
     */
    public Call $call;

    /**
     * The resolution being edited.
     */
    public Resolution $resolution;

    /**
     * Call ID (pre-filled).
     */
    public int $call_id;

    /**
     * Call phase ID.
     */
    public ?int $call_phase_id = null;

    /**
     * Resolution type.
     */
    public string $type = 'provisional';

    /**
     * Title.
     */
    public string $title = '';

    /**
     * Description.
     */
    public ?string $description = null;

    /**
     * Evaluation procedure.
     */
    public ?string $evaluation_procedure = null;

    /**
     * Official date.
     */
    public ?string $official_date = null;

    /**
     * Published at date (optional).
     */
    public ?string $published_at = null;

    /**
     * PDF file to upload (new file to replace existing).
     */
    public ?UploadedFile $pdfFile = null;

    /**
     * Whether to remove existing PDF.
     */
    public bool $removeExistingPdf = false;

    /**
     * Mount the component.
     */
    public function mount(Call $call, Resolution $resolution): void
    {
        $this->authorize('update', $resolution);

        $this->call = $call->load(['program', 'academicYear']);
        $this->resolution = $resolution->load(['call', 'callPhase', 'creator']);

        // Pre-fill fields
        $this->call_id = $resolution->call_id;
        $this->call_phase_id = $resolution->call_phase_id;
        $this->type = $resolution->type;
        $this->title = $resolution->title;
        $this->description = $resolution->description;
        $this->evaluation_procedure = $resolution->evaluation_procedure;
        $this->official_date = $resolution->official_date?->format('Y-m-d');
        $this->published_at = $resolution->published_at?->format('Y-m-d');
    }

    /**
     * Validate call_phase_id when it changes.
     */
    public function updatedCallPhaseId(): void
    {
        if ($this->call_phase_id) {
            $this->validateOnly('call_phase_id', [
                'call_phase_id' => [
                    'required',
                    'exists:call_phases,id',
                    function ($attribute, $value, $fail) {
                        $phaseBelongsToCall = \App\Models\CallPhase::where('id', $value)
                            ->where('call_id', $this->call->id)
                            ->exists();

                        if (! $phaseBelongsToCall) {
                            $fail(__('La fase seleccionada no pertenece a la convocatoria especificada.'));
                        }
                    },
                ],
            ]);
        }
    }

    /**
     * Remove existing PDF.
     */
    public function removePdf(): void
    {
        $this->removeExistingPdf = true;
        $this->pdfFile = null;
    }

    /**
     * Update the resolution.
     */
    public function update(): void
    {
        // Prepare data array for validation
        $data = [
            'call_id' => $this->call_id,
            'call_phase_id' => $this->call_phase_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'evaluation_procedure' => $this->evaluation_procedure ?: null,
            'official_date' => $this->official_date,
            'published_at' => $this->published_at ?: null,
            'pdfFile' => $this->pdfFile,
        ];

        // Validate using FormRequest rules
        $rules = (new UpdateResolutionRequest)->rules();
        $messages = (new UpdateResolutionRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // Remove pdfFile from validated data as it's handled separately
        unset($validated['pdfFile']);

        // Update the resolution
        $this->resolution->update($validated);

        // Handle existing PDF
        if ($this->removeExistingPdf) {
            $this->resolution->clearMediaCollection('resolutions');
        }

        // Handle new PDF if uploaded
        if ($this->pdfFile) {
            // Remove existing PDF first
            $this->resolution->clearMediaCollection('resolutions');

            // Add new PDF
            $this->resolution->addMedia($this->pdfFile->getRealPath())
                ->usingName($this->resolution->title)
                ->usingFileName($this->pdfFile->getClientOriginalName())
                ->toMediaCollection('resolutions');
        }

        // Reload to get fresh data
        $this->resolution->refresh();

        $this->dispatch('resolution-updated', [
            'message' => __('La resolución ":title" ha sido actualizada correctamente.', ['title' => $this->resolution->title]),
            'title' => __('Resolución actualizada'),
        ]);

        $this->redirect(route('admin.calls.resolutions.show', [$this->call, $this->resolution]), navigate: true);
    }

    /**
     * Get call phases for select dropdown.
     */
    #[Computed]
    public function callPhases(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->call->phases()
            ->orderBy('order')
            ->get(['id', 'name', 'phase_type']);
    }

    /**
     * Get existing PDF media.
     */
    #[Computed]
    public function existingPdf()
    {
        return $this->resolution->getFirstMedia('resolutions');
    }

    /**
     * Get resolution type options.
     */
    public function getTypeOptions(): array
    {
        return [
            'provisional' => __('Provisional'),
            'definitivo' => __('Definitivo'),
            'alegaciones' => __('Alegaciones'),
        ];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.resolutions.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Resolución'),
            ]);
    }
}
