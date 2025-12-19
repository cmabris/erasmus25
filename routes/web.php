<?php

use App\Livewire\Public\Calls;
use App\Livewire\Public\Home;
use App\Livewire\Public\News;
use App\Livewire\Public\Programs;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', Home::class)->name('home');

// Rutas públicas de programas
Route::get('/programas', Programs\Index::class)->name('programas.index');
Route::get('/programas/{program:slug}', Programs\Show::class)->name('programas.show');

// Rutas públicas de convocatorias
Route::get('/convocatorias', Calls\Index::class)->name('convocatorias.index');
Route::get('/convocatorias/{call:slug}', Calls\Show::class)->name('convocatorias.show');

// Rutas públicas de noticias
Route::get('/noticias', News\Index::class)->name('noticias.index');
Route::get('/noticias/{newsPost:slug}', News\Show::class)->name('noticias.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
