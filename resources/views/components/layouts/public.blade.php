@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'article' => null,
    'jsonLd' => null,
    'noindex' => false,
    'transparentNav' => false,
    'simpleFooter' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head', ['title' => $title])
        
        {{-- SEO Meta Tags --}}
        <x-seo.meta 
            :title="$title"
            :description="$description"
            :image="$image"
            :type="$type"
            :article="$article"
            :noindex="$noindex"
        />
        
        {{-- JSON-LD Structured Data --}}
        @if($jsonLd)
            <x-seo.json-ld :data="$jsonLd" />
        @endif
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
        {{-- Skip to content link for accessibility --}}
        <a 
            href="#main-content" 
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-erasmus-600 focus:px-4 focus:py-2 focus:text-white focus:outline-none"
        >
            {{ __('Saltar al contenido') }}
        </a>

        {{-- Navigation --}}
        <x-nav.public-nav :transparent="$transparentNav" />

        {{-- Main Content --}}
        <main id="main-content" class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <x-footer :simple="$simpleFooter" />

        @fluxScripts
    </body>
</html>
