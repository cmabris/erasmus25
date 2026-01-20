<?php

use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;

it('returns valid xml response', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=utf-8');
});

it('includes xml declaration', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', escape: false);
});

it('includes urlset with sitemap schema', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', escape: false);
});

it('includes static pages', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('home'), escape: false)
        ->assertSee(route('programas.index'), escape: false)
        ->assertSee(route('convocatorias.index'), escape: false)
        ->assertSee(route('noticias.index'), escape: false)
        ->assertSee(route('documentos.index'), escape: false)
        ->assertSee(route('eventos.index'), escape: false)
        ->assertSee(route('calendario'), escape: false)
        ->assertSee(route('search'), escape: false);
});

it('includes active programs', function () {
    $activeProgram = Program::factory()->create(['is_active' => true]);
    $inactiveProgram = Program::factory()->create(['is_active' => false]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('programas.show', $activeProgram), escape: false)
        ->assertDontSee(route('programas.show', $inactiveProgram), escape: false);
});

it('includes published calls', function () {
    $openCall = Call::factory()->create([
        'status' => 'abierta',
        'published_at' => now(),
    ]);
    $closedCall = Call::factory()->create([
        'status' => 'cerrada',
        'published_at' => now(),
    ]);
    $draftCall = Call::factory()->create([
        'status' => 'borrador',
        'published_at' => null,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('convocatorias.show', $openCall), escape: false)
        ->assertSee(route('convocatorias.show', $closedCall), escape: false)
        ->assertDontSee(route('convocatorias.show', $draftCall), escape: false);
});

it('includes published news', function () {
    $publishedNews = NewsPost::factory()->published()->create();
    $draftNews = NewsPost::factory()->create([
        'status' => 'borrador',
        'published_at' => null,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('noticias.show', $publishedNews), escape: false)
        ->assertDontSee(route('noticias.show', $draftNews), escape: false);
});

it('includes active documents', function () {
    $activeDocument = Document::factory()->create(['is_active' => true]);
    $inactiveDocument = Document::factory()->create(['is_active' => false]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('documentos.show', $activeDocument), escape: false)
        ->assertDontSee(route('documentos.show', $inactiveDocument), escape: false);
});

it('includes recent public events', function () {
    $recentEvent = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(5),
    ]);
    $privateEvent = ErasmusEvent::factory()->create([
        'is_public' => false,
        'start_date' => now()->addDays(5),
    ]);
    $oldEvent = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->subMonths(6),
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee(route('eventos.show', $recentEvent), escape: false)
        ->assertDontSee(route('eventos.show', $privateEvent), escape: false)
        ->assertDontSee(route('eventos.show', $oldEvent), escape: false);
});

it('includes lastmod dates', function () {
    $program = Program::factory()->create(['is_active' => true]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee('<lastmod>', escape: false)
        ->assertSee($program->updated_at->toW3cString(), escape: false);
});

it('includes priority values', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<priority>1.0</priority>', escape: false)
        ->assertSee('<priority>0.9</priority>', escape: false)
        ->assertSee('<priority>0.8</priority>', escape: false);
});

it('includes changefreq values', function () {
    // Create content to ensure monthly changefreq appears
    Program::factory()->create(['is_active' => true]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('<changefreq>daily</changefreq>', escape: false)
        ->assertSee('<changefreq>weekly</changefreq>', escape: false)
        ->assertSee('<changefreq>monthly</changefreq>', escape: false);
});

it('does not include admin routes', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee('/admin', escape: false);
});

it('does not include auth routes', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee('/login', escape: false)
        ->assertDontSee('/register', escape: false)
        ->assertDontSee('/password', escape: false);
});

it('gives higher priority to open calls than closed', function () {
    $openCall = Call::factory()->create([
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $response = $this->get('/sitemap.xml');
    $content = $response->getContent();

    // Open call should have priority 0.9
    $openCallUrl = route('convocatorias.show', $openCall);
    $this->assertStringContainsString($openCallUrl, $content);

    // Check that open calls have higher priority marker
    preg_match_all('/<url>.*?<loc>'.preg_quote($openCallUrl, '/').'<\/loc>.*?<priority>([\d.]+)<\/priority>.*?<\/url>/s', $content, $matches);
    if (isset($matches[1][0])) {
        expect((float) $matches[1][0])->toBe(0.9);
    }
});
