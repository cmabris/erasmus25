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

it('is deleted in cascade when category is deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    $category->delete();

    expect(Document::find($document->id))->toBeNull();
});

it('sets program_id to null when program is deleted', function () {
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

    $program->delete();
    $document->refresh();

    expect($document->program_id)->toBeNull()
        ->and($document->program)->toBeNull();
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

it('sets created_by to null when creator user is deleted', function () {
    $category = DocumentCategory::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    $user->delete();
    $document->refresh();

    expect($document->created_by)->toBeNull()
        ->and($document->creator)->toBeNull();
});

it('sets updated_by to null when updater user is deleted', function () {
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

    $updater->delete();
    $document->refresh();

    expect($document->updated_by)->toBeNull()
        ->and($document->updater)->toBeNull();
});

