<?php

use App\Livewire\Public\Programs\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'slug' => 'movilidad-formacion-profesional',
        'description' => 'Descripción detallada del programa de formación profesional.',
        'is_active' => true,
    ]);
});

it('renders the program show page', function () {
    $this->get(route('programas.show', $this->program->slug))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('displays program information', function () {
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee($this->program->name)
        ->assertSee($this->program->code)
        ->assertSee($this->program->description);
});

it('shows active status badge for active programs', function () {
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee(__('Activo'));
});

it('shows inactive status badge for inactive programs', function () {
    $inactiveProgram = Program::factory()->create(['is_active' => false]);

    Livewire::test(Show::class, ['program' => $inactiveProgram])
        ->assertSee(__('Inactivo'));
});

it('displays related calls when available', function () {
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de prueba',
    ]);

    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Convocatoria de prueba')
        ->assertSee(__('Convocatorias de este programa'));
});

it('does not show calls section when no calls available', function () {
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertDontSee(__('Convocatorias de este programa'));
});

it('displays related news when available', function () {
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia de prueba',
    ]);

    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Noticia de prueba')
        ->assertSee(__('Noticias relacionadas'));
});

it('does not show news section when no news available', function () {
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertDontSee(__('Noticias relacionadas'));
});

it('displays other programs suggestion', function () {
    $otherProgram = Program::factory()->create([
        'name' => 'Otro programa activo',
        'is_active' => true,
    ]);

    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Otro programa activo')
        ->assertSee(__('Otros programas que te pueden interesar'));
});

it('returns correct program config for VET code', function () {
    // VET programs should show the FP type badge
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSet('program.code', 'KA121-VET')
        ->assertSee(__('Formación Profesional'));
});

it('returns correct program config for HED code', function () {
    $hedProgram = Program::factory()->create(['code' => 'KA131-HED']);

    // HED programs should show Education Superior type
    Livewire::test(Show::class, ['program' => $hedProgram])
        ->assertSee(__('Educación Superior'));
});

it('returns correct program config for KA2 code', function () {
    // KA220-SCH will match SCH first (Educación Escolar), so use a pure KA2 code
    $ka2Program = Program::factory()->create(['code' => 'KA201-GEN']);

    // KA2 programs should show Cooperation type
    Livewire::test(Show::class, ['program' => $ka2Program])
        ->assertSee(__('Cooperación'));
});

it('returns correct program config for SCH code', function () {
    $schProgram = Program::factory()->create(['code' => 'KA220-SCH']);

    // SCH programs should show Educación Escolar type
    Livewire::test(Show::class, ['program' => $schProgram])
        ->assertSee(__('Educación Escolar'));
});

it('returns 404 for non-existent program', function () {
    $this->get(route('programas.show', 'non-existent-slug'))
        ->assertNotFound();
});

it('shows breadcrumbs with correct links', function () {
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee(__('Programas'))
        ->assertSee($this->program->name);
});

it('has correct seo title', function () {
    $this->get(route('programas.show', $this->program->slug))
        ->assertOk()
        ->assertSee($this->program->name);
});

it('shows empty state when no content available', function () {
    // Program without calls or news
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee(__('Contenido próximamente'));
});

it('only shows open and closed calls', function () {
    $academicYear = AcademicYear::factory()->create();

    // Draft call (should not appear)
    Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'borrador',
        'published_at' => now(),
        'title' => 'Borrador de convocatoria',
    ]);

    // Open call (should appear)
    Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria abierta',
    ]);

    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Convocatoria abierta')
        ->assertDontSee('Borrador de convocatoria');
});

it('only shows published news', function () {
    $author = User::factory()->create();

    // Draft news (should not appear)
    NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'author_id' => $author->id,
        'status' => 'borrador',
        'published_at' => null,
        'title' => 'Noticia borrador',
    ]);

    // Published news (should appear)
    NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia publicada',
    ]);

    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Noticia publicada')
        ->assertDontSee('Noticia borrador');
});
