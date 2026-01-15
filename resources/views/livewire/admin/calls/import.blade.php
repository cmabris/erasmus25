<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Importar Convocatorias') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Importa convocatorias desde un archivo Excel o CSV') }}
                </p>
            </div>
            <flux:button 
                href="{{ route('admin.calls.index') }}" 
                variant="ghost"
                wire:navigate
                icon="arrow-left"
            >
                {{ __('common.actions.back') }}
            </flux:button>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('common.nav.calls'), 'href' => route('admin.calls.index'), 'icon' => 'megaphone'],
                ['label' => __('Importar'), 'icon' => 'arrow-up-tray'],
            ]"
        />
    </div>

    {{-- Import Form --}}
    <div class="animate-fade-in" style="animation-delay: 0.1s;">
        <x-ui.card>
            <div class="mb-4">
                <flux:heading size="sm">{{ __('Archivo de Importación') }}</flux:heading>
                <flux:description class="mt-2">
                    {{ __('Selecciona un archivo Excel (.xlsx, .xls) o CSV (.csv) con las convocatorias a importar. Puedes descargar una plantilla para ver el formato requerido.') }}
                </flux:description>
            </div>

            <div class="space-y-6">
                {{-- Download Template Button --}}
                <div class="flex items-center gap-4">
                    <flux:button 
                        wire:click="downloadTemplate"
                        variant="outline"
                        icon="document-arrow-down"
                    >
                        {{ __('Descargar Plantilla') }}
                    </flux:button>
                    <flux:description>
                        {{ __('Descarga una plantilla Excel con el formato correcto y ejemplos de datos.') }}
                    </flux:description>
                </div>

                {{-- File Upload --}}
                <flux:field>
                    <flux:label>{{ __('Archivo de Importación') }} <span class="text-red-500">*</span></flux:label>
                    
                    <x-filepond::upload 
                        wire:model="file"
                        accepted-file-types="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv"
                        max-file-size="10MB"
                        label-idle='{{ __("Arrastra tu archivo aquí o") }} <span class="filepond--label-action">{{ __("selecciona") }}</span>'
                        label-file-type-not-allowed="{{ __('Solo se permiten archivos Excel (.xlsx, .xls) o CSV (.csv)') }}"
                        label-file-size-too-large="{{ __('El archivo es demasiado grande (máximo 10MB)') }}"
                        label-file-size-too-small="{{ __('El archivo es demasiado pequeño') }}"
                        label-file-loading="{{ __('Cargando') }}"
                        label-file-processing="{{ __('Subiendo') }}"
                        label-file-processing-complete="{{ __('Subida completa') }}"
                        label-file-processing-error="{{ __('Error durante la subida') }}"
                        label-tap-to-cancel="{{ __('Toca para cancelar') }}"
                        label-tap-to-retry="{{ __('Toca para reintentar') }}"
                        label-tap-to-undo="{{ __('Toca para deshacer') }}"
                    />
                    
                    <flux:description>
                        {{ __('Formatos aceptados: Excel (.xlsx, .xls) o CSV (.csv). Tamaño máximo: 10MB.') }}
                    </flux:description>
                    @error('file')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                {{-- Dry Run Checkbox --}}
                <flux:field>
                    <flux:checkbox wire:model="dryRun" />
                    <flux:label>{{ __('Modo de Prueba (Solo Validar)') }}</flux:label>
                    <flux:description>
                        {{ __('Si está activado, solo se validarán los datos sin guardar nada en la base de datos. Útil para verificar el formato antes de importar.') }}
                    </flux:description>
                </flux:field>

                {{-- Import Button --}}
                <div class="flex items-center gap-4">
                    <flux:button 
                        wire:click="import"
                        variant="primary"
                        icon="arrow-up-tray"
                        wire:loading.attr="disabled"
                        wire:target="import"
                        :disabled="!$file || $isProcessing"
                    >
                        <span wire:loading.remove wire:target="import">
                            {{ $dryRun ? __('Validar Archivo') : __('Importar') }}
                        </span>
                        <span wire:loading wire:target="import">
                            {{ $dryRun ? __('Validando...') : __('Importando...') }}
                        </span>
                    </flux:button>
                    @if($results)
                        <flux:button 
                            wire:click="resetForm"
                            variant="ghost"
                            icon="arrow-path"
                        >
                            {{ __('Nueva Importación') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- Results --}}
    @if($results)
        <div class="mt-6 animate-fade-in" style="animation-delay: 0.2s;">
            <x-ui.card>
                <div class="mb-4">
                    <flux:heading size="sm">
                        {{ $results['dry_run'] ? __('Resultados de Validación') : __('Resultados de Importación') }}
                    </flux:heading>
                </div>

                <div class="space-y-6">
                    {{-- Summary --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        {{-- Imported/Validated Count --}}
                        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                            <div class="flex items-center gap-2">
                                <flux:icon.check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                                <div>
                                    <div class="text-sm font-medium text-green-900 dark:text-green-100">
                                        {{ $results['dry_run'] ? __('Registros Válidos') : __('Registros Importados') }}
                                    </div>
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ $results['imported'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Failed Count --}}
                        @if($results['failed'] > 0)
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                                <div class="flex items-center gap-2">
                                    <flux:icon.x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                                    <div>
                                        <div class="text-sm font-medium text-red-900 dark:text-red-100">
                                            {{ __('Registros con Errores') }}
                                        </div>
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                            {{ $results['failed'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Errors Table --}}
                    @if(!empty($results['errors']))
                        <div>
                            <flux:heading size="xs" class="mb-3">{{ __('Errores Encontrados') }}</flux:heading>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                                {{ __('Fila') }}
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                                {{ __('Errores') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                        @foreach($results['errors'] as $error)
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $error['row'] }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                                    <ul class="list-disc space-y-1 pl-5">
                                                        @foreach($error['errors'] as $errorMessage)
                                                            <li>{{ $errorMessage }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Success Message --}}
                    @if($results['imported'] > 0 && $results['failed'] === 0)
                        <flux:callout variant="success">
                            <flux:callout.heading>
                                {{ $results['dry_run'] ? __('Validación Exitosa') : __('Importación Exitosa') }}
                            </flux:callout.heading>
                            <flux:callout.text>
                                {{ $results['dry_run'] 
                                    ? __('Todos los registros son válidos y pueden ser importados.') 
                                    : __('Todas las convocatorias se importaron correctamente.') }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                </div>
            </x-ui.card>
        </div>
    @endif
</div>
