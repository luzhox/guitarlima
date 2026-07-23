# Quitar el skin oscuro del wp-admin (conservar GL Subs y login) — 2026-07-23

## Objetivo

El wp-admin vuelve al aspecto claro estándar de WordPress. La UI oscura queda
solo en las pantallas del plugin GL Subs (que ya trae su propio CSS scoped) y
en la pantalla de login (que se conserva tal cual).

## Cambios

### 1. `inc/admin-style.php`

Eliminar exactamente 3 secciones:

- **Sección 1** — enqueue global de `admin/admin.css` (handle `gl-admin-ui`).
- **Sección 2** — filtro `admin_body_class` que agrega la clase `gl-admin`.
- **Sección 5** — hook `user_register` que fuerza `admin_color = midnight`.

Se conservan sin tocar: sección 3 (logo GL en admin bar), 4 (footer custom),
6 (ocultar welcome panel / update-nag), 7 (quitar widgets de noticias WP) y
8 (login personalizado: `admin/login.css`, `login_headerurl`, `login_title`).

### 2. Archivos

Borrar `admin/admin.css`, `admin/admin.css.map` y `admin/admin.scss`
(fuente muerta; recuperable desde git). `admin/login.css` se queda.

### 3. Base de datos (una sola vez, local + producción al desplegar)

```sql
UPDATE wp_usermeta SET meta_value='fresh'
WHERE meta_key='admin_color' AND meta_value='midnight';
```

(9 usuarios en local. Los 44 con `modern` no se tocan.)

### 4. GL Subs

Cero cambios. Su CSS (`assets/css/admin.css`, scoped a `body.gls-page`) se
encola solo en sus pantallas vía `GLS_Admin::is_gls_screen()`.

## Verificación

Con usuario temporal admin + Playwright contra `http://glmusic.local`:

1. `wp-login.php` → sigue con el diseño personalizado.
2. Dashboard y listado de Canciones (`edit.php?post_type=cursos`) → admin claro estándar.
3. `admin.php?page=gls_admin` → UI oscura de GL Subs intacta.

Borrar el usuario temporal al terminar. `php -l` sobre el archivo editado.

## Fuera de alcance

- No se tocan los 44 usuarios con esquema `modern`.
- No se toca el front-end ni el build de webpack (no compila `admin.scss`).
- El despliegue a producción (incluido el UPDATE de BD) queda para el deploy.
