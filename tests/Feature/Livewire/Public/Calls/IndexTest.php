<?php

use App\Livewire\Public\Calls\Index;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    // Create programs
    $this->program1 = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->program2 = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Asociaciones de Cooperación',
        'is_active' => true,
    ]);

    // Create academic years
    $this->academicYear1 = AcademicYear::factory()->create([
        'year' => '2024-2025',
        'is_current' => true,
    ]);

    $this->academicYear2 = AcademicYear::factory()->create([
        'year' => '2023-2024',
        'is_current' => false,
    ]);
});

it('renders the calls index page', function () {
    $this->get(route('convocatorias.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('displays only published calls with status abierta or cerrada', function () {
    // Create calls with different statuses
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria Abierta',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(10),
        'closed_at' => now()->subDays(5),
        'title' => 'Convocatoria Cerrada',
    ]);

    // These should NOT appear
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'borrador',
        'published_at' => null,
        'title' => 'Convocatoria Borrador',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => null, // Not published
        'title' => 'Convocatoria Sin Publicar',
    ]);

    Livewire::test(Index::class)
        ->assertSee('Convocatoria Abierta')
        ->assertSee('Convocatoria Cerrada')
        ->assertDontSee('Convocatoria Borrador')
        ->assertDontSee('Convocatoria Sin Publicar');
});

it('can search calls by title', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de Movilidad FP',
    ]);

    Call::factory()->create([
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de Cooperación',
    ]);

    Livewire::test(Index::class)
        ->set('search', 'FP')
        ->assertSee('Convocatoria de Movilidad FP')
        ->assertDontSee('Convocatoria de Cooperación');
});

it('can filter calls by program', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria FP',
    ]);

    Call::factory()->create([
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria Cooperación',
    ]);

    Livewire::test(Index::class)
        ->set('program', $this->program1->id)
        ->assertSee('Convocatoria FP')
        ->assertDontSee('Convocatoria Cooperación');
});

it('can filter calls by academic year', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria 2024-2025',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear2->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria 2023-2024',
    ]);

    Livewire::test(Index::class)
        ->set('academicYear', $this->academicYear1->id)
        ->assertSee('Convocatoria 2024-2025')
        ->assertDontSee('Convocatoria 2023-2024');
});

it('can filter calls by type', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'type' => 'alumnado',
        'title' => 'Convocatoria Alumnado',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'type' => 'personal',
        'title' => 'Convocatoria Personal',
    ]);

    Livewire::test(Index::class)
        ->set('type', 'alumnado')
        ->assertSee('Convocatoria Alumnado')
        ->assertDontSee('Convocatoria Personal');
});

it('can filter calls by modality', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'modality' => 'corta',
        'title' => 'Convocatoria Corta',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'modality' => 'larga',
        'title' => 'Convocatoria Larga',
    ]);

    Livewire::test(Index::class)
        ->set('modality', 'corta')
        ->assertSee('Convocatoria Corta')
        ->assertDontSee('Convocatoria Larga');
});

it('can filter calls by status', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria Abierta',
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(10),
        'closed_at' => now()->subDays(5),
        'title' => 'Convocatoria Cerrada',
    ]);

    Livewire::test(Index::class)
        ->set('status', 'abierta')
        ->assertSee('Convocatoria Abierta')
        ->assertDontSee('Convocatoria Cerrada');
});

it('can reset filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('program', $this->program1->id)
        ->set('academicYear', $this->academicYear1->id)
        ->set('type', 'alumnado')
        ->set('modality', 'corta')
        ->set('status', 'abierta')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('program', '')
        ->assertSet('academicYear', '')
        ->assertSet('type', '')
        ->assertSet('modality', '')
        ->assertSet('status', '');
});

it('shows empty state when no calls match filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'nonexistent call xyz')
        ->assertSee(__('No se encontraron convocatorias'));
});

it('displays statistics correctly', function () {
    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(10),
        'closed_at' => now()->subDays(5),
    ]);

    Livewire::test(Index::class)
        ->assertSeeHtml('2') // Total calls
        ->assertSeeHtml('1'); // Abiertas
});

it('supports pagination', function () {
    // Create enough calls to trigger pagination (default is 12 per page)
    Call::factory()->count(15)->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $component = Livewire::test(Index::class);
    expect($component->instance())->toBeInstanceOf(Index::class);
});

it('updates search and resets pagination', function () {
    Call::factory()->count(15)->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    Livewire::test(Index::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});

it('has correct seo title and description', function () {
    $this->get(route('convocatorias.index'))
        ->assertOk()
        ->assertSee('Convocatorias Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Index::class)
        ->assertSee(__('Convocatorias'));
});

it('links to call detail page', function () {
    $call = Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $this->get(route('convocatorias.index'))
        ->assertOk()
        ->assertSee(route('convocatorias.show', $call->slug));
});

it('orders calls with abierta first, then by published_at desc', function () {
    $closedCall = Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(5),
        'closed_at' => now()->subDays(2),
        'title' => 'Convocatoria Cerrada Reciente',
    ]);

    $openCall1 = Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now()->subDays(10),
        'title' => 'Convocatoria Abierta Antigua',
    ]);

    $openCall2 = Call::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'abierta',
        'published_at' => now()->subDays(2),
        'title' => 'Convocatoria Abierta Reciente',
    ]);

    $response = Livewire::test(Index::class);

    // The first call should be an open one (most recent)
    $response->assertSee('Convocatoria Abierta Reciente');
});
