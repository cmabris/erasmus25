<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallPhase extends Model
{
    /** @use HasFactory<\Database\Factories\CallPhaseFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'call_id',
        'phase_type',
        'name',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Get the call that owns the phase.
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Get the resolutions for the phase.
     */
    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Handle cascading deletes when CallPhase is soft deleted
        static::deleting(function ($callPhase) {
            if ($callPhase->isForceDeleting()) {
                // Force delete - let database constraints handle it
                return;
            }

            // Soft delete - delete related resolutions physically (they don't have SoftDeletes)
            $callPhase->resolutions()->delete();
        });
    }
}
