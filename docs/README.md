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
- **[Paso 13: Suscripción a Newsletter](pasos/paso13.md)**: Implementación del sistema completo de suscripción a newsletter con formulario público, verificación por email, baja por token/email, seeder con 80 suscripciones y 45+ tests completos.
- **[Paso 14: Internacionalización Completa](pasos/paso14.md)**: Implementación completa del sistema de internacionalización (i18n) con middleware SetLocale, componente Language Switcher, helpers globales, trait Translatable, traducciones estáticas y dinámicas, internacionalización de todas las vistas públicas (~200 strings), y sistema preparado para añadir nuevos idiomas fácilmente.
- **[Paso 15: Dashboard de Administración](pasos/paso15.md)**: Implementación completa del Dashboard de Administración con estadísticas en tiempo real, gráficos interactivos (Chart.js), actividad reciente, alertas, optimización con caché, animaciones CSS, internacionalización completa, y 29 tests completos. Incluye corrección de 65 tests existentes relacionados con locale.
- **[Paso 16: CRUD de Programas en Panel de Administración](pasos/paso16.md)**: Implementación completa del CRUD de Programas en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de imágenes mediante Laravel Media Library, sistema de traducciones, ordenamiento, SoftDeletes, validación de relaciones, mejoras de UX (notificaciones, tooltips, estados de carga), y 837 tests completos (1955 assertions).
- **[Paso 17: CRUD de Años Académicos en Panel de Administración](pasos/paso17.md)**: Implementación completa del CRUD de Años Académicos en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión del "año actual", SoftDeletes, validación de relaciones, optimizaciones de rendimiento (caché, índices de BD, búsqueda optimizada), manejo de relaciones en soft delete, y 61 tests completos (149 assertions).
- **[Paso 18: CRUD de Convocatorias en Panel de Administración](pasos/paso18.md)**: Implementación completa del CRUD de Convocatorias en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de estados, publicación, gestión de fases y resoluciones, SoftDeletes, validación de relaciones, campos dinámicos, y tests completos.
- **[Paso 19: CRUD de Fases de Convocatorias en Panel de Administración](pasos/paso19.md)**: Implementación completa del CRUD de Fases de Convocatorias en el panel de administración con componentes Livewire (Index, Create, Edit, Show), rutas anidadas, reordenamiento, gestión de fase actual, validación de fechas, SoftDeletes con cascade delete manual, optimizaciones de rendimiento, y 76 tests completos (203 assertions). Incluye corrección de test intermitente en Events/Show.
- **[Paso 20: CRUD de Resoluciones en Panel de Administración](pasos/paso20.md)**: Implementación completa del CRUD de Resoluciones en el panel de administración con componentes Livewire (Index, Create, Edit, Show), rutas anidadas, gestión de PDFs mediante FilePond y Media Library, sistema de publicación, SoftDeletes, validación de relaciones, optimizaciones de consultas, mejoras de UX, y 68 tests completos (151 assertions).
- **[Paso 21: CRUD de Noticias en Panel de Administración](pasos/paso21.md)**: Implementación completa del CRUD de Noticias en el panel de administración con componentes Livewire (Index, Create, Edit, Show), editor Tiptap, gestión avanzada de imágenes, gestión de etiquetas, publicación/despublicación, SoftDeletes, validación de relaciones, y 1231 tests completos.
- **[Paso 22: CRUD de Etiquetas de Noticias en Panel de Administración](pasos/paso22.md)**: Implementación completa del CRUD de Etiquetas de Noticias en el panel de administración con componentes Livewire (Index, Create, Edit, Show), SoftDeletes, validación de relaciones, generación automática de slugs, gestión de noticias asociadas, y 59 tests completos (129 assertions).
- **[Paso 23: CRUD de Documentos en Panel de Administración](pasos/paso23.md)**: Implementación completa del CRUD de Documentos en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de archivos mediante FilePond y Media Library, SoftDeletes, validación de relaciones con MediaConsent, gestión de consentimientos asociados, y 152 tests completos (~365 assertions).
- **[Paso 24: CRUD de Categorías de Documentos en Panel de Administración](pasos/paso24.md)**: Implementación completa del CRUD de Categorías de Documentos en el panel de administración con componentes Livewire (Index, Create, Edit, Show), SoftDeletes, validación de relaciones, generación automática de slugs, gestión de orden, y 111 tests completos. Incluye corrección de tests de modelos para reflejar comportamiento correcto de SoftDeletes con cascade delete.
- **[Paso 25: CRUD de Eventos Erasmus+ en Panel de Administración](pasos/paso25.md)**: Implementación completa del CRUD de Eventos Erasmus+ en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de imágenes mediante FilePond y Media Library, SoftDeletes con gestión personalizada de imágenes, vista de calendario interactiva (mes/semana/día), asociación con programas y convocatorias, y 135 tests completos (332 assertions).
- **[Paso 26: CRUD de Usuarios y Roles en Panel de Administración](pasos/paso26.md)**: Implementación completa del CRUD de Usuarios y Roles en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de roles mediante Spatie Permission, visualización de audit logs con estadísticas, SoftDeletes, validación de seguridad (usuario no puede eliminarse a sí mismo ni modificar sus propios roles), 4 componentes UI reutilizables, optimizaciones de consultas, y 172 tests completos (397 assertions). Incluye corrección de 14 tests que fallaban en paralelo.
- **[Paso 27: CRUD de Roles y Permisos en Panel de Administración](pasos/paso27.md)**: Implementación completa del CRUD de Roles y Permisos en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de permisos agrupados por módulo, visualización de usuarios asignados, protección de roles del sistema, validación de nombres de roles según constantes del sistema, registro manual de RolePolicy, y 153 tests completos (249 assertions). Incluye corrección de problemas de autorización y registro de policy.
- **[Paso 28: CRUD de Configuración del Sistema en Panel de Administración](pasos/paso28.md)**: Implementación completa del CRUD de Configuración del Sistema en el panel de administración con componentes Livewire (Index, Edit), validación automática por tipo de dato, gestión de traducciones, subida de imágenes para logo del centro mediante FilePond, formateo inteligente de valores, registro de auditoría, integración dinámica del logo y nombre del centro en navegación pública y dashboard, y 82 tests completos (176 assertions). Incluye corrección de tests de permisos y componentes públicos.
- **[Paso 29: CRUD de Traducciones en Panel de Administración](pasos/paso29.md)**: Implementación completa del CRUD de Traducciones en el panel de administración con componentes Livewire (Index, Create, Edit, Show), gestión de traducciones polimórficas (Program, Setting), filtros avanzados, búsqueda en tiempo real, validación de unicidad, manejo de SoftDeletes, optimizaciones de rendimiento (caché, índices de BD), limpieza de caché en tests para evitar interferencias en paralelo, y 66 tests completos (152 assertions). Incluye corrección de tests de permisos y optimizaciones de consultas.
- **[Paso 30: Auditoría y Logs en Panel de Administración](pasos/paso30.md)**: Implementación completa del sistema de Auditoría y Logs en el panel de administración con componentes Livewire (Index, Show), integración con Spatie Laravel Activitylog v4, logging automático en 6 modelos y logging manual en 9 acciones especiales, filtros avanzados, visualización de cambios, exportación a Excel mediante Laravel Excel, optimizaciones de rendimiento (índices, caché, eager loading), limpieza completa de código legacy (eliminación de modelo AuditLog, factory, tests y tabla audit_logs), corrección de 41 tests fallando en paralelo, y 85 tests completos (185 assertions). Estado final: 2277 tests pasando sin problemas.
- **[Paso 31: Gestión de Suscripciones Newsletter en Panel de Administración](pasos/paso31.md)**: Implementación completa del sistema de gestión de Suscripciones Newsletter en el panel de administración con componentes Livewire (Index, Show), filtros avanzados (búsqueda, programa, estado, verificación), exportación a Excel mediante Laravel Excel, eliminación con confirmación (hard delete para GDPR), diseño moderno y responsive con Flux UI, optimizaciones de rendimiento (índices de BD), corrección de 5 tests existentes fallando en paralelo, y 142 tests completos (339 assertions). Estado final: 2352 tests pasando sin problemas.
- **[Paso 32: Rutas Públicas (Paso 3.6.1)](pasos/paso32.md)**: Implementación completa de las rutas públicas con organización, route model binding, documentación y 39 tests completos.
- **[Paso 33: Rutas de Administración (Paso 3.6.2)](pasos/paso33.md)**: Implementación completa de las rutas de administración con middleware, autorización, estructura de rutas anidadas y 90 tests completos.
- **[Paso 34: Navegación Principal (Paso 3.6.3)](pasos/paso34.md)**: Mejoras en la navegación pública y de administración, componentes responsivos, integración de permisos y 41 tests completos.
- **[Paso 35: Breadcrumbs (Paso 3.6.4)](pasos/paso35.md)**: Implementación completa del sistema de breadcrumbs (migas de pan) en todas las vistas públicas y de administración, con componente reutilizable, traducciones y 27 tests completos.
- **[Paso 36: Búsqueda Global (Paso 3.7.1)](pasos/paso36.md)**: Implementación completa de la búsqueda global con detección automática de contexto (público vs admin), búsqueda unificada en 4 tipos de entidades (programas, convocatorias, noticias, documentos), resultados agrupados por tipo, filtros avanzados, enlaces dinámicos según contexto, layout adaptativo, optimizaciones de rendimiento (eager loading, debounce, límites), integración en navegación pública y admin, y 24 tests completos (50 assertions). Estado final: 2546 tests pasando sin problemas.
- **[Paso 37: Sistema de Notificaciones (Paso 3.7.2)](pasos/paso37.md)**: Implementación completa del sistema de notificaciones con servicio NotificationService, componentes Livewire (Bell, Dropdown, Index), integración con Observers para notificaciones automáticas al publicar contenido, polling cada 30 segundos, y 111 tests completos (236 assertions).
- **[Paso 38: Exportación de Datos (Paso 3.7.3)](pasos/paso38.md)**: Implementación completa del sistema de exportación de datos con exportación de Convocatorias y Resoluciones a Excel, verificación y mejora de exportación de Newsletter, traducciones consistentes, botones de exportación con estados de carga, autorización en todas las exportaciones, formateo adecuado de datos para Excel, y 58 tests completos (132 assertions). Estado final: 2715 tests pasando sin problemas.
- **[Paso 39: Importación de Datos (Paso 3.7.4)](pasos/paso39.md)**: Implementación completa del sistema de importación de datos para Convocatorias y Usuarios desde Excel/CSV, modo dry-run para validación, plantillas Excel descargables, manejo de errores con reporte detallado, y 71 tests completos (189 assertions). Estado final: 2786 tests pasando sin problemas.
- **[Paso 40: Tests de Form Requests (Paso 3.8.1)](pasos/paso40.md)**: Completar 100% de cobertura en los 30 Form Requests de la aplicación con 538 tests y 1,391 assertions.
- **[Paso 41: Tests de Policies (Paso 3.8.2)](pasos/paso41.md)**: Completar 100% de cobertura en las 16 Policies de la aplicación (170/170 líneas, 118/118 métodos) con 140 tests totales (569 assertions).
- **[Paso 42: Tests de Componentes Livewire Públicos (Paso 3.8.3)](pasos/paso42.md)**: Completar 100% de cobertura en los 15 componentes Livewire públicos (854/854 líneas, 135/135 métodos) con 47+ tests nuevos.
- **[Paso 43: Tests de Componentes Livewire Admin (Paso 3.8.4)](pasos/paso43.md)**: Completar cobertura de tests en componentes Livewire de administración incluyendo Import, Dashboard, Phases, Translations, Programs y Settings.
- **[Paso 44: Tests de Rutas y Observers (Paso 3.8.5)](pasos/paso44.md)**: Tests para rutas públicas y de administración, Observers (CallObserver, ResolutionObserver), Middleware SetLocale, y componentes Livewire Admin. Estado final: 3,702 tests (8,429 assertions).
- **[Paso 45: Tests de Integración (Paso 3.8.7)](pasos/paso45.md)**: Tests completos de integración incluyendo helpers, Imports, Exports, Middleware, Form Requests y Providers. Estado final: 3,782 tests (8,564 assertions).
- **[Paso 46: Optimización de Consultas (Paso 3.9.1)](pasos/paso46.md)**: Detección y eliminación de N+1 queries, sistema de caché para datos de referencia, índices de BD optimizados, exports con chunking, y 29 tests de rendimiento (83 assertions).
- **[Paso 47: Optimización de Imágenes (Paso 3.9.3)](pasos/paso47.md)**: Configuración de Media Library con WebP, conversiones optimizadas en 4 modelos, componente responsive-image.blade.php, y regeneración de 38 medios. Estado final: 3,830 tests (8,673 assertions).
- **[Paso 48: SEO Completo (Paso 3.9.4-3.9.5)](pasos/paso48.md)**: Componentes SEO (meta tags, JSON-LD), sitemap.xml dinámico, robots.txt mejorado, Open Graph y Twitter Cards. Estado final: 3,867 tests (8,793 assertions).
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

- **[Sistema de Policies](policies.md)**: Documentación técnica de las 16 Policies implementadas, métodos de autorización, matriz de permisos por rol, patrones de uso en Livewire y Blade, y casos especiales documentados

### Validación

- **[Form Requests](form-requests.md)**: Documentación técnica de los 30 Form Requests implementados, reglas de validación por entidad, e internacionalización de mensajes de error

### Testing

**Estado actual**: 3,867 tests, 8,793 assertions ✅

- **[Resumen de Testing](testing-summary.md)**: Estado general de los tests implementados
- **[Tests de Relaciones de Modelos](models-tests.md)**: Tests automatizados que verifican todas las relaciones Eloquent
- **[Cobertura 100% - Modelos](models-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los modelos
- **[Cobertura 100% - Livewire](livewire-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los componentes Livewire
- **[Mejora de Cobertura - Setting](setting-coverage-improvement.md)**: Detalle de la mejora de cobertura del modelo Setting al 100%
- **Tests de Form Requests**: 100% de cobertura en 30 Form Requests (538 tests, 1,391 assertions)
- **Tests de Policies**: 100% de cobertura en 16 Policies (140 tests, 569 assertions)

### Vistas y Componentes

- **[Componentes de la Página Home](home-components.md)**: Documentación técnica de los componentes UI base, componentes de contenido, layout público y componente Livewire Home. Incluye paleta de colores Erasmus+ y guía de uso.
- **[Componentes de Programas](programs-components.md)**: Documentación técnica de los componentes de listado y detalle de programas, incluyendo breadcrumbs, search-input, y seeder de datos.
- **[Componentes de Convocatorias](calls-components.md)**: Documentación técnica de los componentes de listado y detalle de convocatorias, incluyendo filtros avanzados, fases, resoluciones, seeders y tests completos.
- **[Componentes de Noticias](news-components.md)**: Documentación técnica de los componentes de listado y detalle de noticias, incluyendo filtros avanzados, etiquetas interactivas, Media Library para imágenes destacadas, seeders y tests completos.
- **[Componentes de Documentos](documents-components.md)**: Documentación técnica de los componentes de listado y detalle de documentos, incluyendo filtros avanzados, descarga de archivos mediante Media Library, visualización de consentimientos, seeders y tests completos.
- **[Componentes de Eventos](events-components.md)**: Documentación técnica de los componentes de calendario y eventos, incluyendo vista mensual/semanal/diaria, filtros avanzados, integración con convocatorias, seeders y tests completos.
- **[Sistema de Newsletter](newsletter-components.md)**: Documentación técnica del sistema completo de suscripción a newsletter, incluyendo componentes Livewire, email de verificación, scopes del modelo, seeders y tests completos.
- **[Sistema de Internacionalización (i18n)](i18n-system.md)**: Documentación completa del sistema de internacionalización, incluyendo middleware, componente Language Switcher, helpers, trait Translatable, traducciones estáticas y dinámicas, y guía para añadir nuevos idiomas.

### Flujos de Trabajo

- **[Flujos de Trabajo Principales](flujos-trabajo.md)**: Documentación completa de los flujos operativos de la aplicación, incluyendo gestión de convocatorias (ciclo de vida completo), publicación de noticias, gestión de documentos, newsletter (con cumplimiento GDPR), eventos, auditoría, importación/exportación y notificaciones.
- **[Funcionalidades por Módulo](funcionalidades-modulos.md)**: Índice consolidado de todas las funcionalidades organizadas por módulo (12 módulos), con rutas, componentes, permisos y enlaces a documentación detallada.

### Rutas y Navegación

- **[Rutas Públicas](public-routes.md)**: Documentación completa de todas las rutas públicas de la aplicación, incluyendo route model binding, slugs, estructura de rutas, y tests completos.
- **[Rutas de Administración](admin-routes.md)**: Documentación completa de todas las rutas de administración, incluyendo middleware, autorización, estructura de rutas anidadas, y tests completos.
- **[Autorización en Rutas de Administración](admin-routes-authorization.md)**: Documentación sobre la decisión de diseño de autorización en rutas de administración, análisis de opciones, y implementación actual.
- **[Navegación Principal](navigation.md)**: Documentación completa de la navegación pública y de administración, incluyendo estructura de menús, permisos, selector de idioma, y guía para añadir nuevos elementos.
- **[Breadcrumbs](breadcrumbs.md)**: Documentación completa del sistema de breadcrumbs (migas de pan), incluyendo componente reutilizable, patrones de uso para vistas públicas y de administración, tabla de iconos por módulo, y ejemplos de implementación.

### Funcionalidades Avanzadas

- **[Búsqueda Global](global-search.md)**: Documentación técnica completa de la búsqueda global, incluyendo detección automática de contexto (público vs admin), búsqueda unificada en múltiples entidades, resultados agrupados por tipo, filtros avanzados, enlaces dinámicos según contexto, layout adaptativo, optimizaciones de rendimiento, y guía de uso con ejemplos.
- **[Sistema de Notificaciones](notifications-system.md)**: Documentación técnica del sistema de notificaciones con servicio NotificationService, componentes Livewire (Bell, Dropdown, Index), integración con Observers para notificaciones automáticas, polling cada 30 segundos, y guía de implementación.
- **[Sistema de Exportación](exports-system.md)**: Documentación técnica completa del sistema de exportación de datos, incluyendo exportación de Convocatorias, Resoluciones y Newsletter a Excel, filtros aplicados, formateo de datos, autorización, traducciones, y guía de uso con ejemplos.
- **[Sistema de Importación](imports-system.md)**: Documentación técnica del sistema de importación de datos para Convocatorias y Usuarios desde Excel/CSV, modo dry-run, plantillas descargables, y manejo de errores.

### Optimizaciones

- **[Guía Completa de Optimizaciones](optimizaciones.md)**: Documentación técnica consolidada de todas las optimizaciones de rendimiento implementadas, incluyendo consultas N+1, sistema de caché, índices de BD, optimización de imágenes, SEO y exports con chunking.
- **[Detección de N+1 Queries](debugbar-n1-detection.md)**: Guía para detectar y solucionar problemas de N+1 queries usando Laravel Debugbar.

### Panel de Administración

- **[Dashboard de Administración](admin-dashboard.md)**: Documentación técnica completa del Dashboard de Administración, incluyendo estadísticas, gráficos de actividad, alertas, actividad reciente, optimización con caché, y guía de extensibilidad.
- **[CRUD de Programas](admin-programs-crud.md)**: Documentación técnica completa del sistema CRUD de Programas en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de imágenes, traducciones, ordenamiento, SoftDeletes, validación de relaciones, y 837 tests completos.
- **[CRUD de Años Académicos](admin-academic-years-crud.md)**: Documentación técnica completa del sistema CRUD de Años Académicos en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión del "año actual", SoftDeletes, validación de relaciones, optimizaciones de rendimiento (caché, índices), y 61 tests completos (149 assertions).
- **[CRUD de Convocatorias](admin-calls-crud.md)**: Documentación técnica completa del sistema CRUD de Convocatorias en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de estados, publicación, gestión de fases y resoluciones, SoftDeletes, validación de relaciones, y campos dinámicos.
- **[CRUD de Fases de Convocatorias](admin-call-phases-crud.md)**: Documentación técnica completa del sistema CRUD de Fases de Convocatorias en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), rutas anidadas, reordenamiento, gestión de fase actual, validación de fechas, SoftDeletes con cascade delete manual, y 76 tests completos (203 assertions).
- **[CRUD de Resoluciones](admin-resolutions-crud.md)**: Documentación técnica completa del sistema CRUD de Resoluciones en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), rutas anidadas, gestión de PDFs mediante FilePond y Media Library, sistema de publicación, SoftDeletes, validación de relaciones, optimizaciones de consultas, mejoras de UX, y 68 tests completos (151 assertions).
- **[CRUD de Noticias](admin-news-crud.md)**: Documentación técnica completa del sistema CRUD de Noticias en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), editor Tiptap para contenido enriquecido, gestión avanzada de imágenes (soft delete, restauración, eliminación permanente), selección de imágenes desde modal, gestión de etiquetas many-to-many, publicación/despublicación, SoftDeletes, y tests completos.
- **[CRUD de Etiquetas de Noticias](admin-news-tags-crud.md)**: Documentación técnica completa del sistema CRUD de Etiquetas de Noticias en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), SoftDeletes, validación de relaciones, generación automática de slugs, gestión de noticias asociadas, y 59 tests completos (129 assertions).
- **[CRUD de Documentos](admin-documents-crud.md)**: Documentación técnica completa del sistema CRUD de Documentos en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de archivos mediante FilePond y Media Library, SoftDeletes, validación de relaciones con MediaConsent, gestión de consentimientos asociados, y 152 tests completos (~365 assertions).
- **[CRUD de Categorías de Documentos](admin-document-categories-crud.md)**: Documentación técnica completa del sistema CRUD de Categorías de Documentos en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), SoftDeletes, validación de relaciones, generación automática de slugs, gestión de orden, y 111 tests completos.
- **[CRUD de Eventos Erasmus+](admin-events-crud.md)**: Documentación técnica completa del sistema CRUD de Eventos Erasmus+ en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de imágenes mediante FilePond y Media Library, SoftDeletes con gestión personalizada de imágenes, vista de calendario interactiva (mes/semana/día), asociación con programas y convocatorias, y 135 tests completos (332 assertions).
- **[CRUD de Usuarios y Roles](admin-users-crud.md)**: Documentación técnica completa del sistema CRUD de Usuarios y Roles en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de roles mediante Spatie Permission, visualización de audit logs con estadísticas, SoftDeletes, validación de seguridad, 4 componentes UI reutilizables, optimizaciones de consultas, y 172 tests completos (397 assertions).
- **[CRUD de Roles y Permisos](admin-roles-crud.md)**: Documentación técnica completa del sistema CRUD de Roles y Permisos en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de permisos agrupados por módulo, visualización de usuarios asignados, protección de roles del sistema, validación de nombres de roles, registro manual de RolePolicy, y 153 tests completos (249 assertions).
- **[CRUD de Configuración del Sistema](admin-settings-crud.md)**: Documentación técnica completa del sistema CRUD de Configuración del Sistema en el panel de administración, incluyendo componentes Livewire (Index, Edit), validación automática por tipo de dato (string, integer, boolean, json), gestión de traducciones, subida de imágenes para logo del centro, formateo inteligente de valores, registro de auditoría, y 82 tests completos (176 assertions).
- **[CRUD de Traducciones](admin-translations-crud.md)**: Documentación técnica completa del sistema CRUD de Traducciones en el panel de administración, incluyendo componentes Livewire (Index, Create, Edit, Show), gestión de traducciones polimórficas (Program, Setting), filtros avanzados, búsqueda en tiempo real, validación de unicidad, manejo de SoftDeletes, optimizaciones de rendimiento (caché, índices de BD), y 66 tests completos (152 assertions).
- **[CRUD de Auditoría y Logs](admin-audit-logs.md)**: Documentación técnica completa del sistema de Auditoría y Logs en el panel de administración, incluyendo componentes Livewire (Index, Show), integración con Spatie Laravel Activitylog v4, logging automático y manual, filtros avanzados, visualización de cambios, exportación a Excel, optimizaciones de rendimiento (índices, caché, eager loading), y 85 tests completos (185 assertions).
- **[CRUD de Suscripciones Newsletter](admin-newsletter-subscriptions-crud.md)**: Documentación técnica completa del sistema de gestión de Suscripciones Newsletter en el panel de administración, incluyendo componentes Livewire (Index, Show), filtros avanzados (búsqueda, programa, estado, verificación), exportación a Excel mediante Laravel Excel, eliminación con confirmación (hard delete para GDPR), diseño moderno y responsive con Flux UI, optimizaciones de rendimiento (índices de BD), y 142 tests completos (339 assertions).

## Información General del Proyecto

### Tecnologías Utilizadas

- **Framework**: Laravel 12
- **Frontend**: Livewire 3 + Flux UI v2
- **CSS**: Tailwind CSS v4
- **Base de Datos**: MySQL 8.0+
- **Autenticación**: Laravel Fortify
- **Permisos**: Spatie Laravel Permission v6
- **Multimedia**: Spatie Laravel Media Library v11
- **Auditoría**: Spatie Laravel Activitylog v4
- **Excel**: Laravel Excel (Maatwebsite/Excel)
- **Editor de Texto**: Tiptap (editor de contenido enriquecido)
- **Gráficos**: Chart.js (dashboard de administración)
- **Upload de Archivos**: FilePond
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
- La tabla `activity_log` es gestionada por Spatie Laravel Activitylog
- Todas las foreign keys a `users` utilizan `nullOnDelete()` para mantener el historial
- La aplicación soporta multilingüe (ES/EN mínimo)
- Todos los modelos principales implementan SoftDeletes
- Las imágenes se convierten automáticamente a formato WebP para optimización
- El sistema de notificaciones utiliza polling cada 30 segundos

---

**Última actualización**: Enero 2026  
**Estado del proyecto**: En desarrollo activo (Paso 3.10 - Documentación Final)

