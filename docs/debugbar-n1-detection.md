# Guía de Detección de N+1 con Laravel Debugbar

## Introducción

Laravel Debugbar es una herramienta de desarrollo que permite analizar el rendimiento de la aplicación, incluyendo la detección de problemas N+1 en consultas de base de datos.

## Configuración

El Debugbar está configurado para:
- Mostrar **todas las consultas** (no solo las lentas)
- Resaltar consultas que superen **100ms**
- Mostrar **hints** para problemas comunes (incluyendo N+1)
- Incluir el **backtrace** para rastrear el origen de cada consulta
- Mostrar consultas en el **timeline**
- Permitir **EXPLAIN** en consultas

## Cómo Detectar Problemas N+1

### 1. Abrir la Aplicación en el Navegador

1. Navegar a cualquier página de la aplicación (ej: `https://erasmus25.test`)
2. El Debugbar aparecerá en la parte inferior de la pantalla

### 2. Revisar la Pestaña "Queries"

- **Número de consultas**: Observar el número total de consultas
- **Consultas duplicadas**: Buscar consultas similares que se repiten múltiples veces
- **Patrón N+1**: Si ves una consulta seguida de N consultas similares con diferentes IDs, es un problema N+1

**Ejemplo de N+1:**
```sql
-- 1 consulta para obtener posts
SELECT * FROM posts;

-- N consultas para obtener el autor de cada post (PROBLEMA!)
SELECT * FROM users WHERE id = 1;
SELECT * FROM users WHERE id = 2;
SELECT * FROM users WHERE id = 3;
...
```

**Solución con Eager Loading:**
```sql
-- 1 consulta para posts
SELECT * FROM posts;

-- 1 consulta para todos los autores (CORRECTO!)
SELECT * FROM users WHERE id IN (1, 2, 3, ...);
```

### 3. Revisar la Pestaña "Models"

Muestra qué modelos Eloquent se están cargando y cuántas instancias.

### 4. Revisar la Pestaña "Timeline"

Visualiza el orden de ejecución de consultas y vistas, ayudando a identificar dónde ocurren los problemas.

## Páginas a Auditar (Prioridad)

### Alta Prioridad (Páginas de listados)

| Página | Ruta | Qué Buscar |
|--------|------|------------|
| Calls Index (Admin) | `/admin/convocatorias` | Relaciones: program, academicYear, creator, updater |
| Calls Index (Public) | `/convocatorias` | Relaciones: program, academicYear |
| News Index (Admin) | `/admin/noticias` | Relaciones: program, academicYear, author, tags |
| Documents Index | `/admin/documentos` | Relaciones: category, program, academicYear, creator |
| Users Index | `/admin/usuarios` | Relaciones: roles, permissions |
| Events Index | `/admin/eventos` | Relaciones: program, call, organizer |
| Dashboard | `/admin` | Múltiples consultas de estadísticas |

### Media Prioridad (Páginas de detalle)

| Página | Ruta | Qué Buscar |
|--------|------|------------|
| Call Show (Public) | `/convocatorias/{slug}` | Relaciones: program, academicYear, phases, resolutions |
| Call Show (Admin) | `/admin/convocatorias/{id}` | Todas las relaciones |
| News Show | `/noticias/{slug}` | Relaciones: program, author, tags, media |
| Home Page | `/` | Todas las consultas de la página principal |

## Métricas Objetivo

Para cada página auditada, registrar:

| Métrica | Objetivo |
|---------|----------|
| Total de consultas | < 15 para listados, < 25 para páginas complejas |
| Consultas duplicadas | 0 |
| Tiempo total de DB | < 100ms |
| Consultas > 100ms | 0 |

## Formato de Reporte de Auditoría

```markdown
### Página: [Nombre]
**URL**: [url]
**Componente**: [App\Livewire\...]

#### Métricas
- Total consultas: X
- Tiempo DB: Xms
- Consultas > 100ms: X

#### Problemas Encontrados
1. N+1 en relación `X` - Línea XX del componente
2. ...

#### Solución Propuesta
- Añadir `->with(['relacion'])` en la consulta
```

## Comandos Útiles

```bash
# Ver consultas en tiempo real con Laravel Pail
php artisan pail --filter="query"

# Ejecutar tests con detección de N+1 (si se implementa)
php artisan test --filter=QueryOptimization
```

## Próximos Pasos

1. Auditar cada página de la lista de prioridad alta
2. Documentar problemas encontrados
3. Implementar soluciones (Fase 2 del plan 3.9.1)
4. Re-auditar para verificar mejoras

---

*Documento creado: 2026-01-20*
*Parte del Paso 3.9.1 - Optimización de Consultas*
