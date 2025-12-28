<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;

it('has many calls', function () {
    $program = Program::factory()->create(['code' => 'KA993', 'name' => 'Programa Test D', 'slug' => 'programa-test-d']);
    $academicYear = AcademicYear::factory()->create(['year' => '2027-2028']);
    Call::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($program->calls)->toHaveCount(3)
        ->and($program->calls->first())->toBeInstanceOf(Call::class);
});

it('has many news posts', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($program->newsPosts)->toHaveCount(5)
        ->and($program->newsPosts->first())->toBeInstanceOf(NewsPost::class);
});

it('does not delete calls when program is soft deleted', function () {
    $program = Program::factory()->create(['code' => 'KA992', 'name' => 'Programa Test E', 'slug' => 'programa-test-e']);
    $academicYear = AcademicYear::factory()->create(['year' => '2028-2029']);
    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $program->delete(); // Soft delete

    // With SoftDeletes, calls are not deleted, they still exist
    expect(Call::find($call1->id))->not->toBeNull()
        ->and(Call::find($call2->id))->not->toBeNull()
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('does not set program_id to null when program is soft deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $newsPost1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $newsPost2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $program->delete(); // Soft delete

    // With SoftDeletes, program_id is not set to null because the program still exists
    expect(NewsPost::find($newsPost1->id))->not->toBeNull()
        ->and(NewsPost::find($newsPost1->id)->program_id)->toBe($program->id)
        ->and(NewsPost::find($newsPost2->id))->not->toBeNull()
        ->and(NewsPost::find($newsPost2->id)->program_id)->toBe($program->id)
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('can have calls from different academic years', function () {
    $program = Program::factory()->create();
    $academicYear1 = AcademicYear::factory()->create(['year' => '2023-2024']);
    $academicYear2 = AcademicYear::factory()->create(['year' => '2024-2025']);

    Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear1->id,
    ]);
    Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear2->id,
    ]);

    expect($program->calls)->toHaveCount(2)
        ->and($program->calls->pluck('academic_year_id')->unique())->toHaveCount(2);
});

it('can have news posts from different academic years', function () {
    $program = Program::factory()->create();
    $academicYear1 = AcademicYear::factory()->create(['year' => '2023-2024']);
    $academicYear2 = AcademicYear::factory()->create(['year' => '2024-2025']);

    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear1->id,
    ]);
    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear2->id,
    ]);

    expect($program->newsPosts)->toHaveCount(2)
        ->and($program->newsPosts->pluck('academic_year_id')->unique())->toHaveCount(2);
});

it('generates slug automatically when slug is empty', function () {
    $program = Program::create([
        'code' => 'KA999',
        'name' => 'Test Program Name',
        'slug' => '', // Empty slug
        'is_active' => true,
        'order' => 1,
    ]);

    expect($program->slug)->toBe('test-program-name');
});
