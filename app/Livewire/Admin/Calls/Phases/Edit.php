<?php

namespace App\Livewire\Admin\Calls\Phases;

use App\Http\Requests\UpdateCallPhaseRequest;
use App\Models\Call;
use App\Models\CallPhase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The call that owns this phase.
     */
    public Call $call;

    /**
     * The phase being edited.
     */
    public CallPhase $callPhase;

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
     * Order.
     */
    public ?int $order = null;

    /**
     * Mount the component.
     */
    public function mount(Call $call, CallPhase $call_phase): void
    {
        $this->authorize('update', $call_phase);

        $this->call = $call;
        $this->callPhase = $call_phase;

        // Load phase data
        $this->call_id = $call_phase->call_id;
        $this->phase_type = $call_phase->phase_type;
        $this->name = $call_phase->name;
        $this->description = $call_phase->description;
        $this->start_date = $call_phase->start_date?->format('Y-m-d');
        $this->end_date = $call_phase->end_date?->format('Y-m-d');
        $this->is_current = $call_phase->is_current;
        $this->order = $call_phase->order;
    }

    /**
     * Handle is_current toggle - if set to true, unset other current phases.
     */
    public function updatedIsCurrent(): void
    {
        if ($this->is_current) {
            // Unset other current phases for this call (excluding the current one being edited)
            CallPhase::where('call_id', $this->call->id)
                ->where('id', '!=', $this->callPhase->id)
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
            ->where('id', '!=', $this->callPhase->id)
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
     * Update the phase.
     */
    public function update(): void
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
            'order' => $this->order,
        ];

        // Validate using FormRequest rules
        $rules = (new UpdateCallPhaseRequest)->rules();
        $messages = (new UpdateCallPhaseRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // If marking as current, unset other current phases first (excluding this one)
        if ($validated['is_current'] ?? false) {
            CallPhase::where('call_id', $this->call->id)
                ->where('id', '!=', $this->callPhase->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        // Update the phase
        $this->callPhase->update($validated);

        // Reload to get fresh data
        $this->callPhase->refresh();

        $this->dispatch('phase-updated', [
            'message' => __('La fase ":name" ha sido actualizada correctamente.', ['name' => $this->callPhase->name]),
            'title' => __('Fase actualizada'),
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
     * Check if there's already a current phase (excluding this one).
     */
    public function hasCurrentPhase(): bool
    {
        return CallPhase::where('call_id', $this->call->id)
            ->where('id', '!=', $this->callPhase->id)
            ->where('is_current', true)
            ->exists();
    }

    /**
     * Get current phase name if exists (excluding this one).
     */
    public function getCurrentPhaseName(): ?string
    {
        $currentPhase = CallPhase::where('call_id', $this->call->id)
            ->where('id', '!=', $this->callPhase->id)
            ->where('is_current', true)
            ->first();

        return $currentPhase?->name;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.phases.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Fase'),
            ]);
    }
}
