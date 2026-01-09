<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

    use Translatable;

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
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Clear cache when settings are updated
        static::saved(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.all');
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("setting.{$setting->key}");
            Cache::forget('settings.all');
        });
    }

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

    /**
     * Get a setting value by key (static helper).
     *
     * @param  string  $key  Setting key
     * @param  mixed  $default  Default value if not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(
            "setting.{$key}",
            now()->addHours(24),
            function () use ($key, $default) {
                $setting = static::where('key', $key)->first();

                if (! $setting) {
                    return $default;
                }

                $value = $setting->value;

                // If this is center_logo and the value is a storage path, convert to public URL
                if ($key === 'center_logo' && $value) {
                    // If it's already a full URL, return it
                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        return $value;
                    }

                    // If it's a storage path (logos/xxx.jpg), return the public URL
                    if (str_starts_with($value, 'logos/')) {
                        return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
                    }

                    // If it's a public path starting with /, return as is
                    if (str_starts_with($value, '/')) {
                        return $value;
                    }
                }

                return $value ?? $default;
            }
        );
    }
}
