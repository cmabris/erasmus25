<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErasmusEvent extends Model
{
    /** @use HasFactory<\Database\Factories\ErasmusEventFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'call_id',
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'location',
        'is_public',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the program that owns the event.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the call that owns the event.
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include public events.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now()->startOfDay());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('start_date', '<', now()->startOfDay());
    }

    /**
     * Scope a query to only include events for a specific date.
     */
    public function scopeForDate(Builder $query, Carbon|string $date): Builder
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $query->whereDate('start_date', $date);
    }

    /**
     * Scope a query to only include events for a specific month.
     */
    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('start_date', $year)
            ->whereMonth('start_date', $month);
    }

    /**
     * Scope a query to only include events for a specific program.
     */
    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope a query to only include events for a specific call.
     */
    public function scopeForCall(Builder $query, int $callId): Builder
    {
        return $query->where('call_id', $callId);
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to only include events in a date range.
     */
    public function scopeInDateRange(Builder $query, Carbon|string $from, Carbon|string $to): Builder
    {
        $from = $from instanceof Carbon ? $from : Carbon::parse($from);
        $to = $to instanceof Carbon ? $to : Carbon::parse($to);

        return $query->whereBetween('start_date', [
            $from->startOfDay(),
            $to->endOfDay(),
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date->isFuture();
    }

    /**
     * Check if the event is today.
     */
    public function isToday(): bool
    {
        return $this->start_date->isToday();
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return $this->start_date->isPast() && ! $this->isToday();
    }

    /**
     * Get the duration of the event in hours.
     */
    public function duration(): ?float
    {
        if (! $this->end_date) {
            return null;
        }

        return $this->start_date->diffInHours($this->end_date, true);
    }

    /**
     * Check if the event is all day (no specific time).
     */
    public function isAllDay(): bool
    {
        return $this->start_date->format('H:i') === '00:00' &&
            (! $this->end_date || $this->end_date->format('H:i') === '00:00');
    }

    /**
     * Get formatted date range string.
     */
    public function getFormattedDateRangeAttribute(): string
    {
        $start = $this->start_date->translatedFormat('d F Y');
        $startTime = $this->start_date->format('H:i');

        if ($this->end_date) {
            $end = $this->end_date->translatedFormat('d F Y');
            $endTime = $this->end_date->format('H:i');

            if ($this->start_date->isSameDay($this->end_date)) {
                return "{$start} de {$startTime} a {$endTime}";
            }

            return "Del {$start} ({$startTime}) al {$end} ({$endTime})";
        }

        return "{$start} a las {$startTime}";
    }
}
