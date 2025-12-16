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

it('deletes documents in cascade when category is deleted', function () {
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

    $category->delete();

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

