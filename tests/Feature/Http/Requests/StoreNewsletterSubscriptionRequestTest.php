<?php

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

describe('StoreNewsletterSubscriptionRequest - Authorization', function () {
    it('allows any user to create subscription (public endpoint)', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);

        expect($request->authorize())->toBeTrue();
    });

    it('allows unauthenticated user to create subscription', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeTrue();
    });
});

describe('StoreNewsletterSubscriptionRequest - Validation Rules', function () {
    it('validates email is required', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email format', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'invalid-email',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email max length', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => str_repeat('a', 250) . '@example.com', // Más de 255 caracteres
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email uniqueness', function () {
        NewsletterSubscription::factory()->create(['email' => 'existing@example.com']);

        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'existing@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates name is nullable', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'name' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates name max length', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'name' => str_repeat('a', 256),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates programs is nullable', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'programs' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates programs is array', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'programs' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('programs'))->toBeTrue();
    });

    it('validates programs.* exists in programs table by code', function () {
        $program = Program::factory()->create(['code' => 'KA131-HED']);

        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'programs' => ['KA131-HED', 'INVALID-CODE'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('programs.1'))->toBeTrue();
    });

    it('accepts valid program codes', function () {
        $program1 = Program::factory()->create(['code' => 'KA131-HED']);
        $program2 = Program::factory()->create(['code' => 'KA121-VET']);

        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'programs' => ['KA131-HED', 'KA121-VET'],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts valid email with optional fields', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'programs' => [],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts valid email without optional fields', function () {
        $request = StoreNewsletterSubscriptionRequest::create('/newsletter/subscribe', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});
