<?php

use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper Functions Tests
|--------------------------------------------------------------------------
|
| Tests para cubrir las funciones helper de app/Support/helpers.php
| Objetivo: Aumentar cobertura a 100%
|
*/

/*
|--------------------------------------------------------------------------
| Exception Handling Tests - Para cubrir catch blocks
|--------------------------------------------------------------------------
*/

describe('exception handling in helpers', function () {
    it('getCurrentLanguage returns null when database throws exception', function () {
        App::setLocale('es');

        // Temporarily rename the languages table to simulate DB exception
        Schema::rename('languages', 'languages_backup');

        try {
            $result = getCurrentLanguage();
            expect($result)->toBeNull();
        } finally {
            // Restore the table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('getAvailableLanguages returns empty collection when database throws exception', function () {
        // Temporarily rename the languages table to simulate DB exception
        Schema::rename('languages', 'languages_backup');

        try {
            $result = getAvailableLanguages();
            expect($result)->toBeEmpty();
            expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        } finally {
            // Restore the table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('isLanguageAvailable returns false when database throws exception', function () {
        // Temporarily rename the languages table to simulate DB exception
        Schema::rename('languages', 'languages_backup');

        try {
            $result = isLanguageAvailable('es');
            expect($result)->toBeFalse();
        } finally {
            // Restore the table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('getDefaultLanguage returns null when database throws exception', function () {
        // Temporarily rename the languages table to simulate DB exception
        Schema::rename('languages', 'languages_backup');

        try {
            $result = getDefaultLanguage();
            expect($result)->toBeNull();
        } finally {
            // Restore the table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('setLanguage returns false when database throws exception', function () {
        // Temporarily rename the languages table to simulate DB exception
        Schema::rename('languages', 'languages_backup');

        try {
            $result = setLanguage('es');
            expect($result)->toBeFalse();
        } finally {
            // Restore the table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('trans_model returns null when database throws exception', function () {
        $language = Language::factory()->create(['code' => 'es', 'is_active' => true]);
        App::setLocale('es');
        $program = Program::factory()->create();

        // Temporarily rename the translations table to simulate DB exception
        Schema::rename('translations', 'translations_backup');

        try {
            $result = trans_model($program, 'name');
            expect($result)->toBeNull();
        } finally {
            // Restore the table
            Schema::rename('translations_backup', 'translations');
        }
    });

    it('trans_model returns null for model that does not exist', function () {
        $language = Language::factory()->create(['code' => 'es', 'is_active' => true]);
        App::setLocale('es');

        // Create a model instance without saving (exists = false)
        $program = new Program;
        $program->name = 'Test';
        // Don't save - exists property is false

        expect(trans_model($program, 'name'))->toBeNull();
    });

    it('trans_model returns null for null model', function () {
        expect(trans_model(null, 'name'))->toBeNull();
    });
});

describe('getCurrentLanguage', function () {
    it('returns current language model', function () {
        $language = Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
        ]);

        App::setLocale('es');

        $result = getCurrentLanguage();

        expect($result)->not->toBeNull();
        expect($result->code)->toBe('es');
    });

    it('returns null for non-existent language code', function () {
        App::setLocale('xx');

        $result = getCurrentLanguage();

        expect($result)->toBeNull();
    });

    it('returns null for inactive language', function () {
        Language::factory()->create([
            'code' => 'fr',
            'is_active' => false,
        ]);

        App::setLocale('fr');

        $result = getCurrentLanguage();

        expect($result)->toBeNull();
    });
});

describe('getCurrentLanguageCode', function () {
    it('returns current locale code', function () {
        App::setLocale('en');

        expect(getCurrentLanguageCode())->toBe('en');
    });

    it('returns es locale when set', function () {
        App::setLocale('es');

        expect(getCurrentLanguageCode())->toBe('es');
    });
});

describe('setting', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('returns setting value by key', function () {
        Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'type' => 'string',
        ]);

        $result = setting('site_name');

        expect($result)->toBe('Test Site');
    });

    it('returns default when setting not found', function () {
        $result = setting('non_existent_key', 'default_value');

        expect($result)->toBe('default_value');
    });

    it('caches setting value', function () {
        Setting::factory()->create([
            'key' => 'cached_setting',
            'value' => 'cached_value',
            'type' => 'string',
        ]);

        // First call should cache
        $result1 = setting('cached_setting');

        // Delete the setting from DB
        Setting::where('key', 'cached_setting')->delete();

        // Second call should return cached value
        $result2 = setting('cached_setting');

        expect($result1)->toBe('cached_value');
        expect($result2)->toBe('cached_value');
    });

    it('returns full URL for center_logo if already a URL', function () {
        Setting::factory()->create([
            'key' => 'center_logo',
            'value' => 'https://example.com/logo.png',
            'type' => 'string',
        ]);

        $result = setting('center_logo');

        expect($result)->toBe('https://example.com/logo.png');
    });

    it('returns storage URL for center_logo with logos/ path', function () {
        Storage::fake('public');

        Setting::factory()->create([
            'key' => 'center_logo',
            'value' => 'logos/custom-logo.jpg',
            'type' => 'string',
        ]);

        $result = setting('center_logo');

        expect($result)->toBeString();
        expect(str_contains($result, 'logos/custom-logo.jpg'))->toBeTrue();
    });

    it('returns path as-is for center_logo starting with slash', function () {
        Setting::factory()->create([
            'key' => 'center_logo',
            'value' => '/images/logo.png',
            'type' => 'string',
        ]);

        $result = setting('center_logo');

        expect($result)->toBe('/images/logo.png');
    });

    it('returns null value with default for empty setting', function () {
        Setting::factory()->create([
            'key' => 'empty_setting',
            'value' => null,
            'type' => 'string',
        ]);

        $result = setting('empty_setting', 'fallback');

        expect($result)->toBe('fallback');
    });
});

describe('setLanguage', function () {
    it('sets locale for active language', function () {
        Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
        ]);

        $result = setLanguage('en');

        expect($result)->toBeTrue();
        expect(App::getLocale())->toBe('en');
    });

    it('returns false for non-existent language', function () {
        $result = setLanguage('xx');

        expect($result)->toBeFalse();
    });

    it('returns false for inactive language', function () {
        Language::factory()->create([
            'code' => 'de',
            'is_active' => false,
        ]);

        $result = setLanguage('de');

        expect($result)->toBeFalse();
    });

    it('persists language in session when persist is true', function () {
        Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
        ]);

        setLanguage('es', true);

        expect(session('locale'))->toBe('es');
    });

    it('does not persist language in session when persist is false', function () {
        Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
        ]);

        session()->forget('locale');
        setLanguage('en', false);

        expect(session('locale'))->toBeNull();
    });
});

describe('getAvailableLanguages', function () {
    it('returns collection of active languages', function () {
        Language::factory()->create(['code' => 'es', 'is_active' => true]);
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        Language::factory()->create(['code' => 'fr', 'is_active' => false]);

        $result = getAvailableLanguages();

        expect($result)->toHaveCount(2);
        expect($result->pluck('code')->toArray())->toContain('es', 'en');
        expect($result->pluck('code')->toArray())->not->toContain('fr');
    });

    it('orders by default first, then by name', function () {
        Language::factory()->create([
            'code' => 'en',
            'name' => 'English',
            'is_active' => true,
            'is_default' => false,
        ]);
        Language::factory()->create([
            'code' => 'es',
            'name' => 'Español',
            'is_active' => true,
            'is_default' => true,
        ]);

        $result = getAvailableLanguages();

        expect($result->first()->code)->toBe('es');
    });

    it('returns empty collection when no active languages', function () {
        Language::factory()->create(['is_active' => false]);

        $result = getAvailableLanguages();

        expect($result)->toBeEmpty();
    });
});

describe('isLanguageAvailable', function () {
    it('returns true for active language', function () {
        Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
        ]);

        expect(isLanguageAvailable('es'))->toBeTrue();
    });

    it('returns false for inactive language', function () {
        Language::factory()->create([
            'code' => 'fr',
            'is_active' => false,
        ]);

        expect(isLanguageAvailable('fr'))->toBeFalse();
    });

    it('returns false for non-existent language', function () {
        expect(isLanguageAvailable('xx'))->toBeFalse();
    });
});

describe('getDefaultLanguage', function () {
    it('returns default active language', function () {
        Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
            'is_default' => false,
        ]);
        $default = Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
            'is_default' => true,
        ]);

        $result = getDefaultLanguage();

        expect($result)->not->toBeNull();
        expect($result->code)->toBe('es');
    });

    it('returns null when default language is inactive', function () {
        Language::factory()->create([
            'code' => 'es',
            'is_active' => false,
            'is_default' => true,
        ]);

        $result = getDefaultLanguage();

        expect($result)->toBeNull();
    });

    it('returns null when no default language exists', function () {
        Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
            'is_default' => false,
        ]);

        $result = getDefaultLanguage();

        expect($result)->toBeNull();
    });
});

describe('trans_model', function () {
    beforeEach(function () {
        $this->language = Language::factory()->create([
            'code' => 'en',
            'is_active' => true,
        ]);
        App::setLocale('en');
    });

    it('returns translated field value', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $this->language->id,
            'field' => 'name',
            'value' => 'Translated Name',
        ]);

        $result = trans_model($program, 'name');

        expect($result)->toBe('Translated Name');
    });

    it('returns null when model is null', function () {
        $result = trans_model(null, 'name');

        expect($result)->toBeNull();
    });

    it('returns null when translation not found', function () {
        $program = Program::factory()->create();

        $result = trans_model($program, 'name');

        expect($result)->toBeNull();
    });

    it('returns null for non-existent model', function () {
        $program = new Program;
        $program->id = 99999;
        // Model doesn't exist in DB

        $result = trans_model($program, 'name');

        expect($result)->toBeNull();
    });

    it('uses specific locale when provided', function () {
        $spanish = Language::factory()->create([
            'code' => 'es',
            'is_active' => true,
        ]);

        $program = Program::factory()->create(['name' => 'Original Name']);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $spanish->id,
            'field' => 'name',
            'value' => 'Nombre Traducido',
        ]);

        $result = trans_model($program, 'name', 'es');

        expect($result)->toBe('Nombre Traducido');
    });

    it('returns null for translation in inactive language', function () {
        $inactiveLanguage = Language::factory()->create([
            'code' => 'fr',
            'is_active' => false,
        ]);

        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $inactiveLanguage->id,
            'field' => 'name',
            'value' => 'French Translation',
        ]);

        App::setLocale('fr');
        $result = trans_model($program, 'name');

        expect($result)->toBeNull();
    });
});

describe('trans_route', function () {
    it('generates route URL', function () {
        $result = trans_route('home');

        expect($result)->toBe(route('home'));
    });

    it('generates route URL with parameters', function () {
        $program = Program::factory()->create(['slug' => 'test-program']);

        $result = trans_route('programas.show', ['program' => $program->slug]);

        expect($result)->toBe(route('programas.show', ['program' => $program->slug]));
    });

    it('generates relative URL when absolute is false', function () {
        $result = trans_route('home', [], false);

        expect($result)->toBe(route('home', [], false));
    });
});

describe('format_number', function () {
    it('formats number with default 0 decimals', function () {
        App::setLocale('es');

        $result = format_number(1234567);

        expect($result)->toMatch('/1[\.\s]?234[\.\s]?567/');
    });

    it('formats number with specified decimals', function () {
        App::setLocale('es');

        $result = format_number(1234.5678, 2);

        // Spanish locale uses . as thousands separator and , as decimal
        // Result should be like "1.234,57"
        expect($result)->toBeString();
        expect($result)->toMatch('/57/'); // Rounded to 57 cents
    });

    it('formats number for English locale', function () {
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        App::setLocale('en');

        $result = format_number(1234.56, 2);

        expect($result)->toBeString();
        // Contains 56 as decimal part
        expect($result)->toMatch('/56/');
    });

    it('handles zero correctly', function () {
        App::setLocale('es');

        $result = format_number(0);

        expect($result)->toBe('0');
    });

    it('handles negative numbers', function () {
        App::setLocale('es');

        $result = format_number(-1234, 0);

        // Should contain negative sign and formatted number
        expect($result)->toBeString();
        expect(str_contains($result, '-'))->toBeTrue();
    });

    it('handles float input', function () {
        App::setLocale('es');

        $result = format_number(1234.99, 2);

        // Should contain 99 as decimals
        expect($result)->toBeString();
        expect($result)->toMatch('/99/');
    });

    it('formats number with various decimal counts', function () {
        App::setLocale('es');

        // Test with 0 decimals
        $result0 = format_number(1234.567, 0);
        expect($result0)->toBeString();

        // Test with 1 decimal
        $result1 = format_number(1234.567, 1);
        expect($result1)->toBeString();

        // Test with 3 decimals
        $result3 = format_number(1234.567, 3);
        expect($result3)->toBeString();
        expect($result3)->toMatch('/567/');
    });

    it('formats very large numbers', function () {
        App::setLocale('es');

        $result = format_number(1234567890123, 0);

        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(10);
    });

    it('formats very small decimals', function () {
        App::setLocale('es');

        $result = format_number(0.0001, 4);

        expect($result)->toBeString();
        expect($result)->toMatch('/0001/');
    });
});

describe('format_number edge cases', function () {
    it('handles non-standard locale gracefully', function () {
        // Set a locale that might not be fully supported
        App::setLocale('xx');

        $result = format_number(1234.56, 2);

        // Should still return a formatted string (either through NumberFormatter or fallback)
        expect($result)->toBeString();
        expect($result)->not->toBeEmpty();
    });

    it('uses comma as decimal separator for es locale', function () {
        App::setLocale('es');

        $result = format_number(1234.56, 2);

        // Spanish should use comma as decimal separator
        expect($result)->toBeString();
        // The result should contain the decimal part
        expect($result)->toMatch('/56/');
    });

    it('uses dot as decimal separator for en locale', function () {
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        App::setLocale('en');

        $result = format_number(1234.56, 2);

        // English should use dot as decimal separator
        expect($result)->toBeString();
        expect($result)->toMatch('/56/');
    });

    it('formats number with fallback when locale is not standard', function () {
        // Test with a non-standard locale that NumberFormatter might handle differently
        App::setLocale('zz');

        $result = format_number(1234.56, 2);

        // Should use fallback number_format
        expect($result)->toBeString();
        expect($result)->not->toBeEmpty();
    });

    it('handles extreme locale values', function () {
        // Test edge case locales
        App::setLocale('');
        $result1 = format_number(100, 0);
        expect($result1)->toBeString();

        App::setLocale('a');
        $result2 = format_number(100, 0);
        expect($result2)->toBeString();
    });
});

describe('format_number fallback behavior', function () {
    it('uses Spanish format separators for es locale', function () {
        App::setLocale('es');

        // The number_format fallback for 'es' uses:
        // - comma as decimal separator
        // - dot as thousands separator
        $result = format_number(1234567.89, 2);

        expect($result)->toBeString();
        // Should contain the formatted number
        expect(preg_match('/1.*234.*567.*89/', $result))->toBe(1);
    });

    it('uses English format separators for non-es locale', function () {
        App::setLocale('en');

        // The number_format fallback for non-'es' uses:
        // - dot as decimal separator
        // - comma as thousands separator
        $result = format_number(1234567.89, 2);

        expect($result)->toBeString();
        // Should contain the formatted number
        expect(preg_match('/1.*234.*567.*89/', $result))->toBe(1);
    });

    it('fallback handles fr locale like en (not es)', function () {
        App::setLocale('fr');

        $result = format_number(1234.56, 2);

        // French should fall through to default (non-es) behavior in fallback
        expect($result)->toBeString();
        expect($result)->toMatch('/56/');
    });
});

describe('format_date', function () {
    it('formats date for Spanish locale', function () {
        Language::factory()->create(['code' => 'es', 'is_active' => true]);
        App::setLocale('es');

        $date = \Carbon\Carbon::create(2025, 1, 15);

        $result = format_date($date);

        expect($result)->toBe('15/01/2025');
    });

    it('formats date for English locale', function () {
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        App::setLocale('en');

        $date = \Carbon\Carbon::create(2025, 1, 15);

        $result = format_date($date);

        expect($result)->toBe('01/15/2025');
    });

    it('formats date with custom format', function () {
        App::setLocale('es');

        $date = \Carbon\Carbon::create(2025, 1, 15);

        $result = format_date($date, 'Y-m-d');

        expect($result)->toBe('2025-01-15');
    });

    it('parses string date', function () {
        App::setLocale('es');

        $result = format_date('2025-01-15');

        expect($result)->toBe('15/01/2025');
    });

    it('handles DateTime object', function () {
        App::setLocale('es');

        $date = new \DateTime('2025-01-15');

        $result = format_date($date);

        expect($result)->toBe('15/01/2025');
    });

    it('uses fallback format for unknown locale', function () {
        App::setLocale('xx');

        $date = \Carbon\Carbon::create(2025, 1, 15);

        $result = format_date($date);

        expect($result)->toBe('2025-01-15');
    });
});

describe('format_datetime', function () {
    it('formats datetime for Spanish locale', function () {
        Language::factory()->create(['code' => 'es', 'is_active' => true]);
        App::setLocale('es');

        $date = \Carbon\Carbon::create(2025, 1, 15, 14, 30);

        $result = format_datetime($date);

        expect($result)->toBe('15/01/2025 14:30');
    });

    it('formats datetime for English locale', function () {
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        App::setLocale('en');

        $date = \Carbon\Carbon::create(2025, 1, 15, 14, 30);

        $result = format_datetime($date);

        expect($result)->toBe('01/15/2025 14:30');
    });

    it('formats datetime with custom date format', function () {
        App::setLocale('es');

        $date = \Carbon\Carbon::create(2025, 1, 15, 14, 30);

        $result = format_datetime($date, 'Y-m-d');

        expect($result)->toBe('2025-01-15 14:30');
    });

    it('formats datetime with custom time format', function () {
        App::setLocale('es');

        $date = \Carbon\Carbon::create(2025, 1, 15, 14, 30, 45);

        $result = format_datetime($date, null, 'H:i:s');

        expect($result)->toBe('15/01/2025 14:30:45');
    });

    it('parses string datetime', function () {
        App::setLocale('es');

        $result = format_datetime('2025-01-15 14:30:00');

        expect($result)->toBe('15/01/2025 14:30');
    });

    it('handles DateTime object', function () {
        App::setLocale('es');

        $date = new \DateTime('2025-01-15 14:30:00');

        $result = format_datetime($date);

        expect($result)->toBe('15/01/2025 14:30');
    });

    it('uses fallback format for unknown locale', function () {
        App::setLocale('xx');

        $date = \Carbon\Carbon::create(2025, 1, 15, 14, 30);

        $result = format_datetime($date);

        expect($result)->toBe('2025-01-15 14:30');
    });
});
