# Theming Guidelines

## Source of truth
- Theme tokens live in `resources/css/theme-tokens.css`.
- Avoid redefining colors in page styles. Prefer token variables.

## Which tokens to use
- `--surface-main`: page background
- `--surface-chrome`: nav/footer/floating chrome
- `--surface-panel`: cards/panels/containers
- `--surface-input`: input/select fields
- `--text-primary`: primary text
- `--text-secondary`: secondary/supporting text
- `--border-soft`: subtle borders/dividers
- `--button-bg` / `--button-hover-bg`: interactive button backgrounds

## CSS layering order
1. `theme-tokens.css`
2. `layout.css`
3. `theme-components.css`
4. `app.css`
5. page module CSS in `resources/css/pages/*`
6. temporary shim `theme-layer.css` (keep minimal)

## Do and don't
- Do: use semantic tokens, even in one-off page modules.
- Do: reuse shared component selectors from `theme-components.css`.
- Don't: hardcode hex/rgb colors in `resources/css/pages/*`.
- Don't: add `!important` unless it's a short-lived migration shim.
