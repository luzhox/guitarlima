# GuitarLima / GL Music Design System

Reference for preserving and extending the current visual language of the GuitarLima WordPress theme.

This document was created after a Playwright walkthrough of `http://glmusic.local` on May 7, 2026, plus inspection of the theme Sass/PHP sources. Evidence files from that pass live in `qa/`:

- `qa/glmusic-desktop-home.png`
- `qa/glmusic-mobile-home.png`
- `qa/glmusic-page-planes.png`
- `qa/glmusic-page-mi-cuenta.png`
- `qa/glmusic-desktop-audit.json`
- `qa/glmusic-mobile-audit.json`

## 1. Brand Feel

GL Music is a dark, image-led music learning platform. The site should feel like an online academy and media library, not a corporate SaaS page.

Core impressions:

- Performance and instruments first: guitars, students, stage lighting, library artwork.
- Dark immersive surfaces with white type and violet/blue highlights.
- Bold italic headings with a music poster feeling.
- Rounded, friendly controls that still feel energetic.
- Course and library cards are visual catalog items, not dense admin cards.

Avoid importing LigoPay's fintech tone. The transferable learning is architecture and discipline, not the fintech visual skin.

## 2. Source Of Truth

Primary styling source:

- `styles/sass/basics/_variables.scss`
- `styles/sass/basics/_typo.scss`
- `styles/sass/basics/_grid.scss`
- `styles/sass/basics/_buttons.scss`
- `styles/sass/basics/_menu.scss`
- `styles/sass/basics/_footer.scss`
- Component/page partials under `styles/sass/components/` and `styles/sass/pages/`

Compiled output:

- `build/css/main.css`
- `build/js/main.js`

Do not edit compiled build files by hand. Change source Sass/JS and run `npm run prod`.

## 3. Color System

Current Sass tokens:

```scss
$color-primary: #3858e9;
$color-primary-hover: #375af7;
$color-primary-text: white;
$color-primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
$color-gray: #383A3F;
$color-gray-light: #dbe3f2;
$color-white: white;
$color-sandwich: #fbb66d;
```

Observed dark surfaces:

```scss
$gl-header-gradient: linear-gradient(105.9deg, rgb(20, 15, 47) 24.4%, rgb(11, 10, 16) 80.5%);
$gl-section-gradient: linear-gradient(105.9deg, rgb(12, 9, 28) 24.4%, rgb(11, 10, 16) 80.5%);
```

Use these as the default dark background language for headers, footers, course catalogs, and library catalogs.

Accent chips currently communicate level:

- Principiante: green gradient, white text.
- Intermedio: orange gradient, white text.
- Avanzado: red gradient, white text.

Do not make the site a one-color purple UI. The violet/blue gradient is the CTA/accent layer; the actual content is carried by dark backgrounds and photographic media.

## 4. Typography

Primary font:

```scss
$font-primary: 'Futura PT', Arial, Helvetica, sans-serif;
```

Loaded from:

- `fonts/futura_pt_book-webfont.woff2`
- `fonts/futura.woff2`
- italic variants in `fonts/`

Observed type scale:

- Hero eyebrow pill: 18px, bold, white, rounded border.
- Hero title desktop: 64px / 76.8px, bold italic, uppercase, white.
- Hero title mobile: 32px / 38.4px, bold italic, uppercase, white.
- Section titles: 40px, bold italic, centered, white.
- Card titles: 20px, bold italic, white.
- Body copy: 18px desktop-ish, white on dark sections.
- Nav links: 16px, medium, white.

Rules:

- Use italic bold for expressive music-facing headings.
- Keep body copy lighter and readable.
- Keep headings white on dark surfaces.
- Do not introduce unrelated typefaces for normal theme work. Product templates currently contain legacy references like `Marvel` and `Iskry`; treat those as local exceptions to audit before expanding.

## 5. Layout And Grid

Container source: `styles/sass/basics/_grid.scss`.

```scss
.container {
  width: 84%;
  max-width: 480px;

  @include tablet {
    max-width: 750px;
  }

  @include desktop {
    max-width: 1184px;
  }
}
```

Breakpoints:

```scss
$breakpoint-tablet: 744px;
$breakpoint-desktop: 1240px;
```

Layout rules:

- Keep dark sections full width and constrain content with `.container`.
- Home uses a full-viewport hero followed by full-width catalog bands.
- Course cards are multi-column on desktop, single-column on mobile.
- Library cards use wider image-led rows on desktop and stacked cards on mobile.
- Use consistent vertical rhythm: large section padding, centered headings, then filters/cards.

## 6. Header

Selector: `.site-header`.

Observed desktop:

- Sticky dark gradient bar.
- White GL logo at left.
- Search field beside logo.
- Nav links: Cursos, Librerias, Planes.
- Gradient rounded "Iniciar sesion" CTA.

Observed mobile:

- Logo left, search field center, large rounded menu button right.
- Header remains dark and compact.
- Mobile menu must not overlap hero text or hide the search.

Header rules:

- Preserve the dark gradient.
- Keep search rounded with translucent border and gradient search button.
- Active nav state may use blue underline/accent.
- CTA buttons in the header use pill radius and the primary gradient.
- Do not make the header transparent over content unless all pages are audited.

## 7. Hero

Modules involved:

- `modules/header-video/header-video.php`
- `styles/sass/components/_header-video.scss`
- `modules/hero/hero.php`
- `styles/sass/components/_hero-principal.scss`

Observed hero:

- Full-viewport media background.
- Strong dark overlay for legibility.
- Left-aligned content inside the container.
- Eyebrow pill: "Academia GL Music".
- Huge bold italic uppercase title.
- Two CTAs: filled gradient and transparent outlined pill.

Hero rules:

- Always use real music/platform imagery or video.
- Keep hero copy over the media, not inside a floating card.
- Maintain strong contrast. Overlay is part of the design, not an afterthought.
- CTA pair pattern: primary filled gradient plus secondary border/transparent action.
- On mobile, keep the hero title large but not overflowing; current target is 32px.

## 8. Course And Library Catalogs

Main component:

- `modules/course-percat/course-percat.php`
- `src/course-percat.js`
- `styles/sass/components/_course-percat.scss`

Observed sections:

- Dark full-width gradient background.
- Centered italic title.
- Short centered description.
- Rounded filter pills.
- Image-led cards.
- Level tags for courses.
- Card title + "GL Music" metadata.

Course card rules:

- Images are primary. Do not replace cards with text-heavy boxes.
- Keep cards visually simple: image, level tag, title, source/meta.
- Hover scale is allowed on desktop but must not cause layout collision.
- Favor 10px-ish card radii only when the card itself has a frame; current image cards are mostly square-edged.
- Filter pills use transparent background, white border, rounded 25px, and stronger border for active state.

Library card rules:

- Use artwork/photos for each library genre.
- Titles stay bold italic and white.
- Avoid extra badges unless they add real scanning value.

## 9. Plans

Module:

- `modules/plans/plans.php`
- `styles/sass/components/_plans.scss`

Observed plans page:

- Dark background matching the site.
- Big centered heading with short violet underline.
- Pricing cards in dark panels.
- Cards use subtle borders and top accent lines.
- Primary plan form uses cyan/blue action emphasis.
- Plan CTAs use rounded gradient pills.

Rules:

- Plan cards may be more panel-like than course cards, because the user is comparing offers.
- Keep pricing typography oversized and high contrast.
- Use short bullets with blue star-like accents.
- Avoid over-decorating; comparison clarity matters.

## 10. Login And Account

Observed login page:

- Dark site background with centered light form panel.
- Panel has generous radius.
- Inputs are large, quiet, and rectangular with soft radius.
- Submit button uses primary gradient.
- Text switches to dark gray inside the panel.

Rules:

- Auth forms can use a light card because they are focused task surfaces.
- Keep one clear primary action.
- Inputs need strong touch targets and visible focus.
- Do not apply the course-card visual style to forms.

## 11. Footer

Selector: `.site-footer`.

Observed:

- Dark section gradient.
- Top border with low-opacity light line.
- White logo.
- Link columns and contact block.
- Social icons are circular violet/blue gradient buttons.
- Phone number is large and prominent.

Rules:

- Footer should feel like the closing band of the same dark stage environment.
- Keep social icons compact, circular, and grouped.
- Preserve strong phone visibility.
- Avoid dense legal/footer clutter unless requested.

## 12. Buttons And Controls

Source: `styles/sass/basics/_buttons.scss`.

Primary CTA:

```scss
.btn__primary {
  background: $color-primary-gradient;
  color: white;
  border-radius: 32px;
  padding: 16px 48px;
}
```

Observed variants:

- Header login: large pill, gradient, 16px.
- Hero primary: compact pill, gradient, 18px.
- Hero secondary: transparent with white border.
- Filters: transparent pills, white border, 14px.
- Search button: circular/pill gradient with icon.

Rules:

- Use gradient pills for primary actions.
- Use outline pills for secondary actions on dark backgrounds.
- Keep filters visually quieter than CTAs.
- Buttons should never use unrelated colors unless they encode state or level.

## 13. Imagery

Imagery is the backbone of the brand.

Use:

- Real students playing instruments.
- Guitars, bass, piano, singers, stage lighting, rehearsals.
- Library/genre artwork that clearly communicates the category.
- Crops that keep instruments and faces readable.

Avoid:

- Generic abstract gradients as main media.
- Corporate stock imagery.
- Decorative SVG hero illustrations.
- Over-dark images where the subject is lost.

Image behavior:

- Hero media fills viewport with `object-fit: cover`.
- Catalog images should maintain stable aspect ratios.
- Mobile cards stack vertically and need enough breathing room.

## 14. Responsive Behavior

Desktop:

- Header has logo, search, nav, and CTA in one row.
- Hero content sits left over wide media.
- Course cards can form compact multi-column rows.
- Footer uses columns.

Mobile:

- Header compresses but keeps search available.
- Hero content remains over image, with title at 32px.
- Course and library cards stack one per row.
- Filter pills wrap and center.
- Footer stacks and keeps touch-friendly spacing.

QA requirements:

- Check `390px` mobile width.
- Check `1440px` or `1512px` desktop width.
- Confirm no horizontal overflow.
- Confirm header/menu/search do not collide.
- Confirm card images do not collapse or crop the subject badly.
- Confirm text does not overflow pills/buttons.

## 15. WordPress / ACF Implementation Rules

The visual system is inseparable from the WordPress/ACF system.

- Pages are composed through ACF Flexible Content and `lib/helpers.php`.
- Layout names use underscores in ACF and map to dashed module folders.
- Module PHP should keep markup close to the existing BEM-ish class patterns.
- Add Sass in component/page partials and import through `styles/sass/style.scss`.
- Add JS only when behavior needs it; initialize with `data-module` when appropriate.
- Always include sensible fallbacks for modules that should render before editors fill all fields.

## 16. Tailwind Learning Applied To Sass

This theme is currently Sass-first, not Tailwind-built. Still apply the lessons learned from Tailwind projects:

- Use tokens instead of ad hoc values.
- Think mobile-first.
- Compose reusable section/card/button patterns.
- Keep spacing scales consistent.
- Keep state variants predictable: hover, active, disabled, focus.
- Avoid one-off utility-like hacks scattered in PHP templates.

## 17. Do / Don't

Do:

- Preserve dark immersive music-platform identity.
- Use real visual assets.
- Keep CTAs rounded and gradient-based.
- Keep course/library cards image-led.
- Keep Futura PT as the default.
- QA visually before claiming UI work is done.

Don't:

- Copy LigoPay's fintech look.
- Replace real media with abstract decoration.
- Invent new palettes for each page.
- Add nested cards inside cards.
- Make filters louder than primary CTAs.
- Hand-edit `build/` output.
- Expand legacy product typography without auditing it.

## 18. Known Design Risks

- Mobile header can become crowded because logo, search, and menu all compete for width.
- Some text labels have typos or inconsistent accents: "Librerias" vs "Librerías".
- The home PWA prompt can appear in audits and should not be mistaken for theme design.
- Product templates contain legacy font/color choices that differ from the main GL Music language.
- Several pages redirect locked course URLs to plans; design QA should account for logged-in vs logged-out states.
