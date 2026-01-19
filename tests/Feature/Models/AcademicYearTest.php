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
    $program1 = Program::factory()->create(['code' => 'KA997-TEST', 'slug' => 'ka997-test']);
    $program2 = Program::factory()->create(['code' => 'KA996-TEST', 'slug' => 'ka996-test']);

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

// ============================================
// SCOPE AND STATIC METHODS TESTS
// ============================================

it('can scope to current academic year', function () {
    AcademicYear::factory()->create(['year' => '2024-2025', 'is_current' => false]);
    $current = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);
    AcademicYear::factory()->create(['year' => '2026-2027', 'is_current' => false]);

    $currentYears = AcademicYear::current()->get();

    expect($currentYears)->toHaveCount(1)
        ->and($currentYears->first()->id)->toBe($current->id);
});

it('returns current academic year using static method', function () {
    \Illuminate\Support\Facades\Cache::flush();

    AcademicYear::factory()->create(['year' => '2024-2025', 'is_current' => false]);
    $current = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    $result = AcademicYear::getCurrent();

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($current->id);
});

it('returns null when no current academic year exists', function () {
    \Illuminate\Support\Facades\Cache::flush();

    AcademicYear::factory()->create(['year' => '2024-2025', 'is_current' => false]);
    AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => false]);

    $result = AcademicYear::getCurrent();

    expect($result)->toBeNull();
});

it('caches current academic year', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $current = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // First call should cache
    $result1 = AcademicYear::getCurrent();

    // Verify it's cached
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();
    expect($result1->id)->toBe($current->id);
});

// ============================================
// MARK AS CURRENT TESTS
// ============================================

it('can mark an academic year as current', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year1 = AcademicYear::factory()->create(['year' => '2024-2025', 'is_current' => true]);
    $year2 = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => false]);

    $result = $year2->markAsCurrent();

    expect($result)->toBeTrue()
        ->and($year2->fresh()->is_current)->toBeTrue()
        ->and($year1->fresh()->is_current)->toBeFalse();
});

it('clears cache when marking as current', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year1 = AcademicYear::factory()->create(['year' => '2024-2025', 'is_current' => true]);
    $year2 = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => false]);

    // Cache the current year
    AcademicYear::getCurrent();
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Mark year2 as current
    $year2->markAsCurrent();

    // Cache should be cleared
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});

it('can unmark an academic year as current', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    $result = $year->unmarkAsCurrent();

    expect($result)->toBeTrue()
        ->and($year->fresh()->is_current)->toBeFalse();
});

it('clears cache when unmarking as current', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // Cache the current year
    AcademicYear::getCurrent();
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Unmark as current
    $year->unmarkAsCurrent();

    // Cache should be cleared
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});

// ============================================
// CACHE CLEAR ON EVENTS TESTS
// ============================================

it('clears cache when academic year is_current is updated', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // Cache the current year
    AcademicYear::getCurrent();
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Update is_current directly
    $year->is_current = false;
    $year->save();

    // Cache should be cleared
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});

it('clears cache when academic year is deleted', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // Cache the current year
    AcademicYear::getCurrent();
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Delete the year
    $year->delete();

    // Cache should be cleared
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});

it('clears cache when academic year is restored', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // Delete the year
    $year->delete();

    // Manually set cache with a value to verify it gets cleared on restore
    \Illuminate\Support\Facades\Cache::put('academic_year.current', 'cached_value', 3600);
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Restore the year
    $year->restore();

    // Cache should be cleared
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});

it('can clear current cache manually', function () {
    \Illuminate\Support\Facades\Cache::flush();

    $year = AcademicYear::factory()->create(['year' => '2025-2026', 'is_current' => true]);

    // Cache the current year
    AcademicYear::getCurrent();
    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeTrue();

    // Clear cache manually
    AcademicYear::clearCurrentCache();

    expect(\Illuminate\Support\Facades\Cache::has('academic_year.current'))->toBeFalse();
});
