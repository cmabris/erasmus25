<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Call extends Model
{
    /** @use HasFactory<\Database\Factories\CallFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'academic_year_id',
        'title',
        'slug',
        'type',
        'modality',
        'number_of_places',
        'destinations',
        'estimated_start_date',
        'estimated_end_date',
        'requirements',
        'documentation',
        'selection_criteria',
        'scoring_table',
        'status',
        'published_at',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'destinations' => 'array',
            'scoring_table' => 'array',
            'estimated_start_date' => 'date',
            'estimated_end_date' => 'date',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'number_of_places' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($call) {
            if (empty($call->slug)) {
                $call->slug = Str::slug($call->title);
            }
        });
    }

    /**
     * Get the program that owns the call.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the academic year that owns the call.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who created the call.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the call.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the phases for the call.
     */
    public function phases(): HasMany
    {
        return $this->hasMany(CallPhase::class)->orderBy('order');
    }

    /**
     * Get the applications for the call.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CallApplication::class);
    }

    /**
     * Get the resolutions for the call.
     */
    public function resolutions(): HasMany
    {
        return $this->hasMany(Resolution::class);
    }
}
