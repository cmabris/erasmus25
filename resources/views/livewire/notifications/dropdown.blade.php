@php
    $hasNotifications = $notifications->isNotEmpty();
    $hasUnread = $unreadCount > 0;
@endphp

<div 
    x-data="{ open: @entangle('isOpen') }"
    @click.away="open = false"
    @notification-read.window="loadNotifications()"
    @notifications-read.window="loadNotifications()"
    class="relative"
    wire:poll.30s="loadNotifications"
>
    {{-- Trigger - This will be replaced by the Bell component or can be used standalone --}}
    <slot name="trigger">
        <button
            type="button"
            @click="open = !open"
            class="relative flex h-10 w-10 items-center justify-center rounded-lg text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100 dark:focus:ring-offset-zinc-900"
            aria-label="{{ __('notifications.dropdown.label') }}"
            aria-expanded="false"
            aria-haspopup="true"
        >
            <flux:icon name="bell" class="size-5" variant="outline" />
            
            @if($hasUnread)
                <span class="absolute top-0 right-0 flex h-4 w-4 -translate-y-1 translate-x-1">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                </span>
            @endif
        </button>
    </slot>

    {{-- Dropdown Menu --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-zinc-800 dark:ring-zinc-700"
        role="menu"
        aria-orientation="vertical"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">
                {{ __('notifications.dropdown.title') }}
            </h3>
            @if($hasUnread)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                >
                    {{ __('notifications.dropdown.mark_all_read') }}
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="max-h-96 overflow-y-auto">
            @if($hasNotifications)
                <div class="py-1" role="none">
                    @foreach($notifications as $notification)
                        <div
                            class="group relative flex items-start gap-3 px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50"
                            role="menuitem"
                        >
                            {{-- Icon --}}
                            @php
                                $iconBgClasses = match($notification->getTypeColor()) {
                                    'primary' => 'bg-blue-100 dark:bg-blue-900/30',
                                    'success' => 'bg-green-100 dark:bg-green-900/30',
                                    'info' => 'bg-cyan-100 dark:bg-cyan-900/30',
                                    'warning' => 'bg-amber-100 dark:bg-amber-900/30',
                                    default => 'bg-zinc-100 dark:bg-zinc-700',
                                };
                                $iconTextClasses = match($notification->getTypeColor()) {
                                    'primary' => 'text-blue-600 dark:text-blue-400',
                                    'success' => 'text-green-600 dark:text-green-400',
                                    'info' => 'text-cyan-600 dark:text-cyan-400',
                                    'warning' => 'text-amber-600 dark:text-amber-400',
                                    default => 'text-zinc-600 dark:text-zinc-400',
                                };
                            @endphp
                            <div class="flex shrink-0 items-center">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $iconBgClasses }}">
                                    <flux:icon 
                                        name="{{ $notification->getTypeIcon() }}" 
                                        class="[:where(&)]:size-4 {{ $iconTextClasses }}" 
                                        variant="outline" 
                                    />
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                @if($notification->link)
                                    <a
                                        href="{{ $notification->link }}"
                                        wire:navigate
                                        @click="open = false"
                                        class="block"
                                    >
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $notification->title }}
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                            {{ $notification->message }}
                                        </p>
                                    </a>
                                @else
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $notification->title }}
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                        {{ $notification->message }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    type="button"
                                    wire:click="markAsRead({{ $notification->id }})"
                                    class="flex h-6 w-6 items-center justify-center rounded text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300"
                                    aria-label="{{ __('notifications.dropdown.mark_read') }}"
                                >
                                    <flux:icon name="check" class="[:where(&)]:size-4" variant="outline" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="py-8 text-center">
                    <flux:icon name="bell-slash" class="[:where(&)]:size-12 mx-auto text-zinc-400 dark:text-zinc-500" variant="outline" />
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('notifications.dropdown.empty') }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        @if($hasNotifications)
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <a
                    href="{{ route('notifications.index') }}"
                    wire:navigate
                    @click="open = false"
                    class="block text-center text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
                >
                    {{ __('notifications.dropdown.view_all') }}
                </a>
            </div>
        @endif
    </div>
</div>
