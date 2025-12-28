@props([
    'event' => null,
    'variant' => 'success', // success, error, warning, info
    'duration' => 5000, // milliseconds
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left
])

@php
    $positionClasses = match($position) {
        'top-right' => 'top-4 right-4',
        'top-left' => 'top-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        default => 'top-4 right-4',
    };

    $variantConfig = match($variant) {
        'success' => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'border' => 'border-green-200 dark:border-green-800',
            'text' => 'text-green-800 dark:text-green-200',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-600 dark:text-green-400',
        ],
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'text' => 'text-red-800 dark:text-red-200',
            'icon' => 'x-circle',
            'iconColor' => 'text-red-600 dark:text-red-400',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'text' => 'text-yellow-800 dark:text-yellow-200',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-yellow-600 dark:text-yellow-400',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'text' => 'text-blue-800 dark:text-blue-200',
            'icon' => 'information-circle',
            'iconColor' => 'text-blue-600 dark:text-blue-400',
        ],
        default => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'border' => 'border-green-200 dark:border-green-800',
            'text' => 'text-green-800 dark:text-green-200',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-600 dark:text-green-400',
        ],
    };
@endphp

<div
    x-data="{
        shown: false,
        message: '',
        timeout: null,
        show(message) {
            this.message = message;
            this.shown = true;
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.shown = false;
            }, {{ $duration }});
        },
        hide() {
            this.shown = false;
            clearTimeout(this.timeout);
        }
    }"
    @if($event)
        x-init="@this.on('{{ $event }}', (event) => {
            $data.show(event.message || event[0]?.message || '{{ __('Operación realizada correctamente') }}');
        })"
    @endif
    x-show="shown"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="fixed {{ $positionClasses }} z-50 max-w-sm w-full"
    style="display: none;"
    role="alert"
    aria-live="polite"
    {{ $attributes }}
>
    <div class="rounded-lg border {{ $variantConfig['border'] }} {{ $variantConfig['bg'] }} p-4 shadow-lg">
        <div class="flex items-start gap-3">
            <flux:icon 
                name="{{ $variantConfig['icon'] }}" 
                class="[:where(&)]:size-5 flex-shrink-0 {{ $variantConfig['iconColor'] }}" 
                variant="solid" 
            />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium {{ $variantConfig['text'] }}" x-text="message"></p>
            </div>
            <button
                @click="hide()"
                class="flex-shrink-0 rounded p-1 transition-colors hover:bg-black/5 dark:hover:bg-white/5"
                aria-label="{{ __('Cerrar') }}"
            >
                <flux:icon name="x-mark" class="[:where(&)]:size-4 {{ $variantConfig['text'] }}" variant="outline" />
            </button>
        </div>
    </div>
</div>

