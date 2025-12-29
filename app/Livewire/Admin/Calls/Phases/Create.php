<?php

namespace App\Livewire\Admin\Calls\Phases;

use App\Http\Requests\StoreCallPhaseRequest;
use App\Models\Call;
use App\Models\CallPhase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * The call that owns this phase.
     */
    public Call $call;

    /**
     * Call ID (pre-filled).
     */
    public int $call_id;

    /**
     * Phase type.
     */
    public string $phase_type = 'publicacion';

    /**
     * Name.
     */
    public string $name = '';

    /**
     * Description.
     */
    public ?string $description = null;

    /**
     * Start date.
     */
    public ?string $start_date = null;

    /**
     * End date.
     */
    public ?string $end_date = null;

    /**
     * Whether this is the current phase.
     */
    public bool $is_current = false;

    /**
     * Order (auto-generated if not specified).
     */
    public ?int $order = null;

    /**
     * Mount the component.
     */
    public function mount(Call $call): void
    {
        $this->authorize('create', CallPhase::class);

        $this->call = $call;
        $this->call_id = $call->id;

        // Auto-generate order if not specified
        if ($this->order === null) {
            $this->order = $this->getNextOrder();
        }
    }

    /**
     * Get next available order for this call.
     */
    public function getNextOrder(): int
    {
        $maxOrder = CallPhase::where('call_id', $this->call->id)
            ->max('order') ?? 0;

        return $maxOrder + 1;
    }

    /**
     * Handle is_current toggle - if set to true, unset other current phases.
     */
    public function updatedIsCurrent(): void
    {
        if ($this->is_current) {
            // Unset other current phases for this call
            CallPhase::where('call_id', $this->call->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }
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

            // Check for date overlaps with other phases
            $this->checkDateOverlaps();
        }
    }

    /**
     * Check if dates overlap with other phases.
     */
    public function checkDateOverlaps(): void
    {
        if (! $this->start_date || ! $this->end_date) {
            return;
        }

        $overlappingPhases = CallPhase::where('call_id', $this->call->id)
            ->whereNull('deleted_at') // Exclude soft-deleted phases
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Phase starts before this ends and ends after this starts
                    $q->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->where('start_date', '<=', $this->end_date)
                        ->where('end_date', '>=', $this->start_date);
                })
                    ->orWhere(function ($q) {
                        // Phase contains this phase
                        $q->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->where('start_date', '<=', $this->start_date)
                            ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->select('id', 'name', 'start_date', 'end_date')
            ->get();

        if ($overlappingPhases->isNotEmpty()) {
            $phaseNames = $overlappingPhases->pluck('name')->implode(', ');
            $this->dispatch('phase-date-overlap-warning', [
                'message' => __('Advertencia: Las fechas se solapan con otras fases: :phases', ['phases' => $phaseNames]),
                'title' => __('Solapamiento de fechas'),
            ]);
        }
    }

    /**
     * Store the phase.
     */
    public function store(): void
    {
        // Prepare data array for validation
        $data = [
            'call_id' => $this->call_id,
            'phase_type' => $this->phase_type,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'is_current' => $this->is_current,
            'order' => $this->order ?? $this->getNextOrder(),
        ];

        // Validate using FormRequest rules
        $rules = (new StoreCallPhaseRequest)->rules();
        $messages = (new StoreCallPhaseRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // If marking as current, unset other current phases first
        if ($validated['is_current'] ?? false) {
            CallPhase::where('call_id', $this->call->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        // Create the phase
        $phase = CallPhase::create($validated);

        $this->dispatch('phase-created', [
            'message' => __('La fase ":name" ha sido creada correctamente.', ['name' => $phase->name]),
            'title' => __('Fase creada'),
        ]);

        $this->redirect(route('admin.calls.phases.index', $this->call), navigate: true);
    }

    /**
     * Get phase type options.
     */
    public function getPhaseTypeOptions(): array
    {
        return [
            'publicacion' => __('Publicación'),
            'solicitudes' => __('Solicitudes'),
            'provisional' => __('Provisional'),
            'alegaciones' => __('Alegaciones'),
            'definitivo' => __('Definitivo'),
            'renuncias' => __('Renuncias'),
            'lista_espera' => __('Lista de Espera'),
        ];
    }

    /**
     * Check if there's already a current phase.
     */
    public function hasCurrentPhase(): bool
    {
        return CallPhase::where('call_id', $this->call->id)
            ->where('is_current', true)
            ->exists();
    }

    /**
     * Get current phase name if exists.
     */
    public function getCurrentPhaseName(): ?string
    {
        $currentPhase = CallPhase::where('call_id', $this->call->id)
            ->where('is_current', true)
            ->first();

        return $currentPhase?->name;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.phases.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Fase'),
            ]);
    }
}
