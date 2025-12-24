<?php

use App\Livewire\Language\Switcher;
use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

use function Pest\Laravel\get;

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

test('language switcher component renders', function () {
    Livewire::test(Switcher::class)
        ->assertSee('Español')
        ->assertSee('English');
});

test('language switcher shows current language', function () {
    App::setLocale('es');
    
    Livewire::test(Switcher::class)
        ->assertSee('Español');
});

test('can switch language', function () {
    Session::put('locale', 'es');
    App::setLocale('es');
    
    Livewire::test(Switcher::class)
        ->call('switchLanguage', 'en')
        ->assertRedirect();
    
    expect(Session::get('locale'))->toBe('en');
});

test('cannot switch to unavailable language', function () {
    Livewire::test(Switcher::class)
        ->call('switchLanguage', 'fr')
        ->assertDispatched('language-error');
});

test('language switcher persists in session', function () {
    Session::put('locale', 'en');
    
    Livewire::test(Switcher::class)
        ->call('switchLanguage', 'es');
    
    expect(Session::get('locale'))->toBe('es');
});

test('language switcher dropdown variant renders', function () {
    Livewire::test(Switcher::class, ['variant' => 'dropdown'])
        ->assertSee('Español')
        ->assertSee('English');
});

test('language switcher buttons variant renders', function () {
    Livewire::test(Switcher::class, ['variant' => 'buttons'])
        ->assertSee('ES')
        ->assertSee('EN');
});

test('language switcher select variant renders', function () {
    Livewire::test(Switcher::class, ['variant' => 'select'])
        ->assertSee('Español')
        ->assertSee('English');
});

