# Campo autocomplete de categorías en Canciones — 2026-07-23

## Contexto

La relación Canción ↔ Curso/Librería se da por la taxonomía core `category`
(los módulos del tema consultan canciones con `tax_query` sobre `category`;
Cursos y Librerías declaran su categoría con el campo ACF `cat`). Asignar
categorías a una canción hoy requiere el metabox nativo de checkboxes
(22 categorías). Se quiere un multi-select con autocomplete.

## Cambios

### 1. `acf-json/group_684cbc3c38408.json` (grupo "Cursos" → CPT `cursos` = Canciones)

Agregar como primer campo:

- `name: categorias`, tipo **Taxonomy** → `category`, apariencia **Multi Select**
  (select2 con búsqueda/autocomplete, selección múltiple en chips).
- `save_terms: 1` y `load_terms: 1` → el campo lee y escribe las categorías
  **reales** del post; las queries del front no cambian.
- `add_term: 1` → permite crear una categoría nueva desde el campo.
- `return_format: id`. Bump de `modified`.

### 2. `inc/canciones-admin.php` (nuevo) + require en `functions.php`

Ocultar el panel nativo "Categorías" del editor de bloques SOLO en `cursos`
(`removeEditorPanel('taxonomy-panel-category')`, store `core/editor` con
fallback a `core/edit-post`). Motivo: con dos UIs, un cambio en el panel
nativo sería pisado por el campo ACF al guardar. El quick-edit del listado
no se toca.

## Verificación

Canción de prueba (draft) creada por bootstrap + Playwright con admin temporal:

1. En el editor: aparece el campo con autocomplete, el panel nativo no.
2. Asignar 2 categorías escribiendo en el buscador, guardar.
3. Confirmar en BD que `wp_get_post_terms` del post devuelve esas categorías.
4. Borrar canción de prueba y usuario temporal. `php -l` y JSON válido.

## Fuera de alcance

- Los campos `cat` de Cursos/Librerías y los módulos del front no se tocan.
- No se migra ningún dato (las asignaciones existentes ya viven en la taxonomía).
