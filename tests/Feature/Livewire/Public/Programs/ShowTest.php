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

it('returns correct program config for ADU code', function () {
    $aduProgram = Program::factory()->create(['code' => 'KA121-ADU']);

    // ADU programs should show Educación de Adultos type
    Livewire::test(Show::class, ['program' => $aduProgram])
        ->assertSee(__('Educación de Adultos'));
});

it('returns correct program config for KA1 pure code', function () {
    // KA1 code that doesn't contain VET, HED, SCH, or ADU
    $ka1Program = Program::factory()->create(['code' => 'KA101-MOB']);

    // KA1 programs should show Movilidad type
    $component = Livewire::test(Show::class, ['program' => $ka1Program]);
    $config = $component->instance()->programConfig;
    
    expect($config['icon'])->toBe('academic-cap')
        ->and($config['color'])->toBe('blue')
        ->and($config['type'])->toBe(__('Movilidad'));
});

it('returns correct program config for JM code', function () {
    $jmProgram = Program::factory()->create(['code' => 'JM-2024-001']);

    // JM programs should show Jean Monnet type
    $component = Livewire::test(Show::class, ['program' => $jmProgram]);
    $config = $component->instance()->programConfig;
    
    expect($config['icon'])->toBe('building-office-2')
        ->and($config['color'])->toBe('indigo')
        ->and($config['type'])->toBe(__('Jean Monnet'));
});

it('returns correct program config for DISCOVER code', function () {
    $discoverProgram = Program::factory()->create(['code' => 'DISCOVER-2024']);

    // DISCOVER programs should show DiscoverEU type
    $component = Livewire::test(Show::class, ['program' => $discoverProgram]);
    $config = $component->instance()->programConfig;
    
    expect($config['icon'])->toBe('map')
        ->and($config['color'])->toBe('rose')
        ->and($config['type'])->toBe(__('DiscoverEU'));
});

it('returns default program config for unknown code', function () {
    $unknownProgram = Program::factory()->create(['code' => 'UNKNOWN-CODE']);

    // Unknown codes should show default Erasmus+ type
    $component = Livewire::test(Show::class, ['program' => $unknownProgram]);
    $config = $component->instance()->programConfig;
    
    expect($config['icon'])->toBe('globe-europe-africa')
        ->and($config['color'])->toBe('erasmus')
        ->and($config['type'])->toBe(__('Erasmus+'));
});

it('returns default program config when code is null', function () {
    // Since code is required in the database, we create with a valid code
    // then use setAttribute to set it to null to test the default case
    $programWithoutCode = Program::factory()->create(['code' => 'TEST-CODE']);
    
    // Set code to null to test the default case
    $programWithoutCode->setAttribute('code', null);

    // Programs without code should show default Erasmus+ type
    $component = Livewire::test(Show::class, ['program' => $programWithoutCode]);
    $config = $component->instance()->programConfig;
    
    expect($config['icon'])->toBe('globe-europe-africa')
        ->and($config['color'])->toBe('erasmus')
        ->and($config['type'])->toBe(__('Erasmus+'));
});

it('returns empty collection for relatedCalls when no calls exist', function () {
    // Program without any calls
    $programWithoutCalls = Program::factory()->create(['is_active' => true]);

    $component = Livewire::test(Show::class, ['program' => $programWithoutCalls]);
    $relatedCalls = $component->instance()->relatedCalls;
    
    expect($relatedCalls)->toBeEmpty();
});

it('returns empty collection for relatedNews when no news exist', function () {
    // Program without any news
    $programWithoutNews = Program::factory()->create(['is_active' => true]);

    $component = Livewire::test(Show::class, ['program' => $programWithoutNews]);
    $relatedNews = $component->instance()->relatedNews;
    
    expect($relatedNews)->toBeEmpty();
});

it('returns empty collection for otherPrograms when no other programs exist', function () {
    // Delete all other programs to ensure empty result
    Program::where('id', '!=', $this->program->id)->delete();

    $component = Livewire::test(Show::class, ['program' => $this->program]);
    $otherPrograms = $component->instance()->otherPrograms;
    
    expect($otherPrograms)->toBeEmpty();
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
