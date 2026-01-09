@php
    $centerLogo = \App\Models\Setting::get('center_logo');
    $centerName = \App\Models\Setting::get('center_name', 'Erasmus+ Centro (Murcia)');
@endphp

@if($centerLogo)
    <img 
        src="{{ $centerLogo }}" 
        alt="{{ $centerName }}"
        class="h-8 w-auto max-w-[120px] object-contain"
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
    />
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground" style="display: none;">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
    </div>
@else
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
    </div>
@endif
<div class="ms-1 flex-1 text-start text-sm">
    <p class="leading-tight font-semibold line-clamp-2">{{ $centerName }}</p>
</div>
