<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="verify-hero-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#verify-hero-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            <div class="max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="envelope" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('Verificación de Suscripción') }}
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('Verifica tu suscripción') }}
                </h1>
            </div>
        </div>
    </section>

    {{-- Verification Result Section --}}
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
                            {{ __('¡Verificación exitosa!') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        @if($subscription)
                            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    <div class="mb-2 font-medium text-zinc-900 dark:text-white">
                                        {{ __('Detalles de tu suscripción:') }}
                                    </div>
                                    <div class="space-y-1">
                                        <div>
                                            <span class="font-medium">{{ __('Email:') }}</span>
                                            <span>{{ $subscription->email }}</span>
                                        </div>
                                        @if($subscription->name)
                                            <div>
                                                <span class="font-medium">{{ __('Nombre:') }}</span>
                                                <span>{{ $subscription->name }}</span>
                                            </div>
                                        @endif
                                        @if($subscription->programs && count($subscription->programs) > 0)
                                            <div>
                                                <span class="font-medium">{{ __('Programas de interés:') }}</span>
                                                <span>{{ implode(', ', $subscription->programs) }}</span>
                                            </div>
                                        @endif
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
                                {{ __('Ir a la página principal') }}
                            </x-ui.button>
                            
                            <x-ui.button 
                                href="{{ route('noticias.index') }}" 
                                variant="outline"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="newspaper" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('Ver noticias') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Already Verified Status --}}
                @if($status === 'already_verified')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <flux:icon name="information-circle" class="[:where(&)]:size-12 text-blue-600 dark:text-blue-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('Suscripción ya verificada') }}
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
                                {{ __('Ir a la página principal') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif

                {{-- Invalid Token Status --}}
                @if($status === 'invalid')
                    <div class="text-center">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <flux:icon name="x-circle" class="[:where(&)]:size-12 text-red-600 dark:text-red-400" variant="solid" />
                        </div>
                        
                        <h2 class="mb-4 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('Token inválido') }}
                        </h2>
                        
                        <p class="mb-6 text-lg text-zinc-600 dark:text-zinc-400">
                            {{ $message }}
                        </p>

                        <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Si necesitas verificar tu suscripción, por favor utiliza el enlace que recibiste por correo electrónico. Si el problema persiste, puedes suscribirte nuevamente.') }}
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
                                {{ __('Suscribirse nuevamente') }}
                            </x-ui.button>
                            
                            <x-ui.button 
                                href="{{ route('home') }}" 
                                variant="outline"
                                size="lg"
                                navigate
                            >
                                <flux:icon name="home" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('Ir a la página principal') }}
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
                            {{ __('Error al verificar') }}
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
                                {{ __('Ir a la página principal') }}
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </x-ui.section>
</div>

