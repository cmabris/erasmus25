@php
    $currentLang = $this->currentLanguage;
    $languages = $this->availableLanguages;
    
    // Clases según tamaño
    $sizeClasses = match($size) {
        'sm' => 'text-xs px-2 py-1',
        'lg' => 'text-base px-4 py-2',
        default => 'text-sm px-3 py-1.5', // md
    };
    
    // Clases para el botón principal
    $buttonClasses = "inline-flex items-center gap-2 rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {$sizeClasses}";
    
    // Variantes de estilo
    $variantClasses = match($variant) {
        'buttons' => 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 focus:ring-zinc-500',
        'select' => 'bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-700 focus:ring-zinc-500',
        default => 'bg-white text-zinc-700 hover:bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 focus:ring-zinc-500 border border-zinc-300 dark:border-zinc-600', // dropdown
    };
@endphp

<div 
    x-data="{ open: false }"
    class="relative"
    wire:key="language-switcher-{{ $variant }}-{{ $size }}"
>
    @if($variant === 'dropdown')
        {{-- Dropdown Variant --}}
        <button
            type="button"
            @click="open = !open"
            @click.away="open = false"
            class="{{ $buttonClasses }} {{ $variantClasses }}"
            aria-label="{{ __('Cambiar idioma') }}"
            aria-expanded="false"
            aria-haspopup="true"
        >
            <flux:icon name="globe-alt" class="[:where(&)]:size-4" variant="outline" />
            <span class="hidden sm:inline">{{ $currentLang?->name ?? __('Idioma') }}</span>
            <span class="sm:hidden">{{ strtoupper($this->currentLanguageCode) }}</span>
            <flux:icon 
                name="chevron-down" 
                class="[:where(&)]:size-4 transition-transform duration-200" 
                x-bind:class="{ 'rotate-180': open }" 
                variant="outline" 
            />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
            class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-zinc-800 dark:ring-zinc-700"
            role="menu"
            aria-orientation="vertical"
        >
            <div class="py-1" role="none">
                @foreach($languages as $language)
                    <button
                        type="button"
                        wire:click="switchLanguage('{{ $language->code }}')"
                        @click="open = false"
                        class="flex w-full items-center gap-3 px-4 py-2 text-sm transition-colors {{ $language->code === $this->currentLanguageCode ? 'bg-erasmus-50 text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                        role="menuitem"
                    >
                        <span class="flex-1 text-left">{{ $language->name }}</span>
                        @if($language->code === $this->currentLanguageCode)
                            <flux:icon name="check" class="[:where(&)]:size-4 text-erasmus-600 dark:text-erasmus-400" variant="outline" />
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

    @elseif($variant === 'buttons')
        {{-- Buttons Variant --}}
        <div class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 bg-zinc-50 p-1 dark:border-zinc-600 dark:bg-zinc-800">
            @foreach($languages as $language)
                <button
                    type="button"
                    wire:click="switchLanguage('{{ $language->code }}')"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors {{ $language->code === $this->currentLanguageCode ? 'bg-white text-erasmus-700 shadow-sm dark:bg-zinc-700 dark:text-erasmus-300' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}"
                    aria-label="{{ __('Cambiar a :language', ['language' => $language->name]) }}"
                >
                    {{ strtoupper($language->code) }}
                </button>
            @endforeach
        </div>

    @elseif($variant === 'select')
        {{-- Select Variant (Mobile-friendly) --}}
        <select
            wire:change="switchLanguage($event.target.value)"
            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm focus:border-erasmus-500 focus:outline-none focus:ring-2 focus:ring-erasmus-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:focus:border-erasmus-400 dark:focus:ring-erasmus-400"
            aria-label="{{ __('Seleccionar idioma') }}"
        >
            @foreach($languages as $language)
                <option value="{{ $language->code }}" {{ $language->code === $this->currentLanguageCode ? 'selected' : '' }}>
                    {{ $language->name }}
                </option>
            @endforeach
        </select>
    @endif
</div>

{{-- Listen for language change events --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('language-changed', (event) => {
            // El componente Livewire manejará la redirección
            // Este evento puede ser usado para otras acciones si es necesario
        });
        
        Livewire.on('language-error', (event) => {
            // Mostrar mensaje de error si es necesario
            console.error('Language error:', event.message);
        });
    });
</script>

