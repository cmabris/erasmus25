@props([
    'items' => [], // Array of ['label' => 'Text', 'href' => '/url', 'icon' => 'icon-name']
    'separator' => 'chevron-right', // chevron-right, slash, arrow-right
    'homeIcon' => true, // Show home icon for first item
])

@php
    $separatorIcon = match($separator) {
        'slash' => 'minus',
        'arrow-right' => 'arrow-right',
        default => 'chevron-right',
    };
@endphp

<nav {{ $attributes->merge(['class' => 'flex', 'aria-label' => __('Breadcrumb')]) }}>
    <ol role="list" class="flex flex-wrap items-center gap-1 text-sm">
        {{-- Home link (always first) --}}
        <li>
            <a 
                href="{{ route('home') }}" 
                wire:navigate
                class="flex items-center gap-1 text-zinc-500 transition-colors hover:text-erasmus-600 dark:text-zinc-400 dark:hover:text-erasmus-400"
            >
                @if($homeIcon)
                    <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                    <span class="sr-only">{{ __('Inicio') }}</span>
                @else
                    <span>{{ __('Inicio') }}</span>
                @endif
            </a>
        </li>

        @foreach($items as $index => $item)
            {{-- Separator --}}
            <li class="flex items-center" aria-hidden="true">
                @if($separator === 'slash')
                    <span class="mx-1 text-zinc-300 dark:text-zinc-600">/</span>
                @else
                    <flux:icon :name="$separatorIcon" class="[:where(&)]:size-4 mx-0.5 text-zinc-400 dark:text-zinc-500" variant="outline" />
                @endif
            </li>

            {{-- Breadcrumb item --}}
            <li>
                @if($loop->last)
                    {{-- Current page (not a link) --}}
                    <span 
                        class="flex items-center gap-1.5 font-medium text-zinc-900 dark:text-white"
                        aria-current="page"
                    >
                        @if(isset($item['icon']))
                            <flux:icon :name="$item['icon']" class="[:where(&)]:size-4" variant="outline" />
                        @endif
                        {{ $item['label'] }}
                    </span>
                @else
                    {{-- Link to parent --}}
                    <a 
                        href="{{ $item['href'] ?? '#' }}"
                        wire:navigate
                        class="flex items-center gap-1.5 text-zinc-500 transition-colors hover:text-erasmus-600 dark:text-zinc-400 dark:hover:text-erasmus-400"
                    >
                        @if(isset($item['icon']))
                            <flux:icon :name="$item['icon']" class="[:where(&)]:size-4" variant="outline" />
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
