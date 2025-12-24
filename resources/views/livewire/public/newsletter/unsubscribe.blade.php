<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="unsubscribe-hero-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#unsubscribe-hero-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            <div class="max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="envelope" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('common.newsletter.cancel_subscription_title') }}
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('common.newsletter.cancel_subscription') }}
                </h1>
                
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('common.newsletter.sorry_unsubscribe') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Unsubscription Form/Result Section --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mx-auto max-w-2xl">
            <x-ui.card class="p-6 sm:p-8">
                {{-- Success Status --}}
                @if($status === 'success')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <flux:icon name="check-circle" class="[:where(&)]:size-12 text-green-600 dark:text-green-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.newsletter.subscription_cancelled') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        @if($subscription)
                            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <div class="mb-2 font-medium text-zinc-900 dark:text-white">
                                        {{ __('common.newsletter.cancelled_details') }}
                                    </div>
                                    <div class="space-y-1">
                                        <div>
                                            <span class="font-medium">{{ __('common.newsletter.email_label') }}</span>
                                            <span>{{ $subscription->email }}</span>
                                        </div>
                                        @if($subscription->name)
                                            <div>
                                                <span class="font-medium">{{ __('common.newsletter.name_label') }}</span>
                                                <span>{{ $subscription->name }}</span>
                                            </div>
                                        @endif
                                        @if($subscription->unsubscribed_at)
                                            <div>
                                                <span class="font-medium">{{ __('common.newsletter.cancellation_date') }}</span>
                                                <span>{{ $subscription->unsubscribed_at->translatedFormat('d F Y, H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                {{ __('common.newsletter.change_mind') }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <x-ui.button 
                                href="{{ route('home') }}" 
                                variant="primary"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.go_home') }}
                            </x-ui.button>
                            
                            <x-ui.button 
                                href="{{ route('newsletter.subscribe') }}" 
                                variant="outline"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="envelope" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.resubscribe') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Already Unsubscribed Status --}}
                @if($status === 'already_unsubscribed')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <flux:icon name="information-circle" class="[:where(&)]:size-12 text-blue-600 dark:text-blue-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.newsletter.already_cancelled') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        @if($subscription && $subscription->unsubscribed_at)
                            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <div>
                                        <span class="font-medium">{{ __('common.newsletter.cancellation_date') }}</span>
                                        <span>{{ $subscription->unsubscribed_at->translatedFormat('d F Y, H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <x-ui.button 
                                href="{{ route('home') }}" 
                                variant="primary"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.go_home') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Not Found Status --}}
                @if($status === 'not_found')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <flux:icon name="x-circle" class="[:where(&)]:size-12 text-red-600 dark:text-red-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.newsletter.not_found') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('common.newsletter.not_found_message') }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <x-ui.button 
                                href="{{ route('newsletter.subscribe') }}" 
                                variant="primary"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="envelope" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.subscribe') }}
                            </x-ui.button>
                            
                            <x-ui.button 
                                href="{{ route('home') }}" 
                                variant="outline"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.go_home') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Error Status --}}
                @if($status === 'error')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <flux:icon name="exclamation-triangle" class="[:where(&)]:size-12 text-red-600 dark:text-red-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.newsletter.error_cancelling') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                            <x-ui.button 
                                href="{{ route('home') }}" 
                                variant="primary"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.go_home') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Unsubscription Form (only show if no status) --}}
                @if(!$status && !$token)
                    <div>
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('common.newsletter.cancel_your_subscription') }}
                        </h2>
                        
                        <p class="mb-6 text-zinc-600 dark:text-zinc-400">
                            {{ __('common.newsletter.enter_email_to_cancel') }}
                        </p>

                        <form wire:submit="unsubscribeByEmail" class="space-y-6">
                            {{-- Email Field --}}
                            <div>
                                <flux:input
                                    wire:model="email"
                                    :label="__('common.newsletter.email')"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    placeholder="tu@email.com"
                                    :error="$errors->first('email')"
                                />
                                @error('email')
                                    <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </flux:text>
                                @enderror
                            </div>

                            {{-- Warning Message --}}
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                                <div class="flex items-start gap-3">
                                    <flux:icon name="exclamation-triangle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400" variant="solid" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-amber-900 dark:text-amber-100">
                                            {{ __('common.newsletter.are_you_sure') }}
                                        </p>
                                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">
                                            {{ __('common.newsletter.cancel_warning') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="flex items-center justify-end gap-4 pt-4">
                                <x-ui.button 
                                    href="{{ route('home') }}" 
                                    variant="ghost"
                                    size="lg"
                                    navigate
                                >
                                    {{ __('common.newsletter.cancel_action') }}
                                </x-ui.button>
                                
                                <x-ui.button 
                                    variant="danger" 
                                    type="submit" 
                                    class="w-full sm:w-auto"
                                    icon="x-mark"
                                >
                                    {{ __('common.newsletter.cancel_subscription') }}
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </x-ui.section>
</div>

