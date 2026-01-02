@props([
    'placeholder' => 'Escribe tu contenido aquí...',
    'label' => null,
    'required' => false,
    'error' => null,
    'description' => null,
])

@php
    // Get the wire model attribute from attributes
    $wireModelAttribute = $attributes->wire('model');
    if (!$wireModelAttribute) {
        $wireModelAttribute = $attributes->get('wire:model', 'content');
    }
@endphp

<div 
    {{ $attributes->only('class')->merge(['class' => 'space-y-2']) }}
    x-data="tiptapEditor($wire.entangle('{{ $wireModelAttribute->value() }}'))"
    wire:ignore
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    @if($label)
        <flux:label>
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </flux:label>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 rounded-t-lg border border-b-0 border-zinc-300 bg-zinc-50 p-2 dark:border-zinc-600 dark:bg-zinc-800">
        {{-- Text Formatting --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="toggleBold()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('bold', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Negrita') }}"
            >
                <flux:icon name="bold" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="toggleItalic()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('italic', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Cursiva') }}"
            >
                <flux:icon name="italic" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="toggleStrike()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('strike', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Tachado') }}"
            >
                <span class="text-xs font-bold line-through">S</span>
            </button>
        </div>

        {{-- Headings --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="toggleHeading(1)"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('heading', { level: 1 }, updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Título 1') }}"
            >
                <span class="text-sm font-bold">H1</span>
            </button>
            <button
                type="button"
                @click="toggleHeading(2)"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('heading', { level: 2 }, updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Título 2') }}"
            >
                <span class="text-sm font-bold">H2</span>
            </button>
            <button
                type="button"
                @click="toggleHeading(3)"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('heading', { level: 3 }, updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Título 3') }}"
            >
                <span class="text-sm font-bold">H3</span>
            </button>
        </div>

        {{-- Lists --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="toggleBulletList()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('bulletList', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Lista con viñetas') }}"
            >
                <flux:icon name="list-bullet" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="toggleOrderedList()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('orderedList', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Lista numerada') }}"
            >
                <span class="text-xs font-bold leading-none">1. 2. 3.</span>
            </button>
        </div>

        {{-- Link --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="setLink()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('link', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Insertar enlace') }}"
            >
                <flux:icon name="link" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                x-show="isActive('link', updatedAt)"
                x-cloak
                @click="unsetLink()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Quitar enlace') }}"
            >
                <flux:icon name="link-slash" class="[:where(&)]:size-4" variant="outline" />
            </button>
        </div>

        {{-- Media --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="setImage()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Insertar imagen') }}"
            >
                <flux:icon name="photo" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="setYoutubeVideo()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Insertar vídeo de YouTube') }}"
            >
                <flux:icon name="play" class="[:where(&)]:size-4" variant="outline" />
            </button>
        </div>

        {{-- Table --}}
        <div class="relative flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600" x-data="{ open: false }">
            <button
                type="button"
                @click="open = !open"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': open || isActive('table', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Tabla') }}"
            >
                <flux:icon name="table-cells" class="[:where(&)]:size-4" variant="outline" />
            </button>
            
            {{-- Dropdown Menu --}}
            <div
                x-show="open"
                x-cloak
                @click.away="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute left-0 top-full z-50 mt-1 w-48 rounded-lg border border-zinc-300 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-800"
            >
                <div class="p-1">
                    <button
                        type="button"
                        @click="insertTable(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Insertar tabla') }}
                    </button>
                    <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        type="button"
                        @click="addRowBefore(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Añadir fila arriba') }}
                    </button>
                    <button
                        type="button"
                        @click="addRowAfter(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Añadir fila abajo') }}
                    </button>
                    <button
                        type="button"
                        @click="deleteRow(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Eliminar fila') }}
                    </button>
                    <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        type="button"
                        @click="addColumnBefore(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Añadir columna izquierda') }}
                    </button>
                    <button
                        type="button"
                        @click="addColumnAfter(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-700"
                    >
                        {{ __('Añadir columna derecha') }}
                    </button>
                    <button
                        type="button"
                        @click="deleteColumn(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Eliminar columna') }}
                    </button>
                    <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        type="button"
                        @click="mergeCells(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Fusionar celdas') }}
                    </button>
                    <button
                        type="button"
                        @click="splitCell(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Dividir celda') }}
                    </button>
                    <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        type="button"
                        @click="toggleHeaderRow(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Alternar fila de encabezado') }}
                    </button>
                    <button
                        type="button"
                        @click="toggleHeaderColumn(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-zinc-700 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        {{ __('Alternar columna de encabezado') }}
                    </button>
                    <div class="my-1 border-t border-zinc-200 dark:border-zinc-700"></div>
                    <button
                        type="button"
                        @click="deleteTable(); open = false;"
                        class="w-full rounded px-3 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                    >
                        {{ __('Eliminar tabla') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Blockquote & Horizontal Rule --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="toggleBlockquote()"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('blockquote', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Cita') }}"
            >
                <flux:icon name="chat-bubble-left-right" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="setHorizontalRule()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Línea horizontal') }}"
            >
                <span class="text-xs font-bold">─</span>
            </button>
        </div>

        {{-- Text Align --}}
        <div class="flex items-center gap-1 border-r border-zinc-300 pr-2 dark:border-zinc-600">
            <button
                type="button"
                @click="setTextAlign('left')"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('textAlign', 'left', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Alinear izquierda') }}"
            >
                <flux:icon name="bars-3-bottom-left" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="setTextAlign('center')"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('textAlign', 'center', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Centrar') }}"
            >
                <flux:icon name="bars-3" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="setTextAlign('right')"
                :class="{ 'bg-zinc-200 dark:bg-zinc-700': isActive('textAlign', 'right', updatedAt) }"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Alinear derecha') }}"
            >
                <flux:icon name="bars-3-bottom-right" class="[:where(&)]:size-4" variant="outline" />
            </button>
        </div>

        {{-- Undo/Redo --}}
        <div class="flex items-center gap-1">
            <button
                type="button"
                @click="undo()"
                :disabled="!canUndo()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Deshacer') }}"
            >
                <flux:icon name="arrow-uturn-left" class="[:where(&)]:size-4" variant="outline" />
            </button>
            <button
                type="button"
                @click="redo()"
                :disabled="!canRedo()"
                class="rounded p-1.5 text-zinc-600 transition-colors hover:bg-zinc-200 disabled:opacity-50 disabled:cursor-not-allowed dark:text-zinc-400 dark:hover:bg-zinc-700"
                title="{{ __('Rehacer') }}"
            >
                <flux:icon name="arrow-uturn-right" class="[:where(&)]:size-4" variant="outline" />
            </button>
        </div>
    </div>

    {{-- Editor Container --}}
    <div class="rounded-b-lg border border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800">
        <div
            x-ref="editor"
            class="min-h-[300px] px-4 py-3 prose prose-sm max-w-none dark:prose-invert [&_.ProseMirror]:min-h-[300px] [&_.ProseMirror]:outline-none [&_.ProseMirror_p]:mb-2 [&_.ProseMirror_strong]:font-bold [&_.ProseMirror_em]:italic [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:ml-6 [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:ml-6 [&_.ProseMirror_h1]:text-2xl [&_.ProseMirror_h1]:font-bold [&_.ProseMirror_h1]:mt-4 [&_.ProseMirror_h1]:mb-2 [&_.ProseMirror_h2]:text-xl [&_.ProseMirror_h2]:font-bold [&_.ProseMirror_h2]:mt-3 [&_.ProseMirror_h2]:mb-2 [&_.ProseMirror_h3]:text-lg [&_.ProseMirror_h3]:font-bold [&_.ProseMirror_h3]:mt-2 [&_.ProseMirror_h3]:mb-1 [&_.ProseMirror_a]:text-blue-600 [&_.ProseMirror_a]:underline [&_.ProseMirror_a]:dark:text-blue-400 [&_.ProseMirror_blockquote]:border-l-4 [&_.ProseMirror_blockquote]:border-zinc-300 [&_.ProseMirror_blockquote]:pl-4 [&_.ProseMirror_blockquote]:italic [&_.ProseMirror_blockquote]:dark:border-zinc-600 [&_.ProseMirror_hr]:my-4 [&_.ProseMirror_hr]:border-t [&_.ProseMirror_hr]:border-zinc-300 [&_.ProseMirror_hr]:dark:border-zinc-600 [&_.ProseMirror_img]:rounded-lg [&_.ProseMirror_img]:my-4 [&_.ProseMirror_table]:border-collapse [&_.ProseMirror_table]:w-full [&_.ProseMirror_table]:my-4 [&_.ProseMirror_table_td]:border [&_.ProseMirror_table_td]:border-zinc-300 [&_.ProseMirror_table_td]:px-2 [&_.ProseMirror_table_td]:py-1 [&_.ProseMirror_table_th]:border [&_.ProseMirror_table_th]:border-zinc-300 [&_.ProseMirror_table_th]:px-2 [&_.ProseMirror_table_th]:py-1 [&_.ProseMirror_table_th]:bg-zinc-100 [&_.ProseMirror_table_th]:dark:bg-zinc-800 [&_.ProseMirror_table_th]:font-bold"
        ></div>
    </div>

    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if($error)
        <flux:error>{{ $error }}</flux:error>
    @endif
</div>
