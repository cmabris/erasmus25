# Plan Detallado: Paso 3.6.1 - Rutas Públicas

## Objetivo

Completar la implementación de las rutas públicas de la aplicación, asegurando que todas estén correctamente definidas, organizadas, documentadas y testeadas.

## Estado Actual

### ✅ Ya Implementado

1. **Rutas definidas** en `routes/web.php`:
   - `/` - Página principal (Home)
   - `/programas` - Listado de programas
   - `/programas/{program:slug}` - Detalle de programa
   - `/convocatorias` - Listado de convocatorias
   - `/convocatorias/{call:slug}` - Detalle de convocatoria
   - `/noticias` - Listado de noticias
   - `/noticias/{newsPost:slug}` - Detalle de noticia
   - `/documentos` - Listado de documentos
   - `/documentos/{document:slug}` - Detalle de documento
   - `/calendario` - Calendario de eventos
   - `/eventos` - Listado de eventos
   - `/eventos/{event}` - Detalle de evento (usa ID, no slug)
   - `/newsletter/suscribir` - Suscripción a newsletter
   - `/newsletter/verificar/{token}` - Verificación de suscripción
   - `/newsletter/baja` - Baja de newsletter
   - `/newsletter/baja/{token}` - Baja con token

2. **Componentes Livewire** existentes para todas las rutas
3. **Layout público** configurado
4. **Navegación pública** implementada
5. **Test parcial** para rutas de documentos

### ⚠️ Pendiente

1. **Organización de rutas**: Agrupar rutas públicas en un grupo lógico
2. **Consistencia en route model binding**: Eventos usa ID en lugar de slug
3. **Tests completos**: Crear tests para todas las rutas públicas
4. **Documentación**: Documentar todas las rutas públicas
5. **Verificación**: Asegurar que todas las rutas funcionan correctamente

---

## Plan de Implementación

### **Fase 1: Revisión y Organización de Rutas**

#### Paso 1.1: Revisar y agrupar rutas públicas

**Objetivo**: Organizar las rutas públicas en grupos lógicos para mejor mantenibilidad.

**Tareas**:
1. Agrupar todas las rutas públicas en un grupo con comentarios descriptivos
2. Separar claramente rutas públicas de rutas de administración
3. Añadir comentarios explicativos para cada sección

**Archivos a modificar**:
- `routes/web.php`

**Resultado esperado**:
- Rutas públicas agrupadas y bien comentadas
- Separación clara entre rutas públicas y de administración
- Código más legible y mantenible

---

#### Paso 1.2: Verificar consistencia en route model binding

**Objetivo**: Asegurar que todas las rutas usan el binding apropiado (slug o ID).

**Análisis actual**:
- ✅ Programas: usa `{program:slug}` ✓
- ✅ Convocatorias: usa `{call:slug}` ✓
- ✅ Noticias: usa `{newsPost:slug}` ✓
- ✅ Documentos: usa `{document:slug}` ✓
- ⚠️ Eventos: usa `{event}` (ID) - **Revisar si debe usar slug**

**Tareas**:
1. Verificar si el modelo `ErasmusEvent` tiene campo `slug`
2. Si tiene slug, actualizar ruta para usar `{event:slug}`
3. Si no tiene slug, documentar por qué usa ID
4. Verificar que el componente `Events\Show` funciona correctamente

**Archivos a revisar**:
- `app/Models/ErasmusEvent.php`
- `app/Livewire/Public/Events/Show.php`
- `routes/web.php`

**Resultado esperado**:
- Decisión documentada sobre el binding de eventos
- Rutas consistentes o documentadas según corresponda

---

### **Fase 2: Tests de Rutas Públicas**

#### Paso 2.1: Crear test base para rutas públicas

**Objetivo**: Crear archivo de test que verifique todas las rutas públicas.

**Tareas**:
1. Crear `tests/Feature/Routes/PublicRoutesTest.php`
2. Implementar tests básicos para cada ruta:
   - Test de acceso a ruta index (si aplica)
   - Test de acceso a ruta show con parámetro válido
   - Test de 404 para parámetro inválido
   - Test de route model binding (slug o ID según corresponda)

**Archivos a crear**:
- `tests/Feature/Routes/PublicRoutesTest.php`

**Tests a implementar**:

```php
describe('Public Routes', function () {
    // Home
    it('can access home route', ...);
    
    // Programs
    it('can access programs index route', ...);
    it('can access program show route with slug', ...);
    it('returns 404 for non-existent program slug', ...);
    
    // Calls
    it('can access calls index route', ...);
    it('can access call show route with slug', ...);
    it('returns 404 for non-existent call slug', ...);
    
    // News
    it('can access news index route', ...);
    it('can access news show route with slug', ...);
    it('returns 404 for non-existent news slug', ...);
    
    // Documents
    it('can access documents index route', ...);
    it('can access document show route with slug', ...);
    it('returns 404 for non-existent document slug', ...);
    
    // Events
    it('can access calendar route', ...);
    it('can access events index route', ...);
    it('can access event show route', ...);
    it('returns 404 for non-existent event', ...);
    
    // Newsletter
    it('can access newsletter subscribe route', ...);
    it('can access newsletter verify route', ...);
    it('can access newsletter unsubscribe route', ...);
});
```

**Resultado esperado**:
- Test completo que verifica todas las rutas públicas
- Cobertura de casos exitosos y de error (404)

---

#### Paso 2.2: Tests específicos por módulo (opcional)

**Objetivo**: Si ya existen tests específicos, verificar que están completos.

**Tareas**:
1. Verificar tests existentes:
   - `tests/Feature/Routes/DocumentsRoutesTest.php` ✓ (ya existe)
2. Crear tests similares para otros módulos si no existen
3. Asegurar que todos los tests pasan

**Archivos a revisar/crear**:
- `tests/Feature/Routes/ProgramsRoutesTest.php` (si no existe)
- `tests/Feature/Routes/CallsRoutesTest.php` (si no existe)
- `tests/Feature/Routes/NewsRoutesTest.php` (si no existe)
- `tests/Feature/Routes/EventsRoutesTest.php` (si no existe)
- `tests/Feature/Routes/NewsletterRoutesTest.php` (si no existe)

**Resultado esperado**:
- Tests completos para cada módulo o test general que cubra todo

---

### **Fase 3: Verificación y Validación**

#### Paso 3.1: Verificar que todas las rutas funcionan

**Objetivo**: Asegurar que todas las rutas responden correctamente.

**Tareas**:
1. Ejecutar todos los tests de rutas
2. Verificar manualmente (opcional) que las rutas funcionan en el navegador
3. Verificar que los componentes Livewire se cargan correctamente
4. Verificar que el layout público se aplica correctamente

**Comandos a ejecutar**:
```bash
php artisan test tests/Feature/Routes/
```

**Resultado esperado**:
- Todos los tests pasan
- Rutas funcionan correctamente

---

#### Paso 3.2: Verificar route model binding

**Objetivo**: Asegurar que el binding funciona correctamente para todos los modelos.

**Tareas**:
1. Verificar que los modelos con slug tienen el método `getRouteKeyName()` o usan `{model:slug}`
2. Verificar que los modelos sin slug usan ID correctamente
3. Probar casos edge:
   - Slug duplicado (no debería ocurrir, pero verificar)
   - Slug con caracteres especiales
   - Slug muy largo

**Archivos a revisar**:
- Modelos: `Program`, `Call`, `NewsPost`, `Document`, `ErasmusEvent`
- `routes/web.php`

**Resultado esperado**:
- Route model binding funciona correctamente para todos los modelos
- Casos edge manejados apropiadamente

---

### **Fase 4: Documentación**

#### Paso 4.1: Documentar rutas públicas

**Objetivo**: Crear documentación completa de las rutas públicas.

**Tareas**:
1. Crear o actualizar `docs/public-routes.md`
2. Documentar cada ruta:
   - URL
   - Método HTTP
   - Nombre de ruta
   - Componente Livewire asociado
   - Parámetros requeridos
   - Route model binding usado
   - Ejemplos de uso

**Archivos a crear/modificar**:
- `docs/public-routes.md`

**Estructura sugerida**:

```markdown
# Rutas Públicas

## Página Principal
- `GET /` → `Home::class` (nombre: `home`)

## Programas
- `GET /programas` → `Programs\Index::class` (nombre: `programas.index`)
- `GET /programas/{program:slug}` → `Programs\Show::class` (nombre: `programas.show`)

## Convocatorias
...

## Newsletter
...
```

**Resultado esperado**:
- Documentación completa y actualizada
- Ejemplos de uso incluidos

---

#### Paso 4.2: Actualizar planificación principal

**Objetivo**: Marcar el paso 3.6.1 como completado en la planificación.

**Tareas**:
1. Actualizar `docs/planificacion_pasos.md`
2. Marcar el paso 3.6.1 como completado `[x]`
3. Añadir referencia a la documentación creada

**Archivos a modificar**:
- `docs/planificacion_pasos.md`

**Resultado esperado**:
- Planificación actualizada
- Paso marcado como completado

---

## Resumen de Archivos

### Archivos a Modificar
- `routes/web.php` - Organizar y documentar rutas públicas
- `docs/planificacion_pasos.md` - Marcar paso como completado

### Archivos a Crear
- `tests/Feature/Routes/PublicRoutesTest.php` - Tests completos de rutas públicas
- `docs/public-routes.md` - Documentación de rutas públicas

### Archivos a Revisar (Opcional)
- `app/Models/ErasmusEvent.php` - Verificar si tiene slug
- `app/Livewire/Public/Events/Show.php` - Verificar binding
- Tests existentes de rutas por módulo

---

## Criterios de Éxito

1. ✅ Todas las rutas públicas están definidas y funcionan
2. ✅ Rutas organizadas y bien comentadas
3. ✅ Route model binding consistente o documentado
4. ✅ Tests completos que verifican todas las rutas
5. ✅ Documentación completa de rutas públicas
6. ✅ Todos los tests pasan
7. ✅ Planificación actualizada

---

## Orden de Ejecución Recomendado

1. **Fase 1**: Revisión y organización (Pasos 1.1 y 1.2)
2. **Fase 2**: Tests (Pasos 2.1 y 2.2)
3. **Fase 3**: Verificación (Pasos 3.1 y 3.2)
4. **Fase 4**: Documentación (Pasos 4.1 y 4.2)

---

## Notas Importantes

1. **Route Model Binding**: Laravel 12 soporta `{model:slug}` directamente en la definición de ruta, lo cual es más limpio que usar `getRouteKeyName()` en el modelo.

2. **Eventos**: Si `ErasmusEvent` no tiene slug, considerar:
   - Añadir campo `slug` al modelo y migración
   - O documentar por qué usa ID (puede ser válido si los eventos no necesitan URLs amigables)

3. **Tests**: Priorizar tests que verifiquen funcionalidad sobre tests exhaustivos. Un test general puede ser suficiente si cubre todos los casos importantes.

4. **Documentación**: La documentación debe ser útil para desarrolladores que trabajen en el proyecto, no solo una lista de rutas.

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan listo para implementación
