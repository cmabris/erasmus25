<?php

use App\Livewire\Public\Events\Show;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->call = Call::factory()->create([
        'program_id' => $this->program->id,
    ]);

    $this->author = User::factory()->create();
});

it('renders the event show page', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Evento de Prueba',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $this->get(route('eventos.show', $event->id))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('displays event information correctly', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Reunión Informativa',
        'description' => 'Descripción del evento',
        'event_type' => 'reunion_informativa',
        'is_public' => true,
        'start_date' => now()->addDays(5)->setTime(17, 0),
        'end_date' => now()->addDays(5)->setTime(19, 0),
        'location' => 'Aula Magna',
        'program_id' => $this->program->id,
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee('Reunión Informativa')
        ->assertSee('Descripción del evento')
        ->assertSee('Aula Magna')
        ->assertSee($this->program->name);
});

it('returns 404 for private events', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => false,
        'start_date' => now()->addDays(5),
    ]);

    $this->get(route('eventos.show', $event->id))
        ->assertNotFound();
});

it('displays related events from same call', function () {
    $event1 = ErasmusEvent::factory()->create([
        'call_id' => $this->call->id,
        'title' => 'Evento Principal',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'call_id' => $this->call->id,
        'title' => 'Evento Relacionado',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Show::class, ['event' => $event1])
        ->assertSee('Eventos relacionados')
        ->assertSee('Evento Relacionado');
});

it('displays related events from same program when no call', function () {
    $event1 = ErasmusEvent::factory()->create([
        'program_id' => $this->program->id,
        'title' => 'Evento Principal',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'program_id' => $this->program->id,
        'title' => 'Evento Relacionado',
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    $component = Livewire::test(Show::class, ['event' => $event1]);
    $relatedEvents = $component->get('relatedEvents');
    
    // Verify that related events collection contains event2
    $this->assertTrue($relatedEvents->contains('id', $event2->id));
    $this->assertFalse($relatedEvents->contains('id', $event1->id)); // Should not include itself
});

it('does not show past events in related events', function () {
    $event1 = ErasmusEvent::factory()->create([
        'call_id' => $this->call->id,
        'title' => 'Evento Principal',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'call_id' => $this->call->id,
        'title' => 'Evento Pasado',
        'is_public' => true,
        'start_date' => now()->subDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event1])
        ->assertDontSee('Evento Pasado');
});

it('displays call information when event is associated with call', function () {
    $event = ErasmusEvent::factory()->create([
        'call_id' => $this->call->id,
        'title' => 'Evento con Convocatoria',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Convocatoria relacionada'))
        ->assertSee($this->call->title);
});

it('displays event type badge correctly', function () {
    $event = ErasmusEvent::factory()->create([
        'event_type' => 'apertura',
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Apertura'));
});

it('displays date and time correctly', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => Carbon::parse('2024-12-25 17:00:00'),
        'end_date' => Carbon::parse('2024-12-25 19:00:00'),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee('25')
        ->assertSee('17:00')
        ->assertSee('19:00');
});

it('shows today badge when event is today', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->setTime(17, 0),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Hoy'));
});

it('shows upcoming badge when event is in the future', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Próximo'));
});

it('shows past badge when event is in the past', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->subDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Pasado'));
});

it('displays duration when event has end date', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(5)->setTime(17, 0),
        'end_date' => now()->addDays(5)->setTime(19, 0),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Duración'))
        ->assertSee('2'); // 2 hours
});

it('displays navigation buttons', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);

    Livewire::test(Show::class, ['event' => $event])
        ->assertSee(__('Volver al listado'))
        ->assertSee(__('Ver calendario'));
});

