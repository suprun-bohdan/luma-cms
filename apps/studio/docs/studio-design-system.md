# Luma Studio Design System

Formal design contract for the Luma CMS admin interface (`apps/studio`).

## Principles

- **Tailwind-first** — utility classes in React components for layout and spacing
- **SCSS tokens** — source of truth in `src/styles/_tokens.scss`
- **CSS custom properties** — `:root` hooks in `src/styles/_theme.scss` for future white-label and dark mode
- **Custom UI kit** — shared primitives in `src/shared/components/` and `src/shared/layout/`
- **No AdminLTE / Bootstrap / shadcn** as dependencies (AdminLTE may be used as workspace reference only)

## Admin theme contract

Future-facing hooks (not all implemented yet):

```typescript
type AdminTheme = {
  mode: 'light' | 'dark'           // future
  density: 'comfortable' | 'compact' // future
  brand: {
    title: string                  // future white-label
    logoUrl?: string               // future
    accentColor?: string           // future via CSS vars
  }
}
```

### Layout slots

| Slot | Component | Use when |
|------|-----------|----------|
| Shell | `AdminShell` | Every authenticated page |
| Sidebar | `AppSidebar` | Primary navigation |
| Top bar | `TopBar` | Global actions / publish bar (optional) |
| Page header | `PageHeader` | Title, description, breadcrumbs, actions |
| Body | `PageBody` | Main content area |
| Section | `PageSection` | Grouped form blocks |
| List scaffold | `ListPage` | Index pages with table or grid |
| Form scaffold | `FormPage` | Create/edit pages |
| Editor workspace | `SplitPane` + `PreviewPane` + `SettingsPanel` | Visual editing, preview, inspector |

## Tokens

SCSS variables live in `src/styles/_tokens.scss`. CSS custom properties are prefixed with `--luma-`.

| Category | SCSS examples | CSS var examples |
|----------|---------------|------------------|
| Colors | `$color-primary`, `$color-danger` | `--luma-color-primary` |
| Typography | `$text-sm`, `$font-mono` | `--luma-text-sm` |
| Spacing | `$space-4`, `$space-6` | `--luma-space-4` |
| Radius | `$radius-md`, `$radius-lg` | `--luma-radius-md` |
| Layout | `$sidebar-width`, `$content-max-width` | `--luma-sidebar-width` |
| Z-index | `$z-modal`, `$z-toast` | `--luma-z-modal` |

Load tokens in component SCSS:

```scss
@use '@styles/tokens' as *;
```

## Layout primitives

Import from `shared/layout`:

```typescript
import { AdminShell, ListPage, SplitPane } from '../../shared/layout'
```

### AdminShell

Root authenticated layout: sidebar + main content. `AppShell` is a backward-compatible alias.

### ListPage

Standard list page: `PageHeader` + loading/error/empty slots + children (usually `Table`).

### FormPage

Standard form page: `PageHeader` + optional breadcrumbs + `Card` body.

### SplitPane

Multi-column workspace for editors. Props: `left`, `center?`, `right?`. No drag resize in MVP.

### PreviewPane / SettingsPanel

Scrollable preview container and fixed-width inspector panel for future visual editing.

## UI primitives

Import from `shared/components`:

| Component | Variants / notes |
|-----------|------------------|
| `Button` | `primary`, `secondary`, `danger`, `ghost` |
| `Input`, `Textarea`, `Select` | Label + error; use for all forms |
| `Checkbox` | Labeled accessible checkbox |
| `Fieldset` | Grouped fields with legend |
| `Table` | `Table`, `TableHead`, `TableBody`, `TableRow`, `TableCell` |
| `Card` | Content panel |
| `Badge` | Status tones |
| `Breadcrumbs` | Navigation trail |
| `EmptyState`, `LoadingState`, `ErrorAlert` | Feedback |
| `ConfirmDialog` | Destructive action confirmation |

### Planned (not in MVP)

Modal (beyond ConfirmDialog), Drawer, Toast, Tabs — document only until needed.

## Migration rules

1. **New pages** must use `ListPage` or `FormPage` scaffolds.
2. **Forms** must use `Input`, `Textarea`, `Select`, `Checkbox` — no raw `<input>` with inline Tailwind.
3. **Tables** must use `Table` primitives — no duplicated `<table className="...">` markup.
4. **Business logic** stays in features; layout/UI stays in `shared/`.
5. **Do not** import AdminLTE CSS/JS or Bootstrap into Studio.

## Reference inspiration

Workspace folder `Admin LTE/AdminLTE/` (outer repo, read-only) may inform sidebar, table, and dashboard information architecture. Do not copy HTML/CSS structure into product code.

## Non-goals (this phase)

- Dark/light mode implementation
- White-label branding UI
- shadcn / Bootstrap / AdminLTE dependency
- Full redesign of every existing page
- Visual page builder
