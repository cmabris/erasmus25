<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Resolution extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ResolutionFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

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

    /**
     * Register media collections for the resolution.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('resolutions')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
            ]);
    }

    /**
     * Register media conversions for the resolution.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // No conversions needed for PDFs
    }
}
