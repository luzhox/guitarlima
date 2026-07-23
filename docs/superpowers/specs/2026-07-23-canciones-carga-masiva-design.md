# Herramienta admin: carga masiva (Excel), asignación masiva y export — 2026-07-23

## Objetivo

Página admin (submenu "Carga masiva" bajo Canciones, capability `publish_posts`
→ admins y profesores) con **tres tabs** (se ve una sección a la vez):

1. **Carga masiva desde Excel:** se descarga la plantilla `.xlsx` desde la misma
   página (hoja "Canciones" con columnas `Titulo | idVideo | Autor | Categorias`
   + hoja "Categorías" con los nombres válidos, generada al vuelo), se completa
   y se sube. La imagen no es obligatoria: toda canción se crea con el dummy
   (logo GL blanco sobre azul `#3858e9`) como featured image (listados) y poster
   ACF (single). Estado borrador/publicada + categorías extra para todo el archivo.
2. **Asignación masiva** de canciones a cursos/librerías (= términos de `category`):
   lista filtrable client-side + modos añadir/reemplazar/quitar.
3. **Export:** descarga un `.xlsx` con todas las canciones (mismo formato que la
   plantilla + columna Estado) — sirve de respaldo y de base re-importable.

## Implementación

- `inc/gl-xlsx.php` — `GL_XLSX`: lector/generador mínimo de .xlsx nativo
  (ZipArchive + SimpleXML, sin librerías; sharedStrings/inline strings; libros
  multi-hoja con celdas de texto). `send_download()` sirve la descarga.
- `inc/canciones-bulk.php` — clase `GL_Canciones_Bulk` (patrón GLS_Admin):
  submenu, POST en `admin_init` por nonce (`gl_bulk_create`/`gl_bulk_assign`) con
  redirect PRG que conserva el tab (`&tab=`), flash notice por transient por
  usuario; downloads vía `admin_post_gl_bulk_template` / `admin_post_gl_bulk_export`
  (nonce + cap). Lógica en métodos públicos `process_create()` / `process_assign()`.
- Parse del Excel: primera hoja, fila de cabecera detectada y saltada, filas
  vacías ignoradas, columna `Categorias` resuelta contra nombre/slug normalizado
  (sin acentos, case-insensitive); categorías inexistentes se reportan y se
  ignoran; duplicados por título exacto omitidos y reportados. Tope 100 filas,
  archivo `.xlsx` máx 5 MB.
- Escritura ACF **siempre con field key** (constantes `FIELD_*` del grupo
  `group_684cbc3c38408.json`); categorías vía `update_field(FIELD_CATEGORIAS, …)`
  (save_terms sincroniza los términos reales — misma vía única que el editor).
- Dummy: `bin/generate-cancion-dummy.php` (PHP GD, `--white`: el wordmark naranja
  dejaba "PLATFORM" ilegible sobre azul) → `images/cancion-dummy.png` (1200x675,
  versionado). Runtime `get_dummy_attachment_id()`: sideload único a la media
  library, option `gl_cancion_dummy_id`, meta `_gl_cancion_dummy` anti-duplicados,
  auto-regeneración si el attachment se borra.
- `admin/canciones-bulk.js/.css` — tabs client-side (estado activo también por
  `?tab=` server-side) + filtro vanilla (texto sin acentos, "solo sin categoría",
  seleccionar visibles, contador). Sin webpack.
- UI moderna sobre el admin claro (2026-07-23): tabs segmentados tipo pill,
  cards con sombra suave, dropzone drag&drop para el .xlsx (muestra el archivo
  elegido), categorías como chips seleccionables, filas con highlight de
  selección (`:has`), modos añadir/reemplazar/quitar como tarjetas de opción,
  export como stat centrado, iconos SVG inline (trazo Lucide), focus-visible y
  `prefers-reduced-motion`. Acento `#3858e9`, texto slate (contraste AA).

## Verificación realizada (2026-07-23)

- CLI (bootstrap + socket): round-trip `GL_XLSX::build→read` con acentos y
  caracteres especiales; dummy idempotente; create desde filas de Excel
  (cabecera saltada, fila vacía ignorada, duplicado omitido, sin título y
  categoría inexistente reportados, merge categorías por fila + globales);
  assign en los 3 modos; límite 100 filas.
- Playwright (admin temporal): tabs muestran una sección a la vez y el POST
  vuelve al tab correcto; plantilla descargada (zip válido, 2 hojas, cabeceras
  OK); subida real del .xlsx → 2 canciones creadas con categorías de la columna;
  export descargado con 348 filas = 348 canciones, formato re-importable.
- Front (verificado en la iteración anterior): dummy como portada del single y
  thumbnail en listados. Rol profesor: página visible y operable.
- Datos de prueba eliminados.
