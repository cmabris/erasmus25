<?php

describe('Responsive Image Component', function () {
    describe('Basic rendering', function () {
        it('renders an image with src attribute', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" alt="Test image" />');

            $view->assertSee('src="https://example.com/image.jpg"', false)
                ->assertSee('alt="Test image"', false)
                ->assertSee('loading="lazy"', false)
                ->assertSee('decoding="async"', false);
        });

        it('renders placeholder when no image provided', function () {
            $view = $this->blade('<x-ui.responsive-image alt="No image" />');

            $view->assertSee('bg-zinc-100')
                ->assertSee('data-flux-icon'); // Flux icon is rendered
        });

        it('does not render placeholder when placeholder is false', function () {
            $view = $this->blade('<x-ui.responsive-image :placeholder="false" />');

            $view->assertDontSee('bg-zinc-100');
        });

        it('renders custom placeholder icon', function () {
            $view = $this->blade('<x-ui.responsive-image placeholder-icon="newspaper" />');

            // Flux renders SVG directly, so we check for the icon container
            $view->assertSee('data-flux-icon')
                ->assertSee('bg-zinc-100');
        });
    });

    describe('Loading behavior', function () {
        it('uses lazy loading by default', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" />');

            $view->assertSee('loading="lazy"', false);
        });

        it('allows eager loading', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" loading="eager" />');

            $view->assertSee('loading="eager"', false);
        });

        it('includes decoding async attribute', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" />');

            $view->assertSee('decoding="async"', false);
        });
    });

    describe('Aspect ratio', function () {
        it('applies 16/9 aspect ratio', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" aspect-ratio="16/9" />');

            $view->assertSee('aspect-[16/9]');
        });

        it('applies 4/3 aspect ratio', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" aspect-ratio="4/3" />');

            $view->assertSee('aspect-[4/3]');
        });

        it('applies square aspect ratio', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" aspect-ratio="1/1" />');

            $view->assertSee('aspect-square');
        });

        it('applies custom aspect ratio', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" aspect-ratio="3/1" />');

            $view->assertSee('aspect-[3/1]');
        });
    });

    describe('Object fit', function () {
        it('uses object-cover by default', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" />');

            $view->assertSee('object-cover');
        });

        it('applies object-contain', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" object-fit="contain" />');

            $view->assertSee('object-contain');
        });

        it('applies object-fill', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" object-fit="fill" />');

            $view->assertSee('object-fill');
        });
    });

    describe('Custom classes', function () {
        it('applies container classes via class attribute', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" class="rounded-lg shadow-md" />');

            $view->assertSee('rounded-lg')
                ->assertSee('shadow-md');
        });

        it('applies image classes via img-class attribute', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" img-class="transition-transform hover:scale-105" />');

            $view->assertSee('transition-transform')
                ->assertSee('hover:scale-105');
        });
    });

    describe('Alt text', function () {
        it('renders provided alt text', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" alt="A beautiful landscape" />');

            $view->assertSee('alt="A beautiful landscape"', false);
        });

        it('renders empty alt for decorative images', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" alt="" />');

            $view->assertSee('alt=""', false);
        });
    });

    describe('Overflow handling', function () {
        it('has overflow-hidden class by default', function () {
            $view = $this->blade('<x-ui.responsive-image src="https://example.com/image.jpg" />');

            $view->assertSee('overflow-hidden');
        });
    });
});
