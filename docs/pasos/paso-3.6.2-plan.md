# Plan Detallado: Paso 3.6.2 - Rutas de Administración

## Objetivo

Completar la implementación de las rutas de administración de la aplicación, asegurando que todas estén correctamente definidas, organizadas, protegidas con middleware apropiado, documentadas y testeadas.

## Estado Actual

### ✅ Ya Implementado

1. **Rutas definidas** en `routes/web.php` (líneas 93-187):
   - ✅ `/admin` - Dashboard
   - ✅ `/admin/programas` - CRUD programas (index, create, show, edit)
   - ✅ `/admin/anios-academicos` - CRUD años académicos (index, create, show, edit)
   - ✅ `/admin/convocatorias` - CRUD convocatorias (index, create, show, edit)
   - ✅ `/admin/convocatorias/{call}/fases` - CRUD fases (index, create, show, edit)
   - ✅ `/admin/convocatorias/{call}/resoluciones` - CRUD resoluciones (index, create, show, edit)
   - ✅ `/admin/noticias` - CRUD noticias (index, create, show, edit)
   - ✅ `/admin/etiquetas` - CRUD etiquetas (index, create, show, edit)
   - ✅ `/admin/documentos` - CRUD documentos (index, create, show, edit)
   - ✅ `/admin/categorias` - CRUD categorías (index, create, show, edit)
   - ✅ `/admin/eventos` - CRUD eventos (index, create, show, edit)
   - ✅ `/admin/usuarios` - CRUD usuarios (index, create, show, edit)
   - ✅ `/admin/roles` - CRUD roles (index, create, show, edit)
   - ✅ `/admin/configuracion` - Configuración del sistema (index, edit)
   - ✅ `/admin/traducciones` - CRUD traducciones (index, create, show, edit)
   - ✅ `/admin/auditoria` - Logs de auditoría (index, show)
   - ✅ `/admin/newsletter` - Suscripciones newsletter (index, show)

2. **Middleware básico**:
   - ✅ `auth` - Requiere autenticación
   - ✅ `verified` - Requiere verificación de email
   - ✅ Prefijo `/admin` aplicado a todas las rutas
   - ✅ Nombre de ruta `admin.*` aplicado a todas las rutas

3. **Componentes Livewire** existentes para todas las rutas

4. **Autorización**:
   - ✅ Implementada en componentes Livewire mediante `authorize()` en `mount()`
   - ✅ Policies implementadas para todos los modelos
   - ✅ Tests de autorización en componentes Livewire

5. **Tests de componentes**:
   - ✅ Tests completos para componentes Livewire (autorización, CRUD, validación)
   - ✅ Tests de FormRequests
   - ✅ Tests de Policies

### ⚠️ Pendiente

1. **Organización de rutas**: Mejorar organización y comentarios en `routes/web.php`
2. **Tests de rutas**: Crear tests específicos para verificar que las rutas funcionan correctamente (similar a `PublicRoutesTest.php`)
3. **Middleware de permisos**: Evaluar si se necesita middleware adicional de permisos en rutas (actualmente se maneja en componentes)
4. **Documentación**: Documentar todas las rutas de administración
5. **Verificación**: Asegurar que todas las rutas están correctamente protegidas y funcionan
6. **Route Model Binding**: Verificar que el binding funciona correctamente para todos los modelos

---

## Plan de Implementación

### **Fase 1: Revisión y Organización de Rutas**

#### Paso 1.1: Revisar y mejorar organización de rutas de administración

**Objetivo**: Organizar las rutas de administración en grupos lógicos para mejor mantenibilidad.

**Tareas**:
1. Revisar estructura actual de rutas en `routes/web.php`
2. Agrupar rutas por módulo con comentarios descriptivos
3. Separar claramente rutas principales de rutas anidadas
4. Añadir comentarios explicativos para cada sección
5. Verificar consistencia en nombres de rutas y parámetros

**Archivos a modificar**:
- `routes/web.php`

**Resultado esperado**:
- Rutas de administración agrupadas y bien comentadas
- Separación clara entre módulos
- Código más legible y mantenible
- Consistencia en nombres y parámetros

---

#### Paso 1.2: Verificar route model binding

**Objetivo**: Asegurar que todas las rutas usan el binding apropiado (slug o ID).

**Análisis actual**:
- ✅ Programas: usa `{program}` (ID) - Verificar si debe usar slug
- ✅ Años académicos: usa `{academic_year}` (ID) - Verificar si debe usar slug
- ✅ Convocatorias: usa `{call}` (ID) - Verificar si debe usar slug
- ✅ Noticias: usa `{news_post}` (ID) - Verificar si debe usar slug
- ✅ Etiquetas: usa `{news_tag}` (ID) - Verificar si debe usar slug
- ✅ Documentos: usa `{document}` (ID) - Verificar si debe usar slug
- ✅ Categorías: usa `{document_category}` (ID) - Verificar si debe usar slug
- ✅ Eventos: usa `{event}` (ID) - Verificar si debe usar slug
- ✅ Fases: usa `{call_phase}` (ID) - Verificar si debe usar slug
- ✅ Resoluciones: usa `{resolution}` (ID) - Verificar si debe usar slug
- ✅ Usuarios: usa `{user}` (ID) - Correcto (usuarios no tienen slug)
- ✅ Roles: usa `{role}` (ID) - Correcto (roles no tienen slug)
- ✅ Configuración: usa `{setting}` (ID) - Verificar si debe usar slug
- ✅ Traducciones: usa `{translation}` (ID) - Verificar si debe usar slug
- ✅ Auditoría: usa `{activity}` (ID) - Correcto (logs no tienen slug)
- ✅ Newsletter: usa `{newsletter_subscription}` (ID) - Verificar si debe usar slug

**Tareas**:
1. Verificar qué modelos tienen campo `slug`
2. Para modelos con slug, considerar usar `{model:slug}` en rutas públicas (ya implementado)
3. Para rutas de administración, decidir si usar ID o slug:
   - **Recomendación**: Usar ID en rutas de administración (más simple, no requiere slug único)
   - Las rutas públicas pueden usar slug para SEO
4. Documentar la decisión

**Archivos a revisar**:
- Modelos: `Program`, `Call`, `NewsPost`, `Document`, `ErasmusEvent`, etc.
- `routes/web.php`

**Resultado esperado**:
- Decisión documentada sobre el binding de cada modelo
- Rutas consistentes según la decisión tomada

---

### **Fase 2: Tests de Rutas de Administración**

#### Paso 2.1: Crear test base para rutas de administración

**Objetivo**: Crear archivo de test que verifique todas las rutas de administración.

**Tareas**:
1. Crear `tests/Feature/Routes/AdminRoutesTest.php`
2. Implementar tests básicos para cada ruta:
   - Test de redirección para usuarios no autenticados
   - Test de acceso para usuarios autenticados con permisos
   - Test de 403 para usuarios sin permisos
   - Test de route model binding (ID según corresponda)
   - Test de 404 para parámetro inválido

**Archivos a crear**:
- `tests/Feature/Routes/AdminRoutesTest.php`

**Tests a implementar**:

```php
describe('Admin Routes', function () {
    // Dashboard
    it('redirects unauthenticated users from dashboard', ...);
    it('allows authenticated users with permissions to access dashboard', ...);
    
    // Programs
    it('redirects unauthenticated users from programs index', ...);
    it('allows authenticated users with permissions to access programs index', ...);
    it('returns 404 for non-existent program', ...);
    
    // Academic Years
    // ... (similar para todos los módulos)
    
    // Calls
    // Calls Phases (rutas anidadas)
    // Calls Resolutions (rutas anidadas)
    
    // News
    // News Tags
    
    // Documents
    // Document Categories
    
    // Events
    
    // Users
    // Roles
    
    // Settings
    // Translations
    // Audit Logs
    // Newsletter
});
```

**Resultado esperado**:
- Test completo que verifica todas las rutas de administración
- Cobertura de casos exitosos, redirecciones y errores (403, 404)

---

#### Paso 2.2: Tests específicos de autorización por módulo

**Objetivo**: Verificar que la autorización funciona correctamente para cada módulo.

**Tareas**:
1. Para cada módulo, crear tests que verifiquen:
   - Usuario sin permisos recibe 403
   - Usuario con permisos puede acceder
   - Super-admin puede acceder a todo
   - Editor solo puede acceder según sus permisos
   - Viewer solo puede ver (no crear/editar/eliminar)

2. Verificar que las rutas anidadas (fases, resoluciones) también están protegidas

**Archivos a crear/modificar**:
- `tests/Feature/Routes/AdminRoutesTest.php` (expandir)

**Resultado esperado**:
- Tests completos de autorización para todas las rutas
- Cobertura de diferentes roles y permisos

---

### **Fase 3: Evaluación de Middleware de Permisos**

#### Paso 3.1: Evaluar necesidad de middleware de permisos en rutas

**Objetivo**: Decidir si se necesita middleware adicional de permisos en las rutas.

**Análisis actual**:
- ✅ Middleware `auth` y `verified` aplicado a todas las rutas
- ✅ Autorización verificada en componentes Livewire mediante `authorize()`
- ✅ Policies implementadas para todos los modelos
- ⚠️ No hay middleware de permisos específicos en rutas

**Opciones**:

**Opción A: Mantener autorización solo en componentes (Recomendada)**
- **Ventajas**:
  - Más flexible (permite lógica compleja en componentes)
  - Ya implementado y funcionando
  - Tests de componentes ya cubren autorización
- **Desventajas**:
  - Si un componente no verifica autorización, la ruta es accesible
  - Requiere disciplina en desarrollo

**Opción B: Añadir middleware de permisos en rutas**
- **Ventajas**:
  - Doble capa de seguridad
  - Más explícito en definición de rutas
  - Falla rápido si no hay permisos
- **Desventajas**:
  - Duplicación de lógica (rutas + componentes)
  - Más complejo de mantener
  - Puede ser redundante si los componentes ya verifican

**Recomendación**: **Opción A** - Mantener autorización en componentes Livewire porque:
1. Ya está implementado y funcionando
2. Permite lógica más compleja (ej: verificar propiedad del recurso)
3. Los tests de componentes ya verifican autorización
4. Es más flexible para casos especiales

**Tareas**:
1. Documentar la decisión
2. Asegurar que todos los componentes verifican autorización en `mount()`
3. Crear checklist de verificación para nuevos componentes

**Archivos a crear/modificar**:
- `docs/admin-routes.md` (documentar decisión)

**Resultado esperado**:
- Decisión documentada
- Checklist de verificación creado

---

### **Fase 4: Verificación y Validación**

#### Paso 4.1: Verificar que todas las rutas funcionan

**Objetivo**: Asegurar que todas las rutas responden correctamente.

**Tareas**:
1. Ejecutar todos los tests de rutas de administración
2. Verificar manualmente (opcional) que las rutas funcionan en el navegador
3. Verificar que los componentes Livewire se cargan correctamente
4. Verificar que el layout de administración se aplica correctamente
5. Verificar que las rutas anidadas funcionan correctamente

**Comandos a ejecutar**:
```bash
php artisan test tests/Feature/Routes/AdminRoutesTest.php
php artisan test tests/Feature/Livewire/Admin/
```

**Resultado esperado**:
- Todos los tests pasan
- Rutas funcionan correctamente

---

#### Paso 4.2: Verificar route model binding

**Objetivo**: Asegurar que el binding funciona correctamente para todos los modelos.

**Tareas**:
1. Verificar que los modelos se resuelven correctamente por ID
2. Probar casos edge:
   - ID no existente (debe retornar 404)
   - ID de registro eliminado (soft delete) - verificar comportamiento
   - ID inválido (no numérico) - verificar comportamiento

**Archivos a revisar**:
- Modelos con SoftDeletes
- `routes/web.php`

**Resultado esperado**:
- Route model binding funciona correctamente para todos los modelos
- Casos edge manejados apropiadamente

---

### **Fase 5: Documentación**

#### Paso 5.1: Documentar rutas de administración

**Objetivo**: Crear documentación completa de las rutas de administración.

**Tareas**:
1. Crear o actualizar `docs/admin-routes.md`
2. Documentar cada ruta:
   - URL
   - Método HTTP
   - Nombre de ruta
   - Componente Livewire asociado
   - Parámetros requeridos
   - Route model binding usado
   - Middleware aplicado
   - Permisos requeridos
   - Ejemplos de uso
3. Documentar rutas anidadas (fases, resoluciones)
4. Documentar decisiones de diseño (middleware, autorización, etc.)

**Archivos a crear/modificar**:
- `docs/admin-routes.md`

**Estructura sugerida**:

```markdown
# Rutas de Administración

## Middleware y Seguridad

Todas las rutas de administración están protegidas por:
- `auth`: Requiere autenticación
- `verified`: Requiere verificación de email
- Prefijo: `/admin`
- Nombre de ruta: `admin.*`

## Autorización

La autorización se verifica en los componentes Livewire mediante `authorize()` en `mount()`.
Cada componente usa su Policy correspondiente para verificar permisos.

## Dashboard

- `GET /admin` → `Admin\Dashboard::class` (nombre: `admin.dashboard`)
- Permisos: Requiere al menos `programs.view` o `users.view`

## Programas

- `GET /admin/programas` → `Admin\Programs\Index::class` (nombre: `admin.programs.index`)
- `GET /admin/programas/crear` → `Admin\Programs\Create::class` (nombre: `admin.programs.create`)
- `GET /admin/programas/{program}` → `Admin\Programs\Show::class` (nombre: `admin.programs.show`)
- `GET /admin/programas/{program}/editar` → `Admin\Programs\Edit::class` (nombre: `admin.programs.edit`)
- Permisos: `programs.view`, `programs.create`, `programs.edit`, `programs.delete`

## Convocatorias

### Rutas Principales
- `GET /admin/convocatorias` → `Admin\Calls\Index::class` (nombre: `admin.calls.index`)
- ...

### Rutas Anidadas - Fases
- `GET /admin/convocatorias/{call}/fases` → `Admin\Calls\Phases\Index::class` (nombre: `admin.calls.phases.index`)
- ...

### Rutas Anidadas - Resoluciones
- `GET /admin/convocatorias/{call}/resoluciones` → `Admin\Calls\Resolutions\Index::class` (nombre: `admin.calls.resolutions.index`)
- ...

## ... (resto de módulos)
```

**Resultado esperado**:
- Documentación completa y actualizada
- Ejemplos de uso incluidos
- Decisiones de diseño documentadas

---

#### Paso 5.2: Actualizar planificación principal

**Objetivo**: Marcar el paso 3.6.2 como completado en la planificación.

**Tareas**:
1. Actualizar `docs/planificacion_pasos.md`
2. Marcar el paso 3.6.2 como completado `[x]`
3. Añadir referencia a la documentación creada

**Archivos a modificar**:
- `docs/planificacion_pasos.md`

**Resultado esperado**:
- Planificación actualizada
- Paso marcado como completado

---

## Resumen de Archivos

### Archivos a Modificar
- `routes/web.php` - Mejorar organización y comentarios de rutas
- `docs/planificacion_pasos.md` - Marcar paso como completado

### Archivos a Crear
- `tests/Feature/Routes/AdminRoutesTest.php` - Tests completos de rutas de administración
- `docs/admin-routes.md` - Documentación de rutas de administración

### Archivos a Revisar
- Modelos con SoftDeletes - Verificar comportamiento de route model binding
- Componentes Livewire - Verificar que todos verifican autorización en `mount()`

---

## Criterios de Éxito

1. ✅ Todas las rutas de administración están definidas y funcionan
2. ✅ Rutas organizadas y bien comentadas
3. ✅ Route model binding consistente y documentado
4. ✅ Tests completos que verifican todas las rutas
5. ✅ Tests de autorización para todas las rutas
6. ✅ Documentación completa de rutas de administración
7. ✅ Decisiones de diseño documentadas
8. ✅ Todos los tests pasan
9. ✅ Planificación actualizada

---

## Orden de Ejecución Recomendado

1. **Fase 1**: Revisión y organización (Pasos 1.1 y 1.2)
2. **Fase 2**: Tests (Pasos 2.1 y 2.2)
3. **Fase 3**: Evaluación de middleware (Paso 3.1)
4. **Fase 4**: Verificación (Pasos 4.1 y 4.2)
5. **Fase 5**: Documentación (Pasos 5.1 y 5.2)

---

## Notas Importantes

1. **Route Model Binding**: En rutas de administración, usar ID es apropiado porque:
   - No requiere slugs únicos
   - Más simple de implementar
   - Los usuarios de administración no necesitan URLs amigables para SEO
   - Las rutas públicas pueden usar slugs para SEO

2. **Autorización**: La autorización en componentes Livewire es más flexible que middleware de rutas porque permite:
   - Verificar propiedad del recurso (ej: solo el autor puede editar)
   - Lógica compleja de permisos
   - Mensajes de error personalizados

3. **Rutas Anidadas**: Las rutas anidadas (fases, resoluciones) deben verificar que:
   - El recurso padre existe
   - El usuario tiene permisos sobre el recurso padre
   - El recurso hijo pertenece al recurso padre

4. **Tests**: Los tests de rutas deben verificar:
   - Redirección de usuarios no autenticados
   - Acceso con permisos correctos
   - Denegación sin permisos (403)
   - 404 para recursos no existentes
   - Route model binding funciona correctamente

5. **Consistencia**: Mantener consistencia en:
   - Nombres de rutas (`admin.{module}.{action}`)
   - Estructura de URLs (`/admin/{module}/{action}`)
   - Parámetros de rutas (`{model}` para ID)

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan listo para implementación
