<?php

namespace App\Observers;

use App\Models\Resolution;
use App\Models\User;
use App\Services\NotificationService;

class ResolutionObserver
{
    /**
     * Handle the Resolution "created" event.
     */
    public function created(Resolution $resolution): void
    {
        // Check if the resolution is being created as published
        if ($resolution->published_at && ($resolution->published_at->isPast() || $resolution->published_at->isToday())) {
            $this->notifyPublished($resolution);
        }
    }

    /**
     * Handle the Resolution "updated" event.
     */
    public function updated(Resolution $resolution): void
    {
        // Check if published_at was just set (changed from null to a date)
        // We need to check if it was null before and is now set
        if ($resolution->isDirty('published_at')) {
            $originalPublishedAt = $resolution->getOriginal('published_at');
            $newPublishedAt = $resolution->published_at;

            // Only notify if:
            // 1. It was null before (not published)
            // 2. It's now set (being published)
            // 3. The date is in the past or today (not future)
            if ($originalPublishedAt === null && $newPublishedAt !== null) {
                if ($newPublishedAt->isPast() || $newPublishedAt->isToday()) {
                    $this->notifyPublished($resolution);
                }
            }
        }
    }

    /**
     * Notify users about a published resolution.
     */
    protected function notifyPublished(Resolution $resolution): void
    {
        // Ensure the call relationship is loaded for the notification service
        if (! $resolution->relationLoaded('call')) {
            $resolution->load('call');
        }

        // Get all active users (not soft deleted)
        // TODO: In the future, consider filtering by subscriptions or preferences
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        // Use the NotificationService to create notifications
        app(NotificationService::class)->notifyResolucionPublished($resolution, $users);
    }

    /**
     * Handle the Resolution "deleted" event.
     */
    public function deleted(Resolution $resolution): void
    {
        //
    }

    /**
     * Handle the Resolution "restored" event.
     */
    public function restored(Resolution $resolution): void
    {
        //
    }

    /**
     * Handle the Resolution "force deleted" event.
     */
    public function forceDeleted(Resolution $resolution): void
    {
        //
    }
}
