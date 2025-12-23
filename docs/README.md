# Documentación de la Aplicación Erasmus+ Centro (Murcia)

Esta carpeta contiene la documentación técnica de la aplicación web "Erasmus+ Centro (Murcia)", un portal que centraliza toda la información Erasmus+ (Educación Escolar, Formación Profesional y Educación Superior).

## Estructura de la Documentación

### Guía de Desarrollo

#### Carpeta `pasos/` - Historial de Desarrollo

> **Propósito**: La carpeta `pasos/` documenta el proceso de desarrollo de la aplicación chat por chat. Cada archivo contiene los prompts utilizados en orden cronológico junto con una descripción de los resultados obtenidos. Esta documentación sirve como referencia para:
> - Entender el contexto de decisiones tomadas
> - Replicar el proceso en otras aplicaciones similares
> - Facilitar la continuidad entre sesiones de desarrollo
> - Mantener un historial de las interacciones con el asistente IA

- **[Paso 1: Diseño de Base de Datos y Cobertura Completa de Tests](pasos/paso1.md)**: Guión completo con todos los prompts utilizados y resultados obtenidos en el desarrollo inicial de la aplicación. Incluye diseño de base de datos, migraciones, modelos, factories, tests y cobertura al 100%.
- **[Paso 2: Generación de Diagramas de Base de Datos](pasos/paso2.md)**: Documentación del proceso de generación de diagramas de base de datos en múltiples formatos (Mermaid, PNG, SVG, MySQL Workbench).
- **[Paso 3: Planificación de Pasos de Desarrollo](pasos/paso3.md)**: Documentación del proceso de establecimiento de la planificación completa y estructurada de los siguientes pasos en el desarrollo de la aplicación.
- **[Paso 4: Configuración Base y Datos Iniciales](pasos/paso4.md)**: Documentación completa del desarrollo de seeders, configuración de roles y permisos, y middleware. Incluye creación de clases de constantes, tests unitarios y corrección de conflictos.
- **[Paso 5: Form Requests y Validación](pasos/paso5.md)**: Creación de 22 Form Requests para validación de datos, internacionalización de mensajes de error en español e inglés, y configuración de traducciones.
- **[Paso 6: Policies y Autorización](pasos/paso6.md)**: Creación de 11 Policies para autorización, implementación del método `before()` para super-admin, y 80 tests de verificación de permisos por rol.
- **[Paso 7: Página Principal - Home](pasos/paso7.md)**: Desarrollo de la página Home con componentes UI reutilizables, layout público, y componentes de contenido especializados.
- **[Paso 8: Listado y Detalle de Programas](pasos/paso8.md)**: Implementación del listado y detalle de programas con filtros, búsqueda, breadcrumbs, y seeder de datos realistas.
- **[Paso 9: Listado y Detalle de Convocatorias](pasos/paso9.md)**: Implementación del listado y detalle de convocatorias con filtros avanzados, fases, resoluciones, seeders y tests completos.
- **[Paso 10: Listado y Detalle de Noticias](pasos/paso10.md)**: Implementación del listado y detalle de noticias con filtros avanzados, etiquetas interactivas, Media Library para imágenes destacadas, seeders y tests completos.
- **[Paso 11: Listado y Detalle de Documentos](pasos/paso11.md)**: Implementación del listado y detalle de documentos con filtros avanzados, descarga de archivos mediante Media Library, visualización de consentimientos, seeders y tests completos.
- **[Paso 12: Calendario de Eventos](pasos/paso12.md)**: Implementación del calendario de eventos con vista mensual/semanal/diaria, filtros avanzados, integración con convocatorias, seeders con 66 eventos realistas y 60 tests completos.
- **[Planificación de Pasos de Desarrollo](planificacion_pasos.md)**: Planificación completa y estructurada de los siguientes pasos en el desarrollo de la aplicación, incluyendo seeders, Form Requests, Policies, área pública, panel de administración, rutas, funcionalidades avanzadas, testing y optimización.

### Base de Datos

- **[Migraciones - Resumen General](migrations-overview.md)**: Visión general del esquema de base de datos
- **[Migraciones - Estructura Base](migrations-structure.md)**: Programas y años académicos
- **[Migraciones - Convocatorias](migrations-calls.md)**: Sistema de convocatorias, fases y resoluciones
- **[Migraciones - Contenido](migrations-content.md)**: Noticias, documentos y multimedia
- **[Migraciones - Sistema](migrations-system.md)**: Auditoría, notificaciones, configuración e internacionalización

### Modelos

- **[Tests de Relaciones de Modelos](models-tests.md)**: Tests automatizados que verifican todas las relaciones Eloquent (113 tests, 209 assertions)
- **[Plan de Trabajo - Tests de Modelos](models-testing-plan.md)**: Plan original de implementación de tests
- **[Resumen de Testing](testing-summary.md)**: Resumen ejecutivo del estado de los tests

### Roles y Permisos

- **[Sistema de Roles y Permisos](roles-and-permissions.md)**: Documentación completa sobre la estructura de roles y permisos, su uso y ejemplos de implementación

### Autorización (Policies)

- **[Sistema de Policies](policies.md)**: Documentación técnica de las 11 Policies implementadas, métodos de autorización, y matriz de permisos por rol

### Validación

- **[Form Requests](form-requests.md)**: Documentación técnica de los 22 Form Requests implementados, reglas de validación por entidad, e internacionalización de mensajes de error

### Testing

- **[Resumen de Testing](testing-summary.md)**: Estado general de los tests implementados
- **[Tests de Relaciones de Modelos](models-tests.md)**: Tests automatizados que verifican todas las relaciones Eloquent (134 tests, 245 assertions)
- **[Cobertura 100% - Modelos](models-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los modelos
- **[Cobertura 100% - Livewire](livewire-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los componentes Livewire
- **[Mejora de Cobertura - Setting](setting-coverage-improvement.md)**: Detalle de la mejora de cobertura del modelo Setting al 100%
- **Tests de Policies**: 80 tests en `tests/Feature/Policies/` verificando autorización por rol (super-admin, admin, editor, viewer, sin rol)

### Controladores

- *Próximamente: Documentación de controladores*

### Vistas y Componentes

- **[Componentes de la Página Home](home-components.md)**: Documentación técnica de los componentes UI base, componentes de contenido, layout público y componente Livewire Home. Incluye paleta de colores Erasmus+ y guía de uso.
- **[Componentes de Programas](programs-components.md)**: Documentación técnica de los componentes de listado y detalle de programas, incluyendo breadcrumbs, search-input, y seeder de datos.
- **[Componentes de Convocatorias](calls-components.md)**: Documentación técnica de los componentes de listado y detalle de convocatorias, incluyendo filtros avanzados, fases, resoluciones, seeders y tests completos.
- **[Componentes de Noticias](news-components.md)**: Documentación técnica de los componentes de listado y detalle de noticias, incluyendo filtros avanzados, etiquetas interactivas, Media Library para imágenes destacadas, seeders y tests completos.
- **[Componentes de Documentos](documents-components.md)**: Documentación técnica de los componentes de listado y detalle de documentos, incluyendo filtros avanzados, descarga de archivos mediante Media Library, visualización de consentimientos, seeders y tests completos.
- **[Componentes de Eventos](events-components.md)**: Documentación técnica de los componentes de calendario y eventos, incluyendo vista mensual/semanal/diaria, filtros avanzados, integración con convocatorias, seeders y tests completos.

## Información General del Proyecto

### Tecnologías Utilizadas

- **Framework**: Laravel 12
- **Frontend**: Livewire 3 + Flux UI v2
- **Base de Datos**: MySQL 8.0+
- **Autenticación**: Laravel Fortify
- **Permisos**: Spatie Laravel Permission v6
- **Multimedia**: Spatie Laravel Media Library v11
- **Testing**: Pest PHP v4

### Estructura de la Aplicación

La aplicación está dividida en dos áreas principales:

1. **Área Pública (Front-office)**: Consulta de información, transparencia de procesos, difusión de resultados y noticias
2. **Panel de Control (Back-office)**: Gestión integral por usuarios administradores con autenticación

### Programas Soportados

- **Educación Escolar** (KA1xx): Movilidades escolares y de personal
- **Formación Profesional** (KA121-VET): FCT, prácticas, job shadowing, cursos
- **Educación Superior** (KA131-HED): Movilidad de estudios/prácticas y personal

## Convenciones

- Las migraciones siguen el formato: `YYYY_MM_DD_HHMMSS_description.php`
- Los modelos utilizan Eloquent con relaciones bien definidas
- Se utiliza Laravel Permission para roles y permisos
- Se utiliza Laravel Media Library para gestión de archivos multimedia

## Notas Importantes

- La tabla `media` es gestionada por Laravel Media Library
- Las tablas de permisos (`roles`, `permissions`, etc.) son gestionadas por Laravel Permission
- Todas las foreign keys a `users` utilizan `nullOnDelete()` para mantener el historial
- La aplicación soporta multilingüe (ES/EN mínimo)

