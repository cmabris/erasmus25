<?php

namespace App\Livewire\Public\Events;

use App\Models\ErasmusEvent;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    /**
     * The event to display.
     */
    public ErasmusEvent $event;

    /**
     * Mount the component.
     */
    public function mount(ErasmusEvent $event): void
    {
        // Only show public events
        if (! $event->is_public) {
            abort(404);
        }

        $this->event = $event->load(['program', 'call', 'creator', 'media']);
    }

    /**
     * Check if the event is upcoming.
     */
    #[Computed]
    public function isUpcoming(): bool
    {
        return $this->event->isUpcoming();
    }

    /**
     * Check if the event is today.
     */
    #[Computed]
    public function isToday(): bool
    {
        return $this->event->isToday();
    }

    /**
     * Check if the event is past.
     */
    #[Computed]
    public function isPast(): bool
    {
        return $this->event->isPast();
    }

    /**
     * Get related events (same program or call).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ErasmusEvent>
     */
    #[Computed]
    public function relatedEvents(): \Illuminate\Database\Eloquent\Collection
    {
        $query = ErasmusEvent::query()
            ->with(['program', 'call'])
            ->public()
            ->where('id', '!=', $this->event->id);

        // If event has a call, find events from the same call
        if ($this->event->call_id) {
            $query->where('call_id', $this->event->call_id);
        }
        // Otherwise, if event has a program, find events from the same program
        elseif ($this->event->program_id) {
            $query->where('program_id', $this->event->program_id)
                ->whereNull('call_id'); // Only events without call_id to avoid mixing
        }

        return $query
            ->upcoming()
            ->orderBy('start_date')
            ->limit(4)
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.events.show')
            ->layout('components.layouts.public', [
                'title' => "{$this->event->title} - Eventos Erasmus+",
                'description' => $this->event->description ?: __('Detalle del evento Erasmus+'),
            ]);
    }
}
