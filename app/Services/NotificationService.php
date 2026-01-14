<?php

namespace App\Services;

use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Notification;
use App\Models\Resolution;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Create a new notification.
     *
     * @param  array<string, mixed>  $data
     * @return Notification
     */
    public function create(array $data): Notification
    {
        return Notification::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'link' => $data['link'] ?? null,
            'is_read' => false,
        ]);
    }

    /**
     * Create and broadcast a notification (prepared for future real-time implementation).
     *
     * @param  array<string, mixed>  $data
     * @return Notification
     */
    public function createAndBroadcast(array $data): Notification
    {
        $notification = $this->create($data);

        // TODO: When implementing real-time notifications, uncomment:
        // event(new NotificationCreated($notification));

        return $notification;
    }

    /**
     * Notify users about a published call.
     *
     * @param  User|Collection<int, User>  $users
     */
    public function notifyConvocatoriaPublished(Call $call, User|Collection $users): void
    {
        $users = $this->normalizeUsers($users);
        $link = route('convocatorias.show', $call);

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'type' => 'convocatoria',
                'title' => __('notifications.types.convocatoria.published.title', ['title' => $call->title]),
                'message' => __('notifications.types.convocatoria.published.message', [
                    'title' => $call->title,
                    'program' => $call->program->name ?? '',
                ]),
                'link' => $link,
            ]);
        }
    }

    /**
     * Notify users about a published resolution.
     *
     * @param  User|Collection<int, User>  $users
     */
    public function notifyResolucionPublished(Resolution $resolution, User|Collection $users): void
    {
        $users = $this->normalizeUsers($users);
        $call = $resolution->call;
        $link = $call ? route('admin.calls.resolutions.show', [$call, $resolution]) : null;

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'type' => 'resolucion',
                'title' => __('notifications.types.resolucion.published.title', ['title' => $resolution->title]),
                'message' => __('notifications.types.resolucion.published.message', [
                    'title' => $resolution->title,
                    'call' => $call->title ?? '',
                ]),
                'link' => $link,
            ]);
        }
    }

    /**
     * Notify users about a published news post.
     *
     * @param  User|Collection<int, User>  $users
     */
    public function notifyNoticiaPublished(NewsPost $newsPost, User|Collection $users): void
    {
        $users = $this->normalizeUsers($users);
        $link = route('noticias.show', $newsPost);

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'type' => 'noticia',
                'title' => __('notifications.types.noticia.published.title', ['title' => $newsPost->title]),
                'message' => __('notifications.types.noticia.published.message', [
                    'title' => $newsPost->title,
                    'excerpt' => Str::limit($newsPost->excerpt ?? '', 100),
                ]),
                'link' => $link,
            ]);
        }
    }

    /**
     * Notify users about a published document.
     *
     * @param  User|Collection<int, User>  $users
     */
    public function notifyDocumentoPublished(Document $document, User|Collection $users): void
    {
        $users = $this->normalizeUsers($users);
        $link = route('documentos.show', $document);

        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'type' => 'sistema',
                'title' => __('notifications.types.documento.published.title', ['title' => $document->title]),
                'message' => __('notifications.types.documento.published.message', [
                    'title' => $document->title,
                    'type' => $document->document_type ?? '',
                ]),
                'link' => $link,
            ]);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get the count of unread notifications for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()
            ->where('is_read', false)
            ->count();
    }

    /**
     * Normalize users input to a collection.
     *
     * @param  User|Collection<int, User>  $users
     * @return Collection<int, User>
     */
    protected function normalizeUsers(User|Collection $users): Collection
    {
        if ($users instanceof User) {
            return collect([$users]);
        }

        return $users;
    }
}
