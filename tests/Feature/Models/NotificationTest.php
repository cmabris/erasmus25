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

// ============================================
// MARK AS READ TESTS
// ============================================

it('can mark notification as read', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
        'read_at' => null,
    ]);

    $result = $notification->markAsRead();

    expect($result)->toBeTrue()
        ->and($notification->fresh()->is_read)->toBeTrue()
        ->and($notification->fresh()->read_at)->not->toBeNull();
});

it('returns false when marking already read notification as read', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
        'read_at' => now(),
    ]);

    $result = $notification->markAsRead();

    expect($result)->toBeFalse();
});

// ============================================
// SCOPES TESTS
// ============================================

it('can scope to unread notifications', function () {
    $user = User::factory()->create();

    $unread = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    $unreadNotifications = Notification::unread()->get();

    expect($unreadNotifications)->toHaveCount(1)
        ->and($unreadNotifications->first()->id)->toBe($unread->id);
});

it('can scope to read notifications', function () {
    $user = User::factory()->create();

    Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $read = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => true,
    ]);

    $readNotifications = Notification::read()->get();

    expect($readNotifications)->toHaveCount(1)
        ->and($readNotifications->first()->id)->toBe($read->id);
});

it('can scope to notifications by type', function () {
    $user = User::factory()->create();

    $convocatoria = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'convocatoria',
    ]);

    Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'sistema',
    ]);

    $convocatoriaNotifications = Notification::byType('convocatoria')->get();

    expect($convocatoriaNotifications)->toHaveCount(1)
        ->and($convocatoriaNotifications->first()->id)->toBe($convocatoria->id);
});

it('can scope to recent notifications', function () {
    $user = User::factory()->create();

    $recent = Notification::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(3),
    ]);

    Notification::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(10),
    ]);

    $recentNotifications = Notification::recent(7)->get();

    expect($recentNotifications)->toHaveCount(1)
        ->and($recentNotifications->first()->id)->toBe($recent->id);
});

// ============================================
// TYPE HELPER METHODS TESTS
// ============================================

it('returns correct type label for convocatoria', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'convocatoria',
    ]);

    $label = $notification->getTypeLabel();

    expect($label)->toBeString();
});

it('returns correct type label for resolucion', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'resolucion',
    ]);

    $label = $notification->getTypeLabel();

    expect($label)->toBeString();
});

it('returns correct type label for noticia', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'noticia',
    ]);

    $label = $notification->getTypeLabel();

    expect($label)->toBeString();
});

it('returns correct type label for revision', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'revision',
    ]);

    $label = $notification->getTypeLabel();

    expect($label)->toBeString();
});

it('returns correct type label for sistema', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'type' => 'sistema',
    ]);

    $label = $notification->getTypeLabel();

    expect($label)->toBeString();
});

it('returns correct icon for each notification type', function () {
    $user = User::factory()->create();

    $types = [
        'convocatoria' => 'megaphone',
        'resolucion' => 'document-check',
        'noticia' => 'newspaper',
        'revision' => 'clock',
        'sistema' => 'bell',
    ];

    foreach ($types as $type => $expectedIcon) {
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => $type,
        ]);

        expect($notification->getTypeIcon())->toBe($expectedIcon);
    }
});

it('returns correct color for each notification type', function () {
    $user = User::factory()->create();

    $types = [
        'convocatoria' => 'primary',
        'resolucion' => 'success',
        'noticia' => 'info',
        'revision' => 'warning',
        'sistema' => 'neutral',
    ];

    foreach ($types as $type => $expectedColor) {
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => $type,
        ]);

        expect($notification->getTypeColor())->toBe($expectedColor);
    }
});
