<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    /** @use HasFactory<\Database\Factories\NewsletterSubscriptionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'name',
        'programs',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
        'verification_token',
        'verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'programs' => 'array',
            'is_active' => 'boolean',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include verified subscriptions.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope a query to only include unverified subscriptions.
     */
    public function scopeUnverified(Builder $query): Builder
    {
        return $query->whereNull('verified_at');
    }

    /**
     * Scope a query to only include subscriptions for a specific program.
     */
    public function scopeForProgram(Builder $query, string $programCode): Builder
    {
        return $query->whereJsonContains('programs', $programCode);
    }

    /**
     * Scope a query to only include verified subscriptions for a specific program.
     */
    public function scopeVerifiedForProgram(Builder $query, string $programCode): Builder
    {
        return $query->verified()->forProgram($programCode);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if the subscription is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Verify the subscription.
     */
    public function verify(): bool
    {
        return $this->update([
            'verified_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Unsubscribe from the newsletter.
     */
    public function unsubscribe(): bool
    {
        return $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    /**
     * Generate a verification token.
     */
    public function generateVerificationToken(): string
    {
        $token = Str::random(32);

        $this->update([
            'verification_token' => $token,
        ]);

        return $token;
    }

    /**
     * Check if the subscription has a specific program.
     */
    public function hasProgram(string $programCode): bool
    {
        if (! $this->programs || ! is_array($this->programs)) {
            return false;
        }

        return in_array($programCode, $this->programs, true);
    }
}
