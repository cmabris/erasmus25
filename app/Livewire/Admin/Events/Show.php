<?php

namespace App\Livewire\Admin\Events;

use App\Models\ErasmusEvent;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The event being displayed.
     */
    public ErasmusEvent $event;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(ErasmusEvent $event): void
    {
        $this->authorize('view', $event);

        // Load relationships with eager loading to avoid N+1 queries
        $this->event = $event->load([
            'program' => fn ($query) => $query->select('id', 'name', 'slug'),
            'call' => fn ($query) => $query->select('id', 'title', 'slug', 'program_id'),
            'creator' => fn ($query) => $query->select('id', 'name', 'email'),
        ])->load('media');
    }

    /**
     * Get all images from the event.
     *
     * @return \Illuminate\Support\Collection<int, Media>
     */
    #[Computed]
    public function images(): \Illuminate\Support\Collection
    {
        return $this->event->getMedia('images');
    }

    /**
     * Get the first image URL (featured).
     */
    #[Computed]
    public function featuredImageUrl(): ?string
    {
        return $this->event->getFirstMediaUrl('images', 'large')
            ?? $this->event->getFirstMediaUrl('images');
    }

    /**
     * Check if event has images.
     */
    #[Computed]
    public function hasImages(): bool
    {
        return $this->event->hasMedia('images');
    }

    /**
     * Get event type configuration.
     *
     * @return array<string, mixed>
     */
    public function getEventTypeConfig(string $eventType): array
    {
        return match ($eventType) {
            'apertura' => ['variant' => 'success', 'icon' => 'play-circle', 'label' => __('Apertura')],
            'cierre' => ['variant' => 'danger', 'icon' => 'stop-circle', 'label' => __('Cierre')],
            'entrevista' => ['variant' => 'info', 'icon' => 'chat-bubble-left-right', 'label' => __('Entrevistas')],
            'publicacion_provisional' => ['variant' => 'warning', 'icon' => 'document-text', 'label' => __('Listado provisional')],
            'publicacion_definitivo' => ['variant' => 'success', 'icon' => 'document-check', 'label' => __('Listado definitivo')],
            'reunion_informativa' => ['variant' => 'primary', 'icon' => 'user-group', 'label' => __('Reunión informativa')],
            default => ['variant' => 'neutral', 'icon' => 'calendar', 'label' => __('Otro')],
        };
    }

    /**
     * Get event status configuration.
     *
     * @return array<string, mixed>
     */
    public function getEventStatusConfig(): array
    {
        if ($this->event->trashed()) {
            return ['variant' => 'danger', 'label' => __('Eliminado')];
        }

        if ($this->event->isUpcoming()) {
            return ['variant' => 'success', 'label' => __('Próximo')];
        }

        if ($this->event->isToday()) {
            return ['variant' => 'info', 'label' => __('Hoy')];
        }

        return ['variant' => 'neutral', 'label' => __('Pasado')];
    }

    /**
     * Get event statistics.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'duration' => $this->event->duration(),
            'is_all_day' => $this->event->isAllDay(),
            'images_count' => $this->images->count(),
        ];
    }

    /**
     * Toggle public/private visibility.
     */
    public function togglePublic(): void
    {
        $this->authorize('update', $this->event);

        $this->event->update([
            'is_public' => ! $this->event->is_public,
        ]);

        $this->dispatch('visibility-toggled', [
            'message' => $this->event->is_public
                ? __('Evento marcado como público.')
                : __('Evento marcado como privado.'),
        ]);
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    /**
     * Delete the event (SoftDelete).
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->event);

        $this->event->delete();

        $this->showDeleteModal = false;

        $this->dispatch('event-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.events.index'), navigate: true);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(): void
    {
        $this->showRestoreModal = true;
    }

    /**
     * Restore the event.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->event);

        $this->event->restore();

        $this->showRestoreModal = false;

        $this->dispatch('event-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(): void
    {
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete the event (permanent deletion).
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->event);

        $this->event->forceDelete();

        $this->showForceDeleteModal = false;

        $this->dispatch('event-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.events.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.events.show')
            ->layout('components.layouts.app', [
                'title' => $this->event->title,
            ]);
    }
}
