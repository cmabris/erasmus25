<?php

use App\Models\Notification;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $user->id]);

    expect($notification->user)->toBeInstanceOf(User::class)
        ->and($notification->user->id)->toBe($user->id);
});

it('is deleted in cascade when user is force deleted', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create(['user_id' => $user->id]);

    // Force delete to trigger foreign key cascade delete
    $user->forceDelete();

    expect(Notification::find($notification->id))->toBeNull();
});

it('can have multiple notifications for the same user', function () {
    $user = User::factory()->create();
    $notifications = Notification::factory()->count(5)->create(['user_id' => $user->id]);

    expect(Notification::where('user_id', $user->id)->count())->toBe(5);
});
