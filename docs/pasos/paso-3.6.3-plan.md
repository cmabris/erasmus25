# Plan Detallado: Paso 3.6.3 - Navegación Principal

## Objetivo

Completar la implementación de la navegación principal de la aplicación, asegurando que tanto la navegación pública como la de administración estén completamente funcionales, responsive, con indicadores de idioma y enlaces según permisos del usuario.

## Estado Actual

### ✅ Ya Implementado

1. **Navegación Pública** (`components/nav/public-nav.blade.php`):
   - ✅ Componente de navegación pública creado
   - ✅ Menú responsive con Flux UI
   - ✅ Indicador de idioma actual (componente `Language\Switcher`)
   - ✅ Enlaces según autenticación (login/register o dashboard)
   - ✅ Menú móvil funcional
   - ✅ Logo y nombre del centro configurable

2. **Navegación de Administración** (`components/layouts/app/sidebar.blade.php`):
   - ✅ Sidebar de administración con Flux UI
   - ✅ Menú responsive (stashable)
   - ✅ Enlaces según permisos usando `@can`
   - ✅ Menú de usuario (desktop y móvil)
   - ✅ Grupos de navegación organizados
   - ✅ Enlaces a todas las secciones de administración

3. **Componente de Idioma**:
   - ✅ Componente Livewire `Language\Switcher` con múltiples variantes
   - ✅ Integrado en navegación pública

### ⚠️ Pendiente

1. **Navegación Pública**:
   - ⚠️ Mostrar enlace al panel de administración si el usuario tiene permisos (actualmente solo muestra si está autenticado)
   - ⚠️ Mejorar la lógica de visibilidad de enlaces según permisos específicos

2. **Navegación de Administración**:
   - ⚠️ Añadir indicador de idioma actual (componente `Language\Switcher`)
   - ⚠️ Mejorar organización del sidebar (considerar extraer navegación a componente separado)

3. **Componente Separado de Navegación Admin**:
   - ⚠️ Evaluar si crear `components/nav/admin-nav.blade.php` separado del sidebar
   - ⚠️ O mantener integrado en el sidebar si funciona bien

4. **Tests**:
   - ⚠️ Crear tests para verificar navegación pública
   - ⚠️ Crear tests para verificar navegación de administración
   - ⚠️ Verificar que los enlaces se muestran según permisos

5. **Documentación**:
   - ⚠️ Documentar estructura de navegación
   - ⚠️ Documentar cómo añadir nuevos elementos de navegación

---

## Plan de Implementación

### **Fase 1: Mejora de Navegación Pública**

#### Paso 1.1: Añadir enlace al panel de administración según permisos

**Objetivo**: Mostrar enlace al panel de administración en la navegación pública solo si el usuario tiene permisos de administración.

**Tareas**:
1. Revisar qué permisos se necesitan para acceder al dashboard de administración
2. Añadir verificación de permisos en `public-nav.blade.php`
3. Mostrar enlace "Panel de Administración" si el usuario tiene permisos
4. Asegurar que el enlace sea visible tanto en desktop como en móvil
5. Usar icono apropiado (ej: `squares-2x2`)

**Archivos a modificar**:
- `resources/views/components/nav/public-nav.blade.php`

**Lógica sugerida**:
```php
@auth
    @can('viewAny', \App\Models\Program::class)
        {{-- Mostrar enlace al panel --}}
    @elsecan('viewAny', \App\Models\Call::class)
        {{-- O cualquier otro permiso de admin --}}
    @endcan
@endauth
```

**Resultado esperado**:
- Enlace al panel de administración visible solo para usuarios con permisos
- Funciona en desktop y móvil
- Estilo consistente con el resto de la navegación

---

#### Paso 1.2: Mejorar organización y estructura del componente

**Objetivo**: Mejorar la legibilidad y mantenibilidad del componente de navegación pública.

**Tareas**:
1. Revisar estructura actual del componente
2. Añadir comentarios descriptivos
3. Organizar secciones lógicamente
4. Verificar que todas las clases de Tailwind sean consistentes
5. Asegurar accesibilidad (ARIA labels, navegación por teclado)

**Archivos a modificar**:
- `resources/views/components/nav/public-nav.blade.php`

**Resultado esperado**:
- Código más legible y mantenible
- Mejor accesibilidad
- Estructura clara y documentada

---

### **Fase 2: Mejora de Navegación de Administración**

#### Paso 2.1: Añadir indicador de idioma al sidebar de administración

**Objetivo**: Integrar el componente `Language\Switcher` en el sidebar de administración.

**Tareas**:
1. Decidir dónde colocar el selector de idioma (parte superior, inferior, o en el menú de usuario)
2. Integrar el componente `Language\Switcher` en el sidebar
3. Asegurar que funcione correctamente en desktop y móvil
4. Verificar que el estilo sea consistente con el resto del sidebar
5. Probar el cambio de idioma desde el panel de administración

**Archivos a modificar**:
- `resources/views/components/layouts/app/sidebar.blade.php`

**Opciones de ubicación**:
- **Opción A**: Parte superior del sidebar (después del logo)
- **Opción B**: Parte inferior del sidebar (antes del menú de usuario)
- **Opción C**: Dentro del menú de usuario (dropdown)
- **Recomendación**: Opción B (parte inferior, antes del menú de usuario) para mantener consistencia con la navegación pública

**Resultado esperado**:
- Selector de idioma visible y funcional en el sidebar
- Estilo consistente con Flux UI
- Funciona correctamente en todos los dispositivos

---

#### Paso 2.2: Evaluar y mejorar organización del sidebar

**Objetivo**: Revisar si es necesario extraer la navegación a un componente separado o mejorar la organización actual.

**Tareas**:
1. Revisar la estructura actual del sidebar
2. Evaluar si crear `components/nav/admin-nav.blade.php` separado
3. Si se crea componente separado:
   - Extraer la lógica de navegación a `admin-nav.blade.php`
   - Incluir el componente en el sidebar
   - Mantener la misma funcionalidad
4. Si se mantiene integrado:
   - Mejorar organización y comentarios
   - Asegurar que los grupos de navegación estén bien organizados
   - Verificar que no haya duplicación de grupos

**Archivos a crear/modificar**:
- `resources/views/components/nav/admin-nav.blade.php` (si se decide crear)
- `resources/views/components/layouts/app/sidebar.blade.php`

**Análisis**:
- **Ventajas de componente separado**:
  - Mejor organización
  - Reutilizable en otros contextos
  - Más fácil de testear
  - Separación de responsabilidades
- **Ventajas de mantener integrado**:
  - Menos archivos
  - Todo en un lugar
  - Ya funciona correctamente

**Recomendación**: Crear componente separado `admin-nav.blade.php` para mejor organización y mantenibilidad, siguiendo el patrón de `public-nav.blade.php`.

**Resultado esperado**:
- Navegación de administración bien organizada
- Código más mantenible
- Estructura clara y documentada

---

### **Fase 3: Optimización y Consistencia**

#### Paso 3.1: Revisar y optimizar grupos de navegación

**Objetivo**: Asegurar que los grupos de navegación en el sidebar estén bien organizados y no haya duplicación.

**Tareas**:
1. Revisar todos los grupos de navegación en el sidebar
2. Identificar duplicaciones (ej: múltiples grupos con heading "Content")
3. Reorganizar grupos lógicamente:
   - Platform (Dashboard)
   - Contenido (Programas, Convocatorias, Noticias, Documentos, Eventos)
   - Gestión (Años Académicos)
   - Sistema (Usuarios, Roles, Configuración, Traducciones, Auditoría, Newsletter)
4. Asegurar que cada grupo tenga un heading único y descriptivo
5. Verificar que los iconos sean apropiados y consistentes

**Archivos a modificar**:
- `resources/views/components/nav/admin-nav.blade.php` (si se crea)
- O `resources/views/components/layouts/app/sidebar.blade.php`

**Resultado esperado**:
- Grupos de navegación bien organizados
- Sin duplicación de headings
- Estructura lógica y clara

---

#### Paso 3.2: Verificar consistencia de estilos y comportamiento

**Objetivo**: Asegurar que ambas navegaciones (pública y admin) sean consistentes en estilo y comportamiento.

**Tareas**:
1. Comparar estilos entre navegación pública y admin
2. Verificar que los iconos sean consistentes
3. Asegurar que las transiciones y animaciones sean similares
4. Verificar que el comportamiento responsive sea consistente
5. Revisar que los estados activos/current sean consistentes

**Archivos a revisar**:
- `resources/views/components/nav/public-nav.blade.php`
- `resources/views/components/nav/admin-nav.blade.php` (o sidebar)

**Resultado esperado**:
- Estilos consistentes entre ambas navegaciones
- Comportamiento similar en diferentes dispositivos
- Experiencia de usuario coherente

---

### **Fase 4: Tests**

#### Paso 4.1: Crear tests para navegación pública

**Objetivo**: Verificar que la navegación pública funciona correctamente y muestra los enlaces apropiados según permisos.

**Tareas**:
1. Crear `tests/Feature/Components/PublicNavTest.php`
2. Implementar tests para:
   - Verificar que los enlaces públicos se muestran correctamente
   - Verificar que el enlace al panel se muestra solo con permisos
   - Verificar que el selector de idioma funciona
   - Verificar que el menú móvil funciona
   - Verificar que los enlaces de autenticación se muestran correctamente
   - Verificar que el logo y nombre del centro se muestran

**Archivos a crear**:
- `tests/Feature/Components/PublicNavTest.php`

**Tests sugeridos**:
```php
describe('Public Navigation Component', function () {
    it('shows public navigation links for unauthenticated users', ...);
    it('shows dashboard link for authenticated users', ...);
    it('shows admin panel link for users with admin permissions', ...);
    it('does not show admin panel link for users without permissions', ...);
    it('shows language switcher', ...);
    it('shows mobile menu toggle', ...);
    // ... más tests
});
```

**Resultado esperado**:
- Tests completos que verifican toda la funcionalidad de la navegación pública
- Cobertura de casos con y sin autenticación
- Cobertura de diferentes permisos

---

#### Paso 4.2: Crear tests para navegación de administración

**Objetivo**: Verificar que la navegación de administración funciona correctamente y muestra los enlaces según permisos.

**Tareas**:
1. Crear `tests/Feature/Components/AdminNavTest.php`
2. Implementar tests para:
   - Verificar que los enlaces se muestran según permisos
   - Verificar que el selector de idioma funciona
   - Verificar que el menú de usuario funciona
   - Verificar que los grupos de navegación se muestran correctamente
   - Verificar que el sidebar es responsive

**Archivos a crear**:
- `tests/Feature/Components/AdminNavTest.php`

**Tests sugeridos**:
```php
describe('Admin Navigation Component', function () {
    it('shows dashboard link for authenticated users', ...);
    it('shows programs link only if user can view programs', ...);
    it('shows calls link only if user can view calls', ...);
    it('shows language switcher', ...);
    it('shows user menu', ...);
    // ... más tests
});
```

**Resultado esperado**:
- Tests completos que verifican toda la funcionalidad de la navegación de administración
- Cobertura de diferentes roles y permisos
- Verificación de que los enlaces se ocultan correctamente sin permisos

---

### **Fase 5: Documentación**

#### Paso 5.1: Documentar estructura de navegación

**Objetivo**: Crear documentación completa sobre la estructura y uso de la navegación.

**Tareas**:
1. Crear o actualizar `docs/navigation.md`
2. Documentar:
   - Estructura de navegación pública
   - Estructura de navegación de administración
   - Cómo añadir nuevos elementos de navegación
   - Cómo usar permisos en navegación
   - Cómo integrar el selector de idioma
   - Ejemplos de uso

**Archivos a crear/modificar**:
- `docs/navigation.md`

**Estructura sugerida**:
```markdown
# Navegación Principal

## Navegación Pública

### Componente
- `components/nav/public-nav.blade.php`

### Características
- Menú responsive
- Selector de idioma
- Enlaces según autenticación y permisos

### Añadir nuevos enlaces
...

## Navegación de Administración

### Componente
- `components/nav/admin-nav.blade.php`

### Características
- Sidebar con Flux UI
- Enlaces según permisos
- Selector de idioma

### Añadir nuevos enlaces
...
```

**Resultado esperado**:
- Documentación completa y actualizada
- Ejemplos de uso incluidos
- Guía para añadir nuevos elementos

---

#### Paso 5.2: Actualizar planificación principal

**Objetivo**: Marcar el paso 3.6.3 como completado en la planificación.

**Tareas**:
1. Actualizar `docs/planificacion_pasos.md`
2. Marcar el paso 3.6.3 como completado `[x]`
3. Añadir referencia a la documentación creada

**Archivos a modificar**:
- `docs/planificacion_pasos.md`

**Resultado esperado**:
- Planificación actualizada
- Paso marcado como completado

---

## Resumen de Archivos

### Archivos a Modificar
- `resources/views/components/nav/public-nav.blade.php` - Mejorar navegación pública
- `resources/views/components/layouts/app/sidebar.blade.php` - Añadir selector de idioma y mejorar organización
- `docs/planificacion_pasos.md` - Marcar paso como completado

### Archivos a Crear
- `resources/views/components/nav/admin-nav.blade.php` - Componente separado de navegación admin (recomendado)
- `tests/Feature/Components/PublicNavTest.php` - Tests de navegación pública
- `tests/Feature/Components/AdminNavTest.php` - Tests de navegación de administración
- `docs/navigation.md` - Documentación de navegación

### Archivos a Revisar
- `app/Livewire/Language/Switcher.php` - Verificar integración
- Traducciones en `lang/*/common.php` - Verificar que existen todas las traducciones necesarias

---

## Criterios de Éxito

1. ✅ Navegación pública completamente funcional
2. ✅ Navegación de administración completamente funcional
3. ✅ Selector de idioma en ambas navegaciones
4. ✅ Enlaces según permisos funcionando correctamente
5. ✅ Menús responsive funcionando en todos los dispositivos
6. ✅ Tests completos para ambas navegaciones
7. ✅ Documentación completa y actualizada
8. ✅ Todos los tests pasan
9. ✅ Planificación actualizada

---

## Orden de Ejecución Recomendado

1. **Fase 1**: Mejora de navegación pública (Pasos 1.1 y 1.2)
2. **Fase 2**: Mejora de navegación de administración (Pasos 2.1 y 2.2)
3. **Fase 3**: Optimización y consistencia (Pasos 3.1 y 3.2)
4. **Fase 4**: Tests (Pasos 4.1 y 4.2)
5. **Fase 5**: Documentación (Pasos 5.1 y 5.2)

---

## Notas Importantes

1. **Permisos en Navegación Pública**: La navegación pública debe mostrar el enlace al panel de administración solo si el usuario tiene permisos. Se puede usar `@can('viewAny', \App\Models\Program::class)` o verificar múltiples permisos.

2. **Selector de Idioma**: El componente `Language\Switcher` ya está implementado y funcional. Solo necesita integrarse en el sidebar de administración.

3. **Componente Separado**: Se recomienda crear `admin-nav.blade.php` separado para mejor organización, siguiendo el patrón de `public-nav.blade.php`.

4. **Grupos de Navegación**: Revisar que no haya duplicación de grupos con el mismo heading. Organizar lógicamente:
   - Platform
   - Contenido
   - Gestión
   - Sistema

5. **Tests**: Los tests deben verificar:
   - Visibilidad de enlaces según autenticación
   - Visibilidad de enlaces según permisos
   - Funcionalidad del selector de idioma
   - Comportamiento responsive

6. **Consistencia**: Mantener consistencia entre navegación pública y admin en:
   - Estilos
   - Comportamiento
   - Iconos
   - Transiciones

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan listo para implementación
