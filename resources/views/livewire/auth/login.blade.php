<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Login Links (Solo en desarrollo) -->
        @env('local')
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                <h3 class="mb-2 text-sm font-semibold text-amber-900 dark:text-amber-200">
                    {{ __('common.admin.dashboard.dev_login_links') }}
                </h3>
                <div class="flex flex-wrap gap-2 text-sm">
                    <x-login-link email="super-admin@erasmus-murcia.es" label="Super Admin" class="cursor-pointer text-amber-700 underline hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100" />
                    <x-login-link email="admin@erasmus-murcia.es" label="Admin" class="cursor-pointer text-amber-700 underline hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100" />
                    <x-login-link email="editor@erasmus-murcia.es" label="Editor" class="cursor-pointer text-amber-700 underline hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100" />
                    <x-login-link email="viewer@erasmus-murcia.es" label="Viewer" class="cursor-pointer text-amber-700 underline hover:text-amber-900 dark:text-amber-300 dark:hover:text-amber-100" />
                </div>
            </div>
        @endenv

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
