@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'siteName' => null,
    'locale' => null,
    'article' => null,
    'noindex' => false,
])

@php
    $title = $title ?? config('app.name');
    $description = $description ?? __('Portal de gestión de movilidades Erasmus+ para alumnado y personal docente. Descubre convocatorias, programas y oportunidades de movilidad internacional.');
    $url = $url ?? request()->url();
    $siteName = $siteName ?? config('app.name');
    $locale = $locale ?? app()->getLocale();
    
    // Default OG image - use app URL for absolute path
    $defaultImage = config('app.url') . '/images/og-default.jpg';
    $ogImage = $image ?: $defaultImage;
    
    // Ensure image URL is absolute
    if ($ogImage && !str_starts_with($ogImage, 'http')) {
        $ogImage = config('app.url') . $ogImage;
    }
@endphp

{{-- Basic Meta --}}
<meta name="description" content="{{ Str::limit(strip_tags($description), 160) }}">

{{-- Robots --}}
@if($noindex)
<meta name="robots" content="noindex, nofollow">
@endif

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $url }}">

{{-- Open Graph --}}
<meta property="og:title" content="{{ Str::limit($title, 70) }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}">
<meta property="og:type" content="{{ $type }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:alt" content="{{ Str::limit($title, 100) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif

{{-- Article specific (for news/blog posts) --}}
@if($type === 'article' && $article)
    @if(isset($article['published_time']))
<meta property="article:published_time" content="{{ $article['published_time'] }}">
    @endif
    @if(isset($article['modified_time']))
<meta property="article:modified_time" content="{{ $article['modified_time'] }}">
    @endif
    @if(isset($article['author']))
<meta property="article:author" content="{{ $article['author'] }}">
    @endif
    @if(isset($article['section']))
<meta property="article:section" content="{{ $article['section'] }}">
    @endif
    @if(isset($article['tags']) && is_array($article['tags']))
        @foreach($article['tags'] as $tag)
<meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif
@endif

{{-- Twitter Cards --}}
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ Str::limit($title, 70) }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($description), 200) }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ Str::limit($title, 100) }}">
@endif
