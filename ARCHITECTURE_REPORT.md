# MindMerge SmartCampus Architecture Report

Audit date: 2026-06-13

## Baseline

MindMerge is a modular file-based PHP ERP. The current architecture is workable for a small ERP, but it needs stricter boundaries before adding Results, Exams, Transport, and QR verification.

## Recommended Target Architecture

### Layers

1. Page controller
   - Includes auth.
   - Calls role guard.
   - Calls permission guard.
   - Reads request data.
   - Calls service.
   - Renders view.

2. Service layer
   - Owns business rules.
   - Owns role scoping.
   - Owns write validation.
   - Returns arrays for views.

3. Repository/query helpers
   - Optional next step.
   - Useful for repeated class/section/student/timetable queries.

4. Shared UI partials
   - Sidebar/topbar.
   - Alerts.
   - Empty states.
   - Filter bars.
   - Timetable grid.
   - Report/export controls.

## Portal Strategy

Keep root modules as admin source of truth. Role portals should be thin and scoped:

- `teacher/`: assigned students, assigned attendance periods, timetable, own attendance, marks entry later.
- `student/`: own timetable, attendance, results, digital ID.
- `parent/`: linked child data only.

Avoid creating duplicate CRUD implementations in portals.

## Profile Strategy

Root `profile/index.php` remains the shared edit/settings/avatar implementation.

Portal profile pages should only do:

- Load role-specific profile data.
- Show role-specific read-only summary.
- Link to shared edit profile if allowed.
- Print profile.

Move duplicated display markup into a shared profile renderer or partial.

## Database Strategy

Before feature expansion:

- Merge mandatory migrations into a schema baseline or add a migration runner.
- Add missing FKs and indexes.
- Normalize parent-child linkage.
- Add unique constraints for timetable entries.
- Remove duplicate unique keys in schema dumps.

## Permission Strategy

Permissions must be enforced at four levels:

- Sidebar visibility.
- Button visibility.
- Direct URL guard.
- Service-level business rule and scope validation.

Route guards alone are not enough for ERP-grade access control.

## Implementation Rule

No new module should be built only as pages. Every new module should include:

- Schema/migration.
- Permissions.
- Services.
- Admin UI.
- Portal read/write scope.
- Reports/exports where required.
- Tests or manual verification checklist.
