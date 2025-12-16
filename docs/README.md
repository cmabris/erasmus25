# Documentación de la Aplicación Erasmus+ Centro (Murcia)

Esta carpeta contiene la documentación técnica de la aplicación web "Erasmus+ Centro (Murcia)", un portal que centraliza toda la información Erasmus+ (Educación Escolar, Formación Profesional y Educación Superior).

## Estructura de la Documentación

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

### Testing

- **[Resumen de Testing](testing-summary.md)**: Estado general de los tests implementados
- **[Tests de Relaciones de Modelos](models-tests.md)**: Tests automatizados que verifican todas las relaciones Eloquent (134 tests, 245 assertions)
- **[Cobertura 100% - Modelos](models-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los modelos
- **[Cobertura 100% - Livewire](livewire-coverage-100.md)**: Detalle del proceso para alcanzar 100% de cobertura en todos los componentes Livewire
- **[Mejora de Cobertura - Setting](setting-coverage-improvement.md)**: Detalle de la mejora de cobertura del modelo Setting al 100%

### Controladores

- *Próximamente: Documentación de controladores*

### Vistas y Componentes

- *Próximamente: Documentación de vistas y componentes Livewire*

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

