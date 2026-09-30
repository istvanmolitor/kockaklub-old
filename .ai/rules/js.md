---
paths:
  - 'resources/js/**'
---

# Js

## resources/js/app.js is the shell/shop entry, resources/js/admin.js is the admin SPA entry
The root Vite build has two JS entrypoints, both registered in vite.config.js `input`:
- `resources/js/app.js`: minimal/empty entry loaded by the default welcome page and by istvanmolitor/shop's Blade+Livewire layout (`packages/shop/resources/views/layouts/base.blade.php`). These pages have no `#app` div and don't need the admin SPA — keep this file lightweight, don't add the Vue admin bootstrap back into it.
- `resources/js/admin.js` (+ `resources/js/Admin.vue`): bootstraps the full Vue 3 admin SPA — creates the router from all `resources/js/packages/vue-*` package routes, registers menu/dashboard builders, mounts on `#app`. This is what istvanmolitor/admin's `packages/admin/resources/views/admin.blade.php` loads via `@vite(['resources/css/app.css', 'resources/js/admin.js'])`.

When adding a new admin-facing vue-* package (routes/menu/dashboard builders), wire it into `resources/js/admin.js`, not `app.js`. Don't merge the two entries back together — that was the original bug (the whole admin SPA bundle was being shipped to the public welcome/shop pages where it silently failed to mount).

Note: `packages/admin/resources/views/admin.blade.php` lives in the separate istvanmolitor/admin package repo (packages/ is git-ignored in this root repo per .ai/rules/packages.md) — changes there need to be committed/pushed in that package's own repo.
