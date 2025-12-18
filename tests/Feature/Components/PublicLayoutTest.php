<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;

/*
|--------------------------------------------------------------------------
| Public Layout Tests
|--------------------------------------------------------------------------
*/

describe('Public Layout', function () {
    it('renders with basic content', function () {
        $html = Blade::render('<x-layouts.public>Page content</x-layouts.public>');

        expect($html)
            ->toContain('Page content')
            ->toContain('<!DOCTYPE html>')
            ->toContain('<html')
            ->toContain('<body')
            ->toContain('main');
    });

    it('includes meta viewport for responsive design', function () {
        $html = Blade::render('<x-layouts.public>Content</x-layouts.public>');

        expect($html)->toContain('viewport');
    });

    it('includes skip to content link for accessibility', function () {
        $html = Blade::render('<x-layouts.public>Content</x-layouts.public>');

        expect($html)
            ->toContain('Saltar al contenido')
            ->toContain('#main-content');
    });

    it('renders with custom title', function () {
        $html = Blade::render('<x-layouts.public title="Custom Title">Content</x-layouts.public>');

        expect($html)->toContain('Custom Title');
    });

    it('renders with meta description', function () {
        $html = Blade::render('<x-layouts.public description="Page description">Content</x-layouts.public>');

        expect($html)->toContain('meta name="description"');
    });
});

/*
|--------------------------------------------------------------------------
| Public Navigation Tests
|--------------------------------------------------------------------------
*/

describe('Public Navigation', function () {
    it('renders navigation component', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)
            ->toContain('<header')
            ->toContain('<nav')
            ->toContain('Erasmus+');
    });

    it('renders logo with link to home', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)
            ->toContain('E+')
            ->toContain('Centro Murcia');
    });

    it('renders navigation items', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)
            ->toContain('Inicio')
            ->toContain('Programas')
            ->toContain('Convocatorias')
            ->toContain('Noticias')
            ->toContain('Documentos')
            ->toContain('Calendario');
    });

    it('renders login link for guests', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)->toContain('Iniciar sesión');
    });

    it('renders panel link for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $html = Blade::render('<x-nav.public-nav />');

        expect($html)
            ->toContain('Panel')
            ->not->toContain('Iniciar sesión');
    });

    it('renders mobile menu button', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)
            ->toContain('Abrir menú')
            ->toContain('lg:hidden');
    });

    it('renders with transparent variant', function () {
        $html = Blade::render('<x-nav.public-nav :transparent="true" />');

        expect($html)->toContain('bg-transparent');
    });

    it('has sticky positioning', function () {
        $html = Blade::render('<x-nav.public-nav />');

        expect($html)->toContain('sticky');
    });
});

/*
|--------------------------------------------------------------------------
| Footer Tests
|--------------------------------------------------------------------------
*/

describe('Footer', function () {
    it('renders full footer by default', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('<footer')
            ->toContain('Erasmus+')
            ->toContain('Centro Murcia');
    });

    it('renders program links', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('Educación Escolar')
            ->toContain('Formación Profesional')
            ->toContain('Educación Superior');
    });

    it('renders resource links', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('Convocatorias')
            ->toContain('Documentos')
            ->toContain('Noticias')
            ->toContain('Calendario');
    });

    it('renders contact information', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('Región de Murcia')
            ->toContain('erasmus@centro.edu');
    });

    it('renders EU cofinancing notice', function () {
        $html = Blade::render('<x-footer />');

        expect($html)->toContain('Cofinanciado por la Unión Europea');
    });

    it('renders social links', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('aria-label="Twitter"')
            ->toContain('aria-label="Facebook"')
            ->toContain('aria-label="Instagram"')
            ->toContain('aria-label="YouTube"');
    });

    it('renders copyright with current year', function () {
        $html = Blade::render('<x-footer />');

        expect($html)->toContain('© '.date('Y'));
    });

    it('renders legal links', function () {
        $html = Blade::render('<x-footer />');

        expect($html)
            ->toContain('Privacidad')
            ->toContain('Términos')
            ->toContain('Accesibilidad')
            ->toContain('Cookies');
    });

    it('renders simple footer variant', function () {
        $html = Blade::render('<x-footer :simple="true" />');

        expect($html)
            ->toContain('©')
            ->not->toContain('Educación Escolar'); // Program links not shown in simple
    });
});
