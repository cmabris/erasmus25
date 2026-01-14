<?php

namespace App\Observers;

use App\Models\Call;
use App\Models\User;
use App\Services\NotificationService;

class CallObserver
{
    /**
     * Handle the Call "created" event.
     */
    public function created(Call $call): void
    {
        // Check if the call is being created as published
        if ($call->published_at && ($call->published_at->isPast() || $call->published_at->isToday())) {
            $this->notifyPublished($call);
        }
    }

    /**
     * Handle the Call "updated" event.
     */
    public function updated(Call $call): void
    {
        // Check if published_at was just set (changed from null to a date)
        // We need to check if it was null before and is now set
        if ($call->isDirty('published_at')) {
            $originalPublishedAt = $call->getOriginal('published_at');
            $newPublishedAt = $call->published_at;

            // Only notify if:
            // 1. It was null before (not published)
            // 2. It's now set (being published)
            // 3. The date is in the past or today (not future)
            if ($originalPublishedAt === null && $newPublishedAt !== null) {
                if ($newPublishedAt->isPast() || $newPublishedAt->isToday()) {
                    $this->notifyPublished($call);
                }
            }
        }
    }

    /**
     * Notify users about a published call.
     */
    protected function notifyPublished(Call $call): void
    {
        // Ensure the program relationship is loaded for the notification service
        if (! $call->relationLoaded('program')) {
            $call->load('program');
        }

        // Get all active users (not soft deleted)
        // TODO: In the future, consider filtering by subscriptions or preferences
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        // Use the NotificationService to create notifications
        app(NotificationService::class)->notifyConvocatoriaPublished($call, $users);
    }

    /**
     * Handle the Call "deleted" event.
     */
    public function deleted(Call $call): void
    {
        //
    }

    /**
     * Handle the Call "restored" event.
     */
    public function restored(Call $call): void
    {
        //
    }

    /**
     * Handle the Call "force deleted" event.
     */
    public function forceDeleted(Call $call): void
    {
        //
    }
}
