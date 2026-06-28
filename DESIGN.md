# Sekawan Coffee Root Login Design System

## 1. Atmosphere & Identity

The root login should feel like stepping up to a well-kept coffee bar at opening hour, warm, calm, premium, and immediately welcoming without turning theatrical. Its signature is an editorial coffee-house split shell: warm ivory and cream surfaces, espresso text, and caramel-copper interaction accents anchored by the existing `public/assets/img/logo.jpeg`. This system is intentionally narrow. It governs only the root login and closely related auth entry moments, not the dashboard, inventory, product screens, or a whole-app retheme.

## 2. Color

### Palette

| Role | Token | Light | Dark | Usage |
|------|-------|-------|------|-------|
| Surface/primary | `--login-surface-primary` | `#F6EFE6` | `#18110E` | Page background for the root login shell |
| Surface/secondary | `--login-surface-secondary` | `#EDE0D0` | `#241915` | Hero panel, inset zones, shell contrast layer |
| Surface/elevated | `--login-surface-elevated` | `#FFF9F2` | `#2E221D` | Login card, status block, elevated content |
| Surface/muted | `--login-surface-muted` | `#E4D3BF` | `#3A2C25` | Subtle local-only blocks, secondary chips, quiet separators |
| Text/primary | `--login-text-primary` | `#2B1B14` | `#F7EBDD` | Headlines, body copy, labels |
| Text/secondary | `--login-text-secondary` | `#6A5142` | `#CDB7A4` | Supporting copy, helper text, captions |
| Text/tertiary | `--login-text-tertiary` | `#927563` | `#A88D7C` | Muted metadata, low-emphasis affordances |
| Border/default | `--login-border-default` | `#D9C4AE` | `#4A382F` | Rare utility dividers where tonal separation alone is not enough |
| Border/subtle | `--login-border-subtle` | `#E8D8C9` | `#3A2B24` | Form grouping, quick-login separators, low-contrast outlines |
| Accent/primary | `--login-accent-primary` | `#B86C3C` | `#D08A58` | Primary CTA, active links, checked states, focus identity |
| Accent/hover | `--login-accent-hover` | `#9F5830` | `#E0A06D` | Hover and active accent state |
| Accent/foreground | `--login-accent-foreground` | `#FFF8F1` | `#1A120E` | Text and icons placed on accent surfaces |
| Focus/ring | `--login-focus-ring` | `#D89A68` | `#E3AE84` | Focus-visible ring for Flux inputs, checkboxes, and buttons |
| Status/success | `--login-status-success` | `#5D7A52` | `#89A57A` | Session status, successful password reset confirmation |
| Status/warning | `--login-status-warning` | `#A6762E` | `#CF9A4A` | Local-only environment notices, caution text |
| Status/error | `--login-status-error` | `#A74A35` | `#D57B67` | Validation and authentication errors |
| Status/info | `--login-status-info` | `#7C5A45` | `#BE9A82` | Neutral helper callouts and support hints |

### Rules
- These tokens belong only to the login and auth entry surface. Later dashboard or inventory work must define its own tokens instead of extending this table by habit.
- Tailwind v4 implementation should map these into scoped custom properties or `@theme` aliases for the login shell, while Flux primitives inherit the same accent, focus, text, and surface values.
- Accent is functional, not decorative. Use it for CTAs, focus, interactive links, and selected states. Do not wash the whole page in caramel.
- Warm neutrals provide the atmosphere. Any new raw hex on the login screen should be added here first.
- Dark values exist for compatibility with the current stack and future auth theming, but the primary visual target for this redesign is the warm light experience.

## 3. Typography

### Scale

| Level | Size | Weight | Line Height | Tracking | Usage |
|-------|------|--------|-------------|----------|-------|
| Display | `clamp(2.75rem, 4vw, 3.5rem)` | 600 | 1.05 | `-0.03em` | Hero headline, branded welcome statement |
| H1 | `2.25rem / 36px` | 600 | 1.1 | `-0.02em` | Card or page title when the hero is not present |
| H2 | `1.75rem / 28px` | 600 | 1.2 | `-0.01em` | Login card title, section heading |
| H3 | `1.375rem / 22px` | 600 | 1.3 | `0` | Supporting panel titles, dev quick-login title if needed |
| Body/lg | `1.125rem / 18px` | 400 | 1.6 | `0` | Lead copy in the shell or hero side |
| Body | `1rem / 16px` | 400 | 1.6 | `0` | Default form and supporting text |
| Body/sm | `0.875rem / 14px` | 400 | 1.5 | `0` | Field hints, utility text, helper copy |
| Caption | `0.75rem / 12px` | 500 | 1.4 | `0.02em` | Small labels, environment badges, metadata |
| Overline | `0.6875rem / 11px` | 600 | 1.3 | `0.08em` | Uppercase support label above hero copy |

### Font Stack
- Primary: `var(--font-sans, 'Instrument Sans', ui-sans-serif, system-ui, sans-serif)`
- Mono: `ui-monospace, 'SFMono-Regular', 'SF Mono', Consolas, 'Liberation Mono', monospace`
- Serif (if used): `'Iowan Old Style', 'Palatino Linotype', 'Book Antiqua', Georgia, serif`

### Rules
- The experience is serif-forward, not serif-saturated. Use the serif stack for hero headlines, brand statements, and occasional card headings. Keep form labels, field values, helper copy, and Flux controls in the sans stack.
- Visible UI should use at most two families, serif plus sans. Mono is reserved for development-only environment labels or diagnostic text if needed.
- Body copy never drops below 14px.
- Display text should use `clamp()` so the headline feels editorial on desktop without turning into a four-line block on mobile.
- Flux field labels should keep the clean sans body tone so the page feels premium and readable, not ornamental.

## 4. Spacing & Layout

### Base Unit
All spacing derives from a base of **4px**.

| Token | Value | Usage |
|-------|-------|-------|
| `--space-1` | `4px` | Tight label and icon spacing |
| `--space-2` | `8px` | Inline gaps, checkbox and helper spacing |
| `--space-3` | `12px` | Input padding context, small stack spacing |
| `--space-4` | `16px` | Mobile shell padding, compact card rhythm |
| `--space-5` | `20px` | Dense panel spacing, grouped action padding |
| `--space-6` | `24px` | Default card padding, field stack spacing |
| `--space-8` | `32px` | Separation between hero content groups and form groups |
| `--space-10` | `40px` | Tablet shell padding, large card rhythm |
| `--space-12` | `48px` | Desktop card padding, major layout gap |
| `--space-16` | `64px` | Primary split-shell inner spacing |
| `--space-20` | `80px` | Desktop hero breathing room |
| `--space-24` | `96px` | Maximum shell section separation |

### Grid
- Max content width: `1280px`
- Column system: 12-column shell with a generous split at desktop, usually 7 columns for the brand or hero side and 5 columns for the form side
- Breakpoints: `sm 640px`, `md 768px`, `lg 1024px`, `xl 1280px`, `2xl 1536px`

### Rules
- The shell must use `min-h-[100dvh]` or the equivalent safe viewport rule, not brittle `100vh` full-height assumptions.
- Mobile collapses to a single vertical stack with the logo, welcome copy, then login card. Desktop can shift into an asymmetrical split to make the brand side feel more editorial.
- The login form card should stay within a readable width, target `400px` to `440px`, even when the shell grows much wider.
- Asymmetry is intentional. The visual weight should lean slightly toward the hero or brand side so the page does not read like a centered default auth scaffold.
- Inputs, buttons, status blocks, and local-only quick-login actions should all align to the same spacing tokens. No one-off padding values.

## 5. Components

### Login Shell
- **Structure**: full-page auth entry wrapper, optional hero or brand panel, logo cluster, editorial welcome copy, login card region, footer-level auth utility links
- **Variants**: stacked mobile shell, split desktop shell, compact shell for short viewport heights, logo-present fallback when hero imagery is visually reduced
- **Spacing**: outer shell uses `--space-4` to `--space-12`, hero copy groups use `--space-6` to `--space-10`, split-shell separation uses `--space-12` to `--space-16`
- **States**: default, viewport-collapsed, focus-within emphasis on form side, image-light fallback if the logo asset is the primary brand element without extra hero media
- **Accessibility**: logo must have meaningful alt text, heading order starts with one clear page title, shell must remain readable at `375px`, and decorative hero treatment must never carry essential meaning
- **Motion**: shell entry may fade and translate upward by a small amount using standard timing. Hero and card should never animate layout dimensions. Reduced-motion mode removes entrance movement and keeps only instant state changes

### Login Form Card
- **Structure**: elevated card surface, auth header, session status area, email input, password input with adjacent recovery link, remember-me checkbox, primary submit button, local-only quick-login block, register prompt row
- **Variants**: default, validation-error, loading submit, session-status present, local development with quick-login actions
- **Spacing**: card body uses `--space-6` or `--space-8`, internal field stacks use `--space-4` to `--space-6`, utility rows use `--space-2` to `--space-4`
- **States**: default, hover on actionable links and buttons, active press, focus-visible, disabled, submitting, authentication error, success message present
- **Accessibility**: rely on Flux labels rather than placeholder-only inputs, maintain strong focus-visible treatment with `--login-focus-ring`, preserve keyboard access for every link and quick-login action, and ensure validation or auth errors are readable with contrast-safe surfaces and text
- **Motion**: button press uses micro transform feedback only, status blocks may fade in, and validation feedback should appear without shaking, resizing, or layout-jumping motion

### Local Dev Quick Login Strip
- **Structure**: muted divider region inside the card, uppercase environment label, two evenly weighted quick-action buttons for local roles
- **Variants**: hidden outside local environments, visible local mode, disabled while auth request is in flight
- **Spacing**: top divider uses `--space-4`, label and button stack use `--space-2` to `--space-3`
- **States**: default, hover, active, focus-visible, disabled, hidden
- **Accessibility**: clearly mark as development-only, keep button copy explicit, and do not rely on color alone to signal that the block is non-production
- **Motion**: same button micro-interactions as the main CTA, no special animation beyond opacity or transform

## 6. Motion & Interaction

### Timing

| Type | Duration | Easing | Usage |
|------|----------|--------|-------|
| Micro | `120ms` | `ease-out` | Button press, link emphasis, checkbox state change |
| Standard | `220ms` | `ease-in-out` | Card or hero entrance, status appearance, hover settling |
| Emphasis | `320ms` | `cubic-bezier(0.16, 1, 0.3, 1)` | Initial shell reveal only, if used sparingly |
| Scroll-driven | `not used` | `n/a` | The login screen should not depend on scroll choreography |

### Rules
- Animate only `transform` and `opacity`.
- Every interactive control on the login surface needs clear hover, active, and focus-visible states, including text links, Flux inputs, checkbox controls, and local dev buttons.
- Prefer CSS transitions and existing Flux state styling over custom JavaScript animation layers.
- Reduced motion must respect `prefers-reduced-motion`. Non-essential entrance animation becomes instant, transform movement is removed, and opacity transitions should be shortened or disabled when they do not help comprehension.
- Focus visibility matters more than decorative motion. The login experience should feel composed even with all motion removed.

## 7. Depth & Surface

### Strategy
Choose ONE and commit: `tonal-shift`

The login redesign should create depth through warm tonal layering instead of borders or shadows. The page background sits on `--login-surface-primary`, the hero or shell contrast zone uses `--login-surface-secondary`, and the form card rises through `--login-surface-elevated`. Muted auxiliary regions, such as the local dev quick-login strip or soft status containers, use `--login-surface-muted` or a close tonal step from the same family. Structural separation comes from hue and value shifts within the same coffee-house palette, while focus rings handle interaction emphasis without turning borders into a general depth device.
