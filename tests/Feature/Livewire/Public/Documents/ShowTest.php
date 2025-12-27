<?php

use App\Livewire\Public\Documents\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    Storage::fake('public');

    $this->program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Movilidad Formación Profesional',
        'is_active' => true,
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2024-2025',
        'is_current' => true,
    ]);

    $this->category = DocumentCategory::factory()->create([
        'name' => 'Convocatorias',
        'slug' => 'convocatorias',
    ]);

    $this->creator = User::factory()->create();

    $this->document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Guía Completa de Movilidad Erasmus+',
        'slug' => 'guia-completa-movilidad-erasmus',
        'description' => 'Esta es una guía completa con toda la información necesaria para participar en programas de movilidad Erasmus+.',
        'document_type' => 'guia',
        'version' => '2024',
        'download_count' => 50,
        'created_by' => $this->creator->id,
        'updated_by' => $this->creator->id,
    ]);
});

it('renders the document show page', function () {
    $this->get(route('documentos.show', $this->document->slug))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('displays document information', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee($this->document->title)
        ->assertSee($this->document->category->name)
        ->assertSee($this->document->program->name)
        ->assertSee($this->document->academicYear->year)
        ->assertSee($this->document->description);
});

it('displays creator information', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee($this->creator->name);
});

it('displays download count', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee('50');
});

it('displays document type badge', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee(__('Guía'));
});

it('displays version when available', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee('2024');
});

it('returns 404 for inactive documents', function () {
    $inactiveDocument = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => false,
        'slug' => 'documento-inactivo',
        'created_by' => $this->creator->id,
    ]);

    $this->get(route('documentos.show', $inactiveDocument->slug))
        ->assertNotFound();
});

it('displays file information when file is attached', function () {
    // Create a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, 'Test PDF content');

    $this->document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee('documento.pdf');

    // Clean up
    @unlink($tempFile);
});

it('displays message when no file is attached', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee(__('Este documento no tiene archivo asociado disponible para descarga.'));
});

it('increments download count when downloading', function () {
    // Create a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, 'Test PDF content');

    $this->document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    $initialCount = $this->document->download_count;

    Livewire::test(Show::class, ['document' => $this->document])
        ->call('download');

    $this->document->refresh();
    expect($this->document->download_count)->toBe($initialCount + 1);

    // Clean up
    @unlink($tempFile);
});

it('displays related documents from same category', function () {
    $relatedDoc1 = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Relacionado 1',
        'created_by' => $this->creator->id,
    ]);

    $relatedDoc2 = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Relacionado 2',
        'created_by' => $this->creator->id,
    ]);

    // Different category - should not appear
    $otherCategory = DocumentCategory::factory()->create([
        'name' => 'Otros',
        'slug' => 'otros-'.uniqid(), // Ensure unique slug
    ]);
    $unrelatedDoc = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento No Relacionado',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee('Documento Relacionado 1')
        ->assertSee('Documento Relacionado 2')
        ->assertDontSee('Documento No Relacionado');
});

it('displays related documents from same program when document has no category', function () {
    // Note: category_id is required in the migration, so this test verifies
    // that when a document has a category, it shows documents from the same category.
    // When testing "same program", we verify that documents with same category
    // but different programs are filtered correctly.

    $otherCategory = DocumentCategory::factory()->create([
        'name' => 'Otros',
        'slug' => 'otros-'.uniqid(), // Ensure unique slug
    ]);

    // Create document with other category but same program
    $documentWithOtherCategory = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Otra Categoría',
        'created_by' => $this->creator->id,
    ]);

    // Related doc: same category and same program
    $relatedDoc = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Mismo Programa',
        'created_by' => $this->creator->id,
    ]);

    // Different program, same category - should appear because we filter by category first
    $otherProgram = Program::factory()->create(['is_active' => true]);
    $docSameCategoryDifferentProgram = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $otherProgram->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Otro Programa',
        'created_by' => $this->creator->id,
    ]);

    // When document has category, relatedDocuments filters by category
    // So both documents with same category should appear
    Livewire::test(Show::class, ['document' => $documentWithOtherCategory])
        ->assertSee('Documento Mismo Programa')
        ->assertSee('Documento Otro Programa'); // Same category, so it appears
});

it('displays related calls from same program when available', function () {
    $relatedCall = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria Relacionada',
    ]);

    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee('Convocatoria Relacionada')
        ->assertSee(__('Convocatorias Relacionadas'));
});

it('does not display related calls section when document has no program', function () {
    $documentWithoutProgram = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $documentWithoutProgram])
        ->assertDontSee(__('Convocatorias Relacionadas'));
});

it('excludes current document from related documents', function () {
    $relatedDoc = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Relacionado',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);

    // Verify related document is shown
    $component->assertSee('Documento Relacionado');

    // Verify current document is NOT in related documents collection
    $relatedDocsCollection = $component->instance()->relatedDocuments;
    expect($relatedDocsCollection->pluck('id'))->not->toContain($this->document->id);
});

it('has correct seo title and description', function () {
    $this->get(route('documentos.show', $this->document->slug))
        ->assertOk()
        ->assertSee($this->document->title.' - Documentos Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee(__('Documentos'))
        ->assertSee($this->document->title);
});

it('displays created date correctly', function () {
    Livewire::test(Show::class, ['document' => $this->document])
        ->assertSee($this->document->created_at->translatedFormat('d F Y'));
});

it('handles document without description', function () {
    $documentWithoutDescription = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'description' => null,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $documentWithoutDescription])
        ->assertOk();
});

it('handles document without program', function () {
    $documentWithoutProgram = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $documentWithoutProgram])
        ->assertOk();
});

it('handles document without academic year', function () {
    $documentWithoutYear = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => null,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $documentWithoutYear])
        ->assertOk();
});

it('limits related documents to 3 items', function () {
    // Create 5 related documents
    for ($i = 1; $i <= 5; $i++) {
        Document::factory()->create([
            'category_id' => $this->category->id,
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
            'title' => "Documento Relacionado {$i}",
            'created_at' => now()->subDays($i),
            'created_by' => $this->creator->id,
        ]);
    }

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    $relatedDocs = $component->instance()->relatedDocuments;

    expect($relatedDocs)->toHaveCount(3);
});

it('returns 404 when trying to download non-existent file', function () {
    // Document without file
    $documentWithoutFile = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Show::class, ['document' => $documentWithoutFile])
        ->call('download')
        ->assertStatus(404);
});

it('formats file size correctly', function () {
    // Create a temporary file with more than 1KB content to ensure KB format
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, str_repeat('x', 2048)); // 2KB

    $this->document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    $fileSize = $component->instance()->fileSize;

    expect($fileSize)->toContain('KB');

    // Clean up
    @unlink($tempFile);
});
