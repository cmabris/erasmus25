# Plan de Desarrollo: Paso 3.5.12 - Configuración del Sistema en Panel de Administración

Este documento establece el plan detallado para desarrollar el sistema completo de gestión de Configuración del Sistema en el panel de administración de la aplicación Erasmus+ Centro (Murcia).

## 🎯 Objetivo

Crear un sistema completo de gestión de Configuraciones del Sistema en el panel de administración con:
- Listado moderno con tabla interactiva agrupada por grupos
- Formulario de edición con validación de tipos de datos
- Gestión de traducciones de configuraciones (description y value cuando aplique)
- Validación automática según tipo (string, integer, boolean, json)
- Diseño moderno y responsive usando Flux UI y Tailwind CSS v4
- Registro de usuario que actualiza cada configuración

---

## 📋 Pasos de Desarrollo (14 Pasos)

### **Fase 1: Preparación Base**

#### **Paso 1: Implementar Trait Translatable en Setting**
- [ ] Añadir el trait `Translatable` al modelo `Setting`
- [ ] Verificar que las relaciones funcionen correctamente
- [ ] Añadir campos traducibles: `description` (siempre) y `value` (opcional, según tipo)
- [ ] Actualizar el modelo para soportar traducciones

#### **Paso 2: Crear/Actualizar FormRequests**
- [ ] Crear `UpdateSettingRequest`:
  - Añadir autorización con `SettingPolicy::update()`
  - Añadir mensajes de error personalizados en español e inglés
  - Validación según tipo:
    - `string`: texto válido
    - `integer`: número entero válido
    - `boolean`: true/false o 1/0
    - `json`: JSON válido
  - Validar que el `key` no se pueda modificar (solo el valor)
  - Validar que el `type` no se pueda modificar (solo el valor)

#### **Paso 3: Crear SettingPolicy**
- [ ] Crear `SettingPolicy` con métodos:
  - `viewAny()` - Ver listado de configuraciones
  - `view()` - Ver detalle de configuración
  - `update()` - Actualizar configuración (solo admin/super-admin)
  - `create()` - Crear configuración (solo super-admin, opcional)
  - `delete()` - Eliminar configuración (solo super-admin, opcional)
- [ ] Implementar lógica de autorización por roles

---

### **Fase 2: Estructura Base y Listado**

#### **Paso 4: Componente Index (Listado)**
- [ ] Crear componente Livewire `Admin\Settings\Index`
- [ ] Implementar propiedades públicas:
  - `Collection $settings` - Lista de configuraciones (computed)
  - `string $search = ''` - Búsqueda (con `#[Url]`)
  - `string $filterGroup = ''` - Filtro por grupo (con `#[Url]`)
  - `string $sortField = 'group'` - Campo de ordenación (con `#[Url]`)
  - `string $sortDirection = 'asc'` - Dirección de ordenación (con `#[Url]`)
  - `int $perPage = 20` - Elementos por página (con `#[Url]`)
- [ ] Implementar métodos:
  - `mount()` - Inicialización con autorización
  - `settings()` - Computed property agrupada por grupos, con filtros y ordenación
  - `sortBy($field)` - Ordenación
  - `resetFilters()` - Resetear filtros
  - `updatedSearch()` - Resetear página al buscar
  - `updatedFilterGroup()` - Resetear página al cambiar filtro
  - `getGroupLabel($group)` - Obtener etiqueta traducida del grupo
  - `getTypeLabel($type)` - Obtener etiqueta traducida del tipo
  - `formatValue($setting)` - Formatear valor según tipo para visualización
  - `render()` - Renderizado
- [ ] Implementar autorización con `SettingPolicy`
- [ ] Crear vista `livewire/admin/settings/index.blade.php`:
  - Header con título y breadcrumbs
  - Filtros: búsqueda, filtro por grupo, reset
  - Agrupación visual por grupos (acordeón o secciones)
  - Tabla responsive con columnas: clave, valor (formateado), tipo, grupo, descripción, última actualización, usuario, acciones
  - Botón de editar para cada configuración
  - Paginación
  - Estado vacío
  - Loading states
  - Badges para tipos y grupos

---

### **Fase 3: Edición**

#### **Paso 5: Componente Edit (Editar)**
- [ ] Crear componente Livewire `Admin\Settings\Edit`
- [ ] Implementar propiedades públicas:
  - `Setting $setting` - Configuración a editar
  - `mixed $value = null` - Valor de la configuración (según tipo)
  - `string $description = ''` - Descripción (traducible)
  - `array $translations = []` - Traducciones de description y value
  - `array $availableLanguages = []` - Idiomas disponibles
  - `string $activeLanguage = 'es'` - Idioma activo para traducciones
- [ ] Implementar métodos:
  - `mount(Setting $setting)` - Cargar datos de la configuración
  - `updatedValue()` - Validar valor según tipo en tiempo real
  - `validateValue()` - Validar valor según tipo
  - `update()` - Actualizar configuración usando `UpdateSettingRequest`
  - `saveTranslation($field, $languageCode, $value)` - Guardar traducción
  - `getTranslatedValue($field, $languageCode)` - Obtener valor traducido
  - `render()` - Renderizado
- [ ] Crear vista `livewire/admin/settings/edit.blade.php`:
  - Header con título y breadcrumbs
  - Información de solo lectura: key, type, group
  - Formulario con Flux UI:
    - Campo valor según tipo:
      - `string`: textarea o input
      - `integer`: input number
      - `boolean`: switch/checkbox
      - `json`: textarea con validación JSON + preview formateado
    - Campo descripción (traducible)
    - Sección de traducciones (si aplica):
      - Tabs por idioma
      - Traducción de description
      - Traducción de value (solo para string)
    - Botones: guardar y cancelar
  - Validación visual en tiempo real
  - Mensajes de error específicos por tipo
  - Preview de JSON formateado
  - Información adicional: fecha creación, última actualización, usuario que actualizó

---

### **Fase 4: Rutas y Navegación**

#### **Paso 6: Configurar Rutas**
- [ ] Añadir rutas en `routes/web.php` dentro del grupo `admin`:
  - `GET /admin/configuracion` → `Admin\Settings\Index` (nombre: `admin.settings.index`)
  - `GET /admin/configuracion/{setting}/editar` → `Admin\Settings\Edit` (nombre: `admin.settings.edit`)
- [ ] Verificar que las rutas usen el middleware correcto (`auth`, `verified`)
- [ ] Añadir middleware de autorización si es necesario

#### **Paso 7: Actualizar Navegación**
- [ ] Añadir enlace en sidebar de administración
- [ ] Añadir traducciones necesarias en `lang/es/common.php` y `lang/en/common.php`:
  - `Configuración del Sistema` / `System Settings`
  - `Editar Configuración` / `Edit Setting`
  - Grupos: `General`, `Email`, `RGPD`, `Media`, `SEO`
  - Tipos: `Texto`, `Número`, `Booleano`, `JSON`
  - Mensajes de éxito/error relacionados
  - Etiquetas de campos

---

### **Fase 5: Validación y Formateo de Valores**

#### **Paso 8: Implementar Validación por Tipo**
- [ ] Crear helper o método para validar valores según tipo:
  - `string`: validar que sea texto válido
  - `integer`: validar que sea número entero
  - `boolean`: validar que sea true/false o 1/0
  - `json`: validar que sea JSON válido y decodificable
- [ ] Añadir mensajes de error específicos por tipo
- [ ] Implementar validación en tiempo real en el componente Edit

#### **Paso 9: Implementar Formateo de Valores**
- [ ] Crear método para formatear valores en Index:
  - `string`: mostrar truncado si es muy largo
  - `integer`: mostrar con formato numérico
  - `boolean`: mostrar badge verde/rojo o icono
  - `json`: mostrar preview formateado o "JSON Object"
- [ ] Añadir tooltip o modal para ver valor completo si está truncado

---

### **Fase 6: Gestión de Traducciones**

#### **Paso 10: Implementar Traducciones de Configuraciones**
- [ ] Añadir trait `Translatable` al modelo Setting
- [ ] Implementar gestión de traducciones en componente Edit:
  - Tabs por idioma para editar traducciones
  - Traducción de `description` (siempre disponible)
  - Traducción de `value` (solo para tipo `string`)
  - Guardar traducciones al actualizar configuración
- [ ] Mostrar traducciones en Index si están disponibles
- [ ] Añadir indicador visual de traducciones disponibles

---

### **Fase 7: Optimizaciones y Mejoras**

#### **Paso 11: Optimizaciones**
- [ ] Añadir eager loading para relación `updater` en Index
- [ ] Añadir caché para configuraciones frecuentes (opcional)
- [ ] Verificar índices en base de datos (ya existen para `group` y `key`)
- [ ] Optimizar consultas de traducciones

#### **Paso 12: Mejoras de UX**
- [ ] Añadir preview de JSON formateado en Edit
- [ ] Añadir validación visual en tiempo real
- [ ] Añadir tooltips explicativos para cada campo
- [ ] Añadir confirmación antes de guardar cambios importantes
- [ ] Añadir historial de cambios (opcional, usando audit logs)

---

### **Fase 8: Tests**

#### **Paso 13: Tests de Componentes Livewire**
- [ ] Crear test `tests/Feature/Livewire/Admin/Settings/IndexTest.php`:
  - Test de autorización (solo usuarios con permisos pueden ver)
  - Test de listado con datos
  - Test de agrupación por grupos
  - Test de búsqueda
  - Test de filtro por grupo
  - Test de ordenación
  - Test de formateo de valores según tipo
  - Test de redirección a editar
- [ ] Crear test `tests/Feature/Livewire/Admin/Settings/EditTest.php`:
  - Test de autorización
  - Test de carga de datos
  - Test de actualización exitosa por tipo (string, integer, boolean, json)
  - Test de validación de valores según tipo
  - Test de validación de JSON inválido
  - Test de guardado de traducciones
  - Test de registro de usuario que actualiza
  - Test de redirección después de actualizar
  - Test de que key y type no se pueden modificar

#### **Paso 14: Tests de FormRequests y Policies**
- [ ] Crear tests para `UpdateSettingRequest`:
  - Test de autorización
  - Test de validación de valores según tipo
  - Test de validación de JSON
  - Test de que key y type no se pueden modificar
- [ ] Crear tests para `SettingPolicy`:
  - Test de autorización por rol
  - Test de permisos de viewAny, view, update

---

## 📝 Notas Importantes

### Gestión de Tipos
- **string**: Texto libre, puede ser largo (usar textarea)
- **integer**: Número entero, validar rango si es necesario
- **boolean**: true/false, mostrar como switch
- **json**: JSON válido, mostrar preview formateado y editor con validación

### Validación de Valores
- Validar en tiempo real mientras el usuario escribe
- Mostrar mensajes de error específicos por tipo
- Para JSON, validar sintaxis y mostrar errores claros
- No permitir modificar `key` ni `type` (son inmutables)

### Traducciones
- `description` siempre es traducible
- `value` solo es traducible para tipo `string`
- Mostrar tabs por idioma en el formulario de edición
- Guardar traducciones al actualizar la configuración

### Registro de Usuario
- Registrar `updated_by` automáticamente al actualizar
- Mostrar usuario que actualizó en el listado
- Mostrar fecha de última actualización

### Diseño y UX
- Usar Flux UI components para mantener consistencia
- Diseño responsive (móvil, tablet, desktop)
- Agrupar configuraciones por grupos visualmente
- Loading states en todas las acciones
- Feedback visual en validaciones
- Preview de JSON formateado
- Tooltips explicativos

### Autorización
- Usar `SettingPolicy` para todas las acciones
- Verificar permisos en cada método
- Solo admin/super-admin puede editar configuraciones
- Opcional: solo super-admin puede crear/eliminar configuraciones

### Grupos de Configuración
- **general**: Configuración general de la aplicación
- **email**: Configuración de correo electrónico
- **rgpd**: Configuración relacionada con RGPD
- **media**: Configuración de multimedia
- **seo**: Configuración SEO

---

## 🎨 Componentes Reutilizables

Se pueden reutilizar los siguientes componentes existentes:
- `x-ui.card` - Tarjetas contenedoras
- `x-ui.breadcrumbs` - Breadcrumbs de navegación
- `x-ui.search-input` - Campo de búsqueda
- `x-ui.empty-state` - Estado vacío
- `flux:button` - Botones con variantes
- `flux:field` - Campos de formulario
- `flux:input` - Inputs
- `flux:textarea` - Textareas
- `flux:label` - Etiquetas
- `flux:badge` - Badges para estados y tipos
- `flux:switch` - Switch para booleanos
- `flux:select` - Select para grupos
- `flux:tabs` - Tabs para traducciones

---

## ✅ Checklist Final

Antes de considerar completado el paso 3.5.12, verificar:

- [ ] Trait Translatable implementado en modelo Setting
- [ ] FormRequest creado con validación por tipo
- [ ] SettingPolicy creado con todos los métodos necesarios
- [ ] Componente Index funcionando con agrupación por grupos
- [ ] Componente Edit funcionando con validación por tipo
- [ ] Validación de valores según tipo implementada
- [ ] Formateo de valores en Index implementado
- [ ] Gestión de traducciones implementada
- [ ] Registro de usuario que actualiza implementado
- [ ] Rutas configuradas correctamente
- [ ] Navegación actualizada
- [ ] Traducciones añadidas
- [ ] Tests completos y pasando
- [ ] Código formateado con Pint
- [ ] Sin errores de linter
- [ ] Diseño responsive verificado
- [ ] Autorización verificada en todas las acciones
- [ ] Preview de JSON funcionando
- [ ] Validación en tiempo real funcionando

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Plan detallado completado - Listo para implementación
