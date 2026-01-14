<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    $this->program = Program::factory()->create(['is_active' => true]);
    $this->academicYear = AcademicYear::factory()->create();
    $this->user = User::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Home Route Tests
|--------------------------------------------------------------------------
*/

describe('Home Route', function () {
    it('can access home route', function () {
        $response = $this->get(route('home'));

        $response->assertOk();
    });

    it('can access home route with slash', function () {
        $response = $this->get('/');

        $response->assertOk();
    });
});

/*
|--------------------------------------------------------------------------
| Programs Routes Tests
|--------------------------------------------------------------------------
*/

describe('Programs Routes', function () {
    it('can access programs index route', function () {
        $response = $this->get(route('programas.index'));

        $response->assertOk();
    });

    it('can access program show route with slug', function () {
        $program = Program::factory()->create([
            'is_active' => true,
            'slug' => 'test-program',
        ]);

        $response = $this->get(route('programas.show', $program->slug));

        $response->assertOk();
    });

    it('returns 404 for non-existent program slug', function () {
        $response = $this->get(route('programas.show', 'non-existent-slug'));

        $response->assertNotFound();
    });

    it('uses slug for route model binding', function () {
        $program = Program::factory()->create([
            'is_active' => true,
            'slug' => 'unique-program-slug',
            'name' => 'Test Program',
        ]);

        $response = $this->get(route('programas.show', 'unique-program-slug'));

        $response->assertOk();
        $response->assertSee('Test Program', false);
    });
});

/*
|--------------------------------------------------------------------------
| Calls Routes Tests
|--------------------------------------------------------------------------
*/

describe('Calls Routes', function () {
    it('can access calls index route', function () {
        $response = $this->get(route('convocatorias.index'));

        $response->assertOk();
    });

    it('can access call show route with slug', function () {
        $call = Call::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'slug' => 'test-call',
            'status' => 'abierta',
        ]);

        $response = $this->get(route('convocatorias.show', $call->slug));

        $response->assertOk();
    });

    it('returns 404 for non-existent call slug', function () {
        $response = $this->get(route('convocatorias.show', 'non-existent-slug'));

        $response->assertNotFound();
    });

    it('uses slug for route model binding', function () {
        $call = Call::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'slug' => 'unique-call-slug',
            'title' => 'Test Call',
            'status' => 'abierta',
        ]);

        $response = $this->get(route('convocatorias.show', 'unique-call-slug'));

        $response->assertOk();
        $response->assertSee('Test Call', false);
    });
});

/*
|--------------------------------------------------------------------------
| News Routes Tests
|--------------------------------------------------------------------------
*/

describe('News Routes', function () {
    it('can access news index route', function () {
        $response = $this->get(route('noticias.index'));

        $response->assertOk();
    });

    it('can access news show route with slug', function () {
        $news = NewsPost::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'slug' => 'test-news',
            'author_id' => $this->user->id,
        ]);

        $response = $this->get(route('noticias.show', $news->slug));

        $response->assertOk();
    });

    it('returns 404 for non-existent news slug', function () {
        $response = $this->get(route('noticias.show', 'non-existent-slug'));

        $response->assertNotFound();
    });

    it('uses slug for route model binding', function () {
        $news = NewsPost::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'slug' => 'unique-news-slug',
            'title' => 'Test News',
            'author_id' => $this->user->id,
        ]);

        $response = $this->get(route('noticias.show', 'unique-news-slug'));

        $response->assertOk();
        $response->assertSee('Test News', false);
    });
});

/*
|--------------------------------------------------------------------------
| Documents Routes Tests
|--------------------------------------------------------------------------
*/

describe('Documents Routes', function () {
    beforeEach(function () {
        $this->category = DocumentCategory::factory()->create();
    });

    it('can access documents index route', function () {
        $response = $this->get(route('documentos.index'));

        $response->assertOk();
    });

    it('can access document show route with slug', function () {
        $document = Document::factory()->create([
            'category_id' => $this->category->id,
            'program_id' => $this->program->id,
            'is_active' => true,
            'slug' => 'test-document',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('documentos.show', $document->slug));

        $response->assertOk();
    });

    it('returns 404 for non-existent document slug', function () {
        $response = $this->get(route('documentos.show', 'non-existent-slug'));

        $response->assertNotFound();
    });

    it('uses slug for route model binding', function () {
        $document = Document::factory()->create([
            'category_id' => $this->category->id,
            'program_id' => $this->program->id,
            'is_active' => true,
            'slug' => 'unique-document-slug',
            'title' => 'Test Document',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('documentos.show', 'unique-document-slug'));

        $response->assertOk();
        $response->assertSee('Test Document', false);
    });
});

/*
|--------------------------------------------------------------------------
| Events Routes Tests
|--------------------------------------------------------------------------
*/

describe('Events Routes', function () {
    it('can access calendar route', function () {
        $response = $this->get(route('calendario'));

        $response->assertOk();
    });

    it('can access events index route', function () {
        $response = $this->get(route('eventos.index'));

        $response->assertOk();
    });

    it('can access event show route with id', function () {
        $event = ErasmusEvent::factory()->create([
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        $response = $this->get(route('eventos.show', $event->id));

        $response->assertOk();
    });

    it('returns 404 for non-existent event id', function () {
        $response = $this->get(route('eventos.show', 99999));

        $response->assertNotFound();
    });

    it('returns 404 for private events', function () {
        $event = ErasmusEvent::factory()->create([
            'is_public' => false,
            'start_date' => now()->addDays(5),
        ]);

        $response = $this->get(route('eventos.show', $event->id));

        $response->assertNotFound();
    });

    it('uses id for route model binding', function () {
        $event = ErasmusEvent::factory()->create([
            'title' => 'Test Event',
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        $response = $this->get(route('eventos.show', $event->id));

        $response->assertOk();
        $response->assertSee('Test Event', false);
    });
});

/*
|--------------------------------------------------------------------------
| Newsletter Routes Tests
|--------------------------------------------------------------------------
*/

describe('Newsletter Routes', function () {
    it('can access newsletter subscribe route', function () {
        $response = $this->get(route('newsletter.subscribe'));

        $response->assertOk();
    });

    it('can access newsletter verify route with token', function () {
        // The route accepts a token parameter, but we'll test with a dummy token
        // The actual verification logic is tested in the component tests
        $response = $this->get(route('newsletter.verify', 'test-token'));

        // The route should be accessible (may return 200 or redirect)
        $response->assertStatus(200);
    });

    it('can access newsletter unsubscribe route', function () {
        $response = $this->get(route('newsletter.unsubscribe'));

        $response->assertOk();
    });

    it('can access newsletter unsubscribe route with token', function () {
        // The route accepts a token parameter
        $response = $this->get(route('newsletter.unsubscribe.token', 'test-token'));

        // The route should be accessible (may return 200 or redirect)
        $response->assertStatus(200);
    });
});

/*
|--------------------------------------------------------------------------
| Route Model Binding Edge Cases Tests
|--------------------------------------------------------------------------
*/

describe('Route Model Binding Edge Cases', function () {
    it('handles slugs with special characters correctly', function () {
        // Laravel's Str::slug() converts special characters to hyphens
        $program = Program::factory()->create([
            'is_active' => true,
            'name' => 'Programa con Acentos y Ñ',
            'slug' => 'programa-con-acentos-y-n', // Slug normalizado
        ]);

        $response = $this->get(route('programas.show', $program->slug));

        $response->assertOk();
        $response->assertSee($program->name, false);
    });

    it('handles slugs with numbers correctly', function () {
        $call = Call::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Convocatoria 2024-2025',
            'slug' => 'convocatoria-2024-2025',
            'status' => 'abierta',
        ]);

        $response = $this->get(route('convocatorias.show', $call->slug));

        $response->assertOk();
        $response->assertSee('2024-2025', false);
    });

    it('handles long slugs correctly', function () {
        // Laravel URLs have a practical limit, but slugs should work up to reasonable lengths
        $longTitle = str_repeat('Test ', 20).'Final';
        $slug = \Illuminate\Support\Str::slug($longTitle);

        $news = NewsPost::factory()->published()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => $longTitle,
            'slug' => $slug,
            'author_id' => $this->user->id,
        ]);

        $response = $this->get(route('noticias.show', $news->slug));

        $response->assertOk();
        $response->assertSee($longTitle, false);
    });

    it('handles slugs with hyphens correctly', function () {
        $document = Document::factory()->create([
            'category_id' => DocumentCategory::factory()->create()->id,
            'program_id' => $this->program->id,
            'is_active' => true,
            'title' => 'Documento-Test-2024',
            'slug' => 'documento-test-2024',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('documentos.show', $document->slug));

        $response->assertOk();
        $response->assertSee('Documento-Test-2024', false);
    });

    it('handles event id binding correctly', function () {
        $event = ErasmusEvent::factory()->create([
            'title' => 'Test Event',
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);

        // Verify that ID binding works (not slug)
        // Events use ID because they don't have a slug field
        $response = $this->get(route('eventos.show', $event->id));

        $response->assertOk();
        $response->assertSee('Test Event', false);

        // Verify that a non-existent ID returns 404
        $response = $this->get(route('eventos.show', 99999));

        $response->assertNotFound();
    });

    it('handles slug uniqueness correctly', function () {
        // This test verifies that route model binding works with unique slugs
        // If two programs have the same slug, Laravel will return the first match
        $program1 = Program::factory()->create([
            'is_active' => true,
            'slug' => 'unique-program-slug',
            'name' => 'First Program',
        ]);

        $response = $this->get(route('programas.show', 'unique-program-slug'));

        $response->assertOk();
        $response->assertSee('First Program', false);

        // Note: In production, slugs should be unique due to database constraints
        // This test verifies that route model binding works correctly
    });
});
