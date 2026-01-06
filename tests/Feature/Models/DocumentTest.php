<?php

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;

it('belongs to a category', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($document->category)->toBeInstanceOf(DocumentCategory::class)
        ->and($document->category->id)->toBe($category->id);
});

it('belongs to a program', function () {
    $category = DocumentCategory::factory()->create();
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($document->program)->toBeInstanceOf(Program::class)
        ->and($document->program->id)->toBe($program->id);
});

it('can have null program', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($document->program)->toBeNull();
});

it('belongs to an academic year', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($document->academicYear)->toBeInstanceOf(AcademicYear::class)
        ->and($document->academicYear->id)->toBe($academicYear->id);
});

it('can have null academic year', function () {
    $category = DocumentCategory::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => null,
        'created_by' => $user->id,
    ]);

    expect($document->academicYear)->toBeNull();
});

it('belongs to a creator user', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($document->creator)->toBeInstanceOf(User::class)
        ->and($document->creator->id)->toBe($user->id);
});

it('can have null creator', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => null,
    ]);

    expect($document->creator)->toBeNull();
});

it('belongs to an updater user', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $creator->id,
        'updated_by' => $updater->id,
    ]);

    expect($document->updater)->toBeInstanceOf(User::class)
        ->and($document->updater->id)->toBe($updater->id);
});

it('can have null updater', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
        'updated_by' => null,
    ]);

    expect($document->updater)->toBeNull();
});

it('is not deleted when category is soft deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Con SoftDeletes, delete() no activa cascadeOnDelete
    // El cascadeOnDelete solo funciona con forceDelete()
    $category->delete();

    // El documento NO se elimina porque el cascadeOnDelete solo funciona con forceDelete
    expect(Document::find($document->id))->not->toBeNull();
});

it('is deleted in cascade when category is force deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // forceDelete() activa el cascadeOnDelete de la base de datos
    $category->forceDelete();

    expect(Document::find($document->id))->toBeNull();
});

it('does not set program_id to null when program is soft deleted', function () {
    $category = DocumentCategory::factory()->create();
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    $program->delete(); // Soft delete
    $document->refresh();

    // With SoftDeletes, program_id is not set to null because the program still exists
    // However, Eloquent excludes soft-deleted records from relationships, so $document->program will be null
    expect($document->program_id)->toBe($program->id)
        ->and($document->program)->toBeNull() // Eloquent excludes soft-deleted records
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('sets academic_year_id to null when academic year is deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    $academicYear->delete();
    $document->refresh();

    expect($document->academic_year_id)->toBeNull()
        ->and($document->academicYear)->toBeNull();
});

it('sets created_by to null when creator user is force deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $document->refresh();

    expect($document->created_by)->toBeNull()
        ->and($document->creator)->toBeNull();
});

it('sets updated_by to null when updater user is force deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $creator->id,
        'updated_by' => $updater->id,
    ]);

    // Force delete to trigger foreign key constraint
    $updater->forceDelete();
    $document->refresh();

    expect($document->updated_by)->toBeNull()
        ->and($document->updater)->toBeNull();
});

it('generates slug automatically when slug is empty', function () {
    $category = DocumentCategory::factory()->create();
    $user = User::factory()->create();
    $document = Document::create([
        'category_id' => $category->id,
        'title' => 'Test Document Title',
        'slug' => '', // Empty slug
        'document_type' => 'convocatoria',
        'created_by' => $user->id,
    ]);

    expect($document->slug)->toBe('test-document-title');
});
