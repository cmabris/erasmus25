<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="squares-2x2" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>

                @can('viewAny', \App\Models\Program::class)
                    <flux:navlist.group :heading="__('common.admin.nav.content')" class="grid">
                        <flux:navlist.item icon="academic-cap" :href="route('admin.programs.index')" :current="request()->routeIs('admin.programs.*')" wire:navigate>{{ __('common.nav.programs') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endcan

                @can('viewAny', \App\Models\AcademicYear::class)
                    <flux:navlist.group :heading="__('common.admin.nav.management')" class="grid">
                        <flux:navlist.item icon="calendar" :href="route('admin.academic-years.index')" :current="request()->routeIs('admin.academic-years.*')" wire:navigate>{{ __('common.nav.academic_years') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endcan

                @can('viewAny', \App\Models\User::class)
                    <flux:navlist.group :heading="__('common.admin.nav.system')" class="grid">
                        <flux:navlist.item icon="user-group" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('common.nav.users') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endcan

                @can('viewAny', \App\Models\Call::class)
                    <flux:navlist.group :heading="__('common.admin.nav.content')" class="grid">
                        <flux:navlist.item icon="document-text" :href="route('admin.calls.index')" :current="request()->routeIs('admin.calls.*')" wire:navigate>{{ __('common.nav.calls') }}</flux:navlist.item>
                    </flux:navlist.group>
                @endcan

                @can('viewAny', \App\Models\NewsPost::class)
                    <flux:navlist.group :heading="__('common.admin.nav.content')" class="grid">
                        <flux:navlist.item icon="newspaper" :href="route('admin.news.index')" :current="request()->routeIs('admin.news.*')" wire:navigate>{{ __('common.nav.news') }}</flux:navlist.item>
                        @can('viewAny', \App\Models\NewsTag::class)
                            <flux:navlist.item icon="tag" :href="route('admin.news-tags.index')" :current="request()->routeIs('admin.news-tags.*')" wire:navigate>{{ __('common.nav.news_tags') }}</flux:navlist.item>
                        @endcan
                        @can('viewAny', \App\Models\Document::class)
                            <flux:navlist.item icon="document" :href="route('admin.documents.index')" :current="request()->routeIs('admin.documents.*')" wire:navigate>{{ __('common.nav.documents') }}</flux:navlist.item>
                        @endcan
                        @can('viewAny', \App\Models\DocumentCategory::class)
                            <flux:navlist.item icon="folder" :href="route('admin.document-categories.index')" :current="request()->routeIs('admin.document-categories.*')" wire:navigate>{{ __('common.nav.document_categories') }}</flux:navlist.item>
                        @endcan
                        @can('viewAny', \App\Models\ErasmusEvent::class)
                            <flux:navlist.item icon="calendar" :href="route('admin.events.index')" :current="request()->routeIs('admin.events.*')" wire:navigate>{{ __('common.nav.events') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist.group>
                @endcan
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
