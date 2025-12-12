<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class NewsPost extends Model
{
    /** @use HasFactory<\Database\Factories\NewsPostFactory> */
    use HasFactory;

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
        'excerpt',
        'content',
        'country',
        'city',
        'host_entity',
        'mobility_type',
        'mobility_category',
        'status',
        'published_at',
        'author_id',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($newsPost) {
            if (empty($newsPost->slug)) {
                $newsPost->slug = Str::slug($newsPost->title);
            }
        });
    }

    /**
     * Get the program that owns the news post.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the academic year that owns the news post.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the author of the news post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the user who reviewed the news post.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the tags for the news post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class);
    }
}
