<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('notifications.title') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('notifications.description') }}
                </p>
            </div>
            @if($this->unreadCount > 0)
                <flux:button 
                    wire:click="markAllAsRead"
                    variant="primary"
                    icon="check"
                    wire:loading.attr="disabled"
                    wire:target="markAllAsRead"
                >
                    <span wire:loading.remove wire:target="markAllAsRead">
                        {{ __('notifications.actions.mark_all_read') }}
                    </span>
                    <span wire:loading wire:target="markAllAsRead">
                        {{ __('notifications.actions.marking') }}
                    </span>
                </flux:button>
            @endif
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('notifications.title'), 'icon' => 'bell'],
            ]"
        />
    </div>

    {{-- Filters --}}
    <div class="mb-6 animate-fade-in" style="animation-delay: 0.1s;">
        <x-ui.card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Filter by Status --}}
                <div class="flex-1">
                    <flux:field>
                        <flux:label>{{ __('notifications.filters.status') }}</flux:label>
                        <select 
                            wire:model.live="filter" 
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="all">{{ __('notifications.filters.all') }}</option>
                            <option value="unread">{{ __('notifications.filters.unread') }}</option>
                            <option value="read">{{ __('notifications.filters.read') }}</option>
                        </select>
                    </flux:field>
                </div>

                {{-- Filter by Type --}}
                <div class="flex-1">
                    <flux:field>
                        <flux:label>{{ __('notifications.filters.type') }}</flux:label>
                        <select 
                            wire:model.live="filterType" 
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-erasmus-500 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            @foreach($this->availableTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </flux:field>
                </div>

                {{-- Reset Filters --}}
                <div>
                    <flux:button 
                        variant="ghost" 
                        wire:click="resetFilters"
                        icon="arrow-path"
                    >
                        {{ __('common.actions.reset') }}
                    </flux:button>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Loading State --}}
    <div wire:loading.delay wire:target="filter,filterType,markAsRead,markAllAsRead,delete,markSelectedAsRead,deleteSelected" class="mb-6">
        <div class="flex items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-400">
                <flux:icon name="arrow-path" class="[:where(&)]:size-5 animate-spin" variant="outline" />
                <span class="text-sm font-medium">{{ __('Cargando...') }}</span>
            </div>
        </div>
    </div>

    {{-- Notifications List --}}
    <div wire:loading.remove.delay wire:target="filter,filterType,markAsRead,markAllAsRead,delete,markSelectedAsRead,deleteSelected" class="animate-fade-in" style="animation-delay: 0.2s;">
        @if($this->notifications->isEmpty())
            <x-ui.empty-state 
                :title="__('notifications.empty.title')"
                :description="$filter !== 'all' || $filterType
                    ? __('notifications.empty.filtered') 
                    : __('notifications.empty.no_notifications')"
                icon="bell-slash"
            >
                @if($filter !== 'all' || $filterType)
                    <x-ui.button wire:click="resetFilters" variant="outline" icon="arrow-path">
                        {{ __('common.filters.clear') }}
                    </x-ui.button>
                @endif
            </x-ui.empty-state>
        @else
            {{-- Batch Actions Bar --}}
            @if($this->selectedCount > 0)
                <div class="mb-4 flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ trans_choice('notifications.batch.selected_count', $this->selectedCount, ['count' => $this->selectedCount]) }}
                        </span>
                        <flux:button 
                            wire:click="clearSelection"
                            variant="ghost" 
                            size="sm"
                            icon="x-mark"
                        >
                            {{ __('notifications.batch.clear') }}
                        </flux:button>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button 
                            wire:click="markSelectedAsRead"
                            variant="primary"
                            size="sm"
                            icon="check"
                            wire:loading.attr="disabled"
                            wire:target="markSelectedAsRead"
                        >
                            <span wire:loading.remove wire:target="markSelectedAsRead">
                                {{ __('notifications.batch.mark_read') }}
                            </span>
                            <span wire:loading wire:target="markSelectedAsRead">
                                {{ __('notifications.actions.marking') }}
                            </span>
                        </flux:button>
                        <flux:button 
                            wire:click="deleteSelected"
                            wire:confirm="{{ __('notifications.batch.delete_confirm', ['count' => $this->selectedCount]) }}"
                            variant="danger"
                            size="sm"
                            icon="trash"
                            wire:loading.attr="disabled"
                            wire:target="deleteSelected"
                        >
                            <span wire:loading.remove wire:target="deleteSelected">
                                {{ __('notifications.batch.delete') }}
                            </span>
                            <span wire:loading wire:target="deleteSelected">
                                {{ __('notifications.actions.deleting') }}
                            </span>
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- Select All Checkbox --}}
            <div class="mb-4 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <label class="flex cursor-pointer items-center gap-3">
                    <input 
                        type="checkbox"
                        wire:model.live="selectAll"
                        wire:change="toggleSelectAll"
                        class="size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                    />
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('notifications.batch.select_all') }}
                    </span>
                </label>
            </div>

            <div class="space-y-4">
                @foreach($this->notifications as $notification)
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
                    @php
                        $isSelected = in_array($notification->id, $this->selectedNotifications);
                    @endphp
                    <x-ui.card 
                        :variant="$notification->is_read ? 'bordered' : 'default'"
                        :class="collect([
                            $notification->is_read ? 'opacity-75' : '',
                            $isSelected ? 'ring-2 ring-blue-500 dark:ring-blue-400 bg-blue-50/50 dark:bg-blue-900/10' : '',
                        ])->filter()->implode(' ')"
                    >
                        <div class="flex items-start gap-4">
                            {{-- Checkbox for batch selection --}}
                            <div class="flex shrink-0 items-center pt-1">
                                <label class="cursor-pointer">
                                    <input 
                                        type="checkbox"
                                        wire:model.live="selectedNotifications"
                                        value="{{ $notification->id }}"
                                        class="size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                    />
                                </label>
                            </div>

                            {{-- Icon --}}
                            <div class="flex shrink-0 items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $iconBgClasses }}">
                                    <flux:icon 
                                        name="{{ $notification->getTypeIcon() }}" 
                                        class="[:where(&)]:size-5 {{ $iconTextClasses }}" 
                                        variant="outline" 
                                    />
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        @if($notification->link)
                                            <a
                                                href="{{ $notification->link }}"
                                                wire:navigate
                                                class="block"
                                            >
                                                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                                                    {{ $notification->title }}
                                                </h3>
                                            </a>
                                        @else
                                            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                                                {{ $notification->title }}
                                            </h3>
                                        @endif
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-4 text-xs text-zinc-400 dark:text-zinc-500">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            <x-ui.badge 
                                                :color="$notification->getTypeColor()" 
                                                size="sm"
                                            >
                                                {{ $notification->getTypeLabel() }}
                                            </x-ui.badge>
                                            @if(!$notification->is_read)
                                                <x-ui.badge color="primary" size="sm">
                                                    {{ __('notifications.unread') }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex shrink-0 items-center gap-2">
                                        @if(!$notification->is_read)
                                            <flux:button 
                                                wire:click="markAsRead({{ $notification->id }})"
                                                variant="ghost" 
                                                size="sm"
                                                icon="check"
                                                :label="__('notifications.actions.mark_read')"
                                                wire:loading.attr="disabled"
                                                wire:target="markAsRead({{ $notification->id }})"
                                            />
                                        @endif
                                        <flux:button 
                                            wire:click="confirmDelete({{ $notification->id }})"
                                            variant="ghost" 
                                            size="sm"
                                            icon="trash"
                                            :label="__('common.actions.delete')"
                                            wire:loading.attr="disabled"
                                            wire:target="confirmDelete({{ $notification->id }})"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($this->notifications->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $this->notifications->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-notification" wire:model.self="showDeleteModal">
        <form wire:submit="delete" class="space-y-4">
            <flux:heading>{{ __('notifications.delete.title') }}</flux:heading>
            <flux:text>
                {{ __('notifications.delete.message') }}
                @if($notificationToDelete)
                    @php
                        $notification = \App\Models\Notification::find($notificationToDelete);
                    @endphp
                    <br>
                    <strong>{{ $notification?->title }}</strong>
                @endif
            </flux:text>
            <div class="flex justify-end gap-2 mt-6">
                <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('common.actions.cancel') }}
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="danger"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                >
                    <span wire:loading.remove wire:target="delete">
                        {{ __('common.actions.delete') }}
                    </span>
                    <span wire:loading wire:target="delete">
                        {{ __('notifications.actions.deleting') }}
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
