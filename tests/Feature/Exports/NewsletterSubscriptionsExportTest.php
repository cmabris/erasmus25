<?php

use App\Exports\NewsletterSubscriptionsExport;
use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('NewsletterSubscriptionsExport - Basic Export', function () {
    it('exports all subscriptions when no filters applied', function () {
        NewsletterSubscription::factory()->count(5)->create();

        $export = new NewsletterSubscriptionsExport([]);
        $collection = $export->collection();

        expect($collection)->toHaveCount(5);
    });

    it('has correct headings', function () {
        $export = new NewsletterSubscriptionsExport([]);
        $headings = $export->headings();

        expect($headings)->toBe([
            __('Email'),
            __('Nombre'),
            __('Programas'),
            __('Estado'),
            __('Verificado'),
            __('Fecha Suscripción'),
            __('Fecha Verificación'),
            __('Fecha Baja'),
        ]);
    });

    it('has correct sheet title', function () {
        $export = new NewsletterSubscriptionsExport([]);
        $title = $export->title();

        expect($title)->toBe(__('Suscripciones Newsletter'));
    });

    it('maps subscription data correctly', function () {
        $program = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Programa Test']);

        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'programs' => ['KA121-VET'],
            'is_active' => true,
            'verified_at' => now(),
            'subscribed_at' => now()->subDays(10),
            'unsubscribed_at' => null,
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[0])->toBe('test@example.com')
            ->and($mapped[1])->toBe('Test User')
            ->and($mapped[2])->toContain('Programa Test')
            ->and($mapped[3])->toBe(__('common.status.active'))
            ->and($mapped[4])->toBe(__('common.messages.yes'))
            ->and($mapped[5])->toBe($subscription->subscribed_at->format('d/m/Y H:i'))
            ->and($mapped[6])->toBe($subscription->verified_at->format('d/m/Y H:i'))
            ->and($mapped[7])->toBe('-');
    });

    it('handles null name correctly', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => null,
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[1])->toBe('-');
    });

    it('handles unverified subscriptions correctly', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verified_at' => null,
            'unsubscribed_at' => null,
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[4])->toBe(__('common.messages.no'))
            ->and($mapped[6])->toBe('-')
            ->and($mapped[7])->toBe('-');
    });

    it('formats programs correctly with names', function () {
        $program1 = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Programa 1']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED', 'name' => 'Programa 2']);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA121-VET', 'KA131-HED'],
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[2])->toContain('Programa 1')
            ->and($mapped[2])->toContain('Programa 2');
    });

    it('formats programs correctly with codes when not found', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['INVALID-CODE'],
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[2])->toBe('INVALID-CODE');
    });

    it('formats empty programs correctly', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => null,
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[2])->toBe('-');
    });
});

describe('NewsletterSubscriptionsExport - Filters', function () {
    it('applies program filter', function () {
        $program1 = Program::factory()->create(['code' => 'KA121-VET']);
        $program2 = Program::factory()->create(['code' => 'KA131-HED']);

        $sub1 = NewsletterSubscription::factory()->create(['programs' => ['KA121-VET']]);
        $sub2 = NewsletterSubscription::factory()->create(['programs' => ['KA131-HED']]);
        $sub3 = NewsletterSubscription::factory()->create(['programs' => ['KA121-VET', 'KA131-HED']]);

        $export = new NewsletterSubscriptionsExport(['filterProgram' => 'KA121-VET']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($sub1->email)
            ->and($emails)->toContain($sub3->email)
            ->and($emails)->not->toContain($sub2->email);
    });

    it('applies status filter (active)', function () {
        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        $export = new NewsletterSubscriptionsExport(['filterStatus' => 'activo']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($active->email)
            ->and($emails)->not->toContain($inactive->email);
    });

    it('applies status filter (inactive)', function () {
        $active = NewsletterSubscription::factory()->create(['is_active' => true]);
        $inactive = NewsletterSubscription::factory()->create(['is_active' => false]);

        $export = new NewsletterSubscriptionsExport(['filterStatus' => 'inactivo']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($inactive->email)
            ->and($emails)->not->toContain($active->email);
    });

    it('applies verification filter (verified)', function () {
        $verified = NewsletterSubscription::factory()->create(['verified_at' => now()]);
        $unverified = NewsletterSubscription::factory()->create(['verified_at' => null]);

        $export = new NewsletterSubscriptionsExport(['filterVerification' => 'verificado']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($verified->email)
            ->and($emails)->not->toContain($unverified->email);
    });

    it('applies verification filter (unverified)', function () {
        $verified = NewsletterSubscription::factory()->create(['verified_at' => now()]);
        $unverified = NewsletterSubscription::factory()->create(['verified_at' => null]);

        $export = new NewsletterSubscriptionsExport(['filterVerification' => 'no-verificado']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($unverified->email)
            ->and($emails)->not->toContain($verified->email);
    });

    it('applies search filter', function () {
        $sub1 = NewsletterSubscription::factory()->create(['email' => 'test1@example.com']);
        $sub2 = NewsletterSubscription::factory()->create(['email' => 'test2@example.com']);

        $export = new NewsletterSubscriptionsExport(['search' => 'test1']);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($sub1->email)
            ->and($emails)->not->toContain($sub2->email);
    });

    it('applies multiple filters together', function () {
        $program = Program::factory()->create(['code' => 'KA121-VET']);

        $sub1 = NewsletterSubscription::factory()->create([
            'email' => 'test1@example.com',
            'programs' => ['KA121-VET'],
            'is_active' => true,
        ]);
        $sub2 = NewsletterSubscription::factory()->create([
            'email' => 'test2@example.com',
            'programs' => ['KA121-VET'],
            'is_active' => false,
        ]);
        $sub3 = NewsletterSubscription::factory()->create([
            'email' => 'other@example.com',
            'programs' => ['KA131-HED'],
            'is_active' => true,
        ]);

        $export = new NewsletterSubscriptionsExport([
            'filterProgram' => 'KA121-VET',
            'filterStatus' => 'activo',
        ]);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails)->toContain($sub1->email)
            ->and($emails)->not->toContain($sub2->email)
            ->and($emails)->not->toContain($sub3->email);
    });

    it('applies sorting', function () {
        $sub1 = NewsletterSubscription::factory()->create(['email' => 'zeta@example.com']);
        $sub2 = NewsletterSubscription::factory()->create(['email' => 'alpha@example.com']);

        $export = new NewsletterSubscriptionsExport([
            'sortField' => 'email',
            'sortDirection' => 'asc',
        ]);
        $collection = $export->collection();

        $emails = $collection->pluck('email')->toArray();
        expect($emails[0])->toBe('alpha@example.com')
            ->and($emails[1])->toBe('zeta@example.com');
    });
});

describe('NewsletterSubscriptionsExport - Data Formatting', function () {
    it('formats dates correctly', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'subscribed_at' => now()->setDate(2024, 1, 15)->setTime(10, 30),
            'verified_at' => now()->setDate(2024, 1, 16)->setTime(11, 45),
            'unsubscribed_at' => now()->setDate(2024, 1, 20)->setTime(14, 20),
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[5])->toBe('15/01/2024 10:30')
            ->and($mapped[6])->toBe('16/01/2024 11:45')
            ->and($mapped[7])->toBe('20/01/2024 14:20');
    });

    it('formats mixed programs correctly', function () {
        $program = Program::factory()->create(['code' => 'KA121-VET', 'name' => 'Programa Existente']);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA121-VET', 'INVALID-CODE'],
        ]);

        $export = new NewsletterSubscriptionsExport([]);
        $mapped = $export->map($subscription);

        expect($mapped[2])->toContain('Programa Existente')
            ->and($mapped[2])->toContain('INVALID-CODE');
    });
});
