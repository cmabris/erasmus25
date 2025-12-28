<?php

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

it('belongs to a program', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->program)->toBeInstanceOf(Program::class)
        ->and($newsPost->program->id)->toBe($program->id);
});

it('can have null program', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->program)->toBeNull();
});

it('belongs to an academic year', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->academicYear)->toBeInstanceOf(AcademicYear::class)
        ->and($newsPost->academicYear->id)->toBe($academicYear->id);
});

it('belongs to an author user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    expect($newsPost->author)->toBeInstanceOf(User::class)
        ->and($newsPost->author->id)->toBe($user->id);
});

it('can have null author', function () {
    $academicYear = AcademicYear::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => null,
    ]);

    expect($newsPost->author)->toBeNull();
});

it('belongs to a reviewer user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $reviewer = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'reviewed_by' => $reviewer->id,
    ]);

    expect($newsPost->reviewer)->toBeInstanceOf(User::class)
        ->and($newsPost->reviewer->id)->toBe($reviewer->id);
});

it('can have null reviewer', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => null,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
        'reviewed_by' => null,
    ]);

    expect($newsPost->reviewer)->toBeNull();
});

it('belongs to many tags', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();
    $tag3 = NewsTag::factory()->create();

    $newsPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

    expect($newsPost->tags)->toHaveCount(3)
        ->and($newsPost->tags->first())->toBeInstanceOf(NewsTag::class);
});

it('can attach and detach tags', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag1 = NewsTag::factory()->create();
    $tag2 = NewsTag::factory()->create();

    $newsPost->tags()->attach($tag1->id);
    expect($newsPost->fresh()->tags)->toHaveCount(1);

    $newsPost->tags()->attach($tag2->id);
    expect($newsPost->fresh()->tags)->toHaveCount(2);

    $newsPost->tags()->detach($tag1->id);
    expect($newsPost->fresh()->tags)->toHaveCount(1)
        ->and($newsPost->fresh()->tags->first()->id)->toBe($tag2->id);
});

it('does not set program_id to null when program is soft deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $program->delete(); // Soft delete
    $newsPost->refresh();

    // With SoftDeletes, program_id is not set to null because the program still exists
    // However, Eloquent excludes soft-deleted records from relationships, so $newsPost->program will be null
    expect($newsPost->program_id)->toBe($program->id)
        ->and($newsPost->program)->toBeNull() // Eloquent excludes soft-deleted records
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('is deleted in cascade when academic year is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $academicYear->delete();

    expect(NewsPost::find($newsPost->id))->toBeNull();
});

it('sets author_id to null when author user is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $user->delete();
    $newsPost->refresh();

    expect($newsPost->author_id)->toBeNull()
        ->and($newsPost->author)->toBeNull();
});

it('sets reviewed_by to null when reviewer user is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $reviewer = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'reviewed_by' => $reviewer->id,
    ]);

    $reviewer->delete();
    $newsPost->refresh();

    expect($newsPost->reviewed_by)->toBeNull()
        ->and($newsPost->reviewer)->toBeNull();
});

it('removes tag relationships when news post is deleted', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag = NewsTag::factory()->create();
    $newsPost->tags()->attach($tag->id);

    $newsPost->delete();

    expect($tag->newsPosts)->toHaveCount(0);
});

it('generates slug automatically when slug is empty', function () {
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $newsPost = NewsPost::create([
        'academic_year_id' => $academicYear->id,
        'title' => 'Test News Post Title',
        'slug' => '', // Empty slug
        'content' => 'Test content',
        'status' => 'borrador',
        'author_id' => $user->id,
    ]);

    expect($newsPost->slug)->toBe('test-news-post-title');
});
