# Migraciones - Resumen General

## Visión General del Esquema de Base de Datos

La aplicación utiliza un esquema de base de datos completo que soporta la gestión integral de programas Erasmus+ con todas sus funcionalidades.

### Estadísticas

- **Total de tablas**: 26 tablas
- **Tablas del sistema**: 3 (users, cache, jobs)
- **Tablas del proyecto**: 20
- **Tablas de librerías**: 3 (roles, permissions, media)

### Orden de Ejecución de Migraciones

Las migraciones se ejecutan en el siguiente orden:

1. **Sistema Base** (Laravel)
   - `0001_01_01_000000_create_users_table.php`
   - `0001_01_01_000001_create_cache_table.php`
   - `0001_01_01_000002_create_jobs_table.php`
   - `2025_09_22_145432_add_two_factor_columns_to_users_table.php`

2. **Estructura Base del Proyecto**
   - `2025_12_12_193645_create_programs_table.php`
   - `2025_12_12_193647_create_academic_years_table.php`

3. **Sistema de Convocatorias**
   - `2025_12_12_193712_create_calls_table.php`
   - `2025_12_12_193714_create_call_phases_table.php`
   - `2025_12_12_193717_create_call_applications_table.php`
   - `2025_12_12_193747_create_resolutions_table.php`

4. **Sistema de Noticias**
   - `2025_12_12_193821_create_news_posts_table.php`
   - `2025_12_12_193821_create_news_tags_table.php`
   - `2025_12_12_193822_create_news_post_tag_table.php` (tabla pivot)

5. **Sistema de Documentos**
   - `2025_12_12_193902_create_document_categories_table.php`
   - `2025_12_12_193902_create_documents_table.php`

6. **Multimedia y Consentimientos**
   - `2025_12_12_193918_create_media_consents_table.php`
   - `2025_12_12_200313_create_media_table.php` (Laravel Media Library)
   - `2025_12_12_200314_add_foreign_key_to_media_consents_table.php`

7. **Sistema de Auditoría y Notificaciones**
   - `2025_12_12_193919_create_audit_logs_table.php`
   - `2025_12_12_193919_create_notifications_table.php`
   - `2025_12_12_193919_create_newsletter_subscriptions_table.php`

8. **Calendario**
   - `2025_12_12_193919_create_erasmus_events_table.php`

9. **Internacionalización**
   - `2025_12_12_193919_create_languages_table.php`
   - `2025_12_12_193919_create_translations_table.php`

10. **Configuración**
    - `2025_12_12_193919_create_settings_table.php`

11. **Permisos** (Laravel Permission)
    - `2025_12_12_195228_create_permission_tables.php`

## Relaciones Principales

### Jerarquía de Datos

```
Programs (Programas)
├── Calls (Convocatorias)
│   ├── CallPhases (Fases)
│   ├── CallApplications (Solicitudes)
│   └── Resolutions (Resoluciones)
├── NewsPosts (Noticias)
└── Documents (Documentos)

AcademicYears (Años Académicos)
├── Calls
├── NewsPosts
└── Documents

Users (Usuarios)
├── Calls (created_by, updated_by)
├── NewsPosts (author_id, reviewed_by)
├── Documents (created_by, updated_by)
├── Resolutions (created_by)
└── ErasmusEvents (created_by)
```

### Tablas de Relación Many-to-Many

- `news_post_tag`: Relación entre noticias y etiquetas
- `model_has_roles`: Relación entre usuarios y roles (Laravel Permission)
- `model_has_permissions`: Relación entre usuarios y permisos (Laravel Permission)
- `role_has_permissions`: Relación entre roles y permisos (Laravel Permission)

## Convenciones de Nomenclatura

### Foreign Keys

- Todas las foreign keys a `users` utilizan `nullOnDelete()` para mantener el historial cuando se elimina un usuario
- Las foreign keys a tablas principales utilizan `cascadeOnDelete()` para mantener la integridad referencial
- Las foreign keys opcionales utilizan `nullOnDelete()`

### Índices

- Se crean índices compuestos para consultas frecuentes
- Se indexan campos de búsqueda y filtrado común
- Se indexan foreign keys para mejorar el rendimiento

### Campos JSON

- `destinations` en `calls`: Array de países/ciudades/entidades
- `scoring_table` en `calls`: Estructura del baremo de evaluación
- `programs` en `newsletter_subscriptions`: Array de programas de interés
- `changes` en `audit_logs`: Objeto con cambios antes/después

## Consideraciones Especiales

### MySQL 8+

Para MySQL 8+ con utf8mb4, los campos `name` y `guard_name` en las tablas de permisos están limitados a 191 caracteres para evitar el error "Specified key was too long".

### Laravel Media Library

La tabla `media` es gestionada por Laravel Media Library. La foreign key en `media_consents` se agrega en una migración separada que se ejecuta después de instalar el paquete.

### Laravel Permission

Las tablas de permisos (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) son gestionadas por Laravel Permission y se crean automáticamente al instalar el paquete.

## Documentación Detallada

Para información detallada sobre cada grupo de migraciones, consulta:

- [Estructura Base](migrations-structure.md)
- [Convocatorias](migrations-calls.md)
- [Contenido](migrations-content.md)
- [Sistema](migrations-system.md)

