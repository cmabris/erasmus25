<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include notifications of a specific type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include recent notifications (within specified days).
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get the human-readable label for the notification type.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'convocatoria' => __('notifications.types.convocatoria.label'),
            'resolucion' => __('notifications.types.resolucion.label'),
            'noticia' => __('notifications.types.noticia.label'),
            'revision' => __('notifications.types.revision.label'),
            'sistema' => __('notifications.types.sistema.label'),
            default => __('notifications.types.unknown.label'),
        };
    }

    /**
     * Get the Flux UI icon name for the notification type.
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'convocatoria' => 'megaphone',
            'resolucion' => 'document-check',
            'noticia' => 'newspaper',
            'revision' => 'clock',
            'sistema' => 'bell',
            default => 'information-circle',
        };
    }

    /**
     * Get the Flux UI badge color variant for the notification type.
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            'convocatoria' => 'primary',
            'resolucion' => 'success',
            'noticia' => 'info',
            'revision' => 'warning',
            'sistema' => 'neutral',
            default => 'neutral',
        };
    }
}
