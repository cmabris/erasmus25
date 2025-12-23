<?php

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = DocumentCategory::factory()->create();
    $this->program = Program::factory()->create(['is_active' => true]);
    $this->creator = User::factory()->create();
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
        'created_by' => $this->creator->id,
    ]);

    $response = $this->get(route('documentos.show', $document->slug));

    $response->assertOk();
});

it('returns 404 for inactive document', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'is_active' => false,
        'slug' => 'inactive-document',
        'created_by' => $this->creator->id,
    ]);

    $response = $this->get(route('documentos.show', $document->slug));

    $response->assertNotFound();
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
        'slug' => 'unique-slug-123',
        'created_by' => $this->creator->id,
    ]);

    $response = $this->get(route('documentos.show', 'unique-slug-123'));

    $response->assertOk();
    $response->assertSee($document->title);
});

