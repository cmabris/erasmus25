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

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Front-office)
|--------------------------------------------------------------------------
|
| Estas rutas son accesibles sin autenticación y están destinadas a los
| visitantes del sitio web. Todas las rutas públicas usan el layout
| 'components.layouts.public' y están disponibles para usuarios anónimos.
|
*/

// Página principal
Route::get('/', Home::class)->name('home');

// Programas Erasmus+
// Listado y detalle de programas disponibles
Route::get('/programas', Programs\Index::class)->name('programas.index');
Route::get('/programas/{program:slug}', Programs\Show::class)->name('programas.show');

// Convocatorias
// Listado y detalle de convocatorias abiertas y cerradas (solo publicadas)
Route::get('/convocatorias', Calls\Index::class)->name('convocatorias.index');
Route::get('/convocatorias/{call:slug}', Calls\Show::class)->name('convocatorias.show');

// Noticias
// Listado y detalle de noticias publicadas
Route::get('/noticias', News\Index::class)->name('noticias.index');
Route::get('/noticias/{newsPost:slug}', News\Show::class)->name('noticias.show');

// Documentos
// Listado y detalle de documentos disponibles para descarga
Route::get('/documentos', Documents\Index::class)->name('documentos.index');
Route::get('/documentos/{document:slug}', Documents\Show::class)->name('documentos.show');

// Eventos
// Calendario de eventos y listado/detalle de eventos públicos
// Nota: Los eventos usan ID en lugar de slug porque son entidades más temporales
// y no requieren URLs amigables para SEO. El modelo ErasmusEvent no tiene campo slug.
Route::get('/calendario', Events\Calendar::class)->name('calendario');
Route::get('/eventos', Events\Index::class)->name('eventos.index');
Route::get('/eventos/{event}', Events\Show::class)->name('eventos.show');

// Newsletter
// Suscripción, verificación y baja del newsletter
Route::get('/newsletter/suscribir', Newsletter\Subscribe::class)->name('newsletter.subscribe');
Route::get('/newsletter/verificar/{token}', Newsletter\Verify::class)->name('newsletter.verify');
Route::get('/newsletter/baja', Newsletter\Unsubscribe::class)->name('newsletter.unsubscribe');
Route::get('/newsletter/baja/{token}', Newsletter\Unsubscribe::class)->name('newsletter.unsubscribe.token');

/*
|--------------------------------------------------------------------------
| Rutas de Usuario Autenticado
|--------------------------------------------------------------------------
|
| Estas rutas requieren autenticación pero no son parte del panel de
| administración. Incluyen el dashboard personal y configuración de usuario.
|
*/

// Dashboard personal (placeholder - redirige al admin si tiene permisos)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rutas de Administración (Back-office)
|--------------------------------------------------------------------------
|
| Estas rutas requieren autenticación y verificación de email. Todas las
| rutas de administración están bajo el prefijo '/admin' y el nombre de
| ruta 'admin.*'. Requieren permisos específicos según la funcionalidad.
|
*/

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard de Administración
    |--------------------------------------------------------------------------
    |
    | Panel principal de administración con estadísticas y accesos rápidos.
    | Requiere permisos: programs.view o users.view (mínimo)
    |
    */
    Route::get('/', AdminDashboard::class)->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Gestión de Contenido Principal
    |--------------------------------------------------------------------------
    |
    | Rutas para gestionar los contenidos principales de la aplicación:
    | programas, años académicos, convocatorias, noticias y documentos.
    |
    */

    // Programas Erasmus+
    // Permisos requeridos: programs.view, programs.create, programs.edit, programs.delete
    // Route Model Binding: {program} usa ID (no slug) para rutas de administración
    Route::get('/programas', \App\Livewire\Admin\Programs\Index::class)->name('programs.index');
    Route::get('/programas/crear', \App\Livewire\Admin\Programs\Create::class)->name('programs.create');
    Route::get('/programas/{program}', \App\Livewire\Admin\Programs\Show::class)->name('programs.show');
    Route::get('/programas/{program}/editar', \App\Livewire\Admin\Programs\Edit::class)->name('programs.edit');

    // Años Académicos
    // Permisos requeridos: academic-years.view, academic-years.create, academic-years.edit, academic-years.delete
    // Route Model Binding: {academic_year} usa ID
    Route::get('/anios-academicos', \App\Livewire\Admin\AcademicYears\Index::class)->name('academic-years.index');
    Route::get('/anios-academicos/crear', \App\Livewire\Admin\AcademicYears\Create::class)->name('academic-years.create');
    Route::get('/anios-academicos/{academic_year}', \App\Livewire\Admin\AcademicYears\Show::class)->name('academic-years.show');
    Route::get('/anios-academicos/{academic_year}/editar', \App\Livewire\Admin\AcademicYears\Edit::class)->name('academic-years.edit');

    // Convocatorias
    // Permisos requeridos: calls.view, calls.create, calls.edit, calls.delete, calls.publish
    // Route Model Binding: {call} usa ID (no slug) para rutas de administración
    Route::get('/convocatorias', \App\Livewire\Admin\Calls\Index::class)->name('calls.index');
    Route::get('/convocatorias/crear', \App\Livewire\Admin\Calls\Create::class)->name('calls.create');
    Route::get('/convocatorias/{call}', \App\Livewire\Admin\Calls\Show::class)->name('calls.show');
    Route::get('/convocatorias/{call}/editar', \App\Livewire\Admin\Calls\Edit::class)->name('calls.edit');

    // Noticias
    // Permisos requeridos: news.view, news.create, news.edit, news.delete, news.publish
    // Route Model Binding: {news_post} usa ID (no slug) para rutas de administración
    Route::get('/noticias', \App\Livewire\Admin\News\Index::class)->name('news.index');
    Route::get('/noticias/crear', \App\Livewire\Admin\News\Create::class)->name('news.create');
    Route::get('/noticias/{news_post}', \App\Livewire\Admin\News\Show::class)->name('news.show');
    Route::get('/noticias/{news_post}/editar', \App\Livewire\Admin\News\Edit::class)->name('news.edit');

    // Etiquetas de Noticias
    // Permisos requeridos: news-tags.view, news-tags.create, news-tags.edit, news-tags.delete
    // Route Model Binding: {news_tag} usa ID
    Route::get('/etiquetas', \App\Livewire\Admin\NewsTags\Index::class)->name('news-tags.index');
    Route::get('/etiquetas/crear', \App\Livewire\Admin\NewsTags\Create::class)->name('news-tags.create');
    Route::get('/etiquetas/{news_tag}', \App\Livewire\Admin\NewsTags\Show::class)->name('news-tags.show');
    Route::get('/etiquetas/{news_tag}/editar', \App\Livewire\Admin\NewsTags\Edit::class)->name('news-tags.edit');

    // Documentos
    // Permisos requeridos: documents.view, documents.create, documents.edit, documents.delete
    // Route Model Binding: {document} usa ID (no slug) para rutas de administración
    Route::get('/documentos', \App\Livewire\Admin\Documents\Index::class)->name('documents.index');
    Route::get('/documentos/crear', \App\Livewire\Admin\Documents\Create::class)->name('documents.create');
    Route::get('/documentos/{document}', \App\Livewire\Admin\Documents\Show::class)->name('documents.show');
    Route::get('/documentos/{document}/editar', \App\Livewire\Admin\Documents\Edit::class)->name('documents.edit');

    // Categorías de Documentos
    // Permisos requeridos: document-categories.view, document-categories.create, document-categories.edit, document-categories.delete
    // Route Model Binding: {document_category} usa ID
    Route::get('/categorias', \App\Livewire\Admin\DocumentCategories\Index::class)->name('document-categories.index');
    Route::get('/categorias/crear', \App\Livewire\Admin\DocumentCategories\Create::class)->name('document-categories.create');
    Route::get('/categorias/{document_category}', \App\Livewire\Admin\DocumentCategories\Show::class)->name('document-categories.show');
    Route::get('/categorias/{document_category}/editar', \App\Livewire\Admin\DocumentCategories\Edit::class)->name('document-categories.edit');

    // Eventos
    // Permisos requeridos: events.view, events.create, events.edit, events.delete
    // Route Model Binding: {event} usa ID (el modelo ErasmusEvent no tiene slug)
    Route::get('/eventos', \App\Livewire\Admin\Events\Index::class)->name('events.index');
    Route::get('/eventos/crear', \App\Livewire\Admin\Events\Create::class)->name('events.create');
    Route::get('/eventos/{event}', \App\Livewire\Admin\Events\Show::class)->name('events.show');
    Route::get('/eventos/{event}/editar', \App\Livewire\Admin\Events\Edit::class)->name('events.edit');

    /*
    |--------------------------------------------------------------------------
    | Rutas Anidadas de Convocatorias
    |--------------------------------------------------------------------------
    |
    | Rutas para gestionar fases y resoluciones de convocatorias.
    | Estas rutas están anidadas bajo una convocatoria específica.
    |
    */

    // Fases de Convocatorias
    // Permisos requeridos: calls.phases.view, calls.phases.create, calls.phases.edit, calls.phases.delete
    // Route Model Binding: {call} y {call_phase} usan ID
    Route::prefix('convocatorias/{call}')->group(function () {
        Route::get('/fases', \App\Livewire\Admin\Calls\Phases\Index::class)->name('calls.phases.index');
        Route::get('/fases/crear', \App\Livewire\Admin\Calls\Phases\Create::class)->name('calls.phases.create');
        Route::get('/fases/{call_phase}', \App\Livewire\Admin\Calls\Phases\Show::class)->name('calls.phases.show');
        Route::get('/fases/{call_phase}/editar', \App\Livewire\Admin\Calls\Phases\Edit::class)->name('calls.phases.edit');

        // Resoluciones de Convocatorias
        // Permisos requeridos: resolutions.view, resolutions.create, resolutions.edit, resolutions.delete
        // Route Model Binding: {call} y {resolution} usan ID
        Route::get('/resoluciones', \App\Livewire\Admin\Calls\Resolutions\Index::class)->name('calls.resolutions.index');
        Route::get('/resoluciones/crear', \App\Livewire\Admin\Calls\Resolutions\Create::class)->name('calls.resolutions.create');
        Route::get('/resoluciones/{resolution}', \App\Livewire\Admin\Calls\Resolutions\Show::class)->name('calls.resolutions.show');
        Route::get('/resoluciones/{resolution}/editar', \App\Livewire\Admin\Calls\Resolutions\Edit::class)->name('calls.resolutions.edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Usuarios y Permisos
    |--------------------------------------------------------------------------
    |
    | Rutas para gestionar usuarios, roles y permisos del sistema.
    |
    */

    // Usuarios
    // Permisos requeridos: users.view, users.create, users.edit, users.delete
    // Route Model Binding: {user} usa ID (los usuarios no tienen slug)
    Route::get('/usuarios', \App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/usuarios/crear', \App\Livewire\Admin\Users\Create::class)->name('users.create');
    Route::get('/usuarios/{user}', \App\Livewire\Admin\Users\Show::class)->name('users.show');
    Route::get('/usuarios/{user}/editar', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');

    // Roles y Permisos
    // Permisos requeridos: roles.view, roles.create, roles.edit, roles.delete (solo super-admin)
    // Route Model Binding: {role} usa ID (los roles no tienen slug)
    Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('roles.index');
    Route::get('/roles/crear', \App\Livewire\Admin\Roles\Create::class)->name('roles.create');
    Route::get('/roles/{role}', \App\Livewire\Admin\Roles\Show::class)->name('roles.show');
    Route::get('/roles/{role}/editar', \App\Livewire\Admin\Roles\Edit::class)->name('roles.edit');

    /*
    |--------------------------------------------------------------------------
    | Configuración y Sistema
    |--------------------------------------------------------------------------
    |
    | Rutas para gestionar configuraciones del sistema, traducciones,
    | auditoría y suscripciones al newsletter.
    |
    */

    // Configuración del Sistema
    // Permisos requeridos: settings.view, settings.edit (solo admin y super-admin)
    // Route Model Binding: {setting} usa ID
    Route::get('/configuracion', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');
    Route::get('/configuracion/{setting}/editar', \App\Livewire\Admin\Settings\Edit::class)->name('settings.edit');

    // Traducciones
    // Permisos requeridos: translations.view, translations.create, translations.edit, translations.delete
    // Route Model Binding: {translation} usa ID
    Route::get('/traducciones', \App\Livewire\Admin\Translations\Index::class)->name('translations.index');
    Route::get('/traducciones/crear', \App\Livewire\Admin\Translations\Create::class)->name('translations.create');
    Route::get('/traducciones/{translation}', \App\Livewire\Admin\Translations\Show::class)->name('translations.show');
    Route::get('/traducciones/{translation}/editar', \App\Livewire\Admin\Translations\Edit::class)->name('translations.edit');

    // Auditoría y Logs
    // Permisos requeridos: audit-logs.view (solo admin y super-admin)
    // Route Model Binding: {activity} usa ID (los logs no tienen slug)
    Route::get('/auditoria', \App\Livewire\Admin\AuditLogs\Index::class)->name('audit-logs.index');
    Route::get('/auditoria/{activity}', \App\Livewire\Admin\AuditLogs\Show::class)->name('audit-logs.show');

    // Suscripciones Newsletter
    // Permisos requeridos: newsletter.view (solo admin y super-admin)
    // Route Model Binding: {newsletter_subscription} usa ID
    Route::get('/newsletter', \App\Livewire\Admin\Newsletter\Index::class)->name('newsletter.index');
    Route::get('/newsletter/{newsletter_subscription}', \App\Livewire\Admin\Newsletter\Show::class)->name('newsletter.show');
});

/*
|--------------------------------------------------------------------------
| Rutas de Configuración de Usuario
|--------------------------------------------------------------------------
|
| Estas rutas permiten a los usuarios autenticados gestionar su perfil,
| contraseña, apariencia y autenticación de dos factores.
|
*/

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
