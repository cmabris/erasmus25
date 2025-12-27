<?php

use App\Livewire\Public\News\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    $this->program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2024-2025',
        'is_current' => true,
    ]);

    $this->author = User::factory()->create();

    $this->newsPost = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Experiencia Erasmus+ en Alemania',
        'slug' => 'experiencia-erasmus-alemania',
        'excerpt' => 'Una experiencia transformadora en el corazón de Europa',
        'content' => 'Este es el contenido completo de la noticia sobre la experiencia en Alemania.',
        'country' => 'Alemania',
        'city' => 'Berlín',
        'host_entity' => 'Instituto Técnico de Berlín',
        'mobility_type' => 'alumnado',
        'mobility_category' => 'FCT',
        'author_id' => $this->author->id,
    ]);

    // Create tags
    $this->tag1 = NewsTag::factory()->create(['name' => 'Movilidad Estudiantil']);
    $this->tag2 = NewsTag::factory()->create(['name' => 'Europa']);
    $this->newsPost->tags()->attach([$this->tag1->id, $this->tag2->id]);
});

it('renders the news show page', function () {
    $this->get(route('noticias.show', $this->newsPost->slug))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('displays news post information', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee($this->newsPost->title)
        ->assertSee($this->newsPost->program->name)
        ->assertSee($this->newsPost->academicYear->year)
        ->assertSee($this->newsPost->excerpt)
        ->assertSee($this->newsPost->content);
});

it('displays author information', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee($this->author->name);
});

it('displays location information when available', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee('Berlín')
        ->assertSee('Alemania');
});

it('displays host entity when available', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee('Instituto Técnico de Berlín');
});

it('displays mobility type and category when available', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee(__('Alumnado'))
        ->assertSee(__('FCT'));
});

it('displays tags when available', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee('Movilidad Estudiantil')
        ->assertSee('Europa');
});

it('returns 404 for non-published news posts', function () {
    $draftNews = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'borrador',
        'published_at' => null,
        'slug' => 'noticia-borrador',
    ]);

    $this->get(route('noticias.show', $draftNews->slug))
        ->assertNotFound();
});

it('returns 404 for news posts without published_at', function () {
    $unpublishedNews = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => null,
        'slug' => 'noticia-sin-publicar',
    ]);

    $this->get(route('noticias.show', $unpublishedNews->slug))
        ->assertNotFound();
});

it('displays related news posts from same program', function () {
    $relatedNews1 = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
        'title' => 'Noticia Relacionada 1',
        'author_id' => $this->author->id,
    ]);
    $relatedNews1->tags()->attach($this->tag1->id);

    $relatedNews2 = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(10),
        'title' => 'Noticia Relacionada 2',
        'author_id' => $this->author->id,
    ]);
    $relatedNews2->tags()->attach($this->tag1->id);

    // Different program - should not appear
    $otherProgram = Program::factory()->create(['is_active' => true]);
    $unrelatedNews = NewsPost::factory()->create([
        'program_id' => $otherProgram->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(3),
        'title' => 'Noticia No Relacionada',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee('Noticia Relacionada 1')
        ->assertSee('Noticia Relacionada 2')
        ->assertDontSee('Noticia No Relacionada');
});

it('displays related news posts with common tags when no program', function () {
    $newsWithoutProgram = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia Sin Programa',
        'author_id' => $this->author->id,
    ]);
    $newsWithoutProgram->tags()->attach([$this->tag1->id, $this->tag2->id]);

    $relatedNews = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
        'title' => 'Noticia con Tag Común',
        'author_id' => $this->author->id,
    ]);
    $relatedNews->tags()->attach($this->tag1->id);

    $unrelatedNews = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(3),
        'title' => 'Noticia Sin Tags Comunes',
        'author_id' => $this->author->id,
    ]);
    $otherTag = NewsTag::factory()->create(['name' => 'Otro Tag']);
    $unrelatedNews->tags()->attach($otherTag->id);

    Livewire::test(Show::class, ['newsPost' => $newsWithoutProgram])
        ->assertSee('Noticia con Tag Común')
        ->assertDontSee('Noticia Sin Tags Comunes');
});

it('displays related calls from same program when available', function () {
    $relatedCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria Relacionada',
    ]);

    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee('Convocatoria Relacionada')
        ->assertSee(__('Convocatorias relacionadas'));
});

it('does not display related calls section when news has no program', function () {
    $newsWithoutProgram = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Show::class, ['newsPost' => $newsWithoutProgram])
        ->assertDontSee(__('Convocatorias relacionadas'));
});

it('excludes current news post from related news', function () {
    $relatedNews = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
        'title' => 'Noticia Relacionada',
        'author_id' => $this->author->id,
    ]);
    $relatedNews->tags()->attach($this->tag1->id);

    $component = Livewire::test(Show::class, ['newsPost' => $this->newsPost]);

    // Verify related news is shown
    $component->assertSee('Noticia Relacionada');

    // Verify current news is NOT in related news collection
    $relatedNewsCollection = $component->instance()->relatedNews;
    expect($relatedNewsCollection->pluck('id'))->not->toContain($this->newsPost->id);
});

it('has correct seo title and description', function () {
    $this->get(route('noticias.show', $this->newsPost->slug))
        ->assertOk()
        ->assertSee($this->newsPost->title.' - Noticias Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee(__('Noticias'))
        ->assertSee($this->newsPost->title);
});

it('displays published date correctly', function () {
    Livewire::test(Show::class, ['newsPost' => $this->newsPost])
        ->assertSee($this->newsPost->published_at->translatedFormat('d F Y'));
});

it('handles news post without excerpt', function () {
    $newsWithoutExcerpt = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
        'excerpt' => null,
        'content' => 'Contenido sin excerpt',
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Show::class, ['newsPost' => $newsWithoutExcerpt])
        ->assertOk()
        ->assertSee('Contenido sin excerpt');
});

it('handles news post without location', function () {
    $newsWithoutLocation = NewsPost::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
        'country' => null,
        'city' => null,
        'author_id' => $this->author->id,
    ]);

    Livewire::test(Show::class, ['newsPost' => $newsWithoutLocation])
        ->assertOk();
});

it('limits related news to 3 items', function () {
    // Create 5 related news posts
    for ($i = 1; $i <= 5; $i++) {
        $relatedNews = NewsPost::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'publicado',
            'published_at' => now()->subDays($i),
            'title' => "Noticia Relacionada {$i}",
            'author_id' => $this->author->id,
        ]);
        $relatedNews->tags()->attach($this->tag1->id);
    }

    $component = Livewire::test(Show::class, ['newsPost' => $this->newsPost]);
    $relatedNews = $component->instance()->relatedNews;

    expect($relatedNews)->toHaveCount(3);
});
