<?php

use App\Livewire\Public\Events\Calendar;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
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

    $this->author = User::factory()->create();
});

it('renders the calendar page', function () {
    $this->get(route('calendario'))
        ->assertOk()
        ->assertSeeLivewire(Calendar::class);
});

it('displays current month by default', function () {
    Livewire::test(Calendar::class)
        ->assertSee(now()->translatedFormat('F Y'));
});

it('navigates to previous month', function () {
    $component = Livewire::test(Calendar::class);
    $component->call('previous');

    $expectedDate = now()->subMonth();
    $this->assertEquals($expectedDate->format('Y-m'), Carbon::parse($component->get('currentDate'))->format('Y-m'));
});

it('navigates to next month', function () {
    $component = Livewire::test(Calendar::class);
    $component->call('next');

    $expectedDate = now()->addMonth();
    $this->assertEquals($expectedDate->format('Y-m'), Carbon::parse($component->get('currentDate'))->format('Y-m'));
});

it('goes to today when clicking today button', function () {
    Livewire::test(Calendar::class)
        ->set('currentDate', now()->addMonths(2)->format('Y-m-d'))
        ->call('goToToday')
        ->assertSet('currentDate', now()->format('Y-m-d'));
});

it('changes view mode to week', function () {
    Livewire::test(Calendar::class)
        ->call('changeView', 'week')
        ->assertSet('viewMode', 'week');
});

it('changes view mode to day', function () {
    Livewire::test(Calendar::class)
        ->call('changeView', 'day')
        ->assertSet('viewMode', 'day');
});

it('changes view mode to month', function () {
    Livewire::test(Calendar::class)
        ->set('viewMode', 'week')
        ->call('changeView', 'month')
        ->assertSet('viewMode', 'month');
});

it('filters events by program', function () {
    $event1 = ErasmusEvent::factory()->create([
        'program_id' => $this->program1->id,
        'title' => 'Evento Programa 1',
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'program_id' => $this->program2->id,
        'title' => 'Evento Programa 2',
        'is_public' => true,
        'start_date' => now()->setDay(20),
    ]);

    Livewire::test(Calendar::class)
        ->set('selectedProgram', $this->program1->id)
        ->assertSee('Evento Programa 1')
        ->assertDontSee('Evento Programa 2');
});

it('filters events by event type', function () {
    $event1 = ErasmusEvent::factory()->create([
        'title' => 'Apertura',
        'event_type' => 'apertura',
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'title' => 'Cierre',
        'event_type' => 'cierre',
        'is_public' => true,
        'start_date' => now()->setDay(20),
    ]);

    $component = Livewire::test(Calendar::class)
        ->set('selectedEventType', 'apertura');

    // Verify that the filtered events collection contains event1 but not event2
    $events = $component->get('calendarEvents');
    $this->assertTrue($events->contains('id', $event1->id));
    $this->assertFalse($events->contains('id', $event2->id));
});

it('resets filters correctly', function () {
    Livewire::test(Calendar::class)
        ->set('selectedProgram', $this->program1->id)
        ->set('selectedEventType', 'apertura')
        ->call('resetFilters')
        ->assertSet('selectedProgram', '')
        ->assertSet('selectedEventType', '');
});

it('displays events in month view', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Evento del Mes',
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    Livewire::test(Calendar::class)
        ->assertSee('Evento del Mes');
});

it('displays events in week view', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Evento de la Semana',
        'is_public' => true,
        'start_date' => now()->startOfWeek()->addDays(2),
    ]);

    Livewire::test(Calendar::class)
        ->set('viewMode', 'week')
        ->set('currentDate', now()->startOfWeek()->format('Y-m-d'))
        ->assertSee('Evento de la Semana');
});

it('displays events in day view', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Evento del Día',
        'is_public' => true,
        'start_date' => now()->setTime(14, 0),
    ]);

    Livewire::test(Calendar::class)
        ->set('viewMode', 'day')
        ->set('currentDate', now()->format('Y-m-d'))
        ->assertSee('Evento del Día');
});

it('displays statistics correctly', function () {
    ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    ErasmusEvent::factory()->count(3)->create([
        'is_public' => true,
        'start_date' => now()->addDays(10),
    ]);

    Livewire::test(Calendar::class)
        ->assertSee('1') // This month
        ->assertSee('4'); // Upcoming
});

it('only shows public events', function () {
    ErasmusEvent::factory()->create([
        'title' => 'Evento Público',
        'is_public' => true,
        'start_date' => now()->setDay(15),
    ]);

    ErasmusEvent::factory()->create([
        'title' => 'Evento Privado',
        'is_public' => false,
        'start_date' => now()->setDay(20),
    ]);

    Livewire::test(Calendar::class)
        ->assertSee('Evento Público')
        ->assertDontSee('Evento Privado');
});

it('displays empty state when no events', function () {
    Livewire::test(Calendar::class)
        ->set('viewMode', 'day')
        ->set('currentDate', now()->addMonths(6)->format('Y-m-d'))
        ->assertSee(__('No hay eventos este día'));
});

it('navigates week view correctly', function () {
    $component = Livewire::test(Calendar::class)
        ->set('viewMode', 'week')
        ->set('currentDate', now()->format('Y-m-d'))
        ->call('next');

    $expectedDate = now()->addWeek();
    $this->assertEquals($expectedDate->format('Y-m-d'), $component->get('currentDate'));
});

it('navigates day view correctly', function () {
    Livewire::test(Calendar::class)
        ->set('viewMode', 'day')
        ->set('currentDate', now()->format('Y-m-d'))
        ->call('next')
        ->assertSet('currentDate', now()->addDay()->format('Y-m-d'));
});

it('goes to specific date', function () {
    $targetDate = now()->addMonths(3)->format('Y-m-d');

    Livewire::test(Calendar::class)
        ->call('goToDate', $targetDate)
        ->assertSet('currentDate', $targetDate);
});

it('navigates to previous week in week view', function () {
    $component = Livewire::test(Calendar::class)
        ->set('viewMode', 'week')
        ->set('currentDate', now()->format('Y-m-d'))
        ->call('previous');

    $expectedDate = now()->subWeek();
    $this->assertEquals($expectedDate->format('Y-m-d'), $component->get('currentDate'));
});

it('navigates to previous day in day view', function () {
    Livewire::test(Calendar::class)
        ->set('viewMode', 'day')
        ->set('currentDate', now()->format('Y-m-d'))
        ->call('previous')
        ->assertSet('currentDate', now()->subDay()->format('Y-m-d'));
});
