<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Http\Requests\StoreResolutionRequest;
use App\Models\Call;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\LivewireFilepond\WithFilePond;

class Create extends Component
{
    use AuthorizesRequests;
    use WithFilePond;
    use WithFileUploads;

    /**
     * The call that owns this resolution.
     */
    public Call $call;

    /**
     * Call ID (pre-filled).
     */
    public int $call_id;

    /**
     * Call phase ID (optional, can be pre-filled).
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
     * PDF file to upload.
     */
    public ?UploadedFile $pdfFile = null;

    /**
     * Mount the component.
     */
    public function mount(Call $call, ?int $call_phase_id = null): void
    {
        $this->authorize('create', Resolution::class);

        $this->call = $call->load(['program', 'academicYear']);
        $this->call_id = $call->id;
        $this->call_phase_id = $call_phase_id;

        // Initialize published_at as null if not provided
        $this->published_at = null;
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
     * Store the resolution.
     */
    public function save(): void
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
        $rules = (new StoreResolutionRequest)->rules();
        $messages = (new StoreResolutionRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // Remove pdfFile from validated data as it's handled separately
        unset($validated['pdfFile']);

        // Create the resolution
        $resolution = Resolution::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        // Handle PDF upload if exists
        if ($this->pdfFile) {
            $resolution->addMedia($this->pdfFile->getRealPath())
                ->usingName($resolution->title)
                ->usingFileName($this->pdfFile->getClientOriginalName())
                ->toMediaCollection('resolutions');
        }

        $this->dispatch('resolution-created', [
            'message' => __('La resolución ":title" ha sido creada correctamente.', ['title' => $resolution->title]),
            'title' => __('Resolución creada'),
        ]);

        $this->redirect(route('admin.calls.resolutions.index', $this->call), navigate: true);
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
        return view('livewire.admin.calls.resolutions.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Resolución'),
            ]);
    }
}
