<?php

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Public\Calls;
use App\Livewire\Public\Documents;
use App\Livewire\Public\Events;
use App\Livewire\Public\Home;
use App\Livewire\Public\News;
use App\Livewire\Public\Newsletter;
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

// Rutas públicas de documentos
Route::get('/documentos', Documents\Index::class)->name('documentos.index');
Route::get('/documentos/{document:slug}', Documents\Show::class)->name('documentos.show');

// Rutas públicas de eventos
Route::get('/calendario', Events\Calendar::class)->name('calendario');
Route::get('/eventos', Events\Index::class)->name('eventos.index');
Route::get('/eventos/{event}', Events\Show::class)->name('eventos.show');

// Rutas públicas de newsletter
Route::get('/newsletter/suscribir', Newsletter\Subscribe::class)->name('newsletter.subscribe');
Route::get('/newsletter/verificar/{token}', Newsletter\Verify::class)->name('newsletter.verify');
Route::get('/newsletter/baja', Newsletter\Unsubscribe::class)->name('newsletter.unsubscribe');
Route::get('/newsletter/baja/{token}', Newsletter\Unsubscribe::class)->name('newsletter.unsubscribe.token');

// Dashboard público (placeholder - redirige al admin si tiene permisos)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rutas de administración
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    // Rutas de Programas
    Route::get('/programas', \App\Livewire\Admin\Programs\Index::class)->name('programs.index');
    Route::get('/programas/crear', \App\Livewire\Admin\Programs\Create::class)->name('programs.create');
    Route::get('/programas/{program}', \App\Livewire\Admin\Programs\Show::class)->name('programs.show');
    Route::get('/programas/{program}/editar', \App\Livewire\Admin\Programs\Edit::class)->name('programs.edit');

    // Rutas de Años Académicos
    Route::get('/anios-academicos', \App\Livewire\Admin\AcademicYears\Index::class)->name('academic-years.index');
    Route::get('/anios-academicos/crear', \App\Livewire\Admin\AcademicYears\Create::class)->name('academic-years.create');
    Route::get('/anios-academicos/{academic_year}', \App\Livewire\Admin\AcademicYears\Show::class)->name('academic-years.show');
    Route::get('/anios-academicos/{academic_year}/editar', \App\Livewire\Admin\AcademicYears\Edit::class)->name('academic-years.edit');

    // Rutas de Convocatorias
    Route::get('/convocatorias', \App\Livewire\Admin\Calls\Index::class)->name('calls.index');
    Route::get('/convocatorias/crear', \App\Livewire\Admin\Calls\Create::class)->name('calls.create');
    Route::get('/convocatorias/{call}', \App\Livewire\Admin\Calls\Show::class)->name('calls.show');
    Route::get('/convocatorias/{call}/editar', \App\Livewire\Admin\Calls\Edit::class)->name('calls.edit');

    // Rutas anidadas de Fases de Convocatorias
    Route::prefix('convocatorias/{call}')->group(function () {
        Route::get('/fases', \App\Livewire\Admin\Calls\Phases\Index::class)->name('calls.phases.index');
        Route::get('/fases/crear', \App\Livewire\Admin\Calls\Phases\Create::class)->name('calls.phases.create');
        Route::get('/fases/{call_phase}', \App\Livewire\Admin\Calls\Phases\Show::class)->name('calls.phases.show');
        Route::get('/fases/{call_phase}/editar', \App\Livewire\Admin\Calls\Phases\Edit::class)->name('calls.phases.edit');
    });
});

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
