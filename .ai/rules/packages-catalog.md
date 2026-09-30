---
paths:
  - 'packages/**'
  - 'resources/js/packages/**'
---

# Packages Catalog

Reference catalog for assembling a new project from the `istvanmolitor/*` backend packages (`packages/*`, path-repo Composer packages, see `.ai/rules/packages.md`) and their paired `resources/js/packages/*` frontend packages (Vite-aliased, see `.ai/rules/js.md`). Most domains ship as a **pair**: a PHP package providing routes/models/API + a Vue package providing the matching admin UI (routes/menu builder/components), connected only by convention (same domain name), not by any code coupling.

## Backend (Composer) packages

| Package | `composer require` | Depends on (`istvanmolitor/*`) | Other deps | Purpose |
| --- | --- | --- | --- | --- |
| menu | `istvanmolitor/menu` | *(none)* | — | Standalone builder for hierarchical admin/frontend navigation menus. Base package, safe starting point. |
| blade-ui | `istvanmolitor/blade-ui` | *(none)* | mallardduck/blade-lucide-icons | Reusable Blade UI components (forms, layout shell, avatar, badge, rating) + showcase page. Base package. |
| admin | `istvanmolitor/admin` | *(none)* | — | PHP-only admin building blocks: catch-all admin route/blade shell, `DataTable`/`HasAdminFilters`, `AdminSearch` registry, config. The actual Vue SPA UI is the separate `vue-admin` frontend package below — this package has no JS of its own (ignore its stray `package.json`/`tsconfig.json`/`tailwind.config.js`, they're dead leftovers). |
| contact | `istvanmolitor/contact` | blade-ui | — | Simple public contact form (route + controller + Blade view). Its view is built from `blade-ui` layouts/components (`blade-ui::layouts.centered`, `x-ui::form.*`, `x-ui::buttons.primary-button`). |
| user | `istvanmolitor/user` | admin, menu | laravel/sanctum | User/user-group/permission (ACL) management with Sanctum API auth. Almost every other domain package ends up pulling this in (directly or transitively) for auth/menu integration. |
| language | `istvanmolitor/language` | user, admin, menu | — | Multi-language/translation management, locale switching, translatable Eloquent models. |
| address | `istvanmolitor/address` | user | — | Country/city/address management with Hungarian seed data + REST API. |
| currency | `istvanmolitor/currency` | language | — | Multi-currency support: exchange rates, price conversion, scheduled rate updates. |
| customer | `istvanmolitor/customer` | language, currency | — | Customer and customer-group management. |
| setting | `istvanmolitor/setting` | user | — | Key-value application settings storage with a form builder. |
| media | `istvanmolitor/media` | admin | — | Media file/folder management with an admin API. |
| product | `istvanmolitor/product` | blade-ui, currency | — | Product catalog: categories, units, custom fields, images, barcodes. Has one Livewire Blade partial (`livewire/category-tree-item`) using `x-ui::layout.icon`. |
| stock | `istvanmolitor/stock` | product | — | Warehouse, stock movement, and inventory management. |
| order | `istvanmolitor/order` | product, user | — | Order, payment, shipping, and status-history management. |
| shop | `istvanmolitor/shop` | blade-ui, menu, product, customer, order, address, currency, language, stock | livewire/livewire | Public storefront (listing, cart, checkout, customer auth) built with Livewire, UI built from `blade-ui` layouts (`blade-ui::layouts.container`/`left-sidebar`) and `x-ui::layout.icon`. Pulls in almost the whole graph — pick this last. |

## Frontend (Vite-aliased) packages

All live under `resources/js/packages/`, are resolved via the `jsPackage()` alias helper in `vite.config.js` (and mirrored in `tsconfig.json` `compilerOptions.paths`), and are **not** real installed npm packages — no workspaces, their `package.json` is metadata only. Import them by alias, never by npm name.

| Package | Alias | npm deps | Depends on (aliases) | Purpose |
| --- | --- | --- | --- | --- |
| ts-menu | `@menu` | *(none)* | *(none)* | Menu registry (`menuRegistry`) that every `*MenuBuilder` registers into. Base package. |
| vue-admin | `@admin` | class-variance-authority, clsx, tailwind-merge, @vueuse/core | @menu, @user | Admin UI kit: layout (`AdminLayout`/`AppSidebar`/`AppHeader`), full form/table/dialog component set, `AdminMenuBuilder`, `DashboardRegistry`, admin router base. Every other admin `vue-*` package depends on this for layout + form components. |
| vue-user | `@user` | axios, lucide-vue-next | @admin, @menu | Auth, user/user-group/permission management, route guards (`authGuard`), permission composables/directives, and the shared `apiClient` other packages reuse. |
| vue-language | `@language` | lucide-vue-next | @admin, @menu, @user | Language/locale CRUD views + menu builder. |
| vue-address | `@address` | lucide-vue-next | @admin, @menu, @user, @language | Country/city CRUD + address input/select components. |
| vue-currency | `@currency` | lucide-vue-next | @admin, @menu, @user | Currency/exchange-rate CRUD. |
| vue-customer | `@customer` | lucide-vue-next | @admin, @menu, @user, @address | Customer/customer-group CRUD, `CustomerSelect` component. |
| vue-setting | `@setting` | lucide-vue-next | @admin, @menu, @user, @media | Settings form UI. |
| vue-media | `@media` | lucide-vue-next | @admin, @menu, @user | Media library browser + `MediaFilePicker`. |
| vue-product | `@product` | lucide-vue-next | @admin, @menu, @user, @currency, @language, @media | Product catalog CRUD (categories, units, custom fields, images). |
| vue-stock | `@stock` | lucide-vue-next | @admin, @menu, @user, @product | Warehouse/inventory/stock-movement CRUD. |
| vue-order | `@order` | lucide-vue-next | @admin, @menu, @user, @customer, @language | Order/payment/shipping CRUD + `orderDashboardBuilder`. |

## Assembling a new project from these packages

1. **Pick backend packages** by domain need, then pull in everything in their "Depends on" chain (the table above already expands one level — check the picked package's own `composer.json` `require` for the full transitive set). `menu`, `blade-ui`, `admin`, `contact` have no internal deps and are always safe to add alone.
2. Copy the chosen `packages/<name>/` dirs into the new project's `packages/` (git-ignored path-repo dir, see `.ai/rules/packages.md` for why — each has its own separate git history).
3. In the new project's root `composer.json`: add the `{"type":"path","url":"packages/*"}` repository and `"istvanmolitor/<name>": "*@dev"` for each chosen package, then `composer install`.
4. Copy the matching `resources/js/packages/<vue-name>/` dirs (and `ts-menu`, always needed if any admin UI is used) into the new project.
5. In `vite.config.js`: add a `jsPackage()` alias entry for each copied frontend package (`resolve.alias`). Mirror the same paths in `tsconfig.json` `compilerOptions.paths`.
6. In root `package.json`: add the union of each copied package's own `package.json` `dependencies` (npm libs actually imported — check the table above) plus `peerDependencies` (`vue`, `vue-router`).
7. Wire the picked packages into `resources/js/admin.js` following the existing pattern: spread each package's route array into the router, `menuRegistry.register(...)` each `*MenuBuilder`, `dashboardRegistry.register(...)` any dashboard builders exported. See `.ai/rules/js.md` for why this lives in `admin.js` and not `app.js`.
8. The picked backend package's own Blade shell view (e.g. `packages/admin/resources/views/admin.blade.php`) is what serves the SPA — it must `@vite(['resources/css/app.css', 'resources/js/admin.js'])`, not `app.js`.
