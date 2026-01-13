<?php

namespace App\Livewire\Admin\News;

use App\Models\NewsPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The news post being displayed.
     */
    public NewsPost $newsPost;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(NewsPost $news_post): void
    {
        $this->authorize('view', $news_post);

        // Load relationships with eager loading to avoid N+1 queries
        $this->newsPost = $news_post->load([
            'program',
            'academicYear',
            'author',
            'reviewer',
            'tags',
        ]);
    }

    /**
     * Get the featured image URL.
     */
    #[Computed]
    public function featuredImageUrl(): ?string
    {
        return $this->newsPost->getFirstMediaUrl('featured');
    }

    /**
     * Get the featured image URL with conversion.
     */
    public function getFeaturedImageUrl(string $conversion = 'large'): ?string
    {
        return $this->newsPost->getFirstMediaUrl('featured', $conversion);
    }

    /**
     * Check if news post has featured image.
     */
    #[Computed]
    public function hasFeaturedImage(): bool
    {
        return $this->newsPost->hasMedia('featured');
    }

    /**
     * Toggle publish/unpublish status.
     */
    public function togglePublish(): void
    {
        if ($this->newsPost->status === 'publicado') {
            $this->unpublish();
        } else {
            $this->publish();
        }
    }

    /**
     * Publish the news post.
     */
    public function publish(): void
    {
        $this->authorize('publish', $this->newsPost);

        $oldStatus = $this->newsPost->status;

        $this->newsPost->update([
            'status' => 'publicado',
            'published_at' => now(),
        ]);

        // Log activity
        activity()
            ->performedOn($this->newsPost)
            ->causedBy(auth()->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_status' => $oldStatus,
                'new_status' => 'publicado',
                'published_at' => $this->newsPost->published_at?->toIso8601String(),
            ])
            ->log('published');

        // Reload the news post to refresh the view
        $this->newsPost->refresh();

        $this->dispatch('news-post-published', [
            'message' => __('Noticia publicada correctamente. La noticia está ahora visible públicamente.'),
        ]);
    }

    /**
     * Unpublish the news post.
     */
    public function unpublish(): void
    {
        $this->authorize('publish', $this->newsPost);

        $oldStatus = $this->newsPost->status;

        $this->newsPost->update([
            'status' => 'borrador',
            'published_at' => null,
        ]);

        // Log activity
        activity()
            ->performedOn($this->newsPost)
            ->causedBy(auth()->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_status' => $oldStatus,
                'new_status' => 'borrador',
            ])
            ->log('unpublished');

        // Reload the news post to refresh the view
        $this->newsPost->refresh();

        $this->dispatch('news-post-unpublished', [
            'message' => __('Noticia despublicada correctamente. La noticia ya no es visible públicamente.'),
        ]);
    }

    /**
     * Delete the news post (soft delete).
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->newsPost);

        $this->newsPost->delete();

        $this->showDeleteModal = false;

        $this->dispatch('news-post-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.news.index'), navigate: true);
    }

    /**
     * Restore the news post.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->newsPost);

        $this->newsPost->restore();

        // Log activity
        activity()
            ->performedOn($this->newsPost)
            ->causedBy(auth()->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('restored');

        // Reload the news post to refresh the view
        $this->newsPost->refresh();

        $this->showRestoreModal = false;

        $this->dispatch('news-post-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Permanently delete the news post.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->newsPost);

        $this->newsPost->forceDelete();

        $this->showForceDeleteModal = false;

        $this->dispatch('news-post-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.news.index'), navigate: true);
    }

    /**
     * Check if user can publish the news post.
     */
    public function canPublish(): bool
    {
        return auth()->user()?->can('publish', $this->newsPost) ?? false;
    }

    /**
     * Get status color for badge.
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'publicado' => 'success',
            'en_revision' => 'warning',
            'archivado' => 'neutral',
            default => 'danger',
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news.show')
            ->layout('components.layouts.app', [
                'title' => $this->newsPost->title,
            ]);
    }
}
