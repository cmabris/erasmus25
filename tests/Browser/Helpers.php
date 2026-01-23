<?php

namespace Tests\Browser\Helpers;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

/**
 * Helper function to create public test data for browser tests.
 *
 * Creates a complete set of test data including:
 * - An active program
 * - An academic year
 * - A published call (abierta)
 * - A published news post
 *
 * @return array<string, mixed> Array containing the created models
 */
function createPublicTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
    ]);
    $news = NewsPost::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'call' => $call,
        'news' => $news,
    ];
}

/**
 * Helper function to create an authenticated user for browser tests.
 *
 * @param  array<string, mixed>  $attributes  Additional attributes for the user
 * @return \App\Models\User
 */
function createAuthenticatedUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}
