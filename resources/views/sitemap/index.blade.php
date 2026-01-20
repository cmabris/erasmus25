<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {{-- Static pages --}}
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('programas.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('convocatorias.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('noticias.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('documentos.index') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('eventos.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('calendario') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc>{{ route('search') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>

    {{-- Programs --}}
    @foreach($programs as $program)
    <url>
        <loc>{{ route('programas.show', $program) }}</loc>
        <lastmod>{{ $program->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Calls --}}
    @foreach($calls as $call)
    <url>
        <loc>{{ route('convocatorias.show', $call) }}</loc>
        <lastmod>{{ $call->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>{{ $call->status === 'abierta' ? '0.9' : '0.7' }}</priority>
    </url>
    @endforeach

    {{-- News --}}
    @foreach($news as $newsPost)
    <url>
        <loc>{{ route('noticias.show', $newsPost) }}</loc>
        <lastmod>{{ $newsPost->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Documents --}}
    @foreach($documents as $document)
    <url>
        <loc>{{ route('documentos.show', $document) }}</loc>
        <lastmod>{{ $document->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    {{-- Events --}}
    @foreach($events as $event)
    <url>
        <loc>{{ route('eventos.show', $event) }}</loc>
        <lastmod>{{ $event->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
