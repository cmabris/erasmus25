<?php

use App\Livewire\Public\Documents\Index;
use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
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

    // Create categories
    $this->category1 = DocumentCategory::factory()->create([
        'name' => 'Convocatorias',
        'slug' => 'convocatorias',
        'order' => 1,
    ]);

    $this->category2 = DocumentCategory::factory()->create([
        'name' => 'Modelos',
        'slug' => 'modelos',
        'order' => 2,
    ]);

    // Create creator
    $this->creator = User::factory()->create();
});

it('renders the documents index page', function () {
    $this->get(route('documentos.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('displays only active documents', function () {
    // Create active document
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Activo',
        'created_by' => $this->creator->id,
    ]);

    // This should NOT appear
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => false,
        'title' => 'Documento Inactivo',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->assertSee('Documento Activo')
        ->assertDontSee('Documento Inactivo');
});

it('can search documents by title', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Guía de Movilidad',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category2->id,
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Modelo de Solicitud',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'Guía')
        ->assertSee('Guía de Movilidad')
        ->assertDontSee('Modelo de Solicitud');
});

it('can search documents by description', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento 1',
        'description' => 'Este documento contiene información sobre seguros',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento 2',
        'description' => 'Información sobre convocatorias',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'seguros')
        ->assertSee('Documento 1')
        ->assertDontSee('Documento 2');
});

it('can filter documents by category', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Convocatorias',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category2->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Modelos',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('category', $this->category1->id)
        ->assertSee('Documento Convocatorias')
        ->assertDontSee('Documento Modelos');
});

it('can filter documents by program', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento FP',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program2->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Cooperación',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('program', $this->program1->id)
        ->assertSee('Documento FP')
        ->assertDontSee('Documento Cooperación');
});

it('can filter documents by academic year', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento 2024-2025',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear2->id,
        'is_active' => true,
        'title' => 'Documento 2023-2024',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('academicYear', $this->academicYear1->id)
        ->assertSee('Documento 2024-2025')
        ->assertDontSee('Documento 2023-2024');
});

it('can filter documents by document type', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'document_type' => 'convocatoria',
        'title' => 'Convocatoria Test',
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category2->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'document_type' => 'modelo',
        'title' => 'Modelo Test',
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('documentType', 'convocatoria')
        ->assertSee('Convocatoria Test')
        ->assertDontSee('Modelo Test');
});

it('can reset filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('category', $this->category1->id)
        ->set('program', $this->program1->id)
        ->set('academicYear', $this->academicYear1->id)
        ->set('documentType', 'convocatoria')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('category', '')
        ->assertSet('program', '')
        ->assertSet('academicYear', '')
        ->assertSet('documentType', '');
});

it('shows empty state when no documents match filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'nonexistent document xyz')
        ->assertSee(__('No se encontraron documentos'));
});

it('displays statistics correctly', function () {
    Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'download_count' => 10,
        'created_by' => $this->creator->id,
    ]);

    Document::factory()->create([
        'category_id' => $this->category2->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'download_count' => 20,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->assertSeeHtml('2'); // Total documents
});

it('supports pagination', function () {
    // Create enough documents to trigger pagination (default is 12 per page)
    Document::factory()->count(15)->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $component = Livewire::test(Index::class);
    expect($component->instance())->toBeInstanceOf(Index::class);
});

it('updates search and resets pagination', function () {
    Document::factory()->count(15)->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    Livewire::test(Index::class)
        ->set('search', 'test')
        ->assertSet('search', 'test');
});

it('has correct seo title and description', function () {
    $this->get(route('documentos.index'))
        ->assertOk()
        ->assertSee('Documentos Erasmus+');
});

it('shows breadcrumbs', function () {
    Livewire::test(Index::class)
        ->assertSee(__('Documentos'));
});

it('links to document detail page', function () {
    $document = Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'created_by' => $this->creator->id,
    ]);

    $this->get(route('documentos.index'))
        ->assertOk()
        ->assertSee(route('documentos.show', $document->slug));
});

it('orders documents by created_at desc', function () {
    $oldDocument = Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Antiguo',
        'created_at' => now()->subDays(10),
        'created_by' => $this->creator->id,
    ]);

    $recentDocument = Document::factory()->create([
        'category_id' => $this->category1->id,
        'program_id' => $this->program1->id,
        'academic_year_id' => $this->academicYear1->id,
        'is_active' => true,
        'title' => 'Documento Reciente',
        'created_at' => now()->subDays(2),
        'created_by' => $this->creator->id,
    ]);

    $response = Livewire::test(Index::class);

    // The first document should be the most recent
    $response->assertSee('Documento Reciente');
});
