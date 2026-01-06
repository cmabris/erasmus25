@props([
    'user',
    'permissions' => null, // Optional: pass permissions directly instead of from user
    'size' => 'sm', // xs, sm, md, lg
    'showEmpty' => false, // Show message when no permissions
    'maxDisplay' => null, // Maximum number of permissions to display (null = all)
])

@php
    $userPermissions = $permissions ?? $user->permissions ?? collect();
    
    if ($maxDisplay && $userPermissions->count() > $maxDisplay) {
        $displayedPermissions = $userPermissions->take($maxDisplay);
        $remainingCount = $userPermissions->count() - $maxDisplay;
    } else {
        $displayedPermissions = $userPermissions;
        $remainingCount = 0;
    }
@endphp

@if($userPermissions->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach($displayedPermissions as $permission)
            <x-ui.badge variant="info" :size="$size">
                {{ $permission->name ?? $permission }}
            </x-ui.badge>
        @endforeach
        
        @if($remainingCount > 0)
            <x-ui.badge variant="neutral" :size="$size">
                +{{ $remainingCount }} {{ __('más') }}
            </x-ui.badge>
        @endif
    </div>
@elseif($showEmpty)
    <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('Sin permisos directos') }}</span>
@endif

