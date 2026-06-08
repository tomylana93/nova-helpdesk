# Frontend Core

- Pages in `resources/js/pages`; shared layouts in `resources/js/layouts`; reusable components in `resources/js/components`; UI primitives under `resources/js/components/ui`.
- `resources/js/app.ts` uses `createInertiaApp` with route-name-based layout selection: `auth/*` → `AuthLayout`, `settings/*` → `[AppLayout, SettingsLayout]`, default → `AppLayout`.
- Auth layout variants: `AuthCardLayout`, `AuthSimpleLayout`, `AuthSplitLayout` selected by `SiteAuthLayout` enum value from backend.
- Shared Inertia props: `auth.user`, `name`, `locale`, `style` (theme, font, layout, etc.), `branding` (logo/icon/favicon URLs), `sidebarOpen`.
- Navigation: `NavMain` renders `NavGroup[]` items with `Link` and active state via `useCurrentUrl`; sidebar state from cookie.
- Reusable components: `AppBrand`, `AppHeader`, `AppSidebar`, `NavUser`, `UserAvatar`, `UserInfo`, `UserStatusBadge`, `PageWrapper`, `Uploader`, `TableSkeleton`.
- Composables: `useAppearance` (theme/dark mode), `useCurrentUrl` (active nav), `useDataTableState` (table query state), `useStyleSettings` (CSS variable injection from settings), `useTrans` (translations), `useInitials`.
- Tailwind v4 configured in CSS via `resources/css/app.css` with `@theme inline`, CSS variables, `dark` custom variant, compatibility border layer, and Instrument Sans via Vite font config.
- Prettier tailwind plugin knows `clsx`, `cn`, `cva` as class functions and uses `resources/css/app.css` as stylesheet.
- Generated Wayfinder code in `resources/js/actions/**` and `resources/js/routes/**`; prefer generated imports over hardcoded URLs. Do not hand-edit generated files.
- Read `mem:frontend/datatable` for deferred data table system.
