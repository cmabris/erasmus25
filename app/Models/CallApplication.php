<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallApplication extends Model
{
    /** @use HasFactory<\Database\Factories\CallApplicationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'call_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'status',
        'score',
        'position',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'position' => 'integer',
        ];
    }

    /**
     * Get the call that owns the application.
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }
}
