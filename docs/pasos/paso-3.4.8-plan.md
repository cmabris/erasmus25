# Paso 14: Internacionalización (i18n) - Paso 3.4.8 de la Planificación

Este documento contiene el plan de desarrollo y los pasos a seguir para implementar el sistema completo de internacionalización (i18n) de la aplicación "Erasmus+ Centro (Murcia)". Este paso corresponde al paso 3.4.8 de la planificación general.

---

## Objetivo

Implementar un sistema completo de internacionalización que permita:
1. Cambiar el idioma de la aplicación desde el frontend
2. Traducir textos estáticos mediante archivos de idioma
3. Traducir contenido dinámico desde la tabla `translations`
4. Facilitar la adición de nuevos idiomas en el futuro
5. Mantener el idioma seleccionado durante la sesión

---

## Análisis del Estado Actual

### ✅ Ya Implementado

- Modelo `Language` con idiomas ES y EN
- Modelo `Translation` para traducciones polimórficas
- Archivos de traducción en `lang/es` y `lang/en` (auth, pagination, passwords, validation)
- Uso de funciones `__()` en vistas para textos estáticos
- Atributo `lang` en HTML usando `app()->getLocale()`
- Seeder `LanguagesSeeder` con ES (default) y EN

### ⏳ Pendiente

- Componente para cambiar idioma en frontend
- Middleware para detectar y establecer idioma desde sesión/cookie
- Helper/trait para traducciones dinámicas desde tabla `translations`
- Archivos de traducción adicionales para textos comunes
- Integración del selector de idioma en navegación
- Tests del sistema de internacionalización

---

## Plan de Desarrollo - Fases

### **Fase 1: Middleware y Configuración de Locale**

**Objetivo**: Establecer el sistema base para detectar y mantener el idioma seleccionado.

**Tareas**:
1. Crear middleware `SetLocale` para detectar idioma desde:
   - Sesión (prioridad alta)
   - Cookie (prioridad media)
   - Header Accept-Language (prioridad baja)
   - Idioma por defecto de la aplicación
2. Registrar middleware en `bootstrap/app.php`
3. Crear helper `app/Support/helpers.php` con funciones:
   - `getCurrentLanguage()` - Obtener idioma actual
   - `setLanguage($code)` - Establecer idioma
   - `getAvailableLanguages()` - Listar idiomas disponibles
4. Actualizar `config/app.php` para usar idioma desde base de datos

**Archivos a crear/modificar**:
- `app/Http/Middleware/SetLocale.php`
- `app/Support/helpers.php`
- `bootstrap/app.php` (registrar middleware)
- `config/app.php` (ajustar configuración)

---

### **Fase 2: Trait para Traducciones Dinámicas**

**Objetivo**: Facilitar el uso de traducciones dinámicas desde la tabla `translations`.

**Tareas**:
1. Crear trait `app/Models/Concerns/Translatable.php` con métodos:
   - `translate($field, $locale = null)` - Obtener traducción de un campo
   - `getTranslatedAttribute($field)` - Accessor para traducciones
   - `setTranslation($field, $locale, $value)` - Establecer traducción
   - `translations($locale = null)` - Obtener todas las traducciones
   - `hasTranslation($field, $locale = null)` - Verificar si existe traducción
2. Crear helper global `trans_model($model, $field, $locale = null)` para usar en vistas
3. Crear helper global `trans_route($route, $params = [])` para rutas con locale

**Archivos a crear**:
- `app/Models/Concerns/Translatable.php`
- `app/Support/helpers.php` (ampliar con helpers de traducción)

**Modelos que usarán el trait** (futuro):
- `Program`, `Call`, `NewsPost`, `Document`, `ErasmusEvent`, etc.

---

### **Fase 3: Componente Livewire Language Switcher**

**Objetivo**: Crear un componente moderno y reutilizable para cambiar idioma.

**Tareas**:
1. Crear componente Livewire `app/Livewire/Language/Switcher.php`:
   - Método `switchLanguage($code)` - Cambiar idioma
   - Propiedad `currentLanguage` - Idioma actual
   - Propiedad `availableLanguages` - Lista de idiomas disponibles
   - Guardar idioma en sesión y cookie
   - Redirigir a la misma página después del cambio
2. Crear vista `resources/views/livewire/language/switcher.blade.php`:
   - Diseño moderno con Flux UI
   - Dropdown con banderas/iconos de idiomas
   - Indicador visual del idioma actual
   - Responsive (mobile-friendly)
   - Variantes: dropdown, buttons, select

**Diseño propuesto**:
- Desktop: Dropdown con icono de globo/idioma
- Mobile: Botón con modal o dropdown
- Mostrar nombre del idioma y código (ej: "Español (ES)")
- Indicar idioma actual con checkmark o highlight

**Archivos a crear**:
- `app/Livewire/Language/Switcher.php`
- `resources/views/livewire/language/switcher.blade.php`

---

### **Fase 4: Archivos de Traducción Comunes**

**Objetivo**: Crear archivos de traducción para textos comunes de la aplicación.

**Tareas**:
1. Crear `lang/es/common.php` con traducciones comunes:
   - Navegación (Inicio, Programas, Convocatorias, etc.)
   - Botones (Ver más, Leer más, Suscribirse, etc.)
   - Mensajes (No hay datos, Cargando, etc.)
   - Etiquetas de formularios
   - Mensajes de éxito/error
2. Crear `lang/en/common.php` con traducciones en inglés
3. Actualizar vistas existentes para usar `__('common.key')` donde corresponda

**Archivos a crear**:
- `lang/es/common.php`
- `lang/en/common.php`

**Archivos a modificar**:
- Vistas públicas existentes (Home, Programs, Calls, News, etc.)
- Componentes de navegación

---

### **Fase 5: Integración en Navegación**

**Objetivo**: Integrar el selector de idioma en las navegaciones pública y de administración.

**Tareas**:
1. Integrar `Language\Switcher` en `components/nav/public-nav.blade.php`:
   - Posición: lado derecho, junto a enlaces de autenticación
   - Diseño consistente con el resto de la navegación
   - Responsive para móviles
2. Integrar `Language\Switcher` en `components/layouts/app/header.blade.php`:
   - Posición: header del panel de administración
   - Diseño consistente con el tema admin
3. Actualizar `components/layouts/public.blade.php`:
   - Asegurar que el locale se establece correctamente
   - Actualizar atributo `lang` dinámicamente

**Archivos a modificar**:
- `resources/views/components/nav/public-nav.blade.php`
- `resources/views/components/layouts/app/header.blade.php`
- `resources/views/components/layouts/public.blade.php`

---

### **Fase 6: Helper para Traducciones en Vistas**

**Objetivo**: Facilitar el uso de traducciones dinámicas en vistas Blade.

**Tareas**:
1. Crear helper `trans_model()` para usar en vistas:
   ```php
   trans_model($model, 'title', 'es') // Obtiene traducción del campo 'title'
   ```
2. Crear helper `trans_route()` para rutas con locale:
   ```php
   trans_route('programas.show', ['program' => $program]) // Mantiene locale en URL
   ```
3. Crear directiva Blade `@trans` para simplificar uso:
   ```blade
   @trans($program, 'title')
   ```
4. Documentar uso de helpers en código

**Archivos a crear/modificar**:
- `app/Support/helpers.php` (ampliar)
- `app/Providers/AppServiceProvider.php` (registrar helpers)
- `resources/views/...` (ejemplos de uso)

---

### **Fase 7: Actualización de Modelos para Traducciones**

**Objetivo**: Preparar modelos para usar traducciones dinámicas.

**Tareas**:
1. Aplicar trait `Translatable` a modelos que necesiten traducciones:
   - `Program` (name, description)
   - `Call` (title, description)
   - `NewsPost` (title, content, excerpt)
   - `Document` (title, description)
   - `ErasmusEvent` (title, description)
   - `DocumentCategory` (name, description)
2. Crear accessors para campos traducibles:
   ```php
   public function getTitleAttribute($value) {
       return $this->translate('title') ?? $value;
   }
   ```
3. Actualizar vistas para usar traducciones cuando estén disponibles

**Archivos a modificar**:
- `app/Models/Program.php`
- `app/Models/Call.php`
- `app/Models/NewsPost.php`
- `app/Models/Document.php`
- `app/Models/ErasmusEvent.php`
- `app/Models/DocumentCategory.php`

**Nota**: Esta fase puede ser parcial, implementando solo lo esencial para demostrar el funcionamiento.

---

### **Fase 8: Tests del Sistema de Internacionalización**

**Objetivo**: Asegurar que el sistema de internacionalización funciona correctamente.

**Tareas**:
1. Crear `tests/Feature/Language/SwitcherTest.php`:
   - Test cambio de idioma
   - Test persistencia en sesión
   - Test persistencia en cookie
   - Test redirección después del cambio
   - Test idioma por defecto
2. Crear `tests/Feature/Middleware/SetLocaleTest.php`:
   - Test detección desde sesión
   - Test detección desde cookie
   - Test detección desde header
   - Test fallback a idioma por defecto
3. Crear `tests/Unit/Models/TranslatableTest.php`:
   - Test trait Translatable
   - Test métodos de traducción
   - Test accessors traducibles
4. Crear `tests/Feature/Helpers/TranslationHelpersTest.php`:
   - Test helper `trans_model()`
   - Test helper `trans_route()`
   - Test directiva Blade `@trans`

**Archivos a crear**:
- `tests/Feature/Language/SwitcherTest.php`
- `tests/Feature/Middleware/SetLocaleTest.php`
- `tests/Unit/Models/TranslatableTest.php`
- `tests/Feature/Helpers/TranslationHelpersTest.php`

---

### **Fase 9: Documentación**

**Objetivo**: Documentar el sistema de internacionalización para futuros desarrolladores.

**Tareas**:
1. Crear `docs/i18n-system.md` con:
   - Arquitectura del sistema
   - Cómo añadir nuevos idiomas
   - Cómo traducir textos estáticos
   - Cómo traducir contenido dinámico
   - Ejemplos de uso
   - Mejores prácticas
2. Actualizar `docs/README.md` con referencia a i18n
3. Actualizar `docs/planificacion_pasos.md` marcando paso 3.4.8 como completado

**Archivos a crear**:
- `docs/i18n-system.md`

**Archivos a modificar**:
- `docs/README.md`
- `docs/planificacion_pasos.md`

---

## Consideraciones de Diseño

### Componente Language Switcher

**Variantes propuestas**:
1. **Dropdown** (recomendado para desktop):
   - Botón con icono de globo/idioma
   - Dropdown con lista de idiomas
   - Indicador visual del idioma actual
2. **Buttons** (alternativa):
   - Botones pequeños con código de idioma (ES, EN)
   - Highlight del idioma actual
3. **Select** (móvil):
   - Select nativo para mejor UX en móviles

**Estilo visual**:
- Usar colores Erasmus+ (azul institucional)
- Iconos de Flux UI
- Transiciones suaves
- Dark mode compatible

### Persistencia del Idioma

**Estrategia**:
1. **Sesión** (prioridad alta): Para usuarios autenticados
2. **Cookie** (prioridad media): Para persistencia entre sesiones
3. **URL** (opcional): Para compartir enlaces en idioma específico
4. **Header Accept-Language** (fallback): Detección automática

---

## Estructura de Archivos Final

```
app/
├── Http/
│   └── Middleware/
│       └── SetLocale.php
├── Livewire/
│   └── Language/
│       └── Switcher.php
├── Models/
│   └── Concerns/
│       └── Translatable.php
└── Support/
    └── helpers.php

lang/
├── es/
│   ├── auth.php
│   ├── common.php (nuevo)
│   ├── pagination.php
│   ├── passwords.php
│   └── validation.php
└── en/
    ├── auth.php
    ├── common.php (nuevo)
    ├── pagination.php
    ├── passwords.php
    └── validation.php

resources/
└── views/
    ├── livewire/
    │   └── language/
    │       └── switcher.blade.php (nuevo)
    └── components/
        ├── nav/
        │   └── public-nav.blade.php (modificar)
        └── layouts/
            ├── app/
            │   └── header.blade.php (modificar)
            └── public.blade.php (modificar)

tests/
├── Feature/
│   ├── Language/
│   │   └── SwitcherTest.php (nuevo)
│   ├── Middleware/
│   │   └── SetLocaleTest.php (nuevo)
│   └── Helpers/
│       └── TranslationHelpersTest.php (nuevo)
└── Unit/
    └── Models/
        └── TranslatableTest.php (nuevo)

docs/
├── i18n-system.md (nuevo)
├── README.md (modificar)
└── planificacion_pasos.md (modificar)
```

---

## Próximos Pasos

Una vez aprobado este plan, comenzaremos con la **Fase 1: Middleware y Configuración de Locale**, que establece la base del sistema de internacionalización.

---

**Fecha de Creación**: Diciembre 2025  
**Estado**: 📋 Planificación completada - Pendiente de aprobación para comenzar implementación

