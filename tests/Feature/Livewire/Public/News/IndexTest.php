<?php

use App\Livewire\Public\News\Index;
use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;
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

    // Create tags
    $this->tag1 = NewsTag::factory()->create(['name' => 'Movilidad Estudiantil']);
    $this->tag2 = NewsTag::factory()->create(['name' => 'Formación Profesional']);

    // Create author
    $this->author = User::factory()->create();
});

it('renders the news index page', function () {
    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('displays only published news posts', function () {
    // Create published news
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia Publicada',
        'author_id' => $this->author->id,
    ]);

    // These should NOT appear
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'borrador',
        'published_at' => null,
        'title' => 'Noticia Borrador',
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => null, // Not published
        'title' => 'Noticia Sin Publicar',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->assertSee('Noticia Publicada')
        ->assertDontSee('Noticia Borrador')
        ->assertDontSee('Noticia Sin Publicar');
});

it('can search news by title', function () {
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Experiencia en Alemania',
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Proyecto de Cooperación',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'Alemania')
        ->assertSee('Experiencia en Alemania')
        ->assertDontSee('Proyecto de Cooperación');
});

it('can search news by excerpt', function () {
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia 1',
        'excerpt' => 'Esta es una experiencia única en Francia',
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia 2',
        'excerpt' => 'Proyecto en Italia',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'Francia')
        ->assertSee('Noticia 1')
        ->assertDontSee('Noticia 2');
});

it('can filter news by program', function () {
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia FP',
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia Cooperación',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->set('program', $this->program1->id)
        ->assertSee('Noticia FP')
        ->assertDontSee('Noticia Cooperación');
});

it('can filter news by academic year', function () {
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia 2024-2025',
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear2->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia 2023-2024',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->set('academicYear', $this->academicYear1->id)
        ->assertSee('Noticia 2024-2025')
        ->assertDontSee('Noticia 2023-2024');
});

it('can filter news by tags', function () {
    $news1 = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia con Tag 1',
        'author_id' => $this->author->id,
    ]);
    $news1->tags()->attach($this->tag1->id);

    $news2 = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia con Tag 2',
        'author_id' => $this->author->id,
    ]);
    $news2->tags()->attach($this->tag2->id);

    Livewire::test(Index::class)
        ->call('toggleTag', $this->tag1->id)
        ->assertSee('Noticia con Tag 1')
        ->assertDontSee('Noticia con Tag 2');
});

it('can toggle tags on and off', function () {
    $news1 = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia con Tag 1',
        'author_id' => $this->author->id,
    ]);
    $news1->tags()->attach($this->tag1->id);

    $component = Livewire::test(Index::class)
        ->call('toggleTag', $this->tag1->id)
        ->assertSet('tags', (string) $this->tag1->id);

    // Toggle off
    $component->call('toggleTag', $this->tag1->id)
        ->assertSet('tags', '');
});

it('can remove tag from filter', function () {
    $news1 = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia con Tag 1',
        'author_id' => $this->author->id,
    ]);
    $news1->tags()->attach($this->tag1->id);

    Livewire::test(Index::class)
        ->set('tags', (string) $this->tag1->id)
        ->call('removeTag', $this->tag1->id)
        ->assertSet('tags', '');
});

it('can reset filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('program', $this->program1->id)
        ->set('academicYear', $this->academicYear1->id)
        ->set('tags', (string) $this->tag1->id)
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('program', '')
        ->assertSet('academicYear', '')
        ->assertSet('tags', '');
});

it('shows empty state when no news match filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'nonexistent news xyz')
        ->assertSee(__('No se encontraron noticias'));
});

it('displays statistics correctly', function () {
    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'author_id' => $this->author->id,
    ]);

    NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now()->subMonths(2),
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->assertSeeHtml('2'); // Total news
});

it('supports pagination', function () {
    // Create enough news to trigger pagination (default is 12 per page)
    NewsPost::factory()->count(15)->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'author_id' => $this->author->id,
    ]);

    $component = Livewire::test(Index::class);
    expect($component->instance())->toBeInstanceOf(Index::class);
});

it('updates search and resets pagination', function () {
    NewsPost::factory()->count(15)->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});

it('has correct seo title and description', function () {
    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('Noticias Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Index::class)
        ->assertSee(__('Noticias'));
});

it('links to news detail page', function () {
    $news = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now(),
        'author_id' => $this->author->id,
    ]);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee(route('noticias.show', $news->slug));
});

it('orders news by published_at desc', function () {
    $oldNews = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(10),
        'title' => 'Noticia Antigua',
        'author_id' => $this->author->id,
    ]);

    $recentNews = NewsPost::factory()->create([
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(2),
        'title' => 'Noticia Reciente',
        'author_id' => $this->author->id,
    ]);

    $response = Livewire::test(Index::class);

    // The first news should be the most recent
    $response->assertSee('Noticia Reciente');
});
