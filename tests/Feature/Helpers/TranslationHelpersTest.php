<?php

use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    // Asegurar que existen los idiomas en la base de datos
    Language::firstOrCreate(
        ['code' => 'es'],
        ['name' => 'Español', 'is_default' => true, 'is_active' => true]
    );
    Language::firstOrCreate(
        ['code' => 'en'],
        ['name' => 'English', 'is_default' => false, 'is_active' => true]
    );
});

test('getCurrentLanguageCode returns current locale', function () {
    App::setLocale('en');
    
    expect(getCurrentLanguageCode())->toBe('en');
});

test('getCurrentLanguage returns language model', function () {
    App::setLocale('es');
    
    $language = getCurrentLanguage();
    
    expect($language)->toBeInstanceOf(Language::class)
        ->and($language->code)->toBe('es');
});

test('getAvailableLanguages returns active languages', function () {
    $languages = getAvailableLanguages();
    
    expect($languages)->toHaveCount(2)
        ->and($languages->pluck('code')->toArray())->toContain('es', 'en');
});

test('isLanguageAvailable checks if language exists and is active', function () {
    expect(isLanguageAvailable('es'))->toBeTrue()
        ->and(isLanguageAvailable('en'))->toBeTrue()
        ->and(isLanguageAvailable('fr'))->toBeFalse();
});

test('setLanguage changes locale and persists in session', function () {
    App::setLocale('es');
    
    setLanguage('en', persist: true);
    
    expect(App::getLocale())->toBe('en')
        ->and(Session::get('locale'))->toBe('en');
});

test('setLanguage returns false for unavailable language', function () {
    $result = setLanguage('fr', persist: false);
    
    expect($result)->toBeFalse();
});

test('getDefaultLanguage returns default language', function () {
    $default = getDefaultLanguage();
    
    expect($default)->toBeInstanceOf(Language::class)
        ->and($default->code)->toBe('es')
        ->and($default->is_default)->toBeTrue();
});

