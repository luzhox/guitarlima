# GuitarLima Theme Agent Guide

Entry point for AI agents working on this WordPress theme.

## Companion Docs

- [`DESIGN.md`](DESIGN.md) — visual system, component rules, responsive QA, and design do/don'ts for GL Music.

## Project Snapshot

- Project: custom WordPress/WooCommerce theme for GuitarLima / GL Music, focused on music courses, digital library content, user accounts, favorites, plans, checkout, and product sales.
- Local theme path: `/Users/luismorales/Local Sites/glmusic/app/public/wp-content/themes/guitarlima`.
- Local site URL is usually a LocalWP domain for GL Music. Confirm the active URL before browser QA.
- Build stack: WordPress PHP templates, ACF Flexible Content, WooCommerce template overrides, Sass, Webpack 4, jQuery, Owl Carousel, AOS.
- Main compiled frontend assets: `build/css/main.css`, `build/js/main.js`.
- Source assets: `styles/sass/style.scss`, `src/index.js`, `modules/*/*.php`, `woocommerce/**/*.php`.

## Before Editing

- Respect existing user changes. This repository often has dirty files, so inspect before touching and never revert unrelated work.
- Use `rg` for search and `rg --files` for file discovery.
- If changing Sass or JavaScript under `styles/sass/` or `src/`, run `npm run prod` so `build/` stays in sync.
- If changing standalone CSS such as `login/css/loginStyles.css` or `admin/*.css`, no webpack build is required.
- If changing PHP templates, run `php -l` on edited PHP files when possible.
- Use browser or Playwright visual QA when touching layout, responsive behavior, menus, checkout, account pages, courses, or product cards.

## Core Learning Priorities

The most important inheritance from the LigoPay agent, `/Users/luismorales/Local Sites/ligopay/app/public/wp-content/themes/ligopay/CLAUDE.md`, and `/Users/luismorales/Local Sites/ligopay/app/public/wp-content/themes/ligopay/docs/project-ingestion.md` is the accumulated learning about WordPress, ACF, Tailwind-style design discipline, and project ingestion. Treat that knowledge as the operating system for future work on GuitarLima.

- WordPress first: understand the theme lifecycle before editing. Check template hierarchy, included files from `functions.php`, hooks/actions/filters, enqueue order, WooCommerce overrides, escaping, sanitization, nonces, AJAX endpoints, and plugin compatibility.
- ACF first: before adding markup or fields, inspect how Flexible Content is rendered through `lib/helpers.php`, how layouts map to `modules/*`, and how field names are used in PHP. Prefer repeaters/groups/options that editors can maintain, and include safe fallbacks when a module must render before content is saved.
- Tailwind learning still matters: even though this theme currently uses Sass instead of a Tailwind build, keep the same discipline learned from Tailwind projects: tokenized spacing, consistent breakpoints, composable utility-like patterns, restrained color usage, responsive-first decisions, and no one-off visual hacks.
- Transfer patterns, not brand skin: reuse the WordPress/ACF/Tailwind architecture lessons from LigoPay, but translate every visual choice into GuitarLima's existing Sass variables, Futura PT typography, modules, and dark music-commerce interface.
- When unsure, document the discovered pattern in this guide or a nearby README so the next agent starts with more context than you had.

## Ingestion Workflow

LigoPay's `docs/project-ingestion.md` is the model for how future agents should learn a WordPress theme before changing it. Apply that habit here.

- Start every substantial task by building a small runtime map: PHP entrypoint, JS entrypoint, Sass entrypoint, compiled outputs, direct-edit stylesheets, local URL, and relevant templates.
- Identify whether the change belongs in PHP templates, ACF field structure, Sass source, JavaScript source, WooCommerce overrides, direct CSS, or admin/login assets before editing.
- Keep a current module inventory in mind. For GuitarLima, page composition is module-driven via `modules/*`, and course/account/shop experiences also span WooCommerce templates and account overrides.
- Separate source and generated files: source changes in `styles/sass/` or `src/` require `npm run prod`; direct CSS files such as `login/css/loginStyles.css` and `admin/*.css` do not.
- For any new user-facing module, document the field source, fallback behavior, Sass partial, JS hook if any, and QA route.
- Track risks explicitly: legacy selectors, placeholder module bootstraps, generated build diffs, plugin-heavy WooCommerce screens, and admin CSS that can affect unrelated wp-admin pages.
- Finish UI work with evidence: what viewport/page was checked, what command or browser flow was used, and what remains untested.

## Transferred Claude.md Learning

Use LigoPay's `CLAUDE.md` as a reference model for how to reason, but adapt every implementation detail to GuitarLima.

- Include order can matter. In this theme `functions.php` loads `inc/opciones.php`, `inc/customizer.php`, `inc/widgets.php`, `inc/login.php`, `inc/menus.php`, `inc/formats.php`, `inc/libraries.php`, `lib/helpers.php`, `inc/etc.php`, `inc/favorites.php`, `inc/admin-style.php`, `inc/woocommerce-account.php`, `inc/plan-pro-checkout.php`, `inc/members-content-permissions-fix.php`, `inc/canciones-admin.php`, and `inc/canciones-bulk.php`. Before moving includes or adding dependencies, confirm which functions are defined where.
- Asset enqueueing belongs in `wp_enqueue_scripts` or `admin_enqueue_scripts`. If PHP needs to pass AJAX URLs or nonces to JavaScript, enqueue first and then call `wp_localize_script()`.
- ACF registrations and options-page setup should happen on `acf/init` when using ACF APIs. GuitarLima already uses `inc/etc.php` to set the Google Maps API key with `acf_update_setting()`.
- Flexible Content layout names use underscores in ACF and this theme maps them to dashed module folders. Example: `course_percat` becomes `modules/course-percat/course-percat.php`.
- Never call `have_rows()` without `the_row()` inside the loop. That can create an infinite loop and white screen.
- Prefer `get_field()` / `get_sub_field()` when you need to sanitize or transform data before output. Use `the_sub_field()` only for trusted WYSIWYG/editor content that is already intended to echo.
- Escape output at the echo point: `esc_html()` for text, `esc_attr()` for attributes, `esc_url()` for URLs, `wp_kses_post()` for allowed editor HTML.
- Sanitize input before use or storage: `sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`, `absint()`, `sanitize_key()`.
- AJAX handlers should verify nonces with `check_ajax_referer()`, check permissions when needed with `current_user_can()`, and respond with `wp_send_json_success()` / `wp_send_json_error()`.
- For custom queries, always call `wp_reset_postdata()` after a secondary `WP_Query` loop.
- For template decisions, use WordPress template hierarchy and conditional tags (`is_front_page()`, `is_page()`, `is_singular()`, `is_archive()`, `is_user_logged_in()`) instead of brittle URL checks.
- When adding CPTs or changing rewrite slugs, remember permalink flushing. Prefer a one-time controlled flush or instruct saving Settings -> Permalinks.
- Local JSON in `acf-json/` is the source of admin-editable ACF field-group sync. Avoid casual manual edits to field keys; if changing field structure, keep keys globally unique and confirm admin sync behavior.
- Visual QA should check mobile and desktop, horizontal overflow, sticky header behavior, logo/menu alignment, carousels, account pages, checkout, and WooCommerce notices.

## Visual Line

- Preserve the current GuitarLima look: dark musical interface, Futura PT typography, image-led heroes, rounded CTAs, and compact commerce/course layouts.
- Primary brand system lives in `styles/sass/basics/_variables.scss`: `$color-primary: #3858e9`, `$color-primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%)`, `$color-gray: #383A3F`, `$color-gray-light: #dbe3f2`, `$color-sandwich: #fbb66d`.
- Header surfaces use the established dark gradient: `linear-gradient(105.9deg, rgb(20, 15, 47) 24.4%, rgb(11, 10, 16) 80.5%)`.
- Typography uses `Futura PT` from `fonts/`; keep headings bold, clean, and slightly tight, matching `_typo.scss`.
- Buttons should follow `.btn` and `.btn__primary`: 32px radius, gradient fill, clear hover brightness, and generous horizontal padding.
- Layouts should respect `.container`: `width: 84%`, mobile max `480px`, tablet max `750px`, desktop max `1184px`.
- Hero modules are image-first with overlays and Owl Carousel. Do not replace the current photographic/music-course feel with generic decorative gradients or illustrations.

## Canciones, wp-admin State & Commerce (updated 2026-07-23)

Facts that override older sections of this guide when they conflict.

- Songs are the CPT with internal slug `cursos` (label "Canciones", rewrite `canciones`). Their relation to Cursos (`cursos-wp`) and Librerías (`libreria`) is the core `category` taxonomy: listings/modules query songs with `tax_query` on `category`, and each Curso/Librería declares its category via the ACF `cat` field.
- Song categories are edited through the ACF field `categorias` (taxonomy, multi-select autocomplete, `save_terms` + `load_terms`, key `field_df38f56dd4534` in `acf-json/group_684cbc3c38408.json`). The native Categories panel is hidden for `cursos` in the block editor (`inc/canciones-admin.php`) so there is a single write path — do not re-enable it without removing the ACF field.
- Bulk tooling lives in **Canciones → Carga masiva** (`inc/canciones-bulk.php`, class `GL_Canciones_Bulk`, capability `publish_posts`): three tabs — Excel bulk upload (template download included), mass category assignment (add/replace/remove), and full catalog export. `.xlsx` read/write is native via `inc/gl-xlsx.php` (`GL_XLSX`), no external libraries.
- Songs no longer require an image: bulk-created songs get `images/cancion-dummy.png` (white GL wordmark on `#3858e9`, regenerable with `bin/generate-cancion-dummy.php --white`) as featured image and ACF `poster`. The attachment is sideloaded once and cached in option `gl_cancion_dummy_id`.
- The global dark wp-admin skin was **removed** (2026-07-23): wp-admin is standard light. What remains custom: admin-bar logo, footer text, dashboard cleanup, and the fully custom login (`admin/login.css` + section 5 of `inc/admin-style.php`). The dark UI lives only in the GL Subs plugin screens (scoped to `body.gls-page`). When deploying, run once in production: `UPDATE wp_usermeta SET meta_value='fresh' WHERE meta_key='admin_color' AND meta_value='midnight';`
- Publishing songs previously failed for every role (broken Members `auth_callback` on `_members_access_role`); the upgrade-safe fix is `inc/members-content-permissions-fix.php`.
- Commerce/memberships do **not** go through WooCommerce anymore: they live in the custom plugin `wp-content/plugins/glmusic-subscriptions/` (`GLS_*` classes, PayPal Subscriptions REST API). WooCommerce is still installed but is being phased out as the payment path — older mentions of WooCommerce as the commerce layer in this guide and `DESIGN.md` are outdated on that point.
- Design/decision records for the above: `docs/superpowers/specs/2026-07-23-*.md`.

## High-Impact Files

### Bootstrap & rendering

- `functions.php` — includes theme subsystems and contains AJAX login/register handlers.
- `inc/libraries.php` — enqueues compiled CSS/JS, AOS, Owl Carousel, and jQuery.
- `lib/helpers.php` — module loader and ACF Flexible Content loop.
- `page.php` / `page-gl.php` — page rendering entry points.
- `menu.php` — sticky header, logo, mobile sandwich, search, favorites and login actions.
- `footer.php` — global footer.

### Modules

- `modules/hero/hero.php` — main homepage hero carousel.
- `modules/header-video/header-video.php` — video/header module.
- `modules/course-percat/course-percat.php` + `src/course-percat.js` — course/category filtering.
- `modules/course-reproduction/course-reproduction.php` — course playback experience.
- `modules/librarie-reproduction/librarie-reproduction.php` — library playback experience.
- `modules/mis-favoritos/mis-favoritos.php` + `js/favorites.js` / `inc/favorites.php` — favorites UI and AJAX.
- `modules/login-modal/login-modal.php`, `modules/login-page/login-page.php`, `modules/register/register.php` — auth flows.
- `modules/plans/plans.php` — plans/pricing area.

### WooCommerce & accounts

- `woocommerce/archive-product.php`, `woocommerce/content-product.php`, `woocommerce/single-product.php`, `woocommerce/content-single-product.php` — shop and product surfaces.
- `woocommerce/cart/*.php` and `woocommerce/checkout/*.php` — checkout/cart overrides.
- `woocommerce/myaccount/*.php` and `inc/woocommerce-account.php` — user account area.
- Keep WooCommerce hook compatibility in mind; template overrides may need matching upstream signatures.

### Styling & scripts

- `styles/sass/style.scss` — imports all Sass partials.
- `styles/sass/basics/*` — variables, typography, grid, buttons, menu, footer, inputs.
- `styles/sass/components/*` — module styles.
- `styles/sass/pages/*` — page-specific styles.
- `src/template.js` — global UI behavior, header scroll, mobile menu, forms, modals, Owl Carousel setup.
- `src/page-modules.js` — initializes JS modules via `data-module`.
- `src/index.js` — JS/Sass entrypoint.

## Module Pattern

When adding modules, keep the existing ACF Flexible Content pattern:

```php
// modules/example/example.php
<section class="example">
  <div class="container">
    <?php the_sub_field('texto'); ?>
  </div>
</section>
```

`lib/helpers.php` maps ACF layout names by replacing underscores with hyphens:

```php
$module_name = str_replace('_', '-', get_row_layout());
the_module($module_name);
```

If a module needs JavaScript, add a `data-module="module-name"` hook and place the matching file in `src/module-name.js`.

## Don't

- Don't flatten or replace the GuitarLima brand with LigoPay's fintech visuals, copy tone, Tailwind tokens, or file conventions.
- Don't dismiss LigoPay's WordPress, ACF, or Tailwind learnings just because the GuitarLima theme is older and Sass-based. Translate the pattern into this stack.
- Don't introduce new color systems unless the existing Sass variables are insufficient.
- Don't edit compiled `build/` files by hand when source Sass/JS should generate them.
- Don't remove WooCommerce template hooks casually; they often support plugin behavior.
- Don't hardcode LocalWP URLs into source files unless the file already uses an absolute production URL pattern.
- Don't add noisy `console.log` statements to new production code.
- Don't change ACF field keys in `acf-json/` unless the task explicitly requires field structure changes.

## Useful Commands

```bash
npm install
npm run dev
npm run prod
php -l functions.php
php -l inc/libraries.php
```

For visual QA, confirm the active LocalWP domain first, then test desktop and mobile widths, especially header/menu, course grids, product cards, checkout, and account pages.
