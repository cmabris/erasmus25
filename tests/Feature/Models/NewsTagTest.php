<?php

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

it('belongs to many news posts', function () {
    $tag = NewsTag::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2023-2024']);
    $user = User::factory()->create();
    $program = Program::factory()->create(['code' => 'KA1xx', 'name' => 'Educación Escolar', 'slug' => 'educacion-escolar']);
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
    $newsPost3 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);

    $tag->newsPosts()->attach([$newsPost1->id, $newsPost2->id, $newsPost3->id]);

    expect($tag->fresh()->newsPosts)->toHaveCount(3)
        ->and($tag->fresh()->newsPosts->first())->toBeInstanceOf(NewsPost::class);
});

it('can attach and detach news posts', function () {
    $tag = NewsTag::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2023-2024']);
    $user = User::factory()->create();
    $program = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Formación Profesional', 'slug' => 'formacion-profesional']);
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

    $tag->newsPosts()->attach($newsPost1->id);
    expect($tag->fresh()->newsPosts)->toHaveCount(1);

    $tag->newsPosts()->attach($newsPost2->id);
    expect($tag->fresh()->newsPosts)->toHaveCount(2);

    $tag->newsPosts()->detach($newsPost1->id);
    expect($tag->fresh()->newsPosts)->toHaveCount(1)
        ->and($tag->fresh()->newsPosts->first()->id)->toBe($newsPost2->id);
});

it('removes relationships when tag is deleted', function () {
    $tag = NewsTag::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2023-2024']);
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag->newsPosts()->attach($newsPost->id);

    $tag->delete();

    expect($newsPost->tags)->toHaveCount(0);
});

it('removes relationships when news post is deleted', function () {
    $tag = NewsTag::factory()->create();
    $academicYear = AcademicYear::factory()->create(['year' => '2023-2024']);
    $user = User::factory()->create();
    $newsPost = NewsPost::factory()->create([
        'academic_year_id' => $academicYear->id,
        'author_id' => $user->id,
    ]);
    $tag->newsPosts()->attach($newsPost->id);

    $newsPost->delete();

    expect($tag->newsPosts)->toHaveCount(0);
});

