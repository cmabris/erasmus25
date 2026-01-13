<div>
    {{-- Header --}}
    <div class="mb-6 animate-fade-in">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    {{ __('Detalle de Log de Auditoría') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Información completa del registro de actividad') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <flux:button 
                    href="{{ route('admin.audit-logs.index') }}" 
                    variant="ghost"
                    wire:navigate
                    icon="arrow-left"
                >
                    {{ __('common.actions.back') }}
                </flux:button>
            </div>
        </div>

        {{-- Breadcrumbs --}}
        <x-ui.breadcrumbs 
            class="mt-4"
            :items="[
                ['label' => __('common.nav.dashboard'), 'href' => route('admin.dashboard'), 'icon' => 'squares-2x2'],
                ['label' => __('Auditoría y Logs'), 'href' => route('admin.audit-logs.index'), 'icon' => 'clipboard-document-list'],
                ['label' => __('Detalle'), 'icon' => 'eye'],
            ]"
        />
    </div>

    {{-- Content --}}
    <div class="space-y-6 animate-fade-in" style="animation-delay: 0.1s;">
        {{-- Main Information Card --}}
        <x-ui.card>
            <flux:heading size="md" class="mb-6">{{ __('Información Principal') }}</flux:heading>
            
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- ID --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('ID del Log') }}</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        #{{ $activity->id }}
                    </p>
                </div>

                {{-- Date/Time --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Fecha y Hora') }}</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $activity->created_at->format('d/m/Y H:i:s') }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $activity->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Description/Action --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Acción') }}</p>
                    <div class="mt-1">
                        <flux:badge :variant="$this->getDescriptionBadgeVariant($activity->description)" size="lg">
                            {{ $this->getDescriptionDisplayName($activity->description) }}
                        </flux:badge>
                    </div>
                </div>

                {{-- Log Name --}}
                @if($activity->log_name)
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Log Name') }}</p>
                        <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $activity->log_name }}
                        </p>
                    </div>
                @endif

                {{-- User/Causer --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Usuario') }}</p>
                    @if($activity->causer)
                        <div class="mt-1 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-erasmus-100 text-sm font-medium text-erasmus-700 dark:bg-erasmus-900/30 dark:text-erasmus-300">
                                {{ $activity->causer->initials() }}
                            </div>
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $activity->causer->name }}
                                </p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $activity->causer->email }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="mt-1 text-lg font-semibold text-zinc-500 dark:text-zinc-400 italic">
                            {{ __('Sistema') }}
                        </p>
                    @endif
                </div>

                {{-- IP Address --}}
                @php
                    $ipAddress = $this->getIpAddress($activity->properties);
                @endphp
                @if($ipAddress)
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Dirección IP') }}</p>
                        <p class="mt-1 text-lg font-mono text-zinc-900 dark:text-white">
                            {{ $ipAddress }}
                        </p>
                    </div>
                @endif

                {{-- User Agent --}}
                @php
                    $userAgent = $this->getUserAgent($activity->properties);
                    $userAgentInfo = $this->parseUserAgent($userAgent);
                @endphp
                @if($userAgent)
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('User Agent') }}</p>
                        @if($userAgentInfo && ($userAgentInfo['browser'] || $userAgentInfo['os']))
                            <div class="mt-1 space-y-1">
                                @if($userAgentInfo['browser'])
                                    <p class="text-sm text-zinc-900 dark:text-white">
                                        <span class="font-medium">{{ __('Navegador') }}:</span> {{ $userAgentInfo['browser'] }}
                                    </p>
                                @endif
                                @if($userAgentInfo['os'])
                                    <p class="text-sm text-zinc-900 dark:text-white">
                                        <span class="font-medium">{{ __('Sistema Operativo') }}:</span> {{ $userAgentInfo['os'] }}
                                    </p>
                                @endif
                                @if($userAgentInfo['device'])
                                    <p class="text-sm text-zinc-900 dark:text-white">
                                        <span class="font-medium">{{ __('Dispositivo') }}:</span> {{ $userAgentInfo['device'] }}
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 break-words">
                                {{ Str::limit($userAgent, 100) }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Subject Information Card --}}
        <x-ui.card>
            <flux:heading size="md" class="mb-6">{{ __('Información del Registro') }}</flux:heading>
            
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Model Type --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Tipo de Modelo') }}</p>
                    <div class="mt-1">
                        <flux:badge variant="info" size="lg">
                            {{ $this->getModelDisplayName($activity->subject_type) }}
                        </flux:badge>
                    </div>
                    @if($activity->subject_type)
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 font-mono">
                            {{ $activity->subject_type }}
                        </p>
                    @endif
                </div>

                {{-- Subject ID --}}
                <div>
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('ID del Registro') }}</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $activity->subject_id ?? '-' }}
                    </p>
                </div>

                {{-- Subject Title/Name --}}
                <div class="sm:col-span-2">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Registro') }}</p>
                    @php
                        $subjectUrl = $this->getSubjectUrl($activity->subject_type, $activity->subject_id);
                        $subjectTitle = $this->getSubjectTitle($activity->subject);
                    @endphp
                    @if($subjectUrl && $activity->subject)
                        <div class="mt-1">
                            <a href="{{ $subjectUrl }}" wire:navigate class="text-lg font-semibold text-erasmus-600 hover:text-erasmus-700 dark:text-erasmus-400 dark:hover:text-erasmus-300">
                                {{ $subjectTitle }}
                            </a>
                        </div>
                    @elseif($activity->subject)
                        <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $subjectTitle }}
                        </p>
                    @else
                        <p class="mt-1 text-lg font-semibold text-zinc-500 dark:text-zinc-400 italic">
                            {{ __('Registro eliminado') }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex flex-wrap gap-3 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                @if($subjectUrl && $activity->subject)
                    <flux:button 
                        href="{{ $subjectUrl }}" 
                        variant="primary"
                        wire:navigate
                        icon="arrow-right"
                    >
                        {{ __('Ver Registro Relacionado') }}
                    </flux:button>
                @endif
                @if($activity->causer)
                    <flux:button 
                        href="{{ route('admin.users.show', $activity->causer) }}" 
                        variant="ghost"
                        wire:navigate
                        icon="user"
                    >
                        {{ __('Ver Usuario') }}
                    </flux:button>
                @endif
            </div>
        </x-ui.card>

        {{-- Changes Card --}}
        @php
            $changes = $this->getChangesFromProperties($activity->properties);
        @endphp
        <x-ui.card>
            <div class="mb-6 flex items-center justify-between">
                <flux:heading size="md">{{ __('Cambios Realizados') }}</flux:heading>
                @if(!empty($changes))
                    <flux:badge variant="info" size="sm">
                        {{ count($changes) }} {{ __('campo(s) modificado(s)') }}
                    </flux:badge>
                @endif
            </div>

            @if(!empty($changes))
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Campo') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Valor Anterior') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('Valor Nuevo') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($changes as $change)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <code class="rounded bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200">
                                            {{ $change['field'] }}
                                        </code>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-md">
                                            <div class="text-sm text-red-600 dark:text-red-400">
                                                {!! $this->formatValueForDisplay($change['old']) !!}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="max-w-md">
                                            <div class="text-sm text-green-600 dark:text-green-400">
                                                {!! $this->formatValueForDisplay($change['new']) !!}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('No se registraron cambios en este log.') }}
                    </p>
                </div>
            @endif

            {{-- JSON View (Collapsible) --}}
            @if($activity->properties)
                <div x-data="{ open: false }" class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                    <button 
                        @click="open = !open"
                        class="flex w-full items-center justify-between text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white"
                    >
                        <span>{{ __('Ver JSON de Cambios') }}</span>
                        <div x-bind:class="open ? 'rotate-180' : ''" class="transition-transform">
                            <flux:icon 
                                name="chevron-down" 
                                class="[:where(&)]:size-4"
                                variant="outline" 
                            />
                        </div>
                    </button>
                    <div x-show="open" x-collapse class="mt-4">
                        <pre class="overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-xs dark:border-zinc-700 dark:bg-zinc-800"><code class="text-zinc-800 dark:text-zinc-200">{{ $this->formatJsonForDisplay($activity->properties) }}</code></pre>
                    </div>
                </div>
            @endif
        </x-ui.card>

        {{-- Custom Properties Card (Collapsible) --}}
        @php
            $customProperties = $this->getCustomProperties($activity->properties);
        @endphp
        @if(!empty($customProperties))
            <x-ui.card>
                <div x-data="{ open: false }">
                    <button 
                        @click="open = !open"
                        class="mb-6 flex w-full items-center justify-between"
                    >
                        <flux:heading size="md">{{ __('Propiedades Personalizadas') }}</flux:heading>
                        <div x-bind:class="open ? 'rotate-180' : ''" class="transition-transform">
                            <flux:icon 
                                name="chevron-down" 
                                class="[:where(&)]:size-5"
                                variant="outline" 
                            />
                        </div>
                    </button>
                    <div x-show="open" x-collapse>
                        <div class="space-y-4">
                            @foreach($customProperties as $key => $value)
                                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                        <code class="rounded bg-zinc-200 px-2 py-1 text-xs dark:bg-zinc-700">{{ $key }}</code>
                                    </p>
                                    <div class="mt-2 text-sm text-zinc-900 dark:text-white">
                                        {!! $this->formatValueForDisplay($value) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @endif

        {{-- Technical Information Card (Collapsible) --}}
        <x-ui.card>
            <div x-data="{ open: false }">
                <button 
                    @click="open = !open"
                    class="mb-6 flex w-full items-center justify-between"
                >
                    <flux:heading size="md">{{ __('Información Técnica') }}</flux:heading>
                    <div x-bind:class="open ? 'rotate-180' : ''" class="transition-transform">
                        <flux:icon 
                            name="chevron-down" 
                            class="[:where(&)]:size-5"
                            variant="outline" 
                        />
                    </div>
                </button>
                <div x-show="open" x-collapse>
                    <div class="space-y-6">
                        {{-- Full Activity JSON --}}
                        <div>
                            <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                {{ __('JSON Completo del Log') }}
                            </p>
                            <pre class="overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-xs dark:border-zinc-700 dark:bg-zinc-800"><code class="text-zinc-800 dark:text-zinc-200">{{ $this->formatJsonForDisplay($activity->toArray()) }}</code></pre>
                        </div>

                        {{-- Properties JSON --}}
                        @if($activity->properties)
                            <div>
                                <p class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    {{ __('Properties Completo') }}
                                </p>
                                <pre class="overflow-x-auto rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-xs dark:border-zinc-700 dark:bg-zinc-800"><code class="text-zinc-800 dark:text-zinc-200">{{ $this->formatJsonForDisplay($activity->properties) }}</code></pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>
