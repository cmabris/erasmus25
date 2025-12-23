<?php

use App\Livewire\Public\Events\Index;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create programs
    $this->program1 = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->program2 = Program::factory()->create([
        'code' => 'KA131-HED',
        'name' => 'Movilidad Educación Superior',
        'is_active' => true,
    ]);

    // Create author
    $this->author = User::factory()->create();
});

it('renders the events index page', function () {
    $this->get(route('eventos.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('displays public events', function () {
    $event1 = ErasmusEvent::factory()->create([
        'title' => 'Evento Público 1',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'title' => 'Evento Público 2',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Index::class)
        ->assertSee('Evento Público 1')
        ->assertSee('Evento Público 2');
});

it('does not display private events', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Evento Privado',
        'is_public' => false,
        'start_date' => now()->addDays(5),
    ]);

    Livewire::test(Index::class)
        ->assertDontSee('Evento Privado');
});

it('filters events by search query', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Reunión Informativa',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'title' => 'Taller de Movilidad',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Index::class)
        ->set('search', 'Reunión')
        ->assertSee('Reunión Informativa')
        ->assertDontSee('Taller de Movilidad');
});

it('filters events by program', function () {
    $event1 = ErasmusEvent::factory()->create([
        'program_id' => $this->program1->id,
        'title' => 'Evento Programa 1',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'program_id' => $this->program2->id,
        'title' => 'Evento Programa 2',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Index::class)
        ->set('program', $this->program1->id)
        ->assertSee('Evento Programa 1')
        ->assertDontSee('Evento Programa 2');
});

it('filters events by event type', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Apertura de Convocatoria',
        'event_type' => 'apertura',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'title' => 'Reunión Informativa',
        'event_type' => 'reunion_informativa',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Index::class)
        ->set('eventType', 'apertura')
        ->assertSee('Apertura de Convocatoria')
        ->assertDontSee('Reunión Informativa');
});

it('filters events by date range', function () {
    $dateFrom = now()->addDays(5)->format('Y-m-d');
    $dateTo = now()->addDays(15)->format('Y-m-d');

    ErasmusEvent::factory()->create([
        'title' => 'Evento en Rango',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    ErasmusEvent::factory()->create([
        'title' => 'Evento Fuera de Rango',
        'is_public' => true,
        'start_date' => now()->addDays(20),
    ]);

    Livewire::test(Index::class)
        ->set('dateFrom', $dateFrom)
        ->set('dateTo', $dateTo)
        ->assertSee('Evento en Rango')
        ->assertDontSee('Evento Fuera de Rango');
});

it('shows only upcoming events by default', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Evento Futuro',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'title' => 'Evento Pasado',
        'is_public' => true,
        'start_date' => now()->subDays(5),
    ]);

    Livewire::test(Index::class)
        ->assertSee('Evento Futuro')
        ->assertDontSee('Evento Pasado');
});

it('shows past events when toggle is enabled', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Evento Pasado',
        'is_public' => true,
        'start_date' => now()->subDays(5),
    ]);

    Livewire::test(Index::class)
        ->set('showPast', true)
        ->assertSee('Evento Pasado');
});

it('resets filters correctly', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('program', $this->program1->id)
        ->set('eventType', 'apertura')
        ->set('dateFrom', '2024-01-01')
        ->set('dateTo', '2024-12-31')
        ->set('showPast', true)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('program', '')
        ->assertSet('eventType', '')
        ->assertSet('dateFrom', '')
        ->assertSet('dateTo', '')
        ->assertSet('showPast', false);
});

it('displays statistics correctly', function () {
    ErasmusEvent::factory()->count(5)->create([
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    Livewire::test(Index::class)
        ->assertSee('6') // Total events
        ->assertSee('1') // This month
        ->assertSee('6'); // Upcoming
});

it('paginates events correctly', function () {
    ErasmusEvent::factory()->count(15)->create([
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $component = Livewire::test(Index::class);
    $events = $component->get('events');
    
    $this->assertEquals(12, $events->count()); // First page shows 12 events
    $this->assertEquals(15, $events->total()); // Total events
});

it('displays empty state when no events match filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'NoExiste')
        ->assertSee(__('No se encontraron eventos'));
});

it('updates page when search changes', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});

it('updates page when filters change', function () {
    Livewire::test(Index::class)
        ->set('program', $this->program1->id)
        ->assertSet('program', (string) $this->program1->id);
});

