<?php

/**
 * Note: robots.txt is a static file in public/ directory.
 * These tests verify the file content directly instead of via HTTP
 * because Laravel's test environment doesn't serve static files.
 */
beforeEach(function () {
    $this->robotsPath = public_path('robots.txt');
    $this->robotsContent = file_get_contents($this->robotsPath);
});

it('robots.txt file exists', function () {
    expect(file_exists($this->robotsPath))->toBeTrue();
});

it('includes sitemap reference', function () {
    expect($this->robotsContent)
        ->toContain('Sitemap:')
        ->toContain('/sitemap.xml');
});

it('allows all user agents', function () {
    expect($this->robotsContent)
        ->toContain('User-agent: *')
        ->toContain('Allow: /');
});

it('disallows admin routes', function () {
    expect($this->robotsContent)
        ->toContain('Disallow: /admin');
});

it('disallows auth routes', function () {
    expect($this->robotsContent)
        ->toContain('Disallow: /login')
        ->toContain('Disallow: /register')
        ->toContain('Disallow: /password');
});

it('disallows settings routes', function () {
    expect($this->robotsContent)
        ->toContain('Disallow: /settings')
        ->toContain('Disallow: /dashboard');
});

it('disallows livewire internal routes', function () {
    expect($this->robotsContent)
        ->toContain('Disallow: /livewire/');
});

it('allows newsletter subscribe page', function () {
    expect($this->robotsContent)
        ->toContain('Allow: /newsletter/suscribir');
});

it('disallows newsletter verification and unsubscribe tokens', function () {
    expect($this->robotsContent)
        ->toContain('Disallow: /newsletter/verificar/')
        ->toContain('Disallow: /newsletter/baja/');
});
