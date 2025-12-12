<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaConsent extends Model
{
    /** @use HasFactory<\Database\Factories\MediaConsentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'media_id',
        'consent_type',
        'person_name',
        'person_email',
        'consent_given',
        'consent_date',
        'consent_document_id',
        'expires_at',
        'revoked_at',
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
            'consent_given' => 'boolean',
            'consent_date' => 'date',
            'expires_at' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Get the document that contains the consent.
     */
    public function consentDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'consent_document_id');
    }
}
