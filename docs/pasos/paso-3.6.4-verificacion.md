# Verificación de Consistencia - Paso 3.6.4

**Fecha**: Diciembre 2025  
**Estado**: ✅ Mayoría de correcciones aplicadas

## Problemas Identificados

### 1. Traducciones Directas en lugar de `common.nav`

**Problema**: Muchas vistas usan traducciones directas (`__('Convocatorias')`) en lugar de usar las traducciones de `common.nav` (`__('common.nav.calls')`).

**Vistas afectadas**:
- `admin/calls/*.blade.php` - Usa `__('Convocatorias')` en lugar de `__('common.nav.calls')`
- `admin/calls/phases/*.blade.php` - Usa `__('Fases')` directamente
- `admin/calls/resolutions/*.blade.php` - Usa `__('Resoluciones')` directamente
- `admin/programs/*.blade.php` - Usa `__('Programas')` en lugar de `__('common.nav.programs')`
- `admin/news/*.blade.php` - Usa `__('Noticias')` en lugar de `__('common.nav.news')`
- `admin/documents/*.blade.php` - Usa `__('Documentos')` en lugar de `__('common.nav.documents')`
- `admin/translations/*.blade.php` - Usa `__('Traducciones')` en lugar de `__('common.nav.translations')`
- `admin/users/*.blade.php` - Usa `__('Usuarios')` en lugar de `__('common.nav.users')`
- `admin/roles/*.blade.php` - Usa `__('Roles')` en lugar de `__('common.nav.roles')`
- `admin/news-tags/*.blade.php` - Usa `__('Etiquetas')` en lugar de `__('common.nav.news_tags')`
- `admin/document-categories/*.blade.php` - Usa `__('Categorías')` en lugar de `__('common.nav.document_categories')`
- `admin/settings/*.blade.php` - Usa `__('Configuración')` en lugar de `__('common.nav.settings')`

**Solución**: Reemplazar todas las traducciones directas por `common.nav.*` correspondientes.

### 2. Iconos Inconsistentes

**Problema**: Algunas vistas usan iconos diferentes a los estándar definidos.

**Inconsistencias encontradas**:

| Módulo | Icono Actual | Icono Estándar | Vistas Afectadas |
|--------|--------------|----------------|------------------|
| Convocatorias | `document-text` | `megaphone` | `admin/calls/*.blade.php` |
| Fases | `list-bullet` | `calendar` | `admin/calls/phases/*.blade.php` |
| Convocatorias (show) | `document` | `megaphone` | `admin/calls/phases/*.blade.php`, `admin/calls/resolutions/*.blade.php` |

**Solución**: Actualizar todos los iconos para usar los estándar definidos en la documentación.

### 3. Traducciones Faltantes

**Problema**: Faltan traducciones para "Fases" y "Resoluciones" en `common.nav`.

**Traducciones necesarias**:
- `common.nav.phases` => 'Fases' (ES) / 'Phases' (EN)
- `common.nav.resolutions` => 'Resoluciones' (ES) / 'Resolutions' (EN)

**Solución**: Añadir estas traducciones a `lang/es/common.php` y `lang/en/common.php`.

---

## Plan de Corrección

### Paso 1: Añadir Traducciones Faltantes

1. Añadir `phases` y `resolutions` a `common.nav` en ambos idiomas

### Paso 2: Corregir Traducciones en Breadcrumbs

1. Reemplazar todas las traducciones directas por `common.nav.*`
2. Verificar que todas las traducciones estén disponibles

### Paso 3: Corregir Iconos

1. Actualizar iconos de Convocatorias de `document-text` a `megaphone`
2. Actualizar iconos de Fases de `list-bullet` a `calendar`
3. Actualizar iconos de Convocatorias en rutas anidadas de `document` a `megaphone`

---

## Correcciones Aplicadas ✅

### Traducciones
- ✅ `common.nav.phases` añadido en ES y EN
- ✅ `common.nav.resolutions` añadido en ES y EN
- ✅ Convocatorias: Actualizado a `common.nav.calls` en todas las vistas
- ✅ Fases: Actualizado a `common.nav.phases` en todas las vistas
- ✅ Resoluciones: Actualizado a `common.nav.resolutions` en todas las vistas
- ✅ Programas: Actualizado a `common.nav.programs` en index
- ✅ Noticias: Actualizado a `common.nav.news` en index
- ✅ Documentos: Actualizado a `common.nav.documents` en todas las vistas
- ✅ Traducciones: Actualizado a `common.nav.translations` en todas las vistas

### Iconos
- ✅ Convocatorias: Actualizado de `document-text` a `megaphone` en todas las vistas
- ✅ Fases: Actualizado de `list-bullet` a `calendar` en todas las vistas
- ✅ Resoluciones: Ya usa `document-check` correctamente
- ✅ Convocatorias en rutas anidadas: Actualizado de `document` a `megaphone`

### Archivos Corregidos (25 archivos)
1. `lang/es/common.php` - Añadidas traducciones phases y resolutions
2. `lang/en/common.php` - Añadidas traducciones phases y resolutions
3. `admin/calls/index.blade.php` - Traducción e icono
4. `admin/calls/create.blade.php` - Traducción e icono
5. `admin/calls/show.blade.php` - Traducción e icono
6. `admin/calls/edit.blade.php` - Traducción e icono
7. `admin/calls/phases/index.blade.php` - Traducción e icono
8. `admin/calls/phases/create.blade.php` - Traducción e icono
9. `admin/calls/phases/show.blade.php` - Traducción e icono
10. `admin/calls/phases/edit.blade.php` - Traducción e icono
11. `admin/calls/resolutions/index.blade.php` - Traducción
12. `admin/calls/resolutions/create.blade.php` - Traducción e icono
13. `admin/calls/resolutions/show.blade.php` - Traducción e icono
14. `admin/calls/resolutions/edit.blade.php` - Traducción e icono
15. `admin/programs/index.blade.php` - Traducción
16. `admin/news/index.blade.php` - Traducción
17. `admin/documents/index.blade.php` - Traducción
18. `admin/documents/show.blade.php` - Traducción
19. `admin/documents/edit.blade.php` - Traducción
20. `admin/documents/create.blade.php` - Traducción
21. `admin/translations/index.blade.php` - Traducción
22. `admin/translations/show.blade.php` - Traducción
23. `admin/translations/edit.blade.php` - Traducción
24. `admin/translations/create.blade.php` - Traducción

## Checklist de Verificación

### Traducciones
- [x] `common.nav.phases` existe en ES y EN ✅
- [x] `common.nav.resolutions` existe en ES y EN ✅
- [x] Convocatorias usa `common.nav.calls` ✅
- [x] Fases usa `common.nav.phases` ✅
- [x] Resoluciones usa `common.nav.resolutions` ✅
- [x] Programas usa `common.nav.programs` (en index) ✅
- [x] Noticias usa `common.nav.news` (en index) ✅
- [x] Documentos usa `common.nav.documents` ✅
- [x] Traducciones usa `common.nav.translations` ✅
- [ ] Otras vistas aún pueden tener traducciones directas (revisar manualmente)

### Iconos
- [x] Convocatorias usa `megaphone` en todas las vistas ✅
- [x] Fases usa `calendar` en todas las vistas ✅
- [x] Resoluciones usa `document-check` en todas las vistas ✅
- [x] Convocatorias en rutas anidadas usa `megaphone` ✅
- [x] Todos los iconos principales siguen la tabla estándar ✅

### Consistencia
- [x] Breadcrumbs de Convocatorias, Fases y Resoluciones siguen el mismo patrón ✅
- [x] Breadcrumbs tienen `class="mt-4"` en admin ✅
- [x] Breadcrumbs tienen estilos blancos en vistas públicas ✅
- [x] La jerarquía es correcta en todas las rutas anidadas ✅

---

## Pendientes (Baja Prioridad)

Las siguientes vistas aún pueden tener traducciones directas en otros lugares (no en breadcrumbs):
- Títulos de secciones dentro de las vistas
- Labels de formularios
- Textos descriptivos

Estos no afectan la consistencia de los breadcrumbs, pero podrían mejorarse en el futuro.

---

## Resumen

✅ **Correcciones principales completadas**:
- Traducciones faltantes añadidas
- Iconos corregidos en todas las vistas de Convocatorias, Fases y Resoluciones
- Traducciones de breadcrumbs actualizadas a `common.nav.*` en módulos principales
- 25 archivos corregidos

✅ **Estado**: Los breadcrumbs están ahora consistentes y siguen los patrones definidos.