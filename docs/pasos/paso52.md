# Paso 52: Corrección de Errores de Lazy Loading y Planificación de Browser Tests

**Fecha**: Enero 2026  
**Estado**: ✅ Completado

---

## Resumen Ejecutivo

Este paso documenta la corrección de errores de lazy loading detectados en producción que no fueron capturados por los tests funcionales existentes, y la planificación de tests de navegador (browser tests) usando Pest v4 para prevenir problemas similares en el futuro.

---

## Problema Inicial

### Error Detectado en Producción

Al acceder a un programa específico en la parte pública (`/programas/movilidad-educacion-escolar`), se producía el siguiente error:

```
Illuminate\Database\LazyLoadingViolationException
Attempted to lazy load [program] on model [App\Models\Call] but lazy loading is disabled.
```

**Stack Trace**:
- Error en `resources/views/components/content/call-card.blade.php:19`
- Llamado desde `resources/views/livewire/public/programs/show.blade.php:152`

### Causa Raíz

El componente `Public\Programs\Show` cargaba las convocatorias relacionadas sin incluir la relación `program` en el eager loading:

```php
// ❌ Antes - Faltaba 'program'
public function relatedCalls(): Collection
{
    return Call::query()
        ->with(['academicYear'])  // Solo academicYear
        ->where('program_id', $this->program->id)
        // ...
}
```

El componente Blade `call-card.blade.php` intentaba acceder a `$call->program` en la línea 34:
```php
$program = $call?->program ?? $program;
```

Como la relación no estaba cargada y el lazy loading está deshabilitado (configurado en `AppServiceProvider`), se lanzaba la excepción.

---

## Correcciones Aplicadas

### 1. Corrección en `Public\Programs\Show::relatedCalls()`

**Archivo**: `app/Livewire/Public/Programs/Show.php`

```php
// ✅ Después
public function relatedCalls(): Collection
{
    return Call::query()
        ->with(['program', 'academicYear'])  // Añadido 'program'
        ->where('program_id', $this->program->id)
        ->whereIn('status', ['abierta', 'cerrada'])
        ->whereNotNull('published_at')
        ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
        ->orderBy('published_at', 'desc')
        ->limit(4)
        ->get();
}
```

### 2. Corrección en `Public\Programs\Show::relatedNews()`

**Archivo**: `app/Livewire/Public/Programs/Show.php`

```php
// ✅ Después
public function relatedNews(): Collection
{
    return NewsPost::query()
        ->with(['program', 'author'])  // Añadido 'program'
        ->where('program_id', $this->program->id)
        ->where('status', 'publicado')
        ->whereNotNull('published_at')
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();
}
```

### 3. Corrección en `Public\Calls\Show::relatedNews()`

**Archivo**: `app/Livewire/Public/Calls/Show.php`

```php
// ✅ Después
public function relatedNews(): Collection
{
    return NewsPost::query()
        ->with(['program', 'author'])  // Añadido 'program'
        ->where('program_id', $this->call->program_id)
        ->where('status', 'publicado')
        ->whereNotNull('published_at')
        ->orderBy('published_at', 'desc')
        ->limit(3)
        ->get();
}
```

---

## Análisis: ¿Por Qué los Tests No Detectaron el Problema?

### Problema con `Livewire::test()`

Los tests existentes usaban `Livewire::test()`, que renderiza el componente Livewire pero **no ejecuta completamente el código Blade** de los componentes anidados (`call-card`, `news-card`).

El acceso a `$call->program` ocurre dentro de un bloque `@php` en el componente Blade, que no se ejecuta cuando se usa `Livewire::test()`.

### Solución: Actualizar Tests para Renderizado Completo

Se actualizaron los tests críticos para usar `$this->get(route(...))` en lugar de `Livewire::test()`, asegurando que se renderice completamente la vista:

**Archivo**: `tests/Feature/Livewire/Public/Programs/ShowTest.php`

```php
// ❌ Antes
it('displays related calls when available', function () {
    // ...
    Livewire::test(Show::class, ['program' => $this->program])
        ->assertSee('Convocatoria de prueba');
});

// ✅ Después
it('displays related calls when available', function () {
    // ...
    $this->get(route('programas.show', $this->program->slug))
        ->assertOk()
        ->assertSee('Convocatoria de prueba');
});
```

**Tests actualizados**:
- `it('displays related calls when available')`
- `it('displays related news when available')`
- `it('only shows open and closed calls')`
- `it('only shows published news')`

---

## Planificación de Browser Tests (Paso 3.11)

### Decisión: Implementar Browser Tests con Pest v4

**Justificación**:
- Los browser tests renderizan completamente la aplicación como un usuario real
- Detectan problemas que solo aparecen en el renderizado completo (lazy loading, JavaScript, CSS)
- Complementan los tests funcionales existentes sin ser redundantes
- Pest v4 ya está instalado y soporta browser testing nativamente

### Estructura del Paso 3.11

Se añadió el **Paso 3.11: Tests de Navegador (Browser Testing)** a `docs/planificacion_pasos.md` con 8 subapartados:

1. **3.11.1. Configuración de Tests de Navegador** - Setup inicial
2. **3.11.2. Tests de Páginas Públicas Críticas** - Home, programas, convocatorias, noticias
3. **3.11.3. Tests de Flujos de Autenticación y Autorización** - Login, registro, permisos
4. **3.11.4. Tests de Formularios y Validación en Tiempo Real** - Newsletter, búsqueda
5. **3.11.5. Tests de Interacciones JavaScript y Componentes Dinámicos** - Livewire, filtros
6. **3.11.6. Tests de Rendimiento y Optimización** - Carga, consultas, lazy loading
7. **3.11.7. Tests de Responsive y Accesibilidad** - Móviles, tablets, desktop
8. **3.11.8. Integración con CI/CD y Documentación** - Automatización y documentación

### Plan Detallado Creado

Se creó el plan detallado `docs/pasos/paso-3.11.1-plan.md` con 8 fases:

1. **Fase 1**: Verificación y Preparación del Entorno
2. **Fase 2**: Instalación del Plugin de Browser Testing
3. **Fase 3**: Configuración de Pest para Browser Tests
4. **Fase 4**: Crear Estructura de Directorios
5. **Fase 5**: Configurar Base de Datos de Testing
6. **Fase 6**: Crear Test de Ejemplo y Verificación
7. **Fase 7**: Documentación
8. **Fase 8**: Integración con CI/CD (Preparación)

---

## Actualización de Documentación Técnica

### Archivo Actualizado: `docs/optimizaciones.md`

Se añadió una nueva sección **"Correcciones Recientes (Enero 2026)"** que documenta:

1. **Problema Detectado**: Descripción del error de lazy loading
2. **Causa Raíz**: Explicación de por qué ocurrió
3. **Correcciones Aplicadas**: Código antes/después de cada corrección
4. **Mejoras en Tests**: Cambios en los tests para detectar problemas similares
5. **Lección Aprendida**: Importancia de los browser tests

También se actualizó la tabla de **Componentes Públicos** para incluir todos los componentes Show con sus relaciones cargadas.

---

## Archivos Modificados

### Código

1. `app/Livewire/Public/Programs/Show.php`
   - Añadido `'program'` a `relatedCalls()`
   - Añadido `'program'` a `relatedNews()`

2. `app/Livewire/Public/Calls/Show.php`
   - Añadido `'program'` a `relatedNews()`

3. `tests/Feature/Livewire/Public/Programs/ShowTest.php`
   - Actualizado 4 tests para usar `$this->get()` en lugar de `Livewire::test()`

### Documentación

1. `docs/planificacion_pasos.md`
   - Añadido Paso 3.11 completo con 8 subapartados
   - Actualizada sección de Priorización Recomendada (Fase 9)

2. `docs/pasos/paso-3.11.1-plan.md`
   - Creado plan detallado de configuración de browser tests (543 líneas)

3. `docs/optimizaciones.md`
   - Añadida sección "Correcciones Recientes (Enero 2026)"
   - Actualizada tabla de Componentes Públicos

---

## Verificación

### Tests Ejecutados

```bash
# Tests relacionados con programas
php artisan test --filter=Programs
# Resultado: 206 tests pasados (407 assertions)

# Tests relacionados con programas y convocatorias
php artisan test --filter="Programs|Calls" --stop-on-failure
# Resultado: Todos los tests pasaron

# Tests específicos actualizados
php artisan test --filter="displays related calls when available|displays related news when available" tests/Feature/Livewire/Public/Programs/ShowTest.php
# Resultado: 2 tests pasados (6 assertions)
```

### Formato de Código

```bash
vendor/bin/pint app/Livewire/Public/Programs/Show.php app/Livewire/Public/Calls/Show.php
# Resultado: 2 archivos formateados correctamente
```

---

## Lecciones Aprendidas

### 1. Limitaciones de `Livewire::test()`

- `Livewire::test()` no renderiza completamente las vistas Blade
- El código dentro de componentes Blade anidados puede no ejecutarse
- No detecta problemas de lazy loading que ocurren en el renderizado completo

### 2. Importancia de Browser Tests

- Los browser tests renderizan completamente la aplicación
- Detectan problemas que solo aparecen en el navegador real
- Son complementarios a los tests funcionales, no redundantes
- Esenciales para detectar problemas de lazy loading, JavaScript y CSS

### 3. Estrategia de Testing Híbrida

**Recomendación**:
- **Tests funcionales** (`Livewire::test()`, `$this->get()`) para la mayoría de casos
- **Browser tests** para:
  - Páginas públicas críticas
  - Flujos críticos de usuario
  - Casos donde ya se han encontrado problemas
  - Validación de renderizado completo

### 4. Mejora Continua de Tests

- Actualizar tests cuando se detectan problemas en producción
- Usar `$this->get()` en tests que renderizan componentes con relaciones
- Considerar browser tests para nuevas funcionalidades críticas

---

## Próximos Pasos

1. **Implementar Paso 3.11.1**: Configurar browser tests según el plan detallado
2. **Implementar Paso 3.11.2**: Crear tests de páginas públicas críticas
3. **Continuar con Pasos 3.11.3-3.11.8**: Completar la suite de browser tests

---

## Referencias

- [Plan de Browser Testing](paso-3.11.1-plan.md)
- [Documentación de Optimizaciones](../optimizaciones.md)
- [Planificación de Pasos](../planificacion_pasos.md#paso-311-tests-de-navegador-browser-testing)

---

**Fecha de Creación**: Enero 2026  
**Autor**: AI Assistant (Auto)  
**Revisado por**: Carlos
