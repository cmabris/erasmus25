<?php

namespace App\Livewire\Admin\NewsTags;

use App\Models\NewsTag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The news tag being displayed.
     */
    public NewsTag $newsTag;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(NewsTag $news_tag): void
    {
        $this->authorize('view', $news_tag);

        // Load relationships with eager loading to avoid N+1 queries
        $this->newsTag = $news_tag->load([
            'newsPosts' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['newsPosts']);
    }

    /**
     * Get statistics for the news tag.
     * Uses loaded counts to avoid N+1 queries.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_news' => $this->newsTag->news_posts_count ?? $this->newsTag->newsPosts()->count(),
        ];
    }

    /**
     * Delete the news tag (soft delete).
     */
    public function delete(): void
    {
        // Check if news tag has relationships using the loaded count
        $hasRelations = ($this->newsTag->news_posts_count ?? 0) > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('news-tag-delete-error', [
                'message' => __('No se puede eliminar la etiqueta porque tiene noticias asociadas.'),
            ]);

            return;
        }

        $this->authorize('delete', $this->newsTag);

        $this->newsTag->delete();

        $this->dispatch('news-tag-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.news-tags.index'), navigate: true);
    }

    /**
     * Restore the news tag.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->newsTag);

        $this->newsTag->restore();

        $this->dispatch('news-tag-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);

        // Reload the news tag to refresh the view
        $this->newsTag->refresh();
    }

    /**
     * Permanently delete the news tag.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->newsTag);

        // Check relations one more time using the loaded count
        $hasRelations = ($this->newsTag->news_posts_count ?? 0) > 0;

        if ($hasRelations) {
            $this->dispatch('news-tag-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente la etiqueta porque tiene noticias asociadas.'),
            ]);

            return;
        }

        $this->newsTag->forceDelete();

        $this->dispatch('news-tag-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.news-tags.index'), navigate: true);
    }

    /**
     * Check if the news tag can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->newsTag)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($this->newsTag->news_posts_count ?? 0) === 0;
    }

    /**
     * Check if the news tag has relationships.
     * Uses the loaded count to avoid additional queries.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return ($this->newsTag->news_posts_count ?? 0) > 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news-tags.show')
            ->layout('components.layouts.app', [
                'title' => $this->newsTag->name ?? 'Etiqueta',
            ]);
    }
}
