<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

it('has many calls', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    Call::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($academicYear->calls)->toHaveCount(3)
        ->and($academicYear->calls->first())->toBeInstanceOf(Call::class);
});

it('has many news posts', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($academicYear->newsPosts)->toHaveCount(5)
        ->and($academicYear->newsPosts->first())->toBeInstanceOf(NewsPost::class);
});

it('has many documents', function () {
    $academicYear = AcademicYear::factory()->create(['year' => '2029-2030']);
    $category = \App\Models\DocumentCategory::factory()->create();
    $program = Program::factory()->create(['code' => 'KA991', 'name' => 'Programa Test F', 'slug' => 'programa-test-f']);
    $user = User::factory()->create();
    Document::factory()->count(4)->create([
        'category_id' => $category->id,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($academicYear->documents)->toHaveCount(4)
        ->and($academicYear->documents->first())->toBeInstanceOf(Document::class);
});

it('deletes calls in cascade when academic year is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $academicYear->delete();

    expect(Call::find($call1->id))->toBeNull()
        ->and(Call::find($call2->id))->toBeNull();
});

it('deletes news posts in cascade when academic year is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $program = Program::factory()->create();
    $user = User::factory()->create();
    $newsPost1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $newsPost2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $academicYear->delete();

    expect(NewsPost::find($newsPost1->id))->toBeNull()
        ->and(NewsPost::find($newsPost2->id))->toBeNull();
});

it('sets academic_year_id to null when academic year is deleted (nullOnDelete)', function () {
    $academicYear = AcademicYear::factory()->create();
    $category = \App\Models\DocumentCategory::factory()->create();
    $user = User::factory()->create();
    $document1 = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);
    $document2 = Document::factory()->create([
        'category_id' => $category->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    $academicYear->delete();

    expect(Document::find($document1->id))->not->toBeNull()
        ->and(Document::find($document1->id)->academic_year_id)->toBeNull()
        ->and(Document::find($document2->id))->not->toBeNull()
        ->and(Document::find($document2->id)->academic_year_id)->toBeNull();
});

it('can have calls from different programs', function () {
    $academicYear = AcademicYear::factory()->create();
    $program1 = Program::factory()->create(['code' => 'KA1xx', 'name' => 'Educación Escolar', 'slug' => 'educacion-escolar']);
    $program2 = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Formación Profesional', 'slug' => 'formacion-profesional']);

    Call::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
    ]);
    Call::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($academicYear->calls)->toHaveCount(2)
        ->and($academicYear->calls->pluck('program_id')->unique())->toHaveCount(2);
});

it('can have news posts from different programs', function () {
    $academicYear = AcademicYear::factory()->create(['year' => '2025-2026']);
    $program1 = Program::factory()->create(['code' => 'KA996', 'name' => 'Programa Test A', 'slug' => 'programa-test-a']);
    $program2 = Program::factory()->create(['code' => 'KA995', 'name' => 'Programa Test B', 'slug' => 'programa-test-b']);
    $user = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    NewsPost::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($academicYear->newsPosts)->toHaveCount(2)
        ->and($academicYear->newsPosts->pluck('program_id')->unique())->toHaveCount(2);
});

