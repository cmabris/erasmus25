<?php

use App\Livewire\Public\Calls\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2024-2025',
        'is_current' => true,
    ]);

    $this->call = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de Movilidad FP',
        'slug' => 'convocatoria-movilidad-fp',
        'type' => 'alumnado',
        'modality' => 'corta',
        'number_of_places' => 15,
        'destinations' => ['Alemania', 'Francia', 'Italia'],
        'requirements' => 'Requisitos de la convocatoria',
        'documentation' => 'Documentación necesaria',
        'selection_criteria' => 'Criterios de selección',
    ]);
});

it('renders the call show page', function () {
    $this->get(route('convocatorias.show', $this->call->slug))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('displays call information', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee($this->call->title)
        ->assertSee($this->call->program->name)
        ->assertSee($this->call->academicYear->year)
        ->assertSee($this->call->requirements)
        ->assertSee($this->call->documentation)
        ->assertSee($this->call->selection_criteria);
});

it('shows status badge for open calls', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee(__('Abierta'));
});

it('shows status badge for closed calls', function () {
    $closedCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(10),
        'closed_at' => now()->subDays(5),
    ]);

    Livewire::test(Show::class, ['call' => $closedCall])
        ->assertSee(__('Cerrada'));
});

it('displays call phases when available', function () {
    $phase1 = CallPhase::factory()->create([
        'call_id' => $this->call->id,
        'phase_type' => 'publicacion',
        'name' => 'Publicación de la convocatoria',
        'order' => 1,
        'is_current' => false,
    ]);

    $phase2 = CallPhase::factory()->create([
        'call_id' => $this->call->id,
        'phase_type' => 'solicitudes',
        'name' => 'Periodo de solicitudes',
        'order' => 2,
        'is_current' => true,
    ]);

    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('Publicación de la convocatoria')
        ->assertSee('Periodo de solicitudes')
        ->assertSee(__('Fases de la convocatoria'));
});

it('does not show phases section when no phases available', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertDontSee(__('Fases de la convocatoria'));
});

it('displays published resolutions when available', function () {
    $phase = CallPhase::factory()->create([
        'call_id' => $this->call->id,
        'phase_type' => 'provisional',
        'order' => 1,
    ]);

    $resolution = Resolution::factory()->create([
        'call_id' => $this->call->id,
        'call_phase_id' => $phase->id,
        'type' => 'provisional',
        'title' => 'Resolución provisional',
        'published_at' => now(),
    ]);

    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('Resolución provisional')
        ->assertSee(__('Resoluciones publicadas'));
});

it('does not show unpublished resolutions', function () {
    $phase = CallPhase::factory()->create([
        'call_id' => $this->call->id,
        'phase_type' => 'provisional',
        'order' => 1,
    ]);

    // Unpublished resolution
    Resolution::factory()->create([
        'call_id' => $this->call->id,
        'call_phase_id' => $phase->id,
        'type' => 'provisional',
        'title' => 'Resolución no publicada',
        'published_at' => null,
    ]);

    Livewire::test(Show::class, ['call' => $this->call])
        ->assertDontSee('Resolución no publicada');
});

it('does not show resolutions section when no resolutions available', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertDontSee(__('Resoluciones publicadas'));
});

it('displays related news when available', function () {
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia relacionada',
    ]);

    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('Noticia relacionada')
        ->assertSee(__('Noticias relacionadas'));
});

it('does not show news section when no news available', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertDontSee(__('Noticias relacionadas'));
});

it('displays other calls from the same program', function () {
    $otherCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Otra convocatoria del programa',
    ]);

    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('Otra convocatoria del programa')
        ->assertSee(__('Otras convocatorias del programa'));
});

it('does not show other calls section when no other calls available', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertDontSee(__('Otras convocatorias del programa'));
});

it('returns 404 for non-existent call', function () {
    $this->get(route('convocatorias.show', 'non-existent-slug'))
        ->assertNotFound();
});

it('returns 404 for draft calls', function () {
    $draftCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'borrador',
        'published_at' => null,
    ]);

    $this->get(route('convocatorias.show', $draftCall->slug))
        ->assertNotFound();
});

it('returns 404 for calls without published_at', function () {
    $unpublishedCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => null,
    ]);

    $this->get(route('convocatorias.show', $unpublishedCall->slug))
        ->assertNotFound();
});

it('returns 404 for calls with invalid status', function () {
    $invalidCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'en_baremacion',
        'published_at' => now(),
    ]);

    $this->get(route('convocatorias.show', $invalidCall->slug))
        ->assertNotFound();
});

it('shows breadcrumbs with correct links', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee(__('Convocatorias'))
        ->assertSee($this->call->title);
});

it('has correct seo title', function () {
    $this->get(route('convocatorias.show', $this->call->slug))
        ->assertOk()
        ->assertSee($this->call->title);
});

it('displays call destinations', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('Alemania')
        ->assertSee('Francia')
        ->assertSee('Italia')
        ->assertSee(__('Países de destino'));
});

it('displays call dates when available', function () {
    $callWithDates = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'estimated_start_date' => now()->addMonths(2),
        'estimated_end_date' => now()->addMonths(8),
    ]);

    Livewire::test(Show::class, ['call' => $callWithDates])
        ->assertSee(__('Fechas estimadas'));
});

it('displays call type and modality correctly', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee(__('Alumnado'))
        ->assertSee(__('Corta duración'));
});

it('displays number of places', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSee('15')
        ->assertSee(__('Plazas'));
});

it('shows correct call config for open status', function () {
    Livewire::test(Show::class, ['call' => $this->call])
        ->assertSet('call.status', 'abierta');
});

it('shows correct call config for closed status', function () {
    $closedCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(10),
        'closed_at' => now()->subDays(5),
    ]);

    Livewire::test(Show::class, ['call' => $closedCall])
        ->assertSet('call.status', 'cerrada');
});
