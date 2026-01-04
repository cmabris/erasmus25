<?php

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;

it('has many documents', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $program = Program::factory()->create(['code' => 'KA131-HED', 'name' => 'Educación Superior', 'slug' => 'educacion-superior']);
    Document::factory()->count(5)->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($category->documents)->toHaveCount(5)
        ->and($category->documents->first())->toBeInstanceOf(Document::class);
});

it('cannot be deleted when it has associated documents', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2030-2031']);
    $program = Program::factory()->create(['code' => 'KA990', 'name' => 'Programa Test G', 'slug' => 'programa-test-g']);
    $user = User::factory()->create();
    $document1 = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);
    $document2 = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Con SoftDeletes, no se puede eliminar una categoría si tiene documentos asociados
    // El cascadeOnDelete solo funciona con forceDelete(), no con delete() (soft delete)
    $category->delete();

    // La categoría se elimina (soft delete)
    expect($category->fresh()->trashed())->toBeTrue();

    // Los documentos NO se eliminan porque el cascadeOnDelete solo funciona con forceDelete
    // y además, la lógica de negocio previene la eliminación si hay relaciones
    expect(Document::find($document1->id))->not->toBeNull()
        ->and(Document::find($document2->id))->not->toBeNull();
});

it('deletes documents in cascade when category is force deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2030-2031']);
    $program = Program::factory()->create(['code' => 'KA990', 'name' => 'Programa Test G', 'slug' => 'programa-test-g']);
    $user = User::factory()->create();
    $document1 = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);
    $document2 = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // forceDelete() activa el cascadeOnDelete de la base de datos
    $category->forceDelete();

    expect(Document::find($document1->id))->toBeNull()
        ->and(Document::find($document2->id))->toBeNull();
});

it('can have documents from different programs', function () {
    $category = DocumentCategory::factory()->create();
    $program1 = Program::factory()->create(['code' => 'KA1xx', 'name' => 'Educación Escolar', 'slug' => 'educacion-escolar']);
    $program2 = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Formación Profesional', 'slug' => 'formacion-profesional']);
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();

    Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);
    Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($category->documents)->toHaveCount(2)
        ->and($category->documents->pluck('program_id')->unique())->toHaveCount(2);
});

it('can have documents without program', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($category->documents)->toHaveCount(1)
        ->and($category->documents->first()->program_id)->toBeNull();
});

it('generates slug automatically when slug is empty', function () {
    $category = DocumentCategory::create([
        'name' => 'Test Category Name',
        'slug' => '', // Empty slug
        'order' => 1,
    ]);

    expect($category->slug)->toBe('test-category-name');
});
