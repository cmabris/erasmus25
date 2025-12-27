<?php

use App\Livewire\Public\Home;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
});

describe('Home Page', function () {
    it('can be rendered', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
    });

    it('displays the livewire component', function () {
        $response = $this->get('/');

        $response->assertSeeLivewire(Home::class);
    });

    it('uses the public layout', function () {
        $response = $this->get('/');

        $response
            ->assertSee('Erasmus+')
            ->assertSee('main-content');
    });
});

describe('Home Component - Programs', function () {
    it('displays active programs', function () {
        $program = Program::factory()->create([
            'name' => 'Test Active Program',
            'is_active' => true,
        ]);

        Livewire::test(Home::class)
            ->assertSee('Test Active Program')
            ->assertSee(__('Programas Erasmus+'));
    });

    it('does not display inactive programs', function () {
        $activeProgram = Program::factory()->create([
            'name' => 'Active Program',
            'is_active' => true,
        ]);

        $inactiveProgram = Program::factory()->create([
            'name' => 'Inactive Program',
            'is_active' => false,
        ]);

        Livewire::test(Home::class)
            ->assertSee('Active Program')
            ->assertDontSee('Inactive Program');
    });

    it('shows empty state when no programs exist', function () {
        Livewire::test(Home::class)
            ->assertSee(__('No hay programas disponibles'));
    });

    it('orders programs by order field', function () {
        $program2 = Program::factory()->create([
            'name' => 'Program B',
            'slug' => 'program-b-'.uniqid(),
            'order' => 2,
            'is_active' => true,
        ]);
        $program1 = Program::factory()->create([
            'name' => 'Program A',
            'slug' => 'program-a-'.uniqid(),
            'order' => 1,
            'is_active' => true,
        ]);

        $component = Livewire::test(Home::class);

        expect($component->get('programs')->first()->name)->toBe('Program A');
    });
});

describe('Home Component - Calls', function () {
    it('displays open calls', function () {
        $call = Call::factory()->published()->create([
            'title' => 'Open Test Call',
            'status' => 'abierta',
        ]);

        Livewire::test(Home::class)
            ->assertSee('Open Test Call')
            ->assertSee(__('Convocatorias Abiertas'));
    });

    it('does not display closed calls', function () {
        $program = Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();

        $openCall = Call::factory()->published()->create([
            'title' => 'Open Call',
            'slug' => 'open-call-'.uniqid(),
            'status' => 'abierta',
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $closedCall = Call::factory()->create([
            'title' => 'Closed Call',
            'slug' => 'closed-call-'.uniqid(),
            'status' => 'cerrada',
            'published_at' => now(),
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Home::class)
            ->assertSee('Open Call')
            ->assertDontSee('Closed Call');
    });

    it('does not display draft calls', function () {
        $draftCall = Call::factory()->draft()->create([
            'title' => 'Draft Call',
        ]);

        Livewire::test(Home::class)
            ->assertDontSee('Draft Call');
    });

    it('shows empty state when no open calls exist', function () {
        Livewire::test(Home::class)
            ->assertSee(__('No hay convocatorias abiertas'));
    });
});

describe('Home Component - News', function () {
    it('displays published news', function () {
        $news = NewsPost::factory()->published()->create([
            'title' => 'Published News Title',
        ]);

        Livewire::test(Home::class)
            ->assertSee('Published News Title')
            ->assertSee(__('Últimas Noticias'));
    });

    it('does not display draft news', function () {
        $publishedNews = NewsPost::factory()->published()->create([
            'title' => 'Published News',
        ]);

        $draftNews = NewsPost::factory()->draft()->create([
            'title' => 'Draft News',
        ]);

        Livewire::test(Home::class)
            ->assertSee('Published News')
            ->assertDontSee('Draft News');
    });

    it('shows empty state when no news exist', function () {
        Livewire::test(Home::class)
            ->assertSee(__('No hay noticias'));
    });

    it('limits news to 3 items', function () {
        // Create a single program and academic year to share
        $program = Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            NewsPost::factory()->published()->create([
                'slug' => 'news-'.$i.'-'.uniqid(),
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
            ]);
        }

        $component = Livewire::test(Home::class);

        expect($component->get('news'))->toHaveCount(3);
    });
});

describe('Home Component - Events', function () {
    it('displays upcoming public events', function () {
        $event = ErasmusEvent::factory()->create([
            'title' => 'Upcoming Event',
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        Livewire::test(Home::class)
            ->assertSee('Upcoming Event')
            ->assertSee(__('Próximos Eventos'));
    });

    it('does not display past events', function () {
        $upcomingEvent = ErasmusEvent::factory()->create([
            'title' => 'Upcoming Event',
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        $pastEvent = ErasmusEvent::factory()->create([
            'title' => 'Past Event',
            'is_public' => true,
            'start_date' => now()->subDays(5),
        ]);

        Livewire::test(Home::class)
            ->assertSee('Upcoming Event')
            ->assertDontSee('Past Event');
    });

    it('does not display private events', function () {
        $publicEvent = ErasmusEvent::factory()->create([
            'title' => 'Public Event',
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        $privateEvent = ErasmusEvent::factory()->private()->create([
            'title' => 'Private Event',
            'start_date' => now()->addDays(5),
        ]);

        Livewire::test(Home::class)
            ->assertSee('Public Event')
            ->assertDontSee('Private Event');
    });

    it('shows empty state when no upcoming events exist', function () {
        Livewire::test(Home::class)
            ->assertSee(__('No hay eventos próximos'));
    });

    it('orders events by start date ascending', function () {
        $laterEvent = ErasmusEvent::factory()->create([
            'title' => 'Later Event',
            'is_public' => true,
            'start_date' => now()->addDays(10),
        ]);

        $soonerEvent = ErasmusEvent::factory()->create([
            'title' => 'Sooner Event',
            'is_public' => true,
            'start_date' => now()->addDays(2),
        ]);

        $component = Livewire::test(Home::class);

        expect($component->get('events')->first()->title)->toBe('Sooner Event');
    });
});

describe('Home Component - Hero Section', function () {
    it('displays hero section with title', function () {
        Livewire::test(Home::class)
            ->assertSee(__('Abre las puertas al mundo'));
    });

    it('displays statistics', function () {
        Program::factory()->create(['is_active' => true]);
        Call::factory()->published()->create(['status' => 'abierta']);

        Livewire::test(Home::class)
            ->assertSee(__('Programas activos'))
            ->assertSee(__('Convocatorias abiertas'))
            ->assertSee(__('Países de destino'))
            ->assertSee(__('Eventos próximos'));
    });
});

describe('Home Component - SEO', function () {
    it('has proper page title', function () {
        $response = $this->get('/');

        $response->assertSee('Erasmus+', false);
    });
});
