# MindMerge SmartCampus UI Audit

Audit date: 2026-06-13

## Current CSS Architecture

- `assets/css/global.css`: variables, reset, typography, layout utilities, dark mode base.
- `assets/css/layout.css`: app shell, sidebar, topbar, dark mode shell/table/form overrides.
- `assets/css/components.css`: cards, buttons, forms, tables, dashboard sections, permissions UI, access denied.
- `assets/css/portals.css`: portal sidebar labels, portal dashboards, charts, attendance hub, digital ID, print rules.

The design system has useful foundations, but component rules overlap heavily across files.

## Current JS Architecture

- `assets/js/common.js`: mobile sidebar open/close, theme localStorage, table sorting.
- `assets/js/notifications.js`: notification bell, dropdown, unread polling.
- `assets/js/sidebar.js`: requested in the blueprint and referenced by `attendance/teacher/mark.php`, but missing from the repository.

## UI Inconsistencies

- Cards use varying radius values: 12px, 14px, 16px, 18px, 20px, 24px, 26px.
- Page-specific inline CSS is widespread, causing inconsistent spacing, buttons, alerts, and layout behavior.
- Some tables use `.custom-table`, others use `.data-table`.
- Alerts are often inline styled instead of using `.alert`, `.alert-success`, `.alert-danger`, and `.alert-warning`.
- Empty states range from plain table rows to styled blocks.
- Dashboard cards mix `dashboard-card`, `stat-card`, `action-card`, `hub-card`, and page-specific card styles.
- Auth pages have large inline CSS and do not use a shared auth layout despite `assets/css/auth.css` existing.

## Portal Inconsistencies

- Admin timetable has a professional weekly grid.
- Teacher and student timetables are plain list tables.
- Teacher profile, student profile, and parent profile have different layouts and depth.
- Teacher "My Attendance" redirects into admin teacher attendance UI.
- Student and parent results are placeholders and not visually aligned with a real results module.

## Sidebar UX Issues

- Active state is regex-based and inconsistent for sub-pages.
- Attendance has multiple conceptual entries: root attendance, teacher student attendance, teacher my attendance, reports.
- There is no collapsed desktop mode.
- There is no persisted expanded/collapsed state.
- `common.js` only supports mobile open/close.
- `attendance/mark.php` manually overrides sidebar active state in page JavaScript, which is fragile.

## Dark Mode Issues

- Dark mode variables are defined in both `global.css` and `layout.css`.
- Dark mode card/table/form overrides are repeated in `layout.css` and `components.css`.
- Inline styles do not always adapt to dark mode.
- Some status colors and chart containers may have insufficient contrast.

## Mobile Responsiveness Issues

- Tables rely on horizontal scroll, which is acceptable for admin grids but not ideal for portal summaries.
- Timetable admin grid has `min-width:1100px`; teacher/student views avoid the grid but lose the professional timetable layout.
- Topbar can become cramped on mobile because notification, theme, profile, and logout all remain visible.
- Some buttons become full width globally at small screens, which can make compact action bars too tall.

## Charts and Reports

- Charts use Chart.js in dashboards, but chart styling and card sizing are inconsistent.
- Attendance analytics are incomplete.
- Export controls are not standardized.
- Reports pages need filter bars, summary cards, chart area, data table, and export actions as a shared pattern.

## Forms and Filters

- Filters are built ad hoc per page.
- Search and reset controls use inconsistent button styles.
- Form validation feedback is inconsistent.
- Required field markers are not standardized.
- Selects for class/section filtering are duplicated in multiple pages.

## Recommended Design System Direction

- Standardize card radius to 8px or the existing design token after approval. The current app uses larger radii, but the target ERP style should be calmer and denser.
- Keep `global.css` for tokens/reset/typography only.
- Keep `layout.css` for shell/sidebar/topbar only.
- Keep `components.css` for reusable buttons, cards, tables, forms, alerts, empty states, filters, badges.
- Keep `portals.css` only for role portal-specific layout variants and print/digital ID.
- Remove page-level inline CSS gradually by extracting reusable classes.

## Required UI Work Before Expansion

1. Rebuild sidebar with parent-child active matching and collapsed mode.
2. Create shared empty state component.
3. Create shared filter bar component styles.
4. Create shared report layout styles.
5. Create shared timetable grid renderer styles.
6. Normalize alerts.
7. Normalize buttons and icon-only actions.
8. Fix dark mode duplication and inline style leaks.
9. Make profile pages use one shared layout.
10. Create a consistent export action cluster for CSV/Excel/PDF.
