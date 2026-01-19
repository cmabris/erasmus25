<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CallObserver - Notification on Publish', function () {
    it('notifies users when call is created as published', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        // Create a user to be notified
        $user = User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyConvocatoriaPublished')
            ->once()
            ->withArgs(function ($call, $users) use ($user) {
                return $call instanceof Call && $users->contains($user);
            });

        $this->app->instance(NotificationService::class, $mock);

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'published_at' => now(),
            'status' => 'abierta',
        ]);
    });

    it('notifies users when call is updated to published', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $user = User::factory()->create();

        // Create call without publishing
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'published_at' => null,
            'status' => 'borrador',
        ]);

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyConvocatoriaPublished')
            ->once()
            ->withArgs(function ($notifiedCall, $users) use ($call, $user) {
                return $notifiedCall->id === $call->id && $users->contains($user);
            });

        $this->app->instance(NotificationService::class, $mock);

        // Update to publish
        $call->update([
            'published_at' => now(),
            'status' => 'abierta',
        ]);
    });

    it('does not notify when there are no users', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        // Create call without events to avoid triggering observer during setup
        $call = Call::withoutEvents(function () use ($program, $academicYear) {
            return Call::factory()->create([
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
                'published_at' => null,
                'status' => 'borrador',
            ]);
        });

        // Delete all users
        User::query()->forceDelete();

        // The notifyConvocatoriaPublished should not be called because users is empty
        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyConvocatoriaPublished');
        $this->app->instance(NotificationService::class, $mock);

        // Now update to trigger the observer with no users
        $call->update([
            'published_at' => now(),
            'status' => 'abierta',
        ]);
    });

    it('does not notify when call is created as draft', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyConvocatoriaPublished');

        $this->app->instance(NotificationService::class, $mock);

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'published_at' => null,
            'status' => 'borrador',
        ]);
    });

    it('does not notify when published_at is in the future', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyConvocatoriaPublished');

        $this->app->instance(NotificationService::class, $mock);

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'published_at' => now()->addDays(5),
            'status' => 'abierta',
        ]);
    });

    it('loads program relationship if not loaded', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $user = User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyConvocatoriaPublished')
            ->once()
            ->withArgs(function ($call, $users) {
                // Verify that program is loaded
                return $call->relationLoaded('program');
            });

        $this->app->instance(NotificationService::class, $mock);

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'published_at' => now(),
            'status' => 'abierta',
        ]);
    });
});
