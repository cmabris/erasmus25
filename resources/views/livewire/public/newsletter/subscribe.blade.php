<div>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-erasmus-600 via-erasmus-700 to-erasmus-900">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="newsletter-hero-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="currentColor" />
                    </pattern>
                </defs>
                <rect fill="url(#newsletter-hero-pattern)" width="100%" height="100%" />
            </svg>
        </div>
        
        {{-- EU Stars decoration --}}
        <div class="absolute right-0 top-0 -translate-y-1/4 translate-x-1/4 opacity-5">
            <svg class="h-96 w-96" viewBox="0 0 100 100">
                @for($i = 0; $i < 12; $i++)
                    <polygon 
                        points="50,15 53,35 75,35 57,47 63,67 50,55 37,67 43,47 25,35 47,35" 
                        fill="currentColor" 
                        transform="rotate({{ $i * 30 }} 50 50) translate(0 -30)"
                    />
                @endfor
            </svg>
        </div>
        
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            {{-- Breadcrumbs --}}
            <div class="mb-8">
                <x-ui.breadcrumbs 
                    :items="[
                        ['label' => __('common.newsletter.title'), 'href' => route('newsletter.subscribe')],
                    ]" 
                    class="text-white/60 [&_a:hover]:text-white [&_a]:text-white/60 [&_span]:text-white"
                />
            </div>
            
            <div class="max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm">
                    <flux:icon name="envelope" class="[:where(&)]:size-5" variant="outline" />
                    {{ __('common.newsletter.title') }}
                </div>
                
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('common.newsletter.stay_informed') }}
                </h1>
                
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-erasmus-100 sm:text-xl">
                    {{ __('common.newsletter.subscribe_description') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Subscription Form Section --}}
    <x-ui.section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mx-auto max-w-2xl">
            {{-- Success Message --}}
            @if(session('newsletter-subscribed') || $subscribed)
                <div class="mb-8 rounded-lg border border-green-200 bg-green-50 p-6 dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex items-start gap-4">
                        <flux:icon name="check-circle" class="[:where(&)]:size-6 flex-shrink-0 text-green-600 dark:text-green-400" variant="solid" />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">
                                {{ __('common.newsletter.subscription_success') }}
                            </h3>
                            <p class="mt-2 text-sm text-green-800 dark:text-green-200">
                                {{ __('common.newsletter.verification_email_sent') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Subscription Form --}}
            @if(!$subscribed && !session('newsletter-subscribed'))
                <x-ui.card class="p-6 sm:p-8">
                    <form wire:submit="subscribe" class="space-y-6">
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

                        {{-- Name Field (Optional) --}}
                        <div>
                            <flux:input
                                wire:model="name"
                                :label="__('common.newsletter.name_optional')"
                                type="text"
                                autocomplete="name"
                                placeholder="{{ __('common.newsletter.your_name') }}"
                                :error="$errors->first('name')"
                            />
                            @error('name')
                                <flux:text class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </flux:text>
                            @enderror
                        </div>

                        {{-- Programs Selection --}}
                        @if($this->availablePrograms->isNotEmpty())
                            <div>
                                <label class="mb-3 block text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ __('common.newsletter.programs_interest') }}
                                </label>
                                <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ __('common.newsletter.select_programs') }}
                                </p>
                                
                                <div class="space-y-3">
                                    @foreach($this->availablePrograms as $program)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-zinc-200 p-4 transition-colors hover:border-erasmus-300 hover:bg-erasmus-50 dark:border-zinc-700 dark:hover:border-erasmus-600 dark:hover:bg-erasmus-900/10">
                                            <input 
                                                type="checkbox"
                                                wire:click="toggleProgram('{{ $program->code }}')"
                                                @if($this->isProgramSelected($program->code)) checked @endif
                                                class="mt-0.5 size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                            />
                                            <div class="flex-1">
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $program->name }}
                                                </div>
                                                @if($program->code)
                                                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ $program->code }}
                                                    </div>
                                                @endif
                                                @if($program->description)
                                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ \Illuminate\Support\Str::limit($program->description, 100) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                
                                @error('programs.*')
                                    <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </flux:text>
                                @enderror
                            </div>
                        @endif

                        {{-- Privacy Policy Acceptance --}}
                        <div>
                            <label class="flex cursor-pointer items-start gap-3">
                                <input 
                                    type="checkbox"
                                    wire:model="acceptPrivacy"
                                    class="mt-0.5 size-4 rounded border-zinc-300 text-erasmus-600 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-700"
                                />
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                    {{ __('common.newsletter.accept_privacy') }}
                                    <a href="#" class="font-medium text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300">
                                        {{ __('common.newsletter.privacy_policy') }}
                                    </a>
                                    {{ __('common.newsletter.accept_data_processing') }}
                                </span>
                            </label>
                            @error('acceptPrivacy')
                                <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </flux:text>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <flux:button 
                                variant="primary" 
                                type="submit" 
                                class="w-full sm:w-auto"
                                :disabled="$errors->any()"
                            >
                                <flux:icon name="envelope" class="[:where(&)]:size-4" variant="outline" />
                                {{ __('common.newsletter.subscribe') }}
                            </flux:button>
                        </div>
                    </form>
                </x-ui.card>
            @endif

            {{-- Additional Information --}}
            <div class="mt-8 rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('common.newsletter.what_will_receive') }}
                </h3>
                <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
                    <li class="flex items-start gap-3">
                        <flux:icon name="check-circle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-erasmus-600" variant="solid" />
                        <span>{{ __('common.newsletter.receive_calls') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon name="check-circle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-erasmus-600" variant="solid" />
                        <span>{{ __('common.newsletter.receive_news') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon name="check-circle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-erasmus-600" variant="solid" />
                        <span>{{ __('common.newsletter.receive_events') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon name="check-circle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-erasmus-600" variant="solid" />
                        <span>{{ __('common.newsletter.receive_resolutions') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon name="check-circle" class="[:where(&)]:size-5 mt-0.5 flex-shrink-0 text-erasmus-600" variant="solid" />
                        <span>{{ __('common.newsletter.unsubscribe_anytime') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </x-ui.section>
</div>

