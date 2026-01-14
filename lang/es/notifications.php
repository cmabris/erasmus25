<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Traducciones de Notificaciones
    |--------------------------------------------------------------------------
    |
    | Traducciones para el sistema de notificaciones.
    |
    */

    'title' => 'Notificaciones',
    'description' => 'Gestiona tus notificaciones y mantente al día con las últimas actualizaciones',
    'unread' => 'No leída',

    // Bell Component
    'bell' => [
        'label' => 'Notificaciones',
        'tooltip' => 'Ver notificaciones',
    ],

    // Dropdown Component
    'dropdown' => [
        'label' => 'Menú de notificaciones',
        'title' => 'Notificaciones',
        'mark_all_read' => 'Marcar todas como leídas',
        'mark_read' => 'Marcar como leída',
        'empty' => 'No hay notificaciones nuevas',
        'view_all' => 'Ver todas las notificaciones',
    ],

    // Filters
    'filters' => [
        'status' => 'Estado',
        'type' => 'Tipo',
        'all' => 'Todas',
        'unread' => 'No leídas',
        'read' => 'Leídas',
        'all_types' => 'Todos los tipos',
    ],

    // Types
    'types' => [
        'convocatoria' => [
            'label' => 'Convocatoria',
            'published' => [
                'title' => 'Nueva convocatoria: :title',
                'message' => 'Se ha publicado una nueva convocatoria: :title para el programa :program',
            ],
        ],
        'resolucion' => [
            'label' => 'Resolución',
            'published' => [
                'title' => 'Nueva resolución: :title',
                'message' => 'Se ha publicado una nueva resolución: :title para la convocatoria :call',
            ],
        ],
        'noticia' => [
            'label' => 'Noticia',
            'published' => [
                'title' => 'Nueva noticia: :title',
                'message' => 'Se ha publicado una nueva noticia: :title. :excerpt',
            ],
        ],
        'revision' => [
            'label' => 'Revisión',
        ],
        'sistema' => [
            'label' => 'Sistema',
        ],
        'documento' => [
            'label' => 'Documento',
            'published' => [
                'title' => 'Nuevo documento: :title',
                'message' => 'Se ha publicado un nuevo documento: :title (Tipo: :type)',
            ],
        ],
        'unknown' => [
            'label' => 'Desconocido',
        ],
    ],

    // Actions
    'actions' => [
        'mark_read' => 'Marcar como leída',
        'mark_all_read' => 'Marcar todas como leídas',
        'marking' => 'Marcando...',
        'deleting' => 'Eliminando...',
    ],

    // Empty States
    'empty' => [
        'title' => 'No hay notificaciones',
        'no_notifications' => 'No tienes notificaciones en este momento. Te notificaremos cuando haya nuevas actualizaciones.',
        'filtered' => 'No hay notificaciones que coincidan con los filtros seleccionados.',
    ],

    // Batch Actions
    'batch' => [
        'select_all' => 'Seleccionar todas',
        'clear' => 'Limpiar selección',
        'selected_count' => ':count notificación seleccionada|:count notificaciones seleccionadas',
        'mark_read' => 'Marcar seleccionadas como leídas',
        'delete' => 'Eliminar seleccionadas',
        'delete_confirm' => '¿Estás seguro de que deseas eliminar :count notificación seleccionada?|¿Estás seguro de que deseas eliminar :count notificaciones seleccionadas?',
    ],

    // Delete Confirmation
    'delete' => [
        'title' => 'Eliminar notificación',
        'message' => '¿Estás seguro de que deseas eliminar esta notificación? Esta acción no se puede deshacer.',
        'confirm_message' => '¿Estás seguro de que deseas eliminar esta notificación?',
    ],

    // Messages
    'messages' => [
        'deleted_successfully' => 'Notificación eliminada correctamente',
        'batch_deleted_successfully' => ':count notificación eliminada correctamente|:count notificaciones eliminadas correctamente',
    ],

];
