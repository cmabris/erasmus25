# Diagrama de Base de Datos - Erasmus25

Este documento contiene el diagrama entidad-relación (ER) de la base de datos de la aplicación Erasmus25.

## Diagrama ER

```mermaid
erDiagram
    %% Tablas principales del sistema
    users ||--o{ calls : "crea/actualiza"
    users ||--o{ news_posts : "autor/revisa"
    users ||--o{ documents : "crea/actualiza"
    users ||--o{ resolutions : "crea"
    users ||--o{ erasmus_events : "crea"
    users ||--o{ sessions : "tiene"
    
    %% Sistema de programas y años académicos
    programs ||--o{ calls : "tiene"
    programs ||--o{ news_posts : "tiene"
    programs ||--o{ documents : "tiene"
    programs ||--o{ erasmus_events : "tiene"
    
    academic_years ||--o{ calls : "tiene"
    academic_years ||--o{ news_posts : "tiene"
    academic_years ||--o{ documents : "tiene"
    
    %% Sistema de convocatorias
    calls ||--o{ call_phases : "tiene"
    calls ||--o{ call_applications : "recibe"
    calls ||--o{ resolutions : "genera"
    calls ||--o{ erasmus_events : "tiene"
    
    call_phases ||--o{ resolutions : "tiene"
    
    %% Sistema de documentos
    document_categories ||--o{ documents : "categoriza"
    documents ||--o{ media_consents : "referencia"
    
    %% Sistema de noticias
    news_posts }o--o{ news_tags : "etiquetado"
    
    %% Sistema de internacionalización
    languages ||--o{ translations : "tiene"
    
    %% Tablas del sistema
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string remember_token
        string two_factor_secret
        string two_factor_recovery_codes
        timestamp two_factor_confirmed_at
        timestamps
    }
    
    programs {
        bigint id PK
        string code UK
        string name
        string slug UK
        text description
        boolean is_active
        integer order
        timestamps
    }
    
    academic_years {
        bigint id PK
        string year UK
        date start_date
        date end_date
        boolean is_current
        timestamps
    }
    
    calls {
        bigint id PK
        bigint program_id FK
        bigint academic_year_id FK
        string title
        string slug UK
        enum type
        enum modality
        integer number_of_places
        json destinations
        date estimated_start_date
        date estimated_end_date
        text requirements
        text documentation
        text selection_criteria
        json scoring_table
        enum status
        timestamp published_at
        timestamp closed_at
        bigint created_by FK
        bigint updated_by FK
        timestamps
    }
    
    call_phases {
        bigint id PK
        bigint call_id FK
        enum phase_type
        string name
        text description
        date start_date
        date end_date
        boolean is_current
        integer order
        timestamps
    }
    
    call_applications {
        bigint id PK
        bigint call_id FK
        string applicant_name
        string applicant_email
        string applicant_phone
        enum status
        decimal score
        integer position
        text notes
        timestamps
    }
    
    resolutions {
        bigint id PK
        bigint call_id FK
        bigint call_phase_id FK
        enum type
        string title
        text description
        text evaluation_procedure
        date official_date
        timestamp published_at
        bigint created_by FK
        timestamps
    }
    
    documents {
        bigint id PK
        bigint category_id FK
        bigint program_id FK
        bigint academic_year_id FK
        string title
        string slug UK
        text description
        enum document_type
        string version
        boolean is_active
        integer download_count
        bigint created_by FK
        bigint updated_by FK
        timestamps
    }
    
    document_categories {
        bigint id PK
        string name
        string slug UK
        text description
        integer order
        timestamps
    }
    
    news_posts {
        bigint id PK
        bigint program_id FK
        bigint academic_year_id FK
        string title
        string slug UK
        text excerpt
        longtext content
        string country
        string city
        string host_entity
        enum mobility_type
        enum mobility_category
        enum status
        timestamp published_at
        bigint author_id FK
        bigint reviewed_by FK
        timestamp reviewed_at
        timestamps
    }
    
    news_tags {
        bigint id PK
        string name UK
        string slug UK
        timestamps
    }
    
    news_post_tag {
        bigint news_post_id FK
        bigint news_tag_id FK
    }
    
    erasmus_events {
        bigint id PK
        bigint program_id FK
        bigint call_id FK
        string title
        text description
        enum event_type
        datetime start_date
        datetime end_date
        string location
        boolean is_public
        bigint created_by FK
        timestamps
    }
    
    media_consents {
        bigint id PK
        bigint media_id FK
        enum consent_type
        string person_name
        string person_email
        boolean consent_given
        date consent_date
        bigint consent_document_id FK
        date expires_at
        timestamp revoked_at
        text notes
        timestamps
    }
    
    languages {
        bigint id PK
        string code UK
        string name
        boolean is_default
        boolean is_active
        timestamps
    }
    
    translations {
        bigint id PK
        string translatable_type
        bigint translatable_id
        bigint language_id FK
        string field
        text value
        timestamps
    }
    
    sessions {
        string id PK
        bigint user_id FK
        string ip_address
        text user_agent
        longtext payload
        integer last_activity
    }
    
    password_reset_tokens {
        string email PK
        string token
        timestamp created_at
    }
    
    %% Tablas de Laravel Permission (Spatie)
    roles {
        bigint id PK
        string name UK
        string guard_name
        timestamps
    }
    
    permissions {
        bigint id PK
        string name UK
        string guard_name
        timestamps
    }
    
    model_has_roles {
        bigint role_id FK
        string model_type
        bigint model_id
    }
    
    model_has_permissions {
        bigint permission_id FK
        string model_type
        bigint model_id
    }
    
    role_has_permissions {
        bigint permission_id FK
        bigint role_id FK
    }
    
    %% Relaciones de Laravel Permission
    users }o--o{ roles : "tiene"
    users }o--o{ permissions : "tiene"
    roles }o--o{ permissions : "tiene"
    
    %% Tablas adicionales del sistema
    settings {
        bigint id PK
        string key UK
        text value
        string type
        timestamps
    }
    
    newsletter_subscriptions {
        bigint id PK
        string email UK
        string name
        boolean is_active
        json programs
        timestamp subscribed_at
        timestamp unsubscribed_at
        timestamps
    }
    
    notifications {
        string id PK
        string type
        string notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at
        timestamps
    }
    
    audit_logs {
        bigint id PK
        string auditable_type
        bigint auditable_id
        string event
        json changes
        bigint user_id FK
        string ip_address
        string user_agent
        timestamps
    }
    
    cache {
        string key PK
        text value
        integer expiration
    }
    
    cache_locks {
        string key PK
        string owner
        integer expiration
    }
    
    jobs {
        bigint id PK
        string queue
        longtext payload
        unsignedTinyInteger attempts
        integer reserved_at
        integer available_at
        integer created_at
    }
    
    job_batches {
        string id PK
        string name
        integer total_jobs
        integer pending_jobs
        integer failed_jobs
        longtext failed_job_ids
        text options
        integer cancelled_at
        integer created_at
        integer finished_at
    }
    
    failed_jobs {
        bigint id PK
        string uuid UK
        text connection
        text queue
        longtext payload
        longtext exception
        timestamp failed_at
    }
    
    media {
        bigint id PK
        string model_type
        bigint model_id
        string uuid UK
        string collection_name
        string name
        string file_name
        string mime_type
        string disk
        string conversions_disk
        unsignedBigInteger size
        json manipulations
        json custom_properties
        json generated_conversions
        json responsive_images
        integer order_column
        timestamps
    }
    
    %% Relaciones adicionales
    users ||--o{ audit_logs : "genera"
    users ||--o{ notifications : "recibe"
```

## Descripción de las Relaciones

### Relaciones Principales

1. **Programs (Programas)**
   - Tiene múltiples `calls` (convocatorias)
   - Tiene múltiples `news_posts` (noticias)
   - Puede tener múltiples `documents` (documentos)
   - Puede tener múltiples `erasmus_events` (eventos)

2. **Academic Years (Años Académicos)**
   - Tiene múltiples `calls` (convocatorias)
   - Tiene múltiples `news_posts` (noticias)
   - Tiene múltiples `documents` (documentos)

3. **Calls (Convocatorias)**
   - Pertenece a un `program` y un `academic_year`
   - Tiene múltiples `call_phases` (fases)
   - Recibe múltiples `call_applications` (solicitudes)
   - Genera múltiples `resolutions` (resoluciones)
   - Puede tener múltiples `erasmus_events` (eventos)
   - Creada y actualizada por `users`

4. **Call Phases (Fases de Convocatoria)**
   - Pertenece a una `call`
   - Puede tener múltiples `resolutions`

5. **Call Applications (Solicitudes)**
   - Pertenece a una `call`
   - Tiene estado, puntuación y posición

6. **Resolutions (Resoluciones)**
   - Pertenece a una `call` y una `call_phase`
   - Creada por un `user`

7. **Documents (Documentos)**
   - Pertenece a una `document_category`
   - Puede pertenecer a un `program` y/o `academic_year`
   - Creada y actualizada por `users`
   - Referenciada por `media_consents`

8. **News Posts (Noticias)**
   - Puede pertenecer a un `program`
   - Pertenece a un `academic_year`
   - Tiene relación many-to-many con `news_tags`
   - Creada por un `user` (author_id)
   - Revisada por un `user` (reviewed_by)

9. **Erasmus Events (Eventos)**
   - Puede pertenecer a un `program` y/o una `call`
   - Creado por un `user`

10. **Translations (Traducciones)**
    - Polimórfica: puede traducir cualquier modelo
    - Pertenece a un `language`

### Relaciones de Usuario

- Los usuarios pueden crear/actualizar convocatorias, documentos, resoluciones y eventos
- Los usuarios pueden ser autores o revisores de noticias
- Los usuarios tienen sesiones activas
- Los usuarios tienen roles y permisos (Laravel Permission)

### Convenciones de Claves Foráneas

- **Cascade Delete**: Se eliminan en cascada cuando se elimina el padre (ej: `call_phases` cuando se elimina `call`)
- **Null On Delete**: Se establecen en NULL cuando se elimina el padre (ej: `created_by` cuando se elimina `user`)
- **Restrict**: No se permite eliminar si hay registros relacionados (no usado en este esquema)

## Notas Técnicas

- Las tablas `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` son del sistema Laravel
- Las tablas `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` son de Laravel Permission (Spatie)
- La tabla `media` es de Laravel Media Library (Spatie)
- La tabla `translations` es polimórfica y puede traducir cualquier modelo
- Los campos JSON (`destinations`, `scoring_table`, `programs`, `changes`) almacenan estructuras de datos complejas

## Visualización

Este diagrama puede visualizarse en:
- GitHub (renderizado automático de Mermaid)
- Editores Markdown con soporte Mermaid (VS Code, Obsidian, etc.)
- Herramientas online como [Mermaid Live Editor](https://mermaid.live/)
- Documentación generada con herramientas como MkDocs o Docusaurus

