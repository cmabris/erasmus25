<?php

namespace App\Observers;

use App\Models\NewsPost;
use App\Models\User;
use App\Services\NotificationService;

class NewsPostObserver
{
    /**
     * Handle the NewsPost "created" event.
     */
    public function created(NewsPost $newsPost): void
    {
        // Check if the news post is being created as published
        if ($newsPost->published_at && ($newsPost->published_at->isPast() || $newsPost->published_at->isToday())) {
            $this->notifyPublished($newsPost);
        }
    }

    /**
     * Handle the NewsPost "updated" event.
     */
    public function updated(NewsPost $newsPost): void
    {
        // Check if published_at was just set (changed from null to a date)
        // We need to check if it was null before and is now set
        if ($newsPost->isDirty('published_at')) {
            $originalPublishedAt = $newsPost->getOriginal('published_at');
            $newPublishedAt = $newsPost->published_at;

            // Only notify if:
            // 1. It was null before (not published)
            // 2. It's now set (being published)
            // 3. The date is in the past or today (not future)
            if ($originalPublishedAt === null && $newPublishedAt !== null) {
                if ($newPublishedAt->isPast() || $newPublishedAt->isToday()) {
                    $this->notifyPublished($newsPost);
                }
            }
        }
    }

    /**
     * Notify users about a published news post.
     */
    protected function notifyPublished(NewsPost $newsPost): void
    {
        // Get all active users (not soft deleted)
        // TODO: In the future, consider filtering by subscriptions or preferences
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        // Use the NotificationService to create notifications
        app(NotificationService::class)->notifyNoticiaPublished($newsPost, $users);
    }

    /**
     * Handle the NewsPost "deleted" event.
     */
    public function deleted(NewsPost $newsPost): void
    {
        //
    }

    /**
     * Handle the NewsPost "restored" event.
     */
    public function restored(NewsPost $newsPost): void
    {
        //
    }

    /**
     * Handle the NewsPost "force deleted" event.
     */
    public function forceDeleted(NewsPost $newsPost): void
    {
        //
    }
}
