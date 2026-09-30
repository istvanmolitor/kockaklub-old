---
paths:
  - 'packages/*/resources/views/**'
---

# Views

## No "theme" package — use blade-ui for all backend-package UI
The old `istvanmolitor/theme` package is gone; `istvanmolitor/blade-ui` replaced it. Never write `theme::` view references or `x-theme::` components — they compile fine in isolation but fail (or silently render nothing) once resolved, and `php artisan view:cache` will fail on ANY leftover `theme::`/`x-theme::` reference even in views that are never actually included at runtime.

Correct usage:
- Layouts: `@extends('blade-ui::layouts.container'|'left-sidebar'|'centered'|...)`.
- Components: `Blade::componentNamespace` is registered as `ui` (not `blade-ui`), e.g. `<x-ui::layout.icon name="..." />` (not `<x-theme::icon>` or `<x-ui::icon>`), `<x-ui::form.form>`, `<x-ui::form.fields.input>`, `<x-ui::buttons.primary-button>`.
- `@yield('page-title')` in `blade-ui::layout.main` is hyphenated, not `page_title` — match it exactly or the page title silently renders empty.
- Any backend package whose Blade views use blade-ui must require `istvanmolitor/blade-ui` in its own composer.json (see `shop`, `contact`), not just rely on the root app requiring it.
- See `packages/blade-ui/skills/sitebuild-integration/SKILL.md` for the full component catalog and the publish/restyle workflow before writing raw Tailwind by hand.
