@props([
    'user',
    'roles' => null, // Optional: pass roles directly instead of from user
    'size' => 'sm', // xs, sm, md, lg
    'showEmpty' => false, // Show message when no roles
])

@php
    $userRoles = $roles ?? $user->roles ?? collect();
    $roleVariants = [
        'super-admin' => 'danger',
        'admin' => 'warning',
        'editor' => 'info',
        'viewer' => 'neutral',
    ];
    
    $getRoleVariant = function($roleName) use ($roleVariants) {
        return $roleVariants[strtolower($roleName)] ?? 'neutral';
    };
    
    $getRoleDisplayName = function($roleName) {
        return match(strtolower($roleName)) {
            'super-admin' => __('Super Administrador'),
            'admin' => __('Administrador'),
            'editor' => __('Editor'),
            'viewer' => __('Visualizador'),
            default => $roleName,
        };
    };
@endphp

@if($userRoles->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach($userRoles as $role)
            <x-ui.badge :variant="$getRoleVariant($role->name ?? $role)" :size="$size">
                {{ $getRoleDisplayName($role->name ?? $role) }}
            </x-ui.badge>
        @endforeach
    </div>
@elseif($showEmpty)
    <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('Sin roles asignados') }}</span>
@endif

