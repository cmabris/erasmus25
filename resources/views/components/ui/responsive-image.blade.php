@props([
    'media' => null,
    'src' => null,
    'alt' => '',
    'conversion' => null,
    'fallbackConversion' => null,
    'class' => '',
    'imgClass' => '',
    'aspectRatio' => null, // '16/9', '4/3', '1/1', 'auto'
    'loading' => 'lazy', // 'lazy', 'eager'
    'placeholder' => true, // Show placeholder when no image
    'placeholderIcon' => 'photo',
    'objectFit' => 'cover', // 'cover', 'contain', 'fill', 'none'
])

@php
    $imageUrl = null;
    $webpUrl = null;
    $originalUrl = null;
    
    // Get URLs from media object or direct src
    if ($media) {
        // Try to get WebP conversion first
        if ($conversion) {
            $imageUrl = $media->getUrl($conversion);
            // Check if original format fallback is needed
            $originalUrl = $fallbackConversion 
                ? $media->getUrl($fallbackConversion) 
                : $media->getUrl();
        } else {
            $imageUrl = $media->getUrl();
        }
        
        // Get alt from media custom properties if not provided
        if (empty($alt)) {
            $alt = $media->getCustomProperty('alt') ?? $media->name ?? '';
        }
    } elseif ($src) {
        $imageUrl = $src;
    }
    
    // Aspect ratio classes
    $aspectClasses = match($aspectRatio) {
        '16/9' => 'aspect-[16/9]',
        '16/10' => 'aspect-[16/10]',
        '4/3' => 'aspect-[4/3]',
        '3/2' => 'aspect-[3/2]',
        '1/1' => 'aspect-square',
        '21/9' => 'aspect-[21/9]',
        'auto' => '',
        default => $aspectRatio ? "aspect-[$aspectRatio]" : '',
    };
    
    // Object fit classes
    $objectFitClass = match($objectFit) {
        'contain' => 'object-contain',
        'fill' => 'object-fill',
        'none' => 'object-none',
        'scale-down' => 'object-scale-down',
        default => 'object-cover',
    };
    
    // Container classes
    $containerClasses = collect([
        'overflow-hidden',
        $aspectClasses,
        $class,
    ])->filter()->implode(' ');
    
    // Image classes
    $imageClasses = collect([
        'h-full w-full',
        $objectFitClass,
        $imgClass,
    ])->filter()->implode(' ');
@endphp

@if($imageUrl)
    <div {{ $attributes->merge(['class' => $containerClasses]) }}>
        {{-- If we have both WebP and fallback, use picture element --}}
        @if($originalUrl && $originalUrl !== $imageUrl)
            <picture>
                <source srcset="{{ $imageUrl }}" type="image/webp">
                <img 
                    src="{{ $originalUrl }}" 
                    alt="{{ $alt }}"
                    class="{{ $imageClasses }}"
                    loading="{{ $loading }}"
                    decoding="async"
                >
            </picture>
        @else
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $alt }}"
                class="{{ $imageClasses }}"
                loading="{{ $loading }}"
                decoding="async"
            >
        @endif
    </div>
@elseif($placeholder)
    {{-- Placeholder when no image --}}
    <div {{ $attributes->merge(['class' => "$containerClasses bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center"]) }}>
        <flux:icon 
            :name="$placeholderIcon" 
            class="[:where(&)]:size-12 text-zinc-300 dark:text-zinc-600" 
            variant="outline" 
        />
    </div>
@endif
