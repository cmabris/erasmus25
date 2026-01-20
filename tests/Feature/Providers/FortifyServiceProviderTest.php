<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Fortify;

uses(RefreshDatabase::class);

describe('FortifyServiceProvider - Rate Limiting', function () {
    it('registers two-factor rate limiter', function () {
        // Check that the 'two-factor' limiter is registered
        $limiter = RateLimiter::limiter('two-factor');

        expect($limiter)->toBeCallable();
    });

    it('two-factor limiter returns Limit with 5 per minute', function () {
        // Create a mock request with a session
        $request = Request::create('/two-factor-challenge', 'POST');
        $request->setLaravelSession(app('session.store'));
        $request->session()->put('login.id', 123);

        $limiter = RateLimiter::limiter('two-factor');
        $limit = $limiter($request);

        expect($limit)->toBeInstanceOf(Limit::class);
        expect($limit->maxAttempts)->toBe(5);
    });

    it('registers login rate limiter', function () {
        $limiter = RateLimiter::limiter('login');

        expect($limiter)->toBeCallable();
    });

    it('login limiter returns Limit with 5 per minute', function () {
        $request = Request::create('/login', 'POST', [
            Fortify::username() => 'test@example.com',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $limiter = RateLimiter::limiter('login');
        $limit = $limiter($request);

        expect($limit)->toBeInstanceOf(Limit::class);
        expect($limit->maxAttempts)->toBe(5);
    });

    it('login limiter uses email and IP for throttle key', function () {
        $email = 'Test@Example.COM'; // Mixed case
        $ip = '192.168.1.100';

        $request = Request::create('/login', 'POST', [
            Fortify::username() => $email,
        ]);
        $request->server->set('REMOTE_ADDR', $ip);

        $limiter = RateLimiter::limiter('login');
        $limit = $limiter($request);

        // The key should be lowercase transliterated email|ip
        expect($limit->key)->toBe('test@example.com|192.168.1.100');
    });

    it('login limiter handles special characters in email', function () {
        // Test with accented characters that should be transliterated
        $email = 'tëst@éxample.com';
        $ip = '10.0.0.1';

        $request = Request::create('/login', 'POST', [
            Fortify::username() => $email,
        ]);
        $request->server->set('REMOTE_ADDR', $ip);

        $limiter = RateLimiter::limiter('login');
        $limit = $limiter($request);

        // Transliterated and lowercased
        expect($limit)->toBeInstanceOf(Limit::class);
        expect($limit->key)->toContain('|10.0.0.1');
    });
});

describe('FortifyServiceProvider - Views are registered', function () {
    it('login route returns the correct view', function () {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('livewire.auth.login');
    });

    it('register route returns the correct view', function () {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('livewire.auth.register');
    });

    it('forgot password route returns the correct view', function () {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertViewIs('livewire.auth.forgot-password');
    });
});
