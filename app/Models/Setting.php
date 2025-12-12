<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'updated_by',
    ];

    /**
     * Get the value attribute based on type.
     */
    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute based on type.
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'integer' => (string) $value,
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => $value,
        };
    }

    /**
     * Get the user who last updated the setting.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
