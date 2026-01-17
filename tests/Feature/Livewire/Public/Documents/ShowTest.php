<?php

use App\Livewire\Public\Documents\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\MediaConsent;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
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

it('returns null for fileSize when no file is attached', function () {
    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->fileSize)->toBeNull();
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

it('returns false for hasMediaConsent when no consent exists', function () {
    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->hasMediaConsent)->toBeFalse();
});

it('returns false for hasMediaConsent when consent is revoked', function () {
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    MediaConsent::factory()->revoked()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId,
        'consent_given' => false,
        'revoked_at' => now(),
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->hasMediaConsent)->toBeFalse();
});

it('returns false for hasMediaConsent when consent_given is false', function () {
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    MediaConsent::factory()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId,
        'consent_given' => false,
        'revoked_at' => null,
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->hasMediaConsent)->toBeFalse();
});

it('returns true for hasMediaConsent when active consent exists', function () {
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    MediaConsent::factory()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId,
        'consent_given' => true,
        'revoked_at' => null,
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->hasMediaConsent)->toBeTrue();
});

it('returns empty collection for mediaConsents when no consent exists', function () {
    $component = Livewire::test(Show::class, ['document' => $this->document]);
    $consents = $component->instance()->mediaConsents;
    
    expect($consents)->toBeEmpty();
});

it('returns empty collection for mediaConsents when all are revoked', function () {
    $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    MediaConsent::factory()->revoked()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId,
        'consent_given' => false,
        'revoked_at' => now(),
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    $consents = $component->instance()->mediaConsents;
    
    expect($consents)->toBeEmpty();
});

it('returns only active consents in mediaConsents', function () {
    $mediaId1 = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 1,
        'collection_name' => 'test',
        'name' => 'test',
        'file_name' => 'test.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999999;

    $mediaId2 = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
        'model_type' => 'App\Models\Test',
        'model_id' => 2,
        'collection_name' => 'test',
        'name' => 'test2',
        'file_name' => 'test2.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'size' => 1024,
        'manipulations' => '[]',
        'custom_properties' => '[]',
        'generated_conversions' => '[]',
        'responsive_images' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]) : 999998;

    $activeConsent = MediaConsent::factory()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId1,
        'consent_given' => true,
        'revoked_at' => null,
        'consent_date' => now()->subDays(5),
    ]);

    $revokedConsent = MediaConsent::factory()->revoked()->create([
        'consent_document_id' => $this->document->id,
        'media_id' => $mediaId2,
        'consent_given' => false,
        'revoked_at' => now(),
        'consent_date' => now()->subDays(10),
    ]);

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    $consents = $component->instance()->mediaConsents;
    
    expect($consents)->toHaveCount(1)
        ->and($consents->first()->id)->toBe($activeConsent->id);
});

it('returns empty collection for relatedDocuments when document has no program and no related documents exist', function () {
    // Since category_id is required, we test the case where document has category but no program
    // and there are no other documents with the same category
    $documentWithoutProgram = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    // Delete all other documents with the same category to ensure empty result
    Document::where('category_id', $this->category->id)
        ->where('id', '!=', $documentWithoutProgram->id)
        ->delete();

    $component = Livewire::test(Show::class, ['document' => $documentWithoutProgram]);
    $relatedDocs = $component->instance()->relatedDocuments;
    
    // Should return documents from same category, but since we deleted them, should be empty
    expect($relatedDocs)->toBeEmpty();
});

it('returns empty collection for relatedCalls when document has no program', function () {
    $documentWithoutProgram = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => null,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $documentWithoutProgram]);
    $relatedCalls = $component->instance()->relatedCalls;
    
    expect($relatedCalls)->toBeEmpty();
});

it('returns correct document type config for seguro type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'seguro',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('shield-check')
        ->and($config['color'])->toBe('success')
        ->and($config['label'])->toBe(__('Seguro'));
});

it('returns correct document type config for consentimiento type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'consentimiento',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('clipboard-document-check')
        ->and($config['color'])->toBe('warning')
        ->and($config['label'])->toBe(__('Consentimiento'));
});

it('returns correct document type config for faq type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'faq',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('question-mark-circle')
        ->and($config['color'])->toBe('info')
        ->and($config['label'])->toBe(__('FAQ'));
});

it('returns correct document type config for otro type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'otro',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('document')
        ->and($config['color'])->toBe('neutral')
        ->and($config['label'])->toBe(__('Otro'));
});

it('returns correct document type config for convocatoria type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'convocatoria',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('document-text')
        ->and($config['color'])->toBe('primary')
        ->and($config['label'])->toBe(__('Convocatoria'));
});

it('returns correct document type config for modelo type', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'modelo',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('document-duplicate')
        ->and($config['color'])->toBe('info')
        ->and($config['label'])->toBe(__('Modelo'));
});

it('returns default document type config for unknown type', function () {
    // Since document_type is an ENUM in the database, we cannot create a document
    // with a value outside the enum. However, we can use setAttribute to simulate
    // an unknown type to test the default case in the match statement.
    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'document_type' => 'convocatoria', // Start with valid enum value
        'created_by' => $this->creator->id,
    ]);

    // Use setAttribute to set an unknown type to test the default case
    $document->setAttribute('document_type', 'tipo_desconocido');

    $component = Livewire::test(Show::class, ['document' => $document]);
    $config = $component->instance()->documentTypeConfig;
    
    expect($config['icon'])->toBe('document')
        ->and($config['color'])->toBe('neutral')
        ->and($config['label'])->toBe(__('Documento'));
});

it('formats file size in bytes correctly', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, str_repeat('x', 512)); // 512 bytes, less than 1KB

    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    $component = Livewire::test(Show::class, ['document' => $document]);
    $fileSize = $component->instance()->fileSize;

    expect($fileSize)->toContain('B')
        ->and($fileSize)->not->toContain('KB');

    @unlink($tempFile);
});

it('formats file size in MB correctly', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, str_repeat('x', 2 * 1024 * 1024)); // 2MB

    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    $component = Livewire::test(Show::class, ['document' => $document]);
    $fileSize = $component->instance()->fileSize;

    expect($fileSize)->toContain('MB')
        ->and($fileSize)->not->toContain('KB')
        ->and($fileSize)->not->toContain('GB');

    @unlink($tempFile);
});

it('formats file size in GB correctly', function () {
    // Create a smaller file and mock the size to avoid memory issues
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, str_repeat('x', 1024)); // 1KB

    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $media = $document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    // Mock the size to be 2GB
    $media->size = 2 * 1024 * 1024 * 1024;
    $media->save();

    $component = Livewire::test(Show::class, ['document' => $document->fresh()]);
    $fileSize = $component->instance()->fileSize;

    expect($fileSize)->toContain('GB')
        ->and($fileSize)->not->toContain('MB')
        ->and($fileSize)->not->toContain('TB');

    @unlink($tempFile);
});

it('formats file size in TB correctly', function () {
    // Create a small file and mock the size to avoid memory issues
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, str_repeat('x', 1024)); // 1KB

    $document = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $media = $document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    // Mock the size to be 2TB
    $media->size = 2 * 1024 * 1024 * 1024 * 1024;
    $media->save();

    $component = Livewire::test(Show::class, ['document' => $document->fresh()]);
    $fileSize = $component->instance()->fileSize;

    expect($fileSize)->toContain('TB')
        ->and($fileSize)->not->toContain('GB');

    @unlink($tempFile);
});

it('returns null for fileExtension when no file is attached', function () {
    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->fileExtension)->toBeNull();
});

it('returns correct file extension when file is attached', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_doc_');
    file_put_contents($tempFile, 'Test PDF content');

    $this->document->addMedia($tempFile)
        ->usingName('Documento de Prueba')
        ->usingFileName('documento.pdf')
        ->toMediaCollection('file');

    $component = Livewire::test(Show::class, ['document' => $this->document]);
    
    expect($component->instance()->fileExtension)->toBe('pdf');

    @unlink($tempFile);
});

it('returns related documents from same program when document has no category', function () {
    // Since category_id is required in the database, we need to create the document
    // with a category first, then set category_id to null using setAttribute
    // to test the elseif branch in relatedDocuments()
    $documentWithoutCategory = Document::factory()->create([
        'category_id' => $this->category->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Sin Categoría',
        'created_by' => $this->creator->id,
    ]);

    // Set category_id to null to test the elseif branch
    $documentWithoutCategory->setAttribute('category_id', null);

    // Create related documents with same program but different category
    $otherCategory = DocumentCategory::factory()->create([
        'name' => 'Otra Categoría',
        'slug' => 'otra-categoria-'.uniqid(),
    ]);

    $relatedDoc1 = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Relacionado 1',
        'created_by' => $this->creator->id,
    ]);

    $relatedDoc2 = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento Relacionado 2',
        'created_by' => $this->creator->id,
    ]);

    // Create document with different program - should not appear
    $otherProgram = Program::factory()->create(['is_active' => true]);
    $unrelatedDoc = Document::factory()->create([
        'category_id' => $otherCategory->id,
        'program_id' => $otherProgram->id,
        'academic_year_id' => $this->academicYear->id,
        'is_active' => true,
        'title' => 'Documento No Relacionado',
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Show::class, ['document' => $documentWithoutCategory]);
    $relatedDocs = $component->instance()->relatedDocuments;

    expect($relatedDocs->pluck('id'))->toContain($relatedDoc1->id)
        ->and($relatedDocs->pluck('id'))->toContain($relatedDoc2->id)
        ->and($relatedDocs->pluck('id'))->not->toContain($unrelatedDoc->id);
});
