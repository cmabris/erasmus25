@props([
    'user',
    'size' => 'md', // xs, sm, md, lg, xl
    'showName' => false,
    'showEmail' => false,
])

@php
    $sizeClasses = match($size) {
        'xs' => 'size-6 text-xs',
        'sm' => 'size-8 text-sm',
        'lg' => 'size-16 text-xl',
        'xl' => 'size-20 text-2xl',
        default => 'size-12 text-base', // md
    };
    
    $initials = $user->initials();
    $name = $user->name ?? '';
    $email = $user->email ?? '';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <span class="relative flex {{ $sizeClasses }} shrink-0 overflow-hidden rounded-lg">
        <span class="flex h-full w-full items-center justify-center rounded-lg bg-erasmus-100 text-erasmus-800 dark:bg-erasmus-900/30 dark:text-erasmus-300 font-semibold">
            {{ $initials }}
        </span>
    </span>
    
    @if($showName || $showEmail)
        <div class="flex flex-col">
            @if($showName && $name)
                <span class="font-medium text-zinc-900 dark:text-white">{{ $name }}</span>
            @endif
            @if($showEmail && $email)
                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $email }}</span>
            @endif
        </div>
    @endif
</div>

