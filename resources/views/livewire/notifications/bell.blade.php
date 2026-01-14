@php
    $hasNotifications = $unreadCount > 0;
@endphp

<div wire:poll.30s="loadUnreadCount" class="relative">
    <flux:tooltip :content="__('notifications.bell.tooltip')" position="bottom">
        <a
            href="{{ route('notifications.index') }}"
            wire:navigate
            class="relative flex h-10 w-10 items-center justify-center rounded-lg text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 dark:focus:ring-offset-zinc-900"
            aria-label="{{ __('notifications.bell.label') }}"
        >
            <flux:icon name="bell" class="size-5" variant="outline" />
            
            @if($hasNotifications)
                <span class="absolute top-0 right-0 flex h-4 w-4 -translate-y-1 translate-x-1">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                </span>
            @endif
        </a>
    </flux:tooltip>
</div>
