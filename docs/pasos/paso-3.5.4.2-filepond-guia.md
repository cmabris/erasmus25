# Guía de Implementación: Spatie Livewire-FilePond para Upload de PDFs

Esta guía detalla cómo implementar **Spatie Livewire-FilePond** para la subida de PDFs en el CRUD de Resoluciones. Este paquete oficial de Spatie facilita enormemente la integración de FilePond con Livewire.

## Instalación

### 1. Instalar paquete PHP

```bash
composer require spatie/livewire-filepond
```

### 2. Instalar dependencias NPM

```bash
npm install filepond filepond-plugin-file-validate-type filepond-plugin-file-validate-size
```

### 3. Publicar Assets (Opcional)

Si quieres personalizar los assets o las vistas:

```bash
# Publicar assets de FilePond
php artisan vendor:publish --tag="livewire-filepond-assets"

# Publicar vistas del componente
php artisan vendor:publish --tag="livewire-filepond-views"
```

## Configuración en Componente Livewire

### En Create.php

```php
<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Http\Requests\StoreResolutionRequest;
use App\Models\Call;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class Create extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

    public Call $call;
    
    public ?int $call_id = null;
    public ?int $call_phase_id = null;
    public string $type = 'provisional';
    public string $title = '';
    public ?string $description = null;
    public ?string $evaluation_procedure = null;
    public ?string $official_date = null;
    public ?string $published_at = null;
    
    /**
     * Archivo PDF a subir.
     */
    public ?UploadedFile $pdfFile = null;

    public function mount(Call $call, ?int $call_phase_id = null): void
    {
        $this->authorize('create', Resolution::class);
        
        $this->call = $call;
        $this->call_id = $call->id;
        $this->call_phase_id = $call_phase_id;
    }

    public function rules(): array
    {
        return [
            'call_id' => ['required', 'exists:calls,id'],
            'call_phase_id' => ['required', 'exists:call_phases,id'],
            'type' => ['required', 'in:provisional,definitivo,alegaciones'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evaluation_procedure' => ['nullable', 'string'],
            'official_date' => ['required', 'date'],
            'published_at' => ['nullable', 'date'],
            'pdfFile' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Remover pdfFile de validated para manejarlo por separado
        unset($validated['pdfFile']);

        $resolution = Resolution::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        // Manejar upload de PDF si existe
        if ($this->pdfFile) {
            $resolution->addMedia($this->pdfFile->getRealPath())
                ->usingName($resolution->title)
                ->usingFileName($this->pdfFile->getClientOriginalName())
                ->toMediaCollection('resolutions');
        }

        $this->dispatch('resolution-created', [
            'message' => __('Resolución creada correctamente'),
        ]);

        $this->redirect(
            route('admin.calls.resolutions.show', ['call' => $this->call, 'resolution' => $resolution]),
            navigate: true
        );
    }

    public function render(): View
    {
        return view('livewire.admin.calls.resolutions.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Resolución'),
            ]);
    }
}
```

### En Create.blade.php

```blade
<div>
    {{-- Header --}}
    <div class="mb-6">
        <x-ui.breadcrumbs>
            <x-ui.breadcrumbs.item href="{{ route('admin.calls.index') }}">
                {{ __('Convocatorias') }}
            </x-ui.breadcrumbs.item>
            <x-ui.breadcrumbs.item href="{{ route('admin.calls.show', $call) }}">
                {{ $call->title }}
            </x-ui.breadcrumbs.item>
            <x-ui.breadcrumbs.item>
                {{ __('Crear Resolución') }}
            </x-ui.breadcrumbs.item>
        </x-ui.breadcrumbs>
        
        <h1 class="mt-4 text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Crear Resolución') }}
        </h1>
    </div>

    <form wire:submit="save">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Formulario Principal --}}
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card>
                    <div class="space-y-6">
                        {{-- Fase --}}
                        <flux:field>
                            <flux:label>{{ __('Fase') }}</flux:label>
                            <flux:select wire:model.live="call_phase_id">
                                <option value="">{{ __('Seleccionar fase') }}</option>
                                @foreach($this->callPhases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                @endforeach
                            </flux:select>
                            @error('call_phase_id')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Tipo --}}
                        <flux:field>
                            <flux:label>{{ __('Tipo de Resolución') }}</flux:label>
                            <flux:select wire:model="type">
                                <option value="provisional">{{ __('Provisional') }}</option>
                                <option value="definitivo">{{ __('Definitivo') }}</option>
                                <option value="alegaciones">{{ __('Alegaciones') }}</option>
                            </flux:select>
                            @error('type')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Título --}}
                        <flux:field>
                            <flux:label>{{ __('Título') }}</flux:label>
                            <flux:input wire:model="title" />
                            @error('title')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Descripción --}}
                        <flux:field>
                            <flux:label>{{ __('Descripción') }}</flux:label>
                            <flux:textarea wire:model="description" rows="4" />
                            @error('description')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Procedimiento de Evaluación --}}
                        <flux:field>
                            <flux:label>{{ __('Procedimiento de Evaluación') }}</flux:label>
                            <flux:textarea wire:model="evaluation_procedure" rows="4" />
                            @error('evaluation_procedure')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Fecha Oficial --}}
                        <flux:field>
                            <flux:label>{{ __('Fecha Oficial') }}</flux:label>
                            <flux:input type="date" wire:model="official_date" />
                            @error('official_date')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Fecha de Publicación (Opcional) --}}
                        <flux:field>
                            <flux:label>{{ __('Fecha de Publicación') }}</flux:label>
                            <flux:input type="datetime-local" wire:model="published_at" />
                            <flux:description>
                                {{ __('Dejar vacío para publicar manualmente más tarde') }}
                            </flux:description>
                            @error('published_at')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>

                        {{-- Upload de PDF con Spatie Livewire-FilePond --}}
                        <flux:field>
                            <flux:label>{{ __('PDF de Resolución') }}</flux:label>
                            
                            <x-filepond::upload 
                                wire:model="pdfFile"
                                accepted-file-types="application/pdf"
                                max-file-size="10MB"
                                label-idle='Arrastra tu PDF aquí o <span class="filepond--label-action">selecciona</span>'
                                label-file-type-not-allowed="Solo se permiten archivos PDF"
                                label-file-size-too-large="El archivo es demasiado grande"
                                label-file-size-too-small="El archivo es demasiado pequeño"
                                label-file-loading="Cargando"
                                label-file-processing="Subiendo"
                                label-file-processing-complete="Subida completa"
                                label-file-processing-error="Error durante la subida"
                                label-tap-to-cancel="Toca para cancelar"
                                label-tap-to-retry="Toca para reintentar"
                                label-tap-to-undo="Toca para deshacer"
                            />
                            
                            <flux:description>
                                {{ __('Formatos aceptados: PDF. Tamaño máximo: 10MB') }}
                            </flux:description>
                            
                            @error('pdfFile')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:field>
                    </div>
                </x-ui.card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <x-ui.card>
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Información de la Convocatoria') }}
                        </h3>
                        
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                {{ __('Título') }}
                            </p>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $call->title }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                {{ __('Programa') }}
                            </p>
                            <p class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $call->program->name }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Acciones --}}
                <x-ui.card>
                    <div class="flex flex-col gap-3">
                        <flux:button type="submit" variant="primary" class="w-full">
                            {{ __('Crear Resolución') }}
                        </flux:button>
                        
                        <flux:button 
                            type="button" 
                            variant="ghost" 
                            href="{{ route('admin.calls.resolutions.index', $call) }}"
                            wire:navigate
                        >
                            {{ __('Cancelar') }}
                        </flux:button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </form>
</div>
```

## Uso en Componente Edit

### En Edit.php

```php
<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Http\Requests\UpdateResolutionRequest;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

    public Resolution $resolution;
    
    public ?int $call_id = null;
    public ?int $call_phase_id = null;
    public string $type = 'provisional';
    public string $title = '';
    public ?string $description = null;
    public ?string $evaluation_procedure = null;
    public ?string $official_date = null;
    public ?string $published_at = null;
    
    /**
     * Archivo PDF nuevo a subir (para reemplazar el existente).
     */
    public ?UploadedFile $pdfFile = null;
    
    /**
     * Si se debe eliminar el PDF existente.
     */
    public bool $removeExistingPdf = false;

    public function mount(Resolution $resolution): void
    {
        $this->authorize('update', $resolution);
        
        $this->resolution = $resolution->load(['call', 'callPhase', 'creator']);
        
        // Pre-llenar campos
        $this->call_id = $resolution->call_id;
        $this->call_phase_id = $resolution->call_phase_id;
        $this->type = $resolution->type;
        $this->title = $resolution->title;
        $this->description = $resolution->description;
        $this->evaluation_procedure = $resolution->evaluation_procedure;
        $this->official_date = $resolution->official_date?->format('Y-m-d');
        $this->published_at = $resolution->published_at?->format('Y-m-d\TH:i');
    }

    public function rules(): array
    {
        return [
            'call_id' => ['required', 'exists:calls,id'],
            'call_phase_id' => ['required', 'exists:call_phases,id'],
            'type' => ['required', 'in:provisional,definitivo,alegaciones'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'evaluation_procedure' => ['nullable', 'string'],
            'official_date' => ['required', 'date'],
            'published_at' => ['nullable', 'date'],
            'pdfFile' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();

        // Remover pdfFile de validated para manejarlo por separado
        unset($validated['pdfFile']);

        $this->resolution->update($validated);

        // Manejar PDF existente
        if ($this->removeExistingPdf) {
            $this->resolution->clearMediaCollection('resolutions');
        }

        // Manejar nuevo PDF si se subió
        if ($this->pdfFile) {
            // Eliminar PDF anterior si existe
            $this->resolution->clearMediaCollection('resolutions');
            
            // Agregar nuevo PDF
            $this->resolution->addMedia($this->pdfFile->getRealPath())
                ->usingName($this->resolution->title)
                ->usingFileName($this->pdfFile->getClientOriginalName())
                ->toMediaCollection('resolutions');
        }

        $this->dispatch('resolution-updated', [
            'message' => __('Resolución actualizada correctamente'),
        ]);

        $this->redirect(
            route('admin.calls.resolutions.show', ['call' => $this->resolution->call, 'resolution' => $this->resolution]),
            navigate: true
        );
    }

    public function removePdf(): void
    {
        $this->removeExistingPdf = true;
        $this->pdfFile = null;
    }

    #[Computed]
    public function callPhases()
    {
        return $this->resolution->call->phases()->orderBy('order')->get();
    }

    #[Computed]
    public function existingPdf()
    {
        return $this->resolution->getFirstMedia('resolutions');
    }

    public function render(): View
    {
        return view('livewire.admin.calls.resolutions.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Resolución'),
            ]);
    }
}
```

### En Edit.blade.php (Sección de PDF)

```blade
{{-- PDF Existente --}}
@if($this->existingPdf && !$removeExistingPdf)
    <flux:field>
        <flux:label>{{ __('PDF Actual') }}</flux:label>
        <div class="flex items-center gap-4 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
            <flux:icon name="document-text" class="size-8 text-red-600" />
            <div class="flex-1">
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                    {{ $this->existingPdf->file_name }}
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ number_format($this->existingPdf->size / 1024, 2) }} KB
                </p>
            </div>
            <div class="flex gap-2">
                <flux:button 
                    type="button"
                    variant="ghost"
                    size="sm"
                    href="{{ $this->existingPdf->getUrl() }}"
                    target="_blank"
                >
                    {{ __('Ver') }}
                </flux:button>
                <flux:button 
                    type="button"
                    variant="ghost"
                    size="sm"
                    wire:click="removePdf"
                >
                    {{ __('Eliminar') }}
                </flux:button>
            </div>
        </div>
    </flux:field>
@endif

{{-- Upload de Nuevo PDF --}}
@if(!$this->existingPdf || $removeExistingPdf)
    <flux:field>
        <flux:label>{{ __('PDF de Resolución') }}</flux:label>
        
        <x-filepond::upload 
            wire:model="pdfFile"
            accepted-file-types="application/pdf"
            max-file-size="10MB"
            label-idle='Arrastra tu PDF aquí o <span class="filepond--label-action">selecciona</span>'
        />
        
        <flux:description>
            {{ __('Formatos aceptados: PDF. Tamaño máximo: 10MB') }}
        </flux:description>
        
        @error('pdfFile')
            <flux:error>{{ $message }}</flux:error>
        @enderror
    </flux:field>
@endif
```

## Ventajas de Spatie Livewire-FilePond

1. ✅ **Integración nativa**: Trait y componente Blade listos para usar
2. ✅ **Menos código**: No necesitas escribir JavaScript personalizado
3. ✅ **Mantenimiento**: Paquete oficial de Spatie con soporte activo
4. ✅ **Documentación**: Documentación completa y ejemplos
5. ✅ **Validación**: Validación integrada con Livewire
6. ✅ **Eventos**: Manejo automático de eventos de upload
7. ✅ **Personalización**: Fácil de personalizar mediante props

## Configuración Avanzada

Si necesitas más control, puedes publicar las vistas y personalizarlas:

```bash
php artisan vendor:publish --tag="livewire-filepond-views"
```

Esto publicará las vistas en `resources/views/vendor/livewire-filepond/` donde puedes personalizarlas según tus necesidades.

## Referencias

- [Documentación oficial de Spatie Livewire-FilePond](https://github.com/spatie/livewire-filepond)
- [Documentación de FilePond](https://pqina.nl/filepond/docs/)
- [Documentación de Livewire File Uploads](https://livewire.laravel.com/docs/uploads)
