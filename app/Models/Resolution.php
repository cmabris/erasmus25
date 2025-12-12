<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resolution extends Model
{
    /** @use HasFactory<\Database\Factories\ResolutionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'call_id',
        'call_phase_id',
        'type',
        'title',
        'description',
        'evaluation_procedure',
        'official_date',
        'published_at',
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
            'official_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the call that owns the resolution.
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Get the call phase that owns the resolution.
     */
    public function callPhase(): BelongsTo
    {
        return $this->belongsTo(CallPhase::class);
    }

    /**
     * Get the user who created the resolution.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
