<?php

use Illuminate\Support\Facades\Blade;

/*
|--------------------------------------------------------------------------
| UI Card Component Tests
|--------------------------------------------------------------------------
*/

describe('Card Component', function () {
    it('renders with default variant', function () {
        $html = Blade::render('<x-ui.card>Card content</x-ui.card>');

        expect($html)
            ->toContain('Card content')
            ->toContain('bg-white')
            ->toContain('rounded-lg');
    });

    it('renders with elevated variant', function () {
        $html = Blade::render('<x-ui.card variant="elevated">Content</x-ui.card>');

        expect($html)
            ->toContain('shadow-lg')
            ->toContain('Content');
    });

    it('renders with bordered variant', function () {
        $html = Blade::render('<x-ui.card variant="bordered">Content</x-ui.card>');

        expect($html)
            ->toContain('border')
            ->toContain('border-zinc-200');
    });

    it('renders with flat variant', function () {
        $html = Blade::render('<x-ui.card variant="flat">Content</x-ui.card>');

        expect($html)
            ->toContain('bg-zinc-50');
    });

    it('renders with different padding sizes', function () {
        $html = Blade::render('<x-ui.card padding="lg">Content</x-ui.card>');

        expect($html)->toContain('p-6');
    });

    it('renders as link when href is provided', function () {
        $html = Blade::render('<x-ui.card href="/test">Link card</x-ui.card>');

        expect($html)
            ->toContain('<a')
            ->toContain('href="/test"')
            ->toContain('wire:navigate');
    });

    it('renders header slot', function () {
        $html = Blade::render('
            <x-ui.card>
                <x-slot:header>Header Content</x-slot:header>
                Body Content
            </x-ui.card>
        ');

        expect($html)
            ->toContain('Header Content')
            ->toContain('Body Content');
    });

    it('renders footer slot', function () {
        $html = Blade::render('
            <x-ui.card>
                Body Content
                <x-slot:footer>Footer Content</x-slot:footer>
            </x-ui.card>
        ');

        expect($html)
            ->toContain('Footer Content')
            ->toContain('border-t');
    });

    it('applies hover classes when hover is enabled', function () {
        $html = Blade::render('<x-ui.card :hover="true">Content</x-ui.card>');

        expect($html)->toContain('hover:shadow-md');
    });
});

/*
|--------------------------------------------------------------------------
| UI Badge Component Tests
|--------------------------------------------------------------------------
*/

describe('Badge Component', function () {
    it('renders with default neutral color', function () {
        $html = Blade::render('<x-ui.badge>Badge text</x-ui.badge>');

        expect($html)
            ->toContain('Badge text')
            ->toContain('bg-zinc-100');
    });

    it('renders with primary color', function () {
        $html = Blade::render('<x-ui.badge color="primary">Primary</x-ui.badge>');

        expect($html)
            ->toContain('bg-blue-100')
            ->toContain('text-blue-800');
    });

    it('renders with success color', function () {
        $html = Blade::render('<x-ui.badge color="success">Success</x-ui.badge>');

        expect($html)->toContain('bg-green-100');
    });

    it('renders with warning color', function () {
        $html = Blade::render('<x-ui.badge color="warning">Warning</x-ui.badge>');

        expect($html)->toContain('bg-amber-100');
    });

    it('renders with danger color', function () {
        $html = Blade::render('<x-ui.badge color="danger">Danger</x-ui.badge>');

        expect($html)->toContain('bg-red-100');
    });

    it('renders with info color', function () {
        $html = Blade::render('<x-ui.badge color="info">Info</x-ui.badge>');

        expect($html)->toContain('bg-cyan-100');
    });

    it('renders with outline variant', function () {
        $html = Blade::render('<x-ui.badge color="primary" :outline="true">Outline</x-ui.badge>');

        expect($html)
            ->toContain('border')
            ->toContain('border-blue-300');
    });

    it('renders with different sizes', function () {
        $htmlXs = Blade::render('<x-ui.badge size="xs">XS</x-ui.badge>');
        $htmlLg = Blade::render('<x-ui.badge size="lg">LG</x-ui.badge>');

        expect($htmlXs)->toContain('text-[10px]');
        expect($htmlLg)->toContain('text-sm');
    });

    it('renders with dot', function () {
        $html = Blade::render('<x-ui.badge :dot="true" color="success">With dot</x-ui.badge>');

        expect($html)
            ->toContain('rounded-full')
            ->toContain('bg-green-500');
    });
});

/*
|--------------------------------------------------------------------------
| UI Button Component Tests
|--------------------------------------------------------------------------
*/

describe('Button Component', function () {
    it('renders with default primary variant', function () {
        $html = Blade::render('<x-ui.button>Click me</x-ui.button>');

        expect($html)
            ->toContain('Click me')
            ->toContain('bg-blue-600')
            ->toContain('text-white');
    });

    it('renders with secondary variant', function () {
        $html = Blade::render('<x-ui.button variant="secondary">Secondary</x-ui.button>');

        expect($html)->toContain('bg-zinc-100');
    });

    it('renders with outline variant', function () {
        $html = Blade::render('<x-ui.button variant="outline">Outline</x-ui.button>');

        expect($html)
            ->toContain('border')
            ->toContain('border-zinc-300');
    });

    it('renders with ghost variant', function () {
        $html = Blade::render('<x-ui.button variant="ghost">Ghost</x-ui.button>');

        expect($html)->toContain('hover:bg-zinc-100');
    });

    it('renders with danger variant', function () {
        $html = Blade::render('<x-ui.button variant="danger">Delete</x-ui.button>');

        expect($html)->toContain('bg-red-600');
    });

    it('renders with success variant', function () {
        $html = Blade::render('<x-ui.button variant="success">Save</x-ui.button>');

        expect($html)->toContain('bg-green-600');
    });

    it('renders as link when href is provided', function () {
        $html = Blade::render('<x-ui.button href="/test">Link Button</x-ui.button>');

        expect($html)
            ->toContain('<a')
            ->toContain('href="/test"')
            ->toContain('wire:navigate');
    });

    it('renders with different sizes', function () {
        $htmlSm = Blade::render('<x-ui.button size="sm">Small</x-ui.button>');
        $htmlLg = Blade::render('<x-ui.button size="lg">Large</x-ui.button>');

        expect($htmlSm)->toContain('text-sm');
        expect($htmlLg)->toContain('text-base');
    });

    it('renders with loading state', function () {
        $html = Blade::render('<x-ui.button :loading="true">Loading</x-ui.button>');

        expect($html)
            ->toContain('animate-spin')
            ->toContain('disabled');
    });

    it('renders with full width', function () {
        $html = Blade::render('<x-ui.button :fullWidth="true">Full Width</x-ui.button>');

        expect($html)->toContain('w-full');
    });
});

/*
|--------------------------------------------------------------------------
| UI Section Component Tests
|--------------------------------------------------------------------------
*/

describe('Section Component', function () {
    it('renders with basic content', function () {
        $html = Blade::render('<x-ui.section>Section content</x-ui.section>');

        expect($html)
            ->toContain('Section content')
            ->toContain('<section')
            ->toContain('max-w-7xl');
    });

    it('renders with title', function () {
        $html = Blade::render('<x-ui.section title="Section Title">Content</x-ui.section>');

        expect($html)
            ->toContain('Section Title')
            ->toContain('<h2');
    });

    it('renders with title and description', function () {
        $html = Blade::render('<x-ui.section title="Title" description="Description text">Content</x-ui.section>');

        expect($html)
            ->toContain('Title')
            ->toContain('Description text');
    });

    it('renders with actions slot', function () {
        $html = Blade::render('
            <x-ui.section title="Title">
                <x-slot:actions>Action Button</x-slot:actions>
                Content
            </x-ui.section>
        ');

        expect($html)->toContain('Action Button');
    });

    it('renders centered', function () {
        $html = Blade::render('<x-ui.section title="Centered" :centered="true">Content</x-ui.section>');

        expect($html)->toContain('text-center');
    });

    it('renders with divider', function () {
        $html = Blade::render('<x-ui.section :divided="true">Content</x-ui.section>');

        expect($html)->toContain('border-t');
    });

    it('renders with different padding sizes', function () {
        $htmlSm = Blade::render('<x-ui.section padding="sm">Content</x-ui.section>');
        $htmlXl = Blade::render('<x-ui.section padding="xl">Content</x-ui.section>');

        expect($htmlSm)->toContain('py-6');
        expect($htmlXl)->toContain('py-16');
    });
});

/*
|--------------------------------------------------------------------------
| UI Stat Card Component Tests
|--------------------------------------------------------------------------
*/

describe('Stat Card Component', function () {
    it('renders with label and value', function () {
        $html = Blade::render('<x-ui.stat-card label="Total Users" value="1,234" />');

        expect($html)
            ->toContain('Total Users')
            ->toContain('1,234');
    });

    it('renders with icon', function () {
        $html = Blade::render('<x-ui.stat-card label="Users" value="100" icon="users" />');

        expect($html)
            ->toContain('Users')
            ->toContain('100');
    });

    it('renders with trend up', function () {
        $html = Blade::render('<x-ui.stat-card label="Sales" value="$5,000" trend="up" trendValue="+12%" />');

        expect($html)
            ->toContain('+12%')
            ->toContain('text-green-600');
    });

    it('renders with trend down', function () {
        $html = Blade::render('<x-ui.stat-card label="Bounces" value="23%" trend="down" trendValue="-5%" />');

        expect($html)
            ->toContain('-5%')
            ->toContain('text-red-600');
    });

    it('renders with description', function () {
        $html = Blade::render('<x-ui.stat-card label="Revenue" value="$10K" description="Last 30 days" />');

        expect($html)->toContain('Last 30 days');
    });

    it('renders with different colors', function () {
        $htmlSuccess = Blade::render('<x-ui.stat-card label="Success" value="100" icon="check" color="success" />');
        $htmlDanger = Blade::render('<x-ui.stat-card label="Danger" value="5" icon="x-mark" color="danger" />');

        expect($htmlSuccess)->toContain('bg-green-100');
        expect($htmlDanger)->toContain('bg-red-100');
    });

    it('renders with footer slot', function () {
        $html = Blade::render('
            <x-ui.stat-card label="Users" value="100">
                <x-slot:footer>View all users</x-slot:footer>
            </x-ui.stat-card>
        ');

        expect($html)->toContain('View all users');
    });
});

/*
|--------------------------------------------------------------------------
| UI Empty State Component Tests
|--------------------------------------------------------------------------
*/

describe('Empty State Component', function () {
    it('renders with default title', function () {
        $html = Blade::render('<x-ui.empty-state />');

        expect($html)
            ->toContain('No hay datos')
            ->toContain('text-center');
    });

    it('renders with custom title', function () {
        $html = Blade::render('<x-ui.empty-state title="No results found" />');

        expect($html)->toContain('No results found');
    });

    it('renders with description', function () {
        $html = Blade::render('<x-ui.empty-state title="Empty" description="Try adjusting your filters" />');

        expect($html)->toContain('Try adjusting your filters');
    });

    it('renders with action button', function () {
        $html = Blade::render('<x-ui.empty-state title="No items" action="Add Item" actionHref="/items/create" />');

        expect($html)
            ->toContain('Add Item')
            ->toContain('href="/items/create"');
    });

    it('renders with different sizes', function () {
        $htmlSm = Blade::render('<x-ui.empty-state size="sm" />');
        $htmlLg = Blade::render('<x-ui.empty-state size="lg" />');

        expect($htmlSm)->toContain('py-6');
        expect($htmlLg)->toContain('py-16');
    });

    it('renders with custom slot content', function () {
        $html = Blade::render('
            <x-ui.empty-state title="Custom">
                <p>Custom slot content</p>
            </x-ui.empty-state>
        ');

        expect($html)->toContain('Custom slot content');
    });
});
