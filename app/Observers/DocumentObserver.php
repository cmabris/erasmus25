<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\User;
use App\Services\NotificationService;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        // Check if the document is being created as active
        if ($document->is_active) {
            $this->notifyPublished($document);
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        // Check if is_active was just set to true (changed from false to true)
        // We need to check if it was false before and is now true
        if ($document->isDirty('is_active')) {
            $originalIsActive = $document->getOriginal('is_active');
            $newIsActive = $document->is_active;

            // Only notify if:
            // 1. It was false before (inactive)
            // 2. It's now true (being activated/published)
            if ($originalIsActive === false && $newIsActive === true) {
                $this->notifyPublished($document);
            }
        }
    }

    /**
     * Notify users about a published document.
     */
    protected function notifyPublished(Document $document): void
    {
        // Get all active users (not soft deleted)
        // TODO: In the future, consider filtering by subscriptions or preferences
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        // Use the NotificationService to create notifications
        app(NotificationService::class)->notifyDocumentoPublished($document, $users);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        //
    }
}
