<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->program = Program::factory()->create();
    $this->academicYear = AcademicYear::factory()->create();
    $this->call = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
    ]);
    $this->phase = CallPhase::factory()->create([
        'call_id' => $this->call->id,
    ]);
});

describe('ResolutionObserver - Notification on Publish', function () {
    it('notifies users when resolution is created as published', function () {
        $user = User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyResolucionPublished')
            ->once()
            ->withArgs(function ($resolution, $users) use ($user) {
                return $resolution instanceof Resolution && $users->contains($user);
            });

        $this->app->instance(NotificationService::class, $mock);

        Resolution::factory()->create([
            'call_id' => $this->call->id,
            'call_phase_id' => $this->phase->id,
            'published_at' => now(),
        ]);
    });

    it('notifies users when resolution is updated to published', function () {
        $user = User::factory()->create();

        // Create resolution without publishing
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
            'call_phase_id' => $this->phase->id,
            'published_at' => null,
        ]);

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyResolucionPublished')
            ->once()
            ->withArgs(function ($notifiedResolution, $users) use ($resolution, $user) {
                return $notifiedResolution->id === $resolution->id && $users->contains($user);
            });

        $this->app->instance(NotificationService::class, $mock);

        // Update to publish
        $resolution->update([
            'published_at' => now(),
        ]);
    });

    it('does not notify when there are no users', function () {
        // Create resolution without events to avoid triggering observer during setup
        $resolution = Resolution::withoutEvents(function () {
            return Resolution::factory()->create([
                'call_id' => $this->call->id,
                'call_phase_id' => $this->phase->id,
                'published_at' => null,
            ]);
        });

        // Delete all users
        User::query()->forceDelete();

        // The notifyResolucionPublished should not be called because users is empty
        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyResolucionPublished');
        $this->app->instance(NotificationService::class, $mock);

        // Now update to trigger the observer with no users
        $resolution->update([
            'published_at' => now(),
        ]);
    });

    it('does not notify when resolution is created unpublished', function () {
        User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyResolucionPublished');

        $this->app->instance(NotificationService::class, $mock);

        Resolution::factory()->create([
            'call_id' => $this->call->id,
            'call_phase_id' => $this->phase->id,
            'published_at' => null,
        ]);
    });

    it('does not notify when published_at is in the future', function () {
        User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldNotReceive('notifyResolucionPublished');

        $this->app->instance(NotificationService::class, $mock);

        Resolution::factory()->create([
            'call_id' => $this->call->id,
            'call_phase_id' => $this->phase->id,
            'published_at' => now()->addDays(5),
        ]);
    });

    it('loads call relationship if not loaded', function () {
        $user = User::factory()->create();

        $mock = Mockery::mock(NotificationService::class);
        $mock->shouldReceive('notifyResolucionPublished')
            ->once()
            ->withArgs(function ($resolution, $users) {
                // Verify that call is loaded
                return $resolution->relationLoaded('call');
            });

        $this->app->instance(NotificationService::class, $mock);

        Resolution::factory()->create([
            'call_id' => $this->call->id,
            'call_phase_id' => $this->phase->id,
            'published_at' => now(),
        ]);
    });
});
