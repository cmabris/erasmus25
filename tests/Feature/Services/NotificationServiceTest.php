<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\NewsPost;
use App\Models\Notification;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    $this->service = app(NotificationService::class);
    $this->user = User::factory()->create();
    $this->program = Program::factory()->create(['is_active' => true]);
    $this->academicYear = AcademicYear::factory()->create();
});

describe('NotificationService - Create Notification', function () {
    it('creates a notification with all required fields', function () {
        $notification = $this->service->create([
            'user_id' => $this->user->id,
            'type' => 'sistema',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'link' => 'https://example.com',
        ]);

        expect($notification)
            ->toBeInstanceOf(Notification::class)
            ->and($notification->user_id)->toBe($this->user->id)
            ->and($notification->type)->toBe('sistema')
            ->and($notification->title)->toBe('Test Notification')
            ->and($notification->message)->toBe('This is a test notification')
            ->and($notification->link)->toBe('https://example.com')
            ->and($notification->is_read)->toBeFalse()
            ->and($notification->read_at)->toBeNull();
    });

    it('creates a notification without link', function () {
        $notification = $this->service->create([
            'user_id' => $this->user->id,
            'type' => 'sistema',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
        ]);

        expect($notification->link)->toBeNull();
    });

    it('creates notification with is_read set to false by default', function () {
        $notification = $this->service->create([
            'user_id' => $this->user->id,
            'type' => 'sistema',
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
        ]);

        expect($notification->is_read)->toBeFalse()
            ->and($notification->read_at)->toBeNull();
    });
});

describe('NotificationService - Notify Convocatoria Published', function () {
    it('creates notifications for all users when a call is published', function () {
        $users = User::factory()->count(3)->create();
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Test Call',
        ]);
        $call->load('program');

        $initialCount = Notification::where('type', 'convocatoria')->count();
        $this->service->notifyConvocatoriaPublished($call, $users);

        expect(Notification::where('type', 'convocatoria')->count())->toBe($initialCount + 3)
            ->and(Notification::where('user_id', $users->first()->id)->where('type', 'convocatoria')->exists())->toBeTrue()
            ->and(Notification::where('user_id', $users->last()->id)->where('type', 'convocatoria')->exists())->toBeTrue();
    });

    it('creates notification with correct type and content for call', function () {
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Convocatoria de Movilidad',
        ]);
        $call->load('program');

        $this->service->notifyConvocatoriaPublished($call, $this->user);

        $notification = Notification::where('user_id', $this->user->id)->first();

        expect($notification)
            ->not->toBeNull()
            ->and($notification->type)->toBe('convocatoria')
            ->and($notification->title)->toContain('Convocatoria de Movilidad')
            ->and($notification->message)->toContain('Convocatoria de Movilidad')
            ->and($notification->link)->toContain('convocatorias');
    });

    it('handles single user input', function () {
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Unique Call Title',
        ]);
        $call->load('program');

        $initialCount = Notification::where('user_id', $this->user->id)
            ->where('type', 'convocatoria')
            ->count();
        $this->service->notifyConvocatoriaPublished($call, $this->user);

        expect(Notification::where('user_id', $this->user->id)
            ->where('type', 'convocatoria')
            ->count())->toBe($initialCount + 1);
    });
});

describe('NotificationService - Notify Resolucion Published', function () {
    it('creates notifications for all users when a resolution is published', function () {
        $users = User::factory()->count(2)->create();
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'title' => 'Resolución Provisional',
        ]);
        $resolution->load('call');

        $initialCount = Notification::where('type', 'resolucion')->count();
        $this->service->notifyResolucionPublished($resolution, $users);

        expect(Notification::where('type', 'resolucion')->count())->toBe($initialCount + 2);
    });

    it('creates notification with correct type and content for resolution', function () {
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Convocatoria Test',
        ]);
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'title' => 'Resolución Provisional Unique',
        ]);
        $resolution->load('call');

        $this->service->notifyResolucionPublished($resolution, $this->user);

        $notification = Notification::where('user_id', $this->user->id)
            ->where('type', 'resolucion')
            ->where('title', 'like', '%Resolución Provisional Unique%')
            ->first();

        expect($notification)
            ->not->toBeNull()
            ->and($notification->type)->toBe('resolucion')
            ->and($notification->title)->toContain('Resolución Provisional Unique')
            ->and($notification->message)->toContain('Resolución Provisional Unique')
            ->and($notification->link)->toContain('resoluciones');
    });

    it('creates notification even when resolution call is soft deleted', function () {
        $call = Call::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'title' => 'Resolución Con Call Eliminado',
        ]);

        // Soft delete the call - the resolution still has call_id but call is deleted
        $call->delete();
        $resolution->refresh();
        $resolution->load('call'); // Load the soft-deleted call

        $this->service->notifyResolucionPublished($resolution, $this->user);

        $notification = Notification::where('user_id', $this->user->id)
            ->where('type', 'resolucion')
            ->where('title', 'like', '%Resolución Con Call Eliminado%')
            ->first();

        // The service will still create a link because the call exists (soft deleted)
        // This is expected behavior - the link will work if the call is restored
        expect($notification)
            ->not->toBeNull()
            ->and($notification->type)->toBe('resolucion');
    });
});

describe('NotificationService - Notify Noticia Published', function () {
    it('creates notifications for all users when a news post is published', function () {
        $users = User::factory()->count(2)->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Nueva Noticia',
            'excerpt' => 'Resumen de la noticia',
        ]);

        $initialCount = Notification::where('type', 'noticia')->count();
        $this->service->notifyNoticiaPublished($newsPost, $users);

        expect(Notification::where('type', 'noticia')->count())->toBe($initialCount + 2);
    });

    it('creates notification with correct type and content for news post', function () {
        $newsPost = NewsPost::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Nueva Noticia Importante',
            'excerpt' => 'Este es el resumen de la noticia',
        ]);

        $this->service->notifyNoticiaPublished($newsPost, $this->user);

        $notification = Notification::where('user_id', $this->user->id)->first();

        expect($notification)
            ->not->toBeNull()
            ->and($notification->type)->toBe('noticia')
            ->and($notification->title)->toContain('Nueva Noticia Importante')
            ->and($notification->message)->toContain('Nueva Noticia Importante')
            ->and($notification->link)->toContain('noticias');
    });

    it('handles news post without excerpt', function () {
        $newsPost = NewsPost::factory()->create([
            'program_id' => $this->program->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Noticia Sin Resumen',
            'excerpt' => null,
        ]);

        $this->service->notifyNoticiaPublished($newsPost, $this->user);

        $notification = Notification::where('user_id', $this->user->id)->first();

        expect($notification)
            ->not->toBeNull()
            ->and($notification->message)->toContain('Noticia Sin Resumen');
    });
});

describe('NotificationService - Notify Documento Published', function () {
    it('creates notifications for all users when a document is published', function () {
        $users = User::factory()->count(2)->create();
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => $this->program->id,
            'title' => 'Nuevo Documento',
            'document_type' => 'guia',
        ]);

        // Count only notifications created by this test (filter by document title in message)
        $initialCount = Notification::where('type', 'sistema')
            ->where('message', 'like', '%Nuevo Documento%')
            ->count();
        $this->service->notifyDocumentoPublished($document, $users);

        expect(Notification::where('type', 'sistema')
            ->where('message', 'like', '%Nuevo Documento%')
            ->count())->toBe($initialCount + 2);
    });

    it('creates notification with correct type and content for document', function () {
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => $this->program->id,
            'title' => 'Guía de Movilidad',
            'document_type' => 'guia',
        ]);

        $this->service->notifyDocumentoPublished($document, $this->user);

        $notification = Notification::where('user_id', $this->user->id)->first();

        expect($notification)
            ->not->toBeNull()
            ->and($notification->type)->toBe('sistema')
            ->and($notification->title)->toContain('Guía de Movilidad')
            ->and($notification->message)->toContain('Guía de Movilidad')
            ->and($notification->link)->toContain('documentos');
    });
});

describe('NotificationService - Mark As Read', function () {
    it('marks a notification as read', function () {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $this->service->markAsRead($notification);

        $notification->refresh();

        expect($notification->is_read)->toBeTrue()
            ->and($notification->read_at)->not->toBeNull();
    });

    it('does not update read_at if notification is already read', function () {
        $readAt = now()->subHour();
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => true,
            'read_at' => $readAt,
        ]);

        $this->service->markAsRead($notification);

        $notification->refresh();

        expect($notification->read_at->format('Y-m-d H:i:s'))
            ->toBe($readAt->format('Y-m-d H:i:s'));
    });

    it('updates read_at timestamp when marking as read', function () {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        $beforeRead = now()->subSecond();
        $this->service->markAsRead($notification);
        $afterRead = now()->addSecond();

        $notification->refresh();

        expect($notification->read_at)
            ->not->toBeNull()
            ->and($notification->read_at->greaterThanOrEqualTo($beforeRead))->toBeTrue()
            ->and($notification->read_at->lessThanOrEqualTo($afterRead))->toBeTrue();
    });
});

describe('NotificationService - Mark All As Read', function () {
    it('marks all unread notifications as read for a user', function () {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->create([
            'user_id' => $this->user->id,
        ]);

        $this->service->markAllAsRead($this->user);

        expect(Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count())->toBe(0)
            ->and(Notification::where('user_id', $this->user->id)
                ->where('is_read', true)->count())->toBe(4);
    });

    it('only marks notifications for the specified user', function () {
        $otherUser = User::factory()->create();
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        $this->service->markAllAsRead($this->user);

        expect(Notification::where('user_id', $this->user->id)
            ->where('is_read', false)->count())->toBe(0)
            ->and(Notification::where('user_id', $otherUser->id)
                ->where('is_read', false)->count())->toBe(2);
    });

    it('sets read_at timestamp for all marked notifications', function () {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        $this->service->markAllAsRead($this->user);

        $notifications = Notification::where('user_id', $this->user->id)->get();

        foreach ($notifications as $notification) {
            expect($notification->read_at)->not->toBeNull();
        }
    });
});

describe('NotificationService - Get Unread Count', function () {
    it('returns correct count of unread notifications', function () {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->read()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $count = $this->service->getUnreadCount($this->user);

        expect($count)->toBe(3);
    });

    it('returns zero when user has no unread notifications', function () {
        Notification::factory()->read()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        $count = $this->service->getUnreadCount($this->user);

        expect($count)->toBe(0);
    });

    it('returns zero when user has no notifications', function () {
        $count = $this->service->getUnreadCount($this->user);

        expect($count)->toBe(0);
    });

    it('only counts notifications for the specified user', function () {
        $otherUser = User::factory()->create();
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(3)->create([
            'user_id' => $otherUser->id,
            'is_read' => false,
        ]);

        $count = $this->service->getUnreadCount($this->user);

        expect($count)->toBe(2);
    });
});

describe('NotificationService - Create And Broadcast', function () {
    it('creates a notification and prepares for broadcasting', function () {
        $notification = $this->service->createAndBroadcast([
            'user_id' => $this->user->id,
            'type' => 'sistema',
            'title' => 'Test Broadcast',
            'message' => 'This is a test broadcast notification',
        ]);

        expect($notification)
            ->toBeInstanceOf(Notification::class)
            ->and($notification->user_id)->toBe($this->user->id)
            ->and($notification->type)->toBe('sistema')
            ->and(Notification::find($notification->id))->not->toBeNull();
    });
});
