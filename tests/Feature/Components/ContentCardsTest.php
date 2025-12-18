<?php

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Support\Facades\Blade;

/*
|--------------------------------------------------------------------------
| Program Card Tests
|--------------------------------------------------------------------------
*/

describe('Program Card', function () {
    it('renders with basic props', function () {
        $html = Blade::render('<x-content.program-card name="Educación Escolar" code="KA1xx" />');

        expect($html)
            ->toContain('Educación Escolar')
            ->toContain('KA1xx');
    });

    it('renders with program model', function () {
        $program = Program::factory()->create([
            'name' => 'Test Program',
            'code' => 'TEST01',
            'description' => 'Test description',
        ]);

        $html = Blade::render('<x-content.program-card :program="$program" />', ['program' => $program]);

        expect($html)
            ->toContain('Test Program')
            ->toContain('TEST01')
            ->toContain('Test description');
    });

    it('renders as link when href provided', function () {
        $html = Blade::render('<x-content.program-card name="Test" code="TEST" href="/programs/test" />');

        expect($html)
            ->toContain('href="/programs/test"')
            ->toContain('wire:navigate');
    });

    it('renders description when provided', function () {
        $html = Blade::render('<x-content.program-card name="Test" code="TEST" description="This is a test description" />');

        expect($html)->toContain('This is a test description');
    });

    it('renders with different program codes', function () {
        $htmlKA1 = Blade::render('<x-content.program-card name="Escolar" code="KA1xx" />');
        $htmlVET = Blade::render('<x-content.program-card name="FP" code="KA121-VET" />');
        $htmlHED = Blade::render('<x-content.program-card name="Superior" code="KA131-HED" />');

        expect($htmlKA1)->toContain('KA1xx');
        expect($htmlVET)->toContain('KA121-VET');
        expect($htmlHED)->toContain('KA131-HED');
    });
});

/*
|--------------------------------------------------------------------------
| Call Card Tests
|--------------------------------------------------------------------------
*/

describe('Call Card', function () {
    it('renders with basic props', function () {
        $html = Blade::render('<x-content.call-card title="Convocatoria Test" status="abierta" />');

        expect($html)
            ->toContain('Convocatoria Test')
            ->toContain('Abierta');
    });

    it('renders with call model', function () {
        $call = Call::factory()->create([
            'title' => 'Model Call',
            'status' => 'abierta',
            'type' => 'alumnado',
            'number_of_places' => 20,
        ]);

        $html = Blade::render('<x-content.call-card :call="$call" />', ['call' => $call]);

        expect($html)
            ->toContain('Model Call')
            ->toContain('Abierta');
    });

    it('shows correct status for open calls', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="abierta" />');

        expect($html)->toContain('Abierta');
    });

    it('shows correct status for closed calls', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="cerrada" />');

        expect($html)->toContain('Cerrada');
    });

    it('shows correct status for resolved calls', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="resuelta" />');

        expect($html)->toContain('Resuelta');
    });

    it('shows correct status for draft calls', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="borrador" />');

        expect($html)->toContain('Borrador');
    });

    it('shows correct status for archived calls', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="archivada" />');

        expect($html)->toContain('Archivada');
    });

    it('renders as link when href provided', function () {
        $html = Blade::render('<x-content.call-card title="Test" status="abierta" href="/calls/test" />');

        expect($html)
            ->toContain('href="/calls/test"')
            ->toContain('wire:navigate');
    });
});

/*
|--------------------------------------------------------------------------
| News Card Tests
|--------------------------------------------------------------------------
*/

describe('News Card', function () {
    it('renders with basic props', function () {
        $html = Blade::render('<x-content.news-card title="News Title" excerpt="News excerpt text" />');

        expect($html)
            ->toContain('News Title')
            ->toContain('News excerpt text');
    });

    it('renders with news model', function () {
        $news = NewsPost::factory()->published()->create([
            'title' => 'Model News',
            'excerpt' => 'Model excerpt',
        ]);

        $html = Blade::render('<x-content.news-card :news="$news" />', ['news' => $news]);

        expect($html)
            ->toContain('Model News')
            ->toContain('Model excerpt');
    });

    it('shows program badge when provided', function () {
        $program = Program::factory()->create(['name' => 'Test Program']);
        $html = Blade::render('<x-content.news-card title="Test" :program="$program" />', ['program' => $program]);

        expect($html)->toContain('Test Program');
    });

    it('shows image when imageUrl provided', function () {
        $html = Blade::render('<x-content.news-card title="Test" imageUrl="https://example.com/image.jpg" />');

        expect($html)->toContain('src="https://example.com/image.jpg"');
    });

    it('renders as link when href provided', function () {
        $html = Blade::render('<x-content.news-card title="Test" href="/news/test" />');

        expect($html)
            ->toContain('href="/news/test"')
            ->toContain('wire:navigate');
    });

    it('renders excerpt when provided', function () {
        $html = Blade::render('<x-content.news-card title="Test" excerpt="This is a news excerpt" />');

        expect($html)->toContain('This is a news excerpt');
    });
});

/*
|--------------------------------------------------------------------------
| Event Card Tests
|--------------------------------------------------------------------------
*/

describe('Event Card', function () {
    it('renders with basic props', function () {
        $html = Blade::render('<x-content.event-card title="Event Title" :startDate="now()->addDays(5)" />');

        expect($html)->toContain('Event Title');
    });

    it('renders with event model', function () {
        $event = ErasmusEvent::factory()->create([
            'title' => 'Model Event',
            'event_type' => 'apertura',
            'start_date' => now()->addDays(10),
        ]);

        $html = Blade::render('<x-content.event-card :event="$event" />', ['event' => $event]);

        expect($html)
            ->toContain('Model Event')
            ->toContain('Apertura');
    });

    it('shows correct type for apertura', function () {
        $html = Blade::render('<x-content.event-card title="Test" eventType="apertura" :startDate="now()" />');

        expect($html)->toContain('Apertura');
    });

    it('shows correct type for cierre', function () {
        $html = Blade::render('<x-content.event-card title="Test" eventType="cierre" :startDate="now()" />');

        expect($html)->toContain('Cierre');
    });

    it('shows correct type for entrevista', function () {
        $html = Blade::render('<x-content.event-card title="Test" eventType="entrevista" :startDate="now()" />');

        expect($html)->toContain('Entrevistas');
    });

    it('shows today badge for today events', function () {
        $html = Blade::render('<x-content.event-card title="Test" :startDate="now()" />');

        expect($html)->toContain('Hoy');
    });

    it('shows location when provided', function () {
        $html = Blade::render('<x-content.event-card title="Test" :startDate="now()" location="Room 101" />');

        expect($html)->toContain('Room 101');
    });

    it('shows time when start date has time', function () {
        $html = Blade::render('<x-content.event-card title="Test" :startDate="now()->setTime(10, 30)" />');

        expect($html)->toContain('10:30');
    });

    it('shows description when provided', function () {
        $html = Blade::render('<x-content.event-card title="Test" :startDate="now()" description="Event description here" />');

        expect($html)->toContain('Event description here');
    });
});
