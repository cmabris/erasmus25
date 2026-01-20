<?php

use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

it('includes open graph meta tags on home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('og:title', escape: false)
        ->assertSee('og:description', escape: false)
        ->assertSee('og:url', escape: false)
        ->assertSee('og:site_name', escape: false)
        ->assertSee('og:locale', escape: false)
        ->assertSee('og:type', escape: false);
});

it('includes twitter card meta tags on home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('twitter:card', escape: false)
        ->assertSee('twitter:title', escape: false)
        ->assertSee('twitter:description', escape: false);
});

it('includes canonical url on home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rel="canonical"', escape: false)
        ->assertSee(route('home'), escape: false);
});

it('includes json-ld structured data on home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('application/ld+json', escape: false)
        ->assertSee('@context', escape: false)
        ->assertSee('EducationalOrganization', escape: false);
});

it('includes article meta tags on news detail page', function () {
    $author = User::factory()->create();
    $news = NewsPost::factory()->published()->create([
        'author_id' => $author->id,
    ]);

    $this->get(route('noticias.show', $news))
        ->assertOk()
        ->assertSee('og:type" content="article"', escape: false)
        ->assertSee('article:published_time', escape: false)
        ->assertSee('article:modified_time', escape: false)
        ->assertSee('article:author', escape: false);
});

it('includes og image on news detail page with featured image', function () {
    $news = NewsPost::factory()->published()->create();

    // Add a featured image
    $news->addMedia(base_path('tests/fixtures/test-image.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('featured');

    $this->get(route('noticias.show', $news))
        ->assertOk()
        ->assertSee('og:image', escape: false)
        ->assertSee('twitter:image', escape: false);
});

it('includes program og image when available', function () {
    $program = Program::factory()->create(['is_active' => true]);

    // Add an image
    $program->addMedia(base_path('tests/fixtures/test-image.jpg'))
        ->preservingOriginal()
        ->toMediaCollection('image');

    $this->get(route('programas.show', $program))
        ->assertOk()
        ->assertSee('og:image', escape: false);
});

it('includes meta description on all public pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $news = NewsPost::factory()->published()->create();

    // Home
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('name="description"', escape: false);

    // Programs index
    $this->get(route('programas.index'))
        ->assertOk()
        ->assertSee('name="description"', escape: false);

    // Program show
    $this->get(route('programas.show', $program))
        ->assertOk()
        ->assertSee('name="description"', escape: false);

    // News index
    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('name="description"', escape: false);

    // News show
    $this->get(route('noticias.show', $news))
        ->assertOk()
        ->assertSee('name="description"', escape: false);
});

it('includes canonical url on paginated pages', function () {
    // Create some news posts for pagination
    NewsPost::factory()->published()->count(15)->create();

    $response = $this->get(route('noticias.index'));

    $response->assertOk()
        ->assertSee('rel="canonical"', escape: false);
});

it('truncates description to 160 characters', function () {
    $longDescription = str_repeat('This is a very long description that should be truncated. ', 10);

    $news = NewsPost::factory()->published()->create([
        'excerpt' => $longDescription,
    ]);

    $response = $this->get(route('noticias.show', $news));

    $response->assertOk();

    // The description should be truncated
    $content = $response->getContent();
    preg_match('/name="description" content="([^"]*)"/', $content, $matches);

    if (isset($matches[1])) {
        expect(strlen($matches[1]))->toBeLessThanOrEqual(163); // 160 + "..."
    }
});

it('sets og type to website for index pages', function () {
    $this->get(route('programas.index'))
        ->assertOk()
        ->assertSee('og:type" content="website"', escape: false);

    $this->get(route('noticias.index'))
        ->assertOk()
        ->assertSee('og:type" content="website"', escape: false);
});

it('sets og type to article for news posts', function () {
    $news = NewsPost::factory()->published()->create();

    $this->get(route('noticias.show', $news))
        ->assertOk()
        ->assertSee('og:type" content="article"', escape: false);
});

it('includes article tags for news with tags', function () {
    $news = NewsPost::factory()->published()->hasTags(3)->create();

    $response = $this->get(route('noticias.show', $news));

    $response->assertOk();

    foreach ($news->tags as $tag) {
        $response->assertSee('article:tag" content="'.$tag->name.'"', escape: false);
    }
});
