# Plan de Trabajo: Paso 3.8.1 - Tests de Form Requests

## Objetivo
Alcanzar **100% de cobertura** en todos los Form Requests de la aplicación.

## Estado Actual de Cobertura

### Resumen General
- **Cobertura Total**: 69.40% (744/1072 líneas)
- **Funciones/Métodos**: 37.89% (36/95)
- **Clases**: 0.00% (0/30)
- **Total de Form Requests**: 30

### Análisis por Form Request

#### 🔴 Cobertura 0% (Crítico - 4 Form Requests)
1. **PublishCallRequest** (0/10 líneas)
   - Métodos: `authorize()`, `rules()`, `messages()`
   - **Prioridad**: ALTA - Form Request funcional importante

2. **UpdateAcademicYearRequest** (0/24 líneas)
   - Métodos: `authorize()`, `rules()`, `messages()`
   - **Prioridad**: ALTA - CRUD completo

3. **UpdateProgramRequest** (0/25 líneas)
   - Métodos: `authorize()`, `rules()`, `messages()`
   - **Prioridad**: ALTA - CRUD completo

4. **UpdateSettingRequest** (0/63 líneas)
   - Métodos: `authorize()`, `rules()`, `prepareForValidation()`, `messages()`, `attributes()`
   - **Prioridad**: ALTA - Lógica compleja con match expressions y preparación de datos

#### 🟠 Cobertura <50% (Baja - 7 Form Requests)
5. **StoreNewsTagRequest** (28.57% - 4/14 líneas)
   - **Prioridad**: MEDIA

6. **StoreAcademicYearRequest** (33.33% - 6/18 líneas)
   - **Prioridad**: ALTA

7. **UpdateNewsTagRequest** (30.00% - 6/20 líneas)
   - **Prioridad**: MEDIA

8. **AssignRoleRequest** (42.86% - 6/14 líneas)
   - **Prioridad**: MEDIA

9. **StoreNewsPostRequest** (44.68% - 21/47 líneas)
   - **Prioridad**: ALTA - Form Request complejo con muchos campos

10. **UpdateNewsPostRequest** (43.40% - 23/53 líneas)
    - **Prioridad**: ALTA - Form Request complejo con muchos campos

11. **StoreProgramRequest** (47.37% - 9/19 líneas)
    - **Prioridad**: ALTA

#### 🟡 Cobertura 50-90% (Media - 8 Form Requests)
12. **UpdateCallRequest** (51.79% - 29/56 líneas)
    - **Prioridad**: MEDIA

13. **UpdateCallPhaseRequest** (83.33% - 55/66 líneas)
    - **Prioridad**: BAJA - Casi completo

14. **StoreResolutionRequest** (86.05% - 37/43 líneas)
    - **Prioridad**: BAJA - Casi completo

15. **UpdateDocumentCategoryRequest** (79.17% - 19/24 líneas)
    - **Prioridad**: BAJA

16. **UpdateDocumentRequest** (87.50% - 35/40 líneas)
    - **Prioridad**: BAJA

17. **UpdateResolutionRequest** (79.59% - 39/49 líneas)
    - **Prioridad**: BAJA

18. **UpdateRoleRequest** (81.82% - 27/33 líneas)
    - **Prioridad**: BAJA

19. **UpdateTranslationRequest** (78.12% - 25/32 líneas)
    - **Prioridad**: BAJA

20. **UpdateUserRequest** (79.17% - 19/24 líneas)
    - **Prioridad**: BAJA

#### 🟢 Cobertura >90% (Alta - 11 Form Requests)
21. **StoreCallPhaseRequest** (98.25% - 56/57 líneas)
    - **Faltan**: 1 línea (probablemente un caso edge)

22. **StoreCallRequest** (98.00% - 49/50 líneas)
    - **Faltan**: 1 línea (probablemente un caso edge)

23. **StoreDocumentCategoryRequest** (94.44% - 17/18 líneas)
    - **Faltan**: 1 línea

24. **StoreDocumentRequest** (97.06% - 33/34 líneas)
    - **Faltan**: 1 línea

25. **StoreErasmusEventRequest** (97.92% - 47/48 líneas)
    - **Faltan**: 1 línea

26. **StoreNewsletterSubscriptionRequest** (95.65% - 22/23 líneas)
    - **Faltan**: 1 línea

27. **StoreResolutionRequest** (86.05% - 37/43 líneas)
    - **Faltan**: 6 líneas

28. **StoreRoleRequest** (94.12% - 16/17 líneas)
    - **Faltan**: 1 línea

29. **StoreTranslationRequest** (97.37% - 74/76 líneas)
    - **Faltan**: 2 líneas

30. **StoreUserRequest** (95.83% - 23/24 líneas)
    - **Faltan**: 1 línea

31. **UpdateErasmusEventRequest** (92.16% - 47/51 líneas)
    - **Faltan**: 4 líneas

---

## Plan de Trabajo Detallado

### Fase 1: Form Requests con 0% de Cobertura (Prioridad CRÍTICA)

#### 1.1. PublishCallRequest
**Archivo**: `tests/Feature/Http/Requests/PublishCallRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización:
  - Usuario autorizado puede publicar convocatoria
  - Usuario no autorizado no puede publicar
  - Usuario sin permisos no puede publicar
  - Route parameter no es instancia de Call retorna false
- ✅ Test de validación:
  - `published_at` es opcional
  - `published_at` debe ser fecha válida si se proporciona
  - `published_at` puede ser null
- ✅ Test de mensajes personalizados:
  - Verificar mensaje de error para `published_at.date`

**Líneas a cubrir**: 10 líneas (authorize: 4, rules: 3, messages: 3)

---

#### 1.2. UpdateAcademicYearRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateAcademicYearRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización:
  - Usuario autorizado puede actualizar año académico
  - Usuario no autorizado no puede actualizar
  - Route parameter no es instancia de AcademicYear retorna false
- ✅ Test de validación:
  - `year` es requerido, string, formato YYYY-YYYY, único (ignorando actual)
  - `start_date` es requerido, fecha válida
  - `end_date` es requerido, fecha válida, posterior a start_date
  - `is_current` es opcional, boolean
  - Validación de unicidad de `year` ignorando el registro actual
  - Manejo de route model binding (instancia vs ID)
- ✅ Test de mensajes personalizados:
  - Todos los mensajes de error personalizados

**Líneas a cubrir**: 24 líneas (authorize: 4, rules: 9, messages: 11)

---

#### 1.3. UpdateProgramRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateProgramRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización:
  - Usuario autorizado puede actualizar programa
  - Usuario no autorizado no puede actualizar
  - Route parameter no es instancia de Program retorna false
- ✅ Test de validación:
  - `code` es requerido, string, max 255, único (ignorando actual)
  - `name` es requerido, string, max 255
  - `slug` es opcional, string, max 255, único (ignorando actual)
  - `description` es opcional, string
  - `is_active` es opcional, boolean
  - `order` es opcional, integer
  - `image` es opcional, imagen, mimes válidos, max 5MB
  - Validación de unicidad de `code` y `slug` ignorando el registro actual
  - Manejo de route model binding (instancia vs ID)
- ✅ Test de mensajes personalizados:
  - Todos los mensajes de error personalizados

**Líneas a cubrir**: 25 líneas (authorize: 4, rules: 13, messages: 8)

---

#### 1.4. UpdateSettingRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateSettingRequestTest.php` (ya existe pero con 0% cobertura)

**Tests a crear/mejorar**:
- ✅ Test de autorización:
  - Usuario autorizado puede actualizar configuración
  - Usuario no autorizado no puede actualizar
  - Route parameter no es instancia de Setting retorna false
- ✅ Test de validación según tipo:
  - **Tipo `integer`**: `value` requerido, integer
  - **Tipo `boolean`**: `value` requerido, boolean
  - **Tipo `json`**: `value` requerido, json válido
  - **Tipo `string` (default)**: `value` requerido, string
  - `description` es opcional, string
  - Route parameter no es instancia de Setting retorna array vacío
- ✅ Test de `prepareForValidation()`:
  - Conversión de boolean string a boolean real ('1', '0', 'true', 'false')
  - Validación y conversión de JSON (string a JSON, array/objeto a JSON)
  - Manejo de errores JSON
  - Route parameter no es instancia de Setting retorna early
- ✅ Test de mensajes personalizados:
  - Mensajes según tipo de configuración (integer, boolean, json, string)
  - Route parameter no es instancia de Setting retorna array vacío
  - Verificar que `$typeLabel` se calcula correctamente (hay duplicación en código)
- ✅ Test de `attributes()`:
  - Verificar nombres de atributos personalizados

**Líneas a cubrir**: 63 líneas (authorize: 4, rules: 14, prepareForValidation: 18, messages: 23, attributes: 4)

**Nota**: Este Form Request tiene lógica compleja con `match` expressions y preparación de datos. Requiere tests exhaustivos.

---

### Fase 2: Form Requests con <50% de Cobertura (Prioridad ALTA)

#### 2.1. StoreAcademicYearRequest
**Archivo**: `tests/Feature/Http/Requests/StoreAcademicYearRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización (si aplica)
- ✅ Test de validación completa:
  - Todos los campos requeridos
  - Validación de formato de `year` (regex YYYY-YYYY)
  - Validación de unicidad de `year`
  - Validación de fechas (start_date, end_date, after:start_date)
  - Validación de `is_current` como boolean
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 12 líneas adicionales (actualmente 6/18)

---

#### 2.2. StoreProgramRequest
**Archivo**: `tests/Feature/Http/Requests/StoreProgramRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización
- ✅ Test de validación completa:
  - Todos los campos requeridos
  - Validación de unicidad de `code` y `slug`
  - Validación de `image` (mimes, max size)
  - Validación de `is_active` y `order`
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 10 líneas adicionales (actualmente 9/19)

---

#### 2.3. StoreNewsPostRequest
**Archivo**: `tests/Feature/Http/Requests/StoreNewsPostRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización
- ✅ Test de validación completa:
  - Campos requeridos: `academic_year_id`, `title`, `content`
  - Campos opcionales: `program_id`, `slug`, `excerpt`, etc.
  - Validación de `exists` para relaciones
  - Validación de `enum` para `mobility_type`, `mobility_category`, `status`
  - Validación de `slug` único
  - Validación de `featured_image` (image, mimes, max)
  - Validación de `tags` (array, exists)
- ✅ Test de mensajes personalizados (muchos mensajes)

**Líneas a cubrir**: 26 líneas adicionales (actualmente 21/47)

---

#### 2.4. UpdateNewsPostRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateNewsPostRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización
- ✅ Test de validación completa:
  - Similar a StoreNewsPostRequest pero con `ignore` en unique
  - Manejo de route model binding
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 30 líneas adicionales (actualmente 23/53)

---

#### 2.5. StoreNewsTagRequest
**Archivo**: `tests/Feature/Http/Requests/StoreNewsTagRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización
- ✅ Test de validación:
  - `name` requerido, string, max 255, único
  - `slug` opcional, string, max 255, único
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 10 líneas adicionales (actualmente 4/14)

---

#### 2.6. UpdateNewsTagRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateNewsTagRequestTest.php`

**Tests a crear**:
- ✅ Test de autorización
- ✅ Test de validación:
  - Similar a StoreNewsTagRequest pero con `ignore` en unique
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 14 líneas adicionales (actualmente 6/20)

---

#### 2.7. AssignRoleRequest
**Archivo**: `tests/Feature/Http/Requests/AssignRoleRequestTest.php` (ya existe pero con 42.86% cobertura)

**Tests a mejorar**:
- ✅ Test de autorización completo
- ✅ Test de validación:
  - `roles` requerido, array
  - `roles.*` debe ser uno de los roles válidos del sistema
  - Validación de roles inválidos
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 8 líneas adicionales (actualmente 6/14)

---

### Fase 3: Form Requests con 50-90% de Cobertura (Prioridad MEDIA)

#### 3.1. UpdateCallRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateCallRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas en cobertura HTML
- ✅ Agregar tests para casos edge faltantes
- ✅ Verificar cobertura de `authorize()` completo
- ✅ Verificar cobertura de `messages()` completo

**Líneas a cubrir**: 27 líneas adicionales (actualmente 29/56)

---

#### 3.2. UpdateCallPhaseRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateCallPhaseRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (11 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 11 líneas adicionales (actualmente 55/66)

---

#### 3.3. StoreResolutionRequest
**Archivo**: `tests/Feature/Http/Requests/StoreResolutionRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (6 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 6 líneas adicionales (actualmente 37/43)

---

#### 3.4. UpdateResolutionRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateResolutionRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (10 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 10 líneas adicionales (actualmente 39/49)

---

#### 3.5. UpdateDocumentCategoryRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateDocumentCategoryRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (5 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 5 líneas adicionales (actualmente 19/24)

---

#### 3.6. UpdateDocumentRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateDocumentRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (5 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 5 líneas adicionales (actualmente 35/40)

---

#### 3.7. UpdateRoleRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateRoleRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (6 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 6 líneas adicionales (actualmente 27/33)

---

#### 3.8. UpdateTranslationRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateTranslationRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (7 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 7 líneas adicionales (actualmente 25/32)

---

#### 3.9. UpdateUserRequest
**Archivo**: `tests/Feature/Http/Requests/UpdateUserRequestTest.php` (ya existe)

**Tests a mejorar**:
- ✅ Revisar líneas no cubiertas (5 líneas faltantes)
- ✅ Agregar tests para casos edge

**Líneas a cubrir**: 5 líneas adicionales (actualmente 19/24)

---

### Fase 4: Form Requests con >90% de Cobertura (Prioridad BAJA - Completar al 100%)

#### 4.1. StoreCallPhaseRequest (98.25%)
**Archivo**: `tests/Feature/Http/Requests/StoreCallPhaseRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta en cobertura HTML
- ✅ Agregar test específico para cubrirla

**Líneas a cubrir**: 1 línea adicional

---

#### 4.2. StoreCallRequest (98.00%)
**Archivo**: `tests/Feature/Http/Requests/StoreCallRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.3. StoreDocumentCategoryRequest (94.44%)
**Archivo**: `tests/Feature/Http/Requests/StoreDocumentCategoryRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.4. StoreDocumentRequest (97.06%)
**Archivo**: `tests/Feature/Http/Requests/StoreDocumentRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.5. StoreErasmusEventRequest (97.92%)
**Archivo**: `tests/Feature/Http/Requests/StoreErasmusEventRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.6. StoreNewsletterSubscriptionRequest (95.65%)
**Archivo**: `tests/Feature/Http/Requests/StoreNewsletterSubscriptionRequestTest.php` (necesita crearse)

**Tests a crear**:
- ✅ Test de autorización (si aplica)
- ✅ Test de validación completa
- ✅ Test de mensajes personalizados

**Líneas a cubrir**: 1 línea adicional (actualmente 22/23)

---

#### 4.7. StoreRoleRequest (94.12%)
**Archivo**: `tests/Feature/Http/Requests/StoreRoleRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.8. StoreTranslationRequest (97.37%)
**Archivo**: `tests/Feature/Http/Requests/StoreTranslationRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar las 2 líneas no cubiertas
- ✅ Agregar tests específicos

**Líneas a cubrir**: 2 líneas adicionales

---

#### 4.9. StoreUserRequest (95.83%)
**Archivo**: `tests/Feature/Http/Requests/StoreUserRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar la línea no cubierta
- ✅ Agregar test específico

**Líneas a cubrir**: 1 línea adicional

---

#### 4.10. UpdateErasmusEventRequest (92.16%)
**Archivo**: `tests/Feature/Http/Requests/UpdateErasmusEventRequestTest.php` (ya existe)

**Tests a agregar**:
- ✅ Identificar las 4 líneas no cubiertas
- ✅ Agregar tests específicos

**Líneas a cubrir**: 4 líneas adicionales

---

## Estrategia de Implementación

### Patrón de Tests

Todos los tests seguirán el patrón establecido en `StoreCallRequestTest.php`:

```php
<?php

use App\Http\Requests\{FormRequest};
use App\Models\{Model};
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup de permisos y roles si es necesario
});

describe('{FormRequest} - Authorization', function () {
    it('authorizes user with permission', function () {
        // Test de autorización
    });
    
    it('denies user without permission', function () {
        // Test de denegación
    });
});

describe('{FormRequest} - Validation Rules', function () {
    it('validates required fields', function () {
        // Test de campos requeridos
    });
    
    it('validates field types', function () {
        // Test de tipos de datos
    });
    
    // Más tests según necesidad
});

describe('{FormRequest} - Custom Messages', function () {
    it('returns custom error messages', function () {
        // Test de mensajes personalizados
    });
});
```

### Estructura de Tests por Método

#### 1. Tests de `authorize()`
- ✅ Usuario autenticado con permisos → `true`
- ✅ Usuario autenticado sin permisos → `false`
- ✅ Usuario no autenticado → `false`
- ✅ Route parameter no es instancia del modelo → `false`
- ✅ Route parameter es null → `false`

#### 2. Tests de `rules()`
- ✅ Campos requeridos
- ✅ Tipos de datos (string, integer, boolean, date, etc.)
- ✅ Validaciones específicas (max, min, regex, enum, exists, unique)
- ✅ Validaciones condicionales (nullable, sometimes)
- ✅ Validaciones con `ignore()` en Update requests
- ✅ Manejo de route model binding (instancia vs ID)

#### 3. Tests de `messages()`
- ✅ Verificar que todos los mensajes personalizados existen
- ✅ Verificar que los mensajes son traducibles (usando `__()`)
- ✅ Verificar mensajes según contexto (si aplica)

#### 4. Tests de `prepareForValidation()` (si existe)
- ✅ Conversión de tipos de datos
- ✅ Transformación de valores
- ✅ Manejo de casos edge

#### 5. Tests de `attributes()` (si existe)
- ✅ Verificar nombres de atributos personalizados

---

## Orden de Implementación Recomendado

### Sprint 1: Form Requests Críticos (0% cobertura)
1. ✅ **PublishCallRequest** - Simple, buen punto de partida
2. ✅ **UpdateAcademicYearRequest** - Complejidad media
3. ✅ **UpdateProgramRequest** - Complejidad media
4. ✅ **UpdateSettingRequest** - Complejidad alta, requiere más tiempo

### Sprint 2: Form Requests con Baja Cobertura (<50%)
5. ✅ **StoreAcademicYearRequest** - Completar cobertura
6. ✅ **StoreProgramRequest** - Completar cobertura
7. ✅ **StoreNewsPostRequest** - Complejo, muchos campos
8. ✅ **UpdateNewsPostRequest** - Complejo, muchos campos
9. ✅ **StoreNewsTagRequest** - Simple
10. ✅ **UpdateNewsTagRequest** - Simple
11. ✅ **AssignRoleRequest** - Mejorar tests existentes

### Sprint 3: Form Requests con Media Cobertura (50-90%)
12. ✅ **UpdateCallRequest** - Revisar y completar
13. ✅ **UpdateCallPhaseRequest** - Completar casos edge
14. ✅ **StoreResolutionRequest** - Completar casos edge
15. ✅ **UpdateResolutionRequest** - Completar casos edge
16. ✅ **UpdateDocumentCategoryRequest** - Completar casos edge
17. ✅ **UpdateDocumentRequest** - Completar casos edge
18. ✅ **UpdateRoleRequest** - Completar casos edge
19. ✅ **UpdateTranslationRequest** - Completar casos edge
20. ✅ **UpdateUserRequest** - Completar casos edge

### Sprint 4: Form Requests con Alta Cobertura (>90%)
21. ✅ **StoreCallPhaseRequest** - Identificar y cubrir línea faltante
22. ✅ **StoreCallRequest** - Identificar y cubrir línea faltante
23. ✅ **StoreDocumentCategoryRequest** - Identificar y cubrir línea faltante
24. ✅ **StoreDocumentRequest** - Identificar y cubrir línea faltante
25. ✅ **StoreErasmusEventRequest** - Identificar y cubrir línea faltante
26. ✅ **StoreNewsletterSubscriptionRequest** - Crear test completo
27. ✅ **StoreRoleRequest** - Identificar y cubrir línea faltante
28. ✅ **StoreTranslationRequest** - Identificar y cubrir 2 líneas faltantes
29. ✅ **StoreUserRequest** - Identificar y cubrir línea faltante
30. ✅ **UpdateErasmusEventRequest** - Identificar y cubrir 4 líneas faltantes

---

## Métricas de Éxito

### Objetivo Principal
- ✅ **100% de cobertura en líneas** para todos los Form Requests
- ✅ **100% de cobertura en métodos** para todos los Form Requests
- ✅ **100% de cobertura en clases** para todos los Form Requests

### Objetivos Secundarios
- ✅ Todos los tests pasan
- ✅ Código de tests bien estructurado y mantenible
- ✅ Tests siguen el patrón establecido
- ✅ Tests cubren casos edge y validaciones complejas

---

## Notas Importantes

### 1. Identificación de Líneas No Cubiertas
Para identificar líneas específicas no cubiertas:
1. Abrir el archivo HTML de cobertura: `tests/coverage/Http/Requests/{FormRequest}.php.html`
2. Buscar líneas con fondo rojo claro (no cubiertas)
3. Analizar el contexto de la línea
4. Crear test específico para cubrirla

### 2. Route Model Binding
Muchos Form Requests usan route model binding. Los tests deben cubrir:
- ✅ Cuando el route parameter es una instancia del modelo
- ✅ Cuando el route parameter es un ID (caso menos común pero posible)

### 3. Validaciones Condicionales
Algunos Form Requests tienen validaciones condicionales basadas en:
- ✅ Tipo de configuración (UpdateSettingRequest)
- ✅ Estado del modelo
- ✅ Permisos del usuario

### 4. Preparación de Datos
Algunos Form Requests preparan datos antes de la validación:
- ✅ UpdateSettingRequest: convierte strings a boolean, valida JSON
- ✅ Otros pueden tener lógica similar

### 5. Mensajes Personalizados
Todos los mensajes deben:
- ✅ Estar traducidos usando `__()`
- ✅ Ser específicos y descriptivos
- ✅ Cubrir todos los casos de error

---

## Recursos y Referencias

### Archivos de Referencia
- ✅ `tests/Feature/Http/Requests/StoreCallRequestTest.php` - Patrón de tests
- ✅ `app/Http/Requests/` - Form Requests a testear
- ✅ `tests/coverage/Http/Requests/index.html` - Estado de cobertura
- ✅ `docs/form-requests.md` - Documentación de Form Requests

### Comandos Útiles
```bash
# Ejecutar tests de Form Requests
php artisan test --filter=Request

# Generar cobertura HTML
php artisan test --coverage-html=tests/coverage

# Ejecutar test específico
php artisan test tests/Feature/Http/Requests/PublishCallRequestTest.php
```

---

## Estimación de Tiempo

### Por Form Request
- **Form Request simple** (<20 líneas): 30-60 minutos
- **Form Request medio** (20-40 líneas): 1-2 horas
- **Form Request complejo** (>40 líneas): 2-4 horas

### Total Estimado
- **Fase 1** (0% cobertura): 8-12 horas
- **Fase 2** (<50% cobertura): 10-16 horas
- **Fase 3** (50-90% cobertura): 8-12 horas
- **Fase 4** (>90% cobertura): 4-6 horas

**Total**: 30-46 horas de desarrollo

---

## Checklist Final

Antes de considerar completado el paso 3.8.1:

- [ ] Todos los Form Requests tienen tests
- [ ] Todos los tests pasan
- [ ] Cobertura de líneas: 100%
- [ ] Cobertura de métodos: 100%
- [ ] Cobertura de clases: 100%
- [ ] Tests siguen el patrón establecido
- [ ] Tests cubren casos edge
- [ ] Documentación actualizada si es necesario

---

**Fecha de Creación**: Enero 2026  
**Estado**: 📋 Plan completado - Listo para implementación