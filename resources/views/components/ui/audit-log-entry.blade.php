@props([
    'log',
    'showModel' => true,
    'showChanges' => true,
    'showTimestamp' => true,
    'compact' => false,
])

@php
    $actionVariants = [
        'create' => 'success',
        'update' => 'info',
        'delete' => 'danger',
        'publish' => 'success',
        'archive' => 'warning',
        'restore' => 'info',
    ];
    
    $getActionVariant = function($action) use ($actionVariants) {
        return $actionVariants[strtolower($action)] ?? 'neutral';
    };
    
    $getActionDisplayName = function($action) {
        return match(strtolower($action)) {
            'create' => __('Crear'),
            'update' => __('Actualizar'),
            'delete' => __('Eliminar'),
            'publish' => __('Publicar'),
            'archive' => __('Archivar'),
            'restore' => __('Restaurar'),
            default => ucfirst($action),
        };
    };
    
    $getModelDisplayName = function($modelType) {
        if (!$modelType) {
            return '-';
        }
        
        return match($modelType) {
            'App\Models\Program' => __('Programa'),
            'App\Models\Call' => __('Convocatoria'),
            'App\Models\NewsPost' => __('Noticia'),
            'App\Models\Document' => __('Documento'),
            'App\Models\ErasmusEvent' => __('Evento'),
            'App\Models\AcademicYear' => __('Año Académico'),
            'App\Models\DocumentCategory' => __('Categoría de Documento'),
            'App\Models\NewsTag' => __('Etiqueta de Noticia'),
            default => class_basename($modelType),
        };
    };
    
    $formatChanges = function($changes) {
        if (!$changes) {
            return '-';
        }
        
        $formatted = [];
        
        // Handle AuditLog format (before/after)
        if (isset($changes['before']) && is_array($changes['before'])) {
            foreach ($changes['before'] as $key => $value) {
                $afterValue = $changes['after'][$key] ?? null;
                if ($value !== $afterValue) {
                    $formatted[] = sprintf(
                        '%s: %s → %s',
                        $key,
                        is_array($value) ? json_encode($value) : ($value ?? 'null'),
                        is_array($afterValue) ? json_encode($afterValue) : ($afterValue ?? 'null')
                    );
                }
            }
        }
        // Handle Activity format (old/attributes)
        elseif (isset($changes['old']) && isset($changes['attributes']) && is_array($changes['old'])) {
            foreach ($changes['old'] as $key => $value) {
                $newValue = $changes['attributes'][$key] ?? null;
                if ($value !== $newValue) {
                    $formatted[] = sprintf(
                        '%s: %s → %s',
                        $key,
                        is_array($value) ? json_encode($value) : ($value ?? 'null'),
                        is_array($newValue) ? json_encode($newValue) : ($newValue ?? 'null')
                    );
                }
            }
        }
        
        return !empty($formatted) ? implode(', ', $formatted) : __('Sin cambios');
    };
    
    $getModelTitle = function($model) {
        if (!$model) {
            return '-';
        }
        
        if (isset($model->title)) {
            return $model->title;
        }
        
        if (isset($model->name)) {
            return $model->name;
        }
        
        return __('Registro #:id', ['id' => $model->id ?? '-']);
    };
    
    $getModelUrl = function($modelType, $modelId) {
        if (!$modelType || !$modelId) {
            return null;
        }
        
        $routeMap = [
            'App\Models\Program' => 'admin.programs.show',
            'App\Models\Call' => 'admin.calls.show',
            'App\Models\NewsPost' => 'admin.news.show',
            'App\Models\Document' => 'admin.documents.show',
            'App\Models\ErasmusEvent' => 'admin.events.show',
            'App\Models\AcademicYear' => 'admin.academic-years.show',
            'App\Models\DocumentCategory' => 'admin.document-categories.show',
            'App\Models\NewsTag' => 'admin.news-tags.show',
        ];
        
        $routeName = $routeMap[$modelType] ?? null;
        
        if (!$routeName) {
            return null;
        }
        
        try {
            return route($routeName, $modelId);
        } catch (\Exception $e) {
            return null;
        }
    };
    
    // Support both AuditLog and Activity (Spatie)
    $isActivity = $log instanceof \Spatie\Activitylog\Models\Activity;
    
    if ($isActivity) {
        $action = $log->description ?? '';
        $modelType = $log->subject_type ?? null;
        $model = $log->subject ?? null;
        $modelId = $log->subject_id ?? null;
        // Extract changes from properties (old/attributes format)
        $properties = $log->properties ?? null;
        $changes = null;
        if ($properties && isset($properties['old']) && isset($properties['attributes'])) {
            $changes = [
                'old' => $properties['old'],
                'attributes' => $properties['attributes'],
            ];
        }
    } else {
        $action = $log->action ?? '';
        $modelType = $log->model_type ?? null;
        $model = $log->model ?? null;
        $modelId = $log->model_id ?? null;
        $changes = $log->changes ?? null;
    }
    
    $modelUrl = $getModelUrl($modelType, $modelId);
@endphp

<div {{ $attributes->merge(['class' => $compact ? 'rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800' : 'rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
                <x-ui.badge :variant="$getActionVariant($action)" size="sm">
                    {{ $getActionDisplayName($action) }}
                </x-ui.badge>
                @if($showModel && $modelType)
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $getModelDisplayName($modelType) }}
                    </span>
                @endif
            </div>
            
            @if($model)
                @if($modelUrl)
                    <a href="{{ $modelUrl }}" wire:navigate class="font-medium text-zinc-900 dark:text-white hover:text-erasmus-600 dark:hover:text-erasmus-400">
                        {{ $getModelTitle($model) }}
                    </a>
                @else
                    <p class="font-medium text-zinc-900 dark:text-white">
                        {{ $getModelTitle($model) }}
                    </p>
                @endif
            @endif
            
            @if($showChanges && $changes)
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $formatChanges($changes) }}
                </p>
            @endif
            
            @if($showTimestamp && $log->created_at)
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ $log->created_at->format('d/m/Y H:i') }} ({{ $log->created_at->diffForHumans() }})
                </p>
            @endif
        </div>
    </div>
</div>

