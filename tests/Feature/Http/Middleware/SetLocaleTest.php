<?php

use App\Http\Middleware\SetLocale;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset locale to default config value
    App::setLocale(config('app.locale', 'es'));

    // Create default languages
    Language::factory()->create([
        'code' => 'es',
        'name' => 'Español',
        'is_active' => true,
        'is_default' => true,
    ]);
    Language::factory()->create([
        'code' => 'en',
        'name' => 'English',
        'is_active' => true,
        'is_default' => false,
    ]);
});

describe('SetLocale Middleware - Session Priority', function () {
    it('uses locale from session when available', function () {
        Session::put('locale', 'en');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('en');
    });

    it('prioritizes session over cookie', function () {
        Session::put('locale', 'es');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->cookies->set('locale', 'en');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('es');
    });
});

describe('SetLocale Middleware - Cookie Priority', function () {
    it('uses locale from cookie when session is empty', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->cookies->set('locale', 'en');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('en');
    });
});

describe('SetLocale Middleware - Accept-Language Header', function () {
    it('uses locale from Accept-Language header when session and cookie are empty', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9,es;q=0.8');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('en');
    });

    it('parses Accept-Language header with quality values', function () {
        Session::forget('locale');

        // Create French as active language
        Language::factory()->create([
            'code' => 'fr',
            'name' => 'Français',
            'is_active' => true,
            'is_default' => false,
        ]);

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // French has higher quality than English
        $request->headers->set('Accept-Language', 'fr;q=0.9,en;q=0.5');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('fr');
    });

    it('falls back to default when Accept-Language has no available language', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Request language that doesn't exist
        $request->headers->set('Accept-Language', 'zh-CN,zh;q=0.9');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should fall back to default (es)
        expect(App::getLocale())->toBe('es');
    });

    it('handles Accept-Language header without quality values', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'en');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('en');
    });

    it('extracts two-letter code from language-region format', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Uses es-ES format
        $request->headers->set('Accept-Language', 'es-ES,es;q=0.9');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('es');
    });
});

describe('SetLocale Middleware - Default Locale', function () {
    it('uses default locale when no preferences are set', function () {
        Session::flush();
        App::setLocale('es'); // Reset to default

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Remove default Accept-Language header that Request::create adds
        $request->headers->remove('Accept-Language');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should use default language (es)
        expect(App::getLocale())->toBe('es');
    });

    it('uses config locale when database has no default language', function () {
        Session::forget('locale');

        // Remove default language
        Language::where('is_default', true)->update(['is_default' => false]);

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Remove default Accept-Language header
        $request->headers->remove('Accept-Language');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should fall back to config('app.locale')
        expect(App::getLocale())->toBe(config('app.locale'));
    });
});

describe('SetLocale Middleware - Validate Locale', function () {
    it('validates locale exists and is active', function () {
        Session::put('locale', 'en');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect(App::getLocale())->toBe('en');
    });

    it('falls back to default for inactive locale', function () {
        // Deactivate English
        Language::where('code', 'en')->update(['is_active' => false]);

        Session::put('locale', 'en');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should fall back to default (es)
        expect(App::getLocale())->toBe('es');
    });

    it('falls back to default for non-existent locale', function () {
        Session::put('locale', 'xyz');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should fall back to default (es)
        expect(App::getLocale())->toBe('es');
    });
});

describe('SetLocale Middleware - Exception Handling', function () {
    it('handles database exception in getDefaultLocale gracefully', function () {
        Session::forget('locale');

        // Rename table to simulate database error
        Schema::rename('languages', 'languages_backup');

        try {
            $middleware = new SetLocale;
            $request = Request::create('/test', 'GET');

            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            // Should fall back to config locale
            expect(App::getLocale())->toBe(config('app.locale'));
        } finally {
            // Restore table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('handles database exception in validateLocale gracefully', function () {
        Session::put('locale', 'es');

        // Rename table to simulate database error
        Schema::rename('languages', 'languages_backup');

        try {
            $middleware = new SetLocale;
            $request = Request::create('/test', 'GET');
            $request->headers->remove('Accept-Language');

            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            // Should fall back to config locale
            expect(App::getLocale())->toBe(config('app.locale'));
        } finally {
            // Restore table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('handles database exception in validateLocale with cookie locale', function () {
        Session::flush();

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->headers->remove('Accept-Language');
        $request->cookies->set('locale', 'en');

        // Rename table to simulate database error
        Schema::rename('languages', 'languages_backup');

        try {
            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            // Should fall back to config locale since validation fails
            expect(App::getLocale())->toBe(config('app.locale'));
        } finally {
            // Restore table
            Schema::rename('languages_backup', 'languages');
        }
    });

    it('handles database exception in isLocaleAvailable gracefully', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'en-US');

        // Rename table to simulate database error
        Schema::rename('languages', 'languages_backup');

        try {
            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            // Should fall back to config locale (isLocaleAvailable returns false on exception)
            expect(App::getLocale())->toBe(config('app.locale'));
        } finally {
            // Restore table
            Schema::rename('languages_backup', 'languages');
        }
    });
});

describe('SetLocale Middleware - Empty Accept-Language Header', function () {
    it('handles empty Accept-Language header', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Empty header
        $request->headers->set('Accept-Language', '');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should use default
        expect(App::getLocale())->toBe('es');
    });

    it('handles missing Accept-Language header', function () {
        Session::forget('locale');

        $middleware = new SetLocale;
        $request = Request::create('/test', 'GET');
        // Remove default Accept-Language header that Request::create adds
        $request->headers->remove('Accept-Language');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Should use default
        expect(App::getLocale())->toBe('es');
    });
});
