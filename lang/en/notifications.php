<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Translations
    |--------------------------------------------------------------------------
    |
    | Translations for the notification system.
    |
    */

    'title' => 'Notifications',
    'description' => 'Manage your notifications and stay up to date with the latest updates',
    'unread' => 'Unread',

    // Bell Component
    'bell' => [
        'label' => 'Notifications',
        'tooltip' => 'View notifications',
    ],

    // Dropdown Component
    'dropdown' => [
        'label' => 'Notifications menu',
        'title' => 'Notifications',
        'mark_all_read' => 'Mark all as read',
        'mark_read' => 'Mark as read',
        'empty' => 'No new notifications',
        'view_all' => 'View all notifications',
    ],

    // Filters
    'filters' => [
        'status' => 'Status',
        'type' => 'Type',
        'all' => 'All',
        'unread' => 'Unread',
        'read' => 'Read',
        'all_types' => 'All types',
    ],

    // Types
    'types' => [
        'convocatoria' => [
            'label' => 'Call',
            'published' => [
                'title' => 'New call: :title',
                'message' => 'A new call has been published: :title for program :program',
            ],
        ],
        'resolucion' => [
            'label' => 'Resolution',
            'published' => [
                'title' => 'New resolution: :title',
                'message' => 'A new resolution has been published: :title for call :call',
            ],
        ],
        'noticia' => [
            'label' => 'News',
            'published' => [
                'title' => 'New news: :title',
                'message' => 'A new news has been published: :title. :excerpt',
            ],
        ],
        'revision' => [
            'label' => 'Review',
        ],
        'sistema' => [
            'label' => 'System',
        ],
        'documento' => [
            'label' => 'Document',
            'published' => [
                'title' => 'New document: :title',
                'message' => 'A new document has been published: :title (Type: :type)',
            ],
        ],
        'unknown' => [
            'label' => 'Unknown',
        ],
    ],

    // Actions
    'actions' => [
        'mark_read' => 'Mark as read',
        'mark_all_read' => 'Mark all as read',
        'marking' => 'Marking...',
        'deleting' => 'Deleting...',
    ],

    // Empty States
    'empty' => [
        'title' => 'No notifications',
        'no_notifications' => 'You have no notifications at this time. We will notify you when there are new updates.',
        'filtered' => 'No notifications match the selected filters.',
    ],

    // Batch Actions
    'batch' => [
        'select_all' => 'Select all',
        'clear' => 'Clear selection',
        'selected_count' => ':count notification selected|:count notifications selected',
        'mark_read' => 'Mark selected as read',
        'delete' => 'Delete selected',
        'delete_confirm' => 'Are you sure you want to delete :count selected notification?|Are you sure you want to delete :count selected notifications?',
    ],

    // Delete Confirmation
    'delete' => [
        'title' => 'Delete notification',
        'message' => 'Are you sure you want to delete this notification? This action cannot be undone.',
        'confirm_message' => 'Are you sure you want to delete this notification?',
    ],

    // Messages
    'messages' => [
        'deleted_successfully' => 'Notification deleted successfully',
        'batch_deleted_successfully' => ':count notification deleted successfully|:count notifications deleted successfully',
    ],

];
