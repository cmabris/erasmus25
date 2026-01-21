# Registro de Decisiones Técnicas (ADR)

Este documento registra las decisiones técnicas importantes tomadas durante el desarrollo de la aplicación Erasmus+ Centro (Murcia). Cada decisión sigue el formato ADR (Architecture Decision Record).

---

## Índice

### Arquitectura
- [ADR-001: Uso de Livewire 3 en lugar de Inertia/Vue](#adr-001-uso-de-livewire-3-en-lugar-de-inertiavue)
- [ADR-002: Uso de Flux UI como biblioteca de componentes](#adr-002-uso-de-flux-ui-como-biblioteca-de-componentes)
- [ADR-003: Separación de área pública y panel de administración](#adr-003-separación-de-área-pública-y-panel-de-administración)

### Base de Datos
- [ADR-004: Uso de SoftDeletes en todos los modelos principales](#adr-004-uso-de-softdeletes-en-todos-los-modelos-principales)
- [ADR-005: Cascade delete manual en lugar de automático](#adr-005-cascade-delete-manual-en-lugar-de-automático)
- [ADR-006: Uso de slugs en URLs públicas](#adr-006-uso-de-slugs-en-urls-públicas)

### Autenticación y Autorización
- [ADR-007: Uso de Laravel Fortify para autenticación](#adr-007-uso-de-laravel-fortify-para-autenticación)
- [ADR-008: Estructura de 4 roles con permisos granulares](#adr-008-estructura-de-4-roles-con-permisos-granulares)
- [ADR-009: Autorización en componentes Livewire vs middleware](#adr-009-autorización-en-componentes-livewire-vs-middleware)

### Multimedia
- [ADR-010: Uso de Spatie Media Library para gestión de archivos](#adr-010-uso-de-spatie-media-library-para-gestión-de-archivos)
- [ADR-011: Conversión automática a WebP](#adr-011-conversión-automática-a-webp)
- [ADR-012: Soft delete de imágenes en News y Events](#adr-012-soft-delete-de-imágenes-en-news-y-events)

### Internacionalización
- [ADR-013: Sistema dual de traducciones](#adr-013-sistema-dual-de-traducciones)
- [ADR-014: Persistencia de idioma en sesión](#adr-014-persistencia-de-idioma-en-sesión)

### Testing
- [ADR-015: Uso de Pest PHP como framework de testing](#adr-015-uso-de-pest-php-como-framework-de-testing)
- [ADR-016: Estrategia de tests por capas](#adr-016-estrategia-de-tests-por-capas)
- [ADR-017: Ejecución de tests en paralelo](#adr-017-ejecución-de-tests-en-paralelo)

### Rendimiento
- [ADR-018: Estrategia de caché para datos de referencia](#adr-018-estrategia-de-caché-para-datos-de-referencia)
- [ADR-019: Índices de base de datos optimizados](#adr-019-índices-de-base-de-datos-optimizados)
- [ADR-020: Chunking en exportaciones](#adr-020-chunking-en-exportaciones)

---

## Arquitectura

### ADR-001: Uso de Livewire 3 en lugar de Inertia/Vue

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba elegir un stack de frontend para la aplicación. Las opciones principales eran:
- **Livewire 3**: Componentes reactivos del lado del servidor
- **Inertia.js + Vue/React**: SPA con backend Laravel
- **Blade tradicional + Alpine.js**: Sin reactividad avanzada

#### Decisión

Se eligió **Livewire 3** como stack principal de frontend.

#### Justificación

1. **Menor complejidad**: No requiere API separada ni gestión de estado en cliente
2. **Productividad**: Desarrollo más rápido con un solo lenguaje (PHP)
3. **SEO friendly**: Renderizado del lado del servidor por defecto
4. **Ecosistema Laravel**: Integración nativa con todas las funcionalidades de Laravel
5. **Flux UI**: Biblioteca de componentes oficial diseñada para Livewire

#### Consecuencias

**Positivas**:
- Desarrollo más rápido de CRUD y formularios
- Menor curva de aprendizaje para el equipo
- Testing más sencillo con `Livewire::test()`

**Negativas**:
- Más peticiones al servidor que una SPA
- Interacciones muy complejas pueden requerir JavaScript adicional
- Dependencia del ecosistema Livewire

---

### ADR-002: Uso de Flux UI como biblioteca de componentes

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba una biblioteca de componentes UI consistente y bien diseñada para Livewire.

#### Decisión

Se eligió **Flux UI v2** (versión gratuita) como biblioteca principal de componentes.

#### Justificación

1. **Diseño profesional**: Componentes modernos y bien diseñados
2. **Integración Livewire**: Diseñados específicamente para Livewire 3
3. **Tailwind CSS**: Basados en Tailwind, consistente con el stack
4. **Mantenimiento oficial**: Desarrollado por el equipo de Livewire

#### Consecuencias

**Positivas**:
- UI consistente en toda la aplicación
- Reducción del tiempo de desarrollo de componentes
- Accesibilidad incluida por defecto

**Negativas**:
- Limitaciones de la versión gratuita (algunos componentes Pro no disponibles)
- Dependencia de actualizaciones del paquete

---

### ADR-003: Separación de área pública y panel de administración

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

La aplicación tiene dos audiencias distintas:
- Público general que consulta información
- Administradores que gestionan contenido

#### Decisión

Separar completamente el área pública (front-office) del panel de administración (back-office):
- Rutas diferentes (`/` vs `/admin`)
- Layouts diferentes
- Componentes Livewire organizados en carpetas separadas

#### Justificación

1. **Seguridad**: Aislamiento claro de funcionalidades privilegiadas
2. **Mantenibilidad**: Código organizado por contexto de uso
3. **UX diferente**: Cada área tiene necesidades de interfaz distintas
4. **Permisos claros**: Middleware de autenticación solo en `/admin/*`

#### Consecuencias

**Positivas**:
- Clara separación de responsabilidades
- Facilita el testing por área
- Permite diseños de UI optimizados para cada audiencia

**Negativas**:
- Algunos componentes se duplican (ej: listados de programas)
- Mayor cantidad de archivos

---

## Base de Datos

### ADR-004: Uso de SoftDeletes en todos los modelos principales

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba decidir cómo manejar la eliminación de registros, considerando:
- Requisitos de auditoría
- Posibilidad de recuperar datos eliminados por error
- Integridad referencial

#### Decisión

Implementar **SoftDeletes** en todos los modelos principales:
- Program, Call, CallPhase, Resolution
- NewsPost, NewsTag, Document, DocumentCategory
- ErasmusEvent, AcademicYear, User

**Excepción**: `NewsletterSubscription` usa hard delete para cumplimiento GDPR.

#### Justificación

1. **Recuperación**: Permite restaurar datos eliminados por error
2. **Auditoría**: Mantiene historial completo de datos
3. **Integridad**: Las foreign keys no se rompen al "eliminar"
4. **Requisito legal**: Algunas normativas exigen conservar registros

#### Consecuencias

**Positivas**:
- Datos nunca se pierden permanentemente
- Facilita la auditoría y el cumplimiento normativo
- UI puede mostrar "papelera" con elementos eliminados

**Negativas**:
- Queries deben incluir `withTrashed()` cuando sea necesario
- Mayor uso de almacenamiento a largo plazo
- Complejidad adicional en relaciones

---

### ADR-005: Cascade delete manual en lugar de automático

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Con SoftDeletes, el cascade delete de MySQL no funciona correctamente. Se necesitaba decidir cómo manejar la eliminación de entidades relacionadas.

#### Decisión

Implementar **cascade delete manual** en los métodos `delete()` de los componentes Livewire o mediante eventos de modelo.

```php
// Ejemplo en CallPhase: eliminar resoluciones al eliminar fase
public function delete(): void
{
    $this->phase->resolutions()->delete(); // Soft delete de resoluciones
    $this->phase->delete();                // Soft delete de fase
}
```

#### Justificación

1. **Control total**: Podemos decidir qué se elimina y qué se mantiene
2. **SoftDeletes compatible**: Funciona correctamente con eliminación suave
3. **Lógica de negocio**: Podemos aplicar reglas específicas por caso

#### Consecuencias

**Positivas**:
- Comportamiento predecible y documentado
- Permite validaciones antes de eliminar
- Compatible con SoftDeletes

**Negativas**:
- Requiere código explícito para cada relación
- Riesgo de olvidar relaciones al añadir nuevas

---

### ADR-006: Uso de slugs en URLs públicas

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Las URLs públicas necesitan ser amigables para SEO y usuarios.

#### Decisión

Usar **slugs** en las URLs públicas para las entidades principales:
- `/programas/{slug}` en lugar de `/programas/{id}`
- `/convocatorias/{slug}`
- `/noticias/{slug}`
- etc.

El panel de administración usa **IDs** para mayor eficiencia.

#### Justificación

1. **SEO**: URLs descriptivas mejoran el posicionamiento
2. **UX**: URLs legibles y compartibles
3. **Seguridad**: No expone IDs internos

#### Consecuencias

**Positivas**:
- Mejor SEO
- URLs más profesionales
- Mayor seguridad por oscuridad

**Negativas**:
- Necesidad de generar y mantener slugs únicos
- Route model binding requiere configuración adicional
- Cambios de título pueden invalidar URLs compartidas

---

## Autenticación y Autorización

### ADR-007: Uso de Laravel Fortify para autenticación

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba un sistema de autenticación robusto con:
- Login/Logout
- Registro de usuarios
- Reset de contraseña
- Autenticación en dos factores (2FA)

#### Decisión

Usar **Laravel Fortify** como backend de autenticación headless, con vistas personalizadas en Livewire.

#### Justificación

1. **Seguridad probada**: Paquete oficial de Laravel, bien auditado
2. **Headless**: Permite personalizar completamente las vistas
3. **2FA incluido**: Soporte nativo para autenticación en dos factores
4. **Estándar**: Sigue las mejores prácticas de Laravel

#### Consecuencias

**Positivas**:
- Autenticación segura sin reinventar la rueda
- 2FA listo para usar
- Integración perfecta con el resto de Laravel

**Negativas**:
- Curva de aprendizaje inicial para personalización
- Algunas features requieren configuración adicional

---

### ADR-008: Estructura de 4 roles con permisos granulares

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba un sistema de autorización que permitiera:
- Diferentes niveles de acceso
- Control fino sobre cada acción
- Escalabilidad para futuros requisitos

#### Decisión

Implementar **4 roles** con **permisos granulares** usando Spatie Permission:

| Rol | Descripción |
|-----|-------------|
| `super-admin` | Acceso total, bypass de todas las verificaciones |
| `admin` | Gestión completa de contenido y usuarios |
| `editor` | Crear y editar contenido (sin publicar ni eliminar) |
| `viewer` | Solo lectura del panel de administración |

Permisos por módulo: `{modulo}.view`, `{modulo}.create`, `{modulo}.edit`, `{modulo}.delete`, `{modulo}.publish`

#### Justificación

1. **Flexibilidad**: Permisos granulares permiten configuraciones precisas
2. **Escalabilidad**: Fácil añadir nuevos roles o permisos
3. **Claridad**: Roles con nombres descriptivos y responsabilidades claras
4. **Spatie Permission**: Paquete maduro y bien mantenido

#### Consecuencias

**Positivas**:
- Control preciso de acceso
- Fácil auditoría de permisos
- Extensible para futuros requisitos

**Negativas**:
- Mayor complejidad que roles simples
- Requiere mantener sincronizados roles, permisos y policies

---

### ADR-009: Autorización en componentes Livewire vs middleware

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se debía decidir dónde verificar la autorización:
- En middleware de rutas
- En los componentes Livewire
- En ambos lugares

#### Decisión

**Autorización en dos niveles**:

1. **Middleware de rutas**: Verificación básica de autenticación
   ```php
   Route::prefix('admin')->middleware(['auth'])->group(...)
   ```

2. **Componentes Livewire**: Verificación de permisos específicos usando Policies
   ```php
   public function mount(): void
   {
       $this->authorize('viewAny', Program::class);
   }
   ```

#### Justificación

1. **Defensa en profundidad**: Múltiples capas de seguridad
2. **Granularidad**: Policies permiten lógica compleja de autorización
3. **Reutilización**: Policies se usan tanto en Livewire como en Blade
4. **Testing**: Facilita probar autorización de forma aislada

#### Consecuencias

**Positivas**:
- Seguridad robusta con múltiples verificaciones
- Código de autorización centralizado en Policies
- Mensajes de error apropiados en cada nivel

**Negativas**:
- Verificaciones potencialmente redundantes
- Requiere consistencia entre middleware y policies

---

## Multimedia

### ADR-010: Uso de Spatie Media Library para gestión de archivos

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

La aplicación necesita gestionar múltiples tipos de archivos:
- Imágenes de programas, noticias, eventos
- PDFs de resoluciones
- Documentos descargables (PDF, Word, Excel)

#### Decisión

Usar **Spatie Laravel Media Library** como sistema centralizado de gestión de archivos.

#### Justificación

1. **Funcionalidad completa**: Colecciones, conversiones, responsive images
2. **Integración Eloquent**: Se asocia naturalmente con modelos
3. **Conversiones**: Genera thumbnails y variantes automáticamente
4. **Almacenamiento flexible**: Soporta disco local, S3, etc.

#### Consecuencias

**Positivas**:
- Sistema unificado para todos los archivos
- Conversiones automáticas de imágenes
- Limpieza automática al eliminar modelos

**Negativas**:
- Curva de aprendizaje inicial
- Tabla `media` puede crecer significativamente
- Dependencia de un paquete externo

---

### ADR-011: Conversión automática a WebP

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Las imágenes son un factor importante en el rendimiento web. Se necesitaba optimizar su tamaño sin sacrificar calidad.

#### Decisión

Configurar Media Library para convertir automáticamente todas las imágenes a **formato WebP** con tres tamaños:
- `thumbnail`: 400x300px
- `medium`: 800x600px
- `large`: 1200x900px

```php
public function registerMediaConversions(Media $media = null): void
{
    $this->addMediaConversion('thumbnail')
        ->width(400)->height(300)
        ->format('webp')
        ->quality(80);
}
```

#### Justificación

1. **Tamaño reducido**: WebP es ~30% más pequeño que JPEG
2. **Soporte universal**: Todos los navegadores modernos lo soportan
3. **Calidad**: Buena relación calidad/tamaño

#### Consecuencias

**Positivas**:
- Páginas más rápidas
- Menor uso de ancho de banda
- Mejor puntuación en Lighthouse/PageSpeed

**Negativas**:
- Tiempo de procesamiento al subir imágenes
- Requiere regenerar imágenes existentes si se cambia configuración

---

### ADR-012: Soft delete de imágenes en News y Events

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

En noticias y eventos, los usuarios pueden querer:
- Eliminar una imagen temporalmente
- Restaurarla si se arrepienten
- Eliminarla permanentemente

#### Decisión

Implementar **soft delete personalizado para imágenes** en NewsPost y ErasmusEvent:
- Las imágenes "eliminadas" se mueven a una colección `deleted_images`
- Se pueden restaurar mientras el modelo no se elimine permanentemente
- La eliminación permanente del modelo limpia todas las imágenes

```php
public function softDeleteImage(Media $media): void
{
    $media->move($this, 'deleted_images');
}

public function restoreImage(Media $media): void
{
    $media->move($this, 'images');
}
```

#### Justificación

1. **UX mejorada**: Permite recuperar imágenes eliminadas por error
2. **Consistencia**: Sigue el patrón de SoftDeletes del resto de la app
3. **Seguridad**: Evita pérdida accidental de contenido

#### Consecuencias

**Positivas**:
- Mayor confianza del usuario al gestionar imágenes
- Recuperación de errores sin intervención técnica

**Negativas**:
- Mayor complejidad en la gestión de imágenes
- Espacio de almacenamiento adicional

---

## Internacionalización

### ADR-013: Sistema dual de traducciones

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

La aplicación debe soportar múltiples idiomas (ES/EN) para:
- Interfaz de usuario (etiquetas, mensajes)
- Contenido dinámico (nombres de programas, descripciones)

#### Decisión

Implementar un **sistema dual de traducciones**:

1. **Traducciones estáticas**: Archivos `lang/es/*.php` y `lang/en/*.php` para la UI
2. **Traducciones dinámicas**: Modelo `Translation` con trait `Translatable` para contenido de BD

```php
// Estática
__('programs.title') // "Programas Erasmus+"

// Dinámica
$program->translate('name', 'en') // "Student Mobility"
```

#### Justificación

1. **Separación de responsabilidades**: UI vs contenido
2. **Rendimiento**: Traducciones estáticas se cachean eficientemente
3. **Flexibilidad**: El contenido dinámico se gestiona desde el panel admin

#### Consecuencias

**Positivas**:
- Cada tipo de traducción se gestiona de forma óptima
- Los administradores pueden traducir contenido sin tocar código

**Negativas**:
- Dos sistemas a mantener
- Posible confusión sobre dónde añadir traducciones

---

### ADR-014: Persistencia de idioma en sesión

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

El usuario debe poder cambiar el idioma y que este se mantenga durante su navegación.

#### Decisión

Persistir el idioma seleccionado en la **sesión del usuario** mediante middleware `SetLocale`:

```php
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale'));
        
        if (in_array($locale, ['es', 'en'])) {
            app()->setLocale($locale);
        }
        
        return $next($request);
    }
}
```

#### Justificación

1. **Simplicidad**: No requiere autenticación ni base de datos
2. **Inmediatez**: El cambio es instantáneo
3. **Privacidad**: No se almacenan preferencias permanentemente

#### Consecuencias

**Positivas**:
- Funciona para usuarios anónimos
- Sin overhead de base de datos
- Fácil de implementar

**Negativas**:
- Se pierde al cerrar el navegador (sin "recordar")
- No sincroniza entre dispositivos

---

## Testing

### ADR-015: Uso de Pest PHP como framework de testing

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba un framework de testing moderno y productivo para PHP.

#### Decisión

Usar **Pest PHP v4** como framework de testing en lugar de PHPUnit directamente.

```php
it('can create a program', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('programs.create');

    Livewire::actingAs($user)
        ->test(Create::class)
        ->set('name', 'Test Program')
        ->call('save')
        ->assertHasNoErrors();
});
```

#### Justificación

1. **Sintaxis expresiva**: Tests más legibles y concisos
2. **Compatibilidad**: Usa PHPUnit por debajo, mismas assertions
3. **Productividad**: Menos boilerplate, más enfoque en el test
4. **Laravel oficial**: Recomendado y soportado por Laravel

#### Consecuencias

**Positivas**:
- Tests más legibles
- Desarrollo más rápido
- Excelente integración con Laravel

**Negativas**:
- Curva de aprendizaje si se viene de PHPUnit tradicional
- Algunas features avanzadas pueden diferir

---

### ADR-016: Estrategia de tests por capas

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Se necesitaba definir qué tipos de tests escribir y su alcance.

#### Decisión

Implementar **tests en tres capas**:

| Capa | Ubicación | Propósito |
|------|-----------|-----------|
| **Unit** | `tests/Unit/` | Form Requests, helpers, lógica aislada |
| **Feature** | `tests/Feature/` | Componentes Livewire, modelos, policies |
| **Integration** | `tests/Feature/` | Flujos completos, imports/exports |

**Cobertura objetivo**: 100% en modelos, policies, form requests y componentes Livewire.

#### Justificación

1. **Pirámide de tests**: Más tests unitarios, menos de integración
2. **Confianza**: Cobertura completa en capas críticas
3. **Velocidad**: Tests unitarios rápidos para feedback inmediato

#### Consecuencias

**Positivas**:
- Alta confianza en el código
- Detección temprana de regresiones
- Documentación viva del comportamiento esperado

**Negativas**:
- Tiempo de desarrollo para mantener tests
- Suite completa tarda varios minutos

---

### ADR-017: Ejecución de tests en paralelo

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Con 3,800+ tests, la ejecución secuencial era demasiado lenta.

#### Decisión

Ejecutar tests en **paralelo** con `--parallel`:

```bash
php artisan test --parallel
```

Esto requirió:
- Aislar tests que comparten estado global
- Usar transacciones de BD (RefreshDatabase)
- Limpiar cachés específicos en `setUp()`

#### Justificación

1. **Velocidad**: Reduce tiempo de ejecución de ~15min a ~3min
2. **CI/CD**: Builds más rápidos
3. **Productividad**: Feedback más rápido durante desarrollo

#### Consecuencias

**Positivas**:
- Ejecución significativamente más rápida
- Mejor experiencia de desarrollo

**Negativas**:
- Algunos tests requieren aislamiento especial
- Debugging más complejo cuando hay fallos intermitentes

---

## Rendimiento

### ADR-018: Estrategia de caché para datos de referencia

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Algunos datos se consultan frecuentemente pero cambian raramente:
- Lista de programas activos
- Años académicos
- Configuraciones del sistema

#### Decisión

Implementar **caché con invalidación selectiva** para datos de referencia:

```php
// Obtener con caché
$programs = Cache::remember('programs.active', 3600, function () {
    return Program::active()->orderBy('order')->get();
});

// Invalidar al modificar
public function updated(Program $program): void
{
    Cache::forget('programs.active');
}
```

**TTL**: 1 hora para datos de referencia.

#### Justificación

1. **Rendimiento**: Reduce queries repetitivas
2. **Consistencia**: Invalidación al modificar garantiza datos frescos
3. **Simplicidad**: Caché de Laravel, sin infraestructura adicional

#### Consecuencias

**Positivas**:
- Menos carga en base de datos
- Respuestas más rápidas
- Escalabilidad mejorada

**Negativas**:
- Complejidad de invalidación
- Posibles inconsistencias temporales si se olvida invalidar

---

### ADR-019: Índices de base de datos optimizados

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Las consultas frecuentes necesitaban optimización para buen rendimiento.

#### Decisión

Añadir **índices específicos** en columnas frecuentemente consultadas:

```php
// En migraciones
$table->index('program_id');
$table->index('published_at');
$table->index('is_active');
$table->index(['subject_type', 'subject_id']); // Para activity_log
```

#### Justificación

1. **Rendimiento**: Queries de filtrado mucho más rápidas
2. **Escalabilidad**: Importante con volumen de datos creciente
3. **Bajo costo**: Pequeño overhead en escrituras, gran beneficio en lecturas

#### Consecuencias

**Positivas**:
- Consultas de listado y filtrado optimizadas
- Mejor rendimiento en búsquedas

**Negativas**:
- Inserciones/actualizaciones ligeramente más lentas
- Mayor uso de espacio en disco

---

### ADR-020: Chunking en exportaciones

**Fecha**: Enero 2026  
**Estado**: Aceptada

#### Contexto

Las exportaciones a Excel pueden incluir miles de registros, causando problemas de memoria.

#### Decisión

Usar **chunking** en todas las exportaciones mediante Laravel Excel:

```php
class CallsExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->when($this->filters['program_id'], ...)
            ->orderBy('created_at', 'desc');
    }
}
```

Laravel Excel procesa automáticamente en chunks cuando se usa `FromQuery`.

#### Justificación

1. **Memoria controlada**: No carga todos los registros a la vez
2. **Escalabilidad**: Funciona con cualquier volumen de datos
3. **Transparente**: Laravel Excel maneja los detalles

#### Consecuencias

**Positivas**:
- Exportaciones de cualquier tamaño sin errores de memoria
- Rendimiento predecible

**Negativas**:
- Exportaciones grandes tardan más
- Requiere usar `FromQuery` en lugar de `FromCollection`

---

## Resumen de Decisiones

| ADR | Decisión | Categoría |
|-----|----------|-----------|
| 001 | Livewire 3 como frontend | Arquitectura |
| 002 | Flux UI como componentes | Arquitectura |
| 003 | Separación público/admin | Arquitectura |
| 004 | SoftDeletes en modelos | Base de Datos |
| 005 | Cascade delete manual | Base de Datos |
| 006 | Slugs en URLs públicas | Base de Datos |
| 007 | Laravel Fortify para auth | Autenticación |
| 008 | 4 roles + permisos granulares | Autorización |
| 009 | Autorización en Livewire | Autorización |
| 010 | Spatie Media Library | Multimedia |
| 011 | Conversión a WebP | Multimedia |
| 012 | Soft delete de imágenes | Multimedia |
| 013 | Sistema dual de traducciones | i18n |
| 014 | Idioma en sesión | i18n |
| 015 | Pest PHP para testing | Testing |
| 016 | Tests por capas | Testing |
| 017 | Tests en paralelo | Testing |
| 018 | Caché de datos de referencia | Rendimiento |
| 019 | Índices optimizados | Rendimiento |
| 020 | Chunking en exports | Rendimiento |

---

**Última actualización**: Enero 2026
