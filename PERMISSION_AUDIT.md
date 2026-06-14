# MindMerge SmartCampus Permission Audit

Audit date: 2026-06-13

## Current Permission Model

Permissions are defined in `database/migrations/permissions_system.sql` and implemented in `config/permissions.php`.

The current API is:

- `canView($module)`
- `canCreate($module)`
- `canEdit($module)`
- `canDelete($module)`
- `requirePermission($module, $action)`
- `permission_guard_request($conn)`

Admin bypasses all permission checks. Non-admin roles use `user_permissions` overrides first, then `role_permissions`.

## Critical Gaps

### P0 - Permission Tables Are Not In Main Schema

`database/schema.sql` does not create `permissions`, `role_permissions`, or `user_permissions`. It only adds a comment pointing to the migration.

Impact: a fresh database import lacks permission enforcement tables.

Current behavior: `permission_guard_request()` returns early when tables are missing, so all protected pages become accessible to any authenticated user.

Required fix: include permission tables in the baseline schema or create a mandatory migration runner. Guards should fail closed for non-admin users if permission tables are expected but unavailable.

### P0 - Self-Registration Can Create Admin Users

`auth/register.php` exposes Admin as a selectable role, and `auth/verify-otp.php` creates the account after OTP.

Impact: anyone who can receive OTP email can create an admin account.

Required fix: disable public admin registration. Admin creation must be installer-only or existing-admin-only.

### P0 - Teacher Attendance Management Is Available To Teacher Defaults

The seed grants teachers `teacher_attendance.view/create/edit`.

Requirement says teacher attendance must only be managed by Admin; teachers should only view their own records/reports.

Current risk:

- Teachers can be granted create/edit for teacher attendance by default.
- `teacher/my-attendance/index.php` redirects to admin teacher attendance dashboard.

Required fix: split `teacher_attendance_admin` from `my_teacher_attendance.view`, or enforce role/scope in routes and services.

### P0 - Assigned Faculty Enforcement Is Incomplete

Teacher portal attendance redirects to `attendance/mark.php`, which is the root marking page.

The page validates that a timetable entry exists for class/section/day/period, but does not enforce that the logged-in teacher is the assigned primary, co-primary, or lab faculty for that period.

Required fix: server-side validation must verify `timetable_entries.teacher_assignment_id` maps to the logged-in teacher before insert/update.

## Sidebar Visibility Gaps

- Sidebar visibility uses `canView()` in `permission_portal_menu_groups()`, which is good.
- Parent-child grouping is limited to regex `match` strings; there is no explicit parent menu map.
- Attendance is split into `attendance`, `teacher_student_attendance`, and `teacher_my_attendance`, which produces confusing navigation and duplicate attendance concepts.
- Dashboard module uses one permission key for all role dashboards.
- Student results and parent results use `exams.view`, even though results should become a separate module.

Required fix:

- Add explicit menu parent keys such as `attendance` for all attendance child URLs.
- Add collapsed/expanded state in `common.js` or a new existing-loaded sidebar script.
- Introduce `results` permission module before implementing results.

## Direct URL Blocking Gaps

The route map in `permission_resolve_route()` is broad and helpful, but gaps remain:

- Unknown routes return `null` and are allowed.
- Portal role checks are not guaranteed by permission route map alone. Some pages call `portal_require_role()`, but direct root modules rely only on generic permission.
- AJAX endpoints such as attendance getters are view-guarded but not scoped by teacher/student/parent context.
- Teacher portal routes map `teacher/attendance/` to `attendance.view`, while actual marking occurs after redirect to `attendance/mark.php`, which requires `attendance.create`. This works only because default teacher permissions grant create.

Required fix:

- Every portal page should call both `portal_require_role()` and `requirePermission()`.
- Every AJAX endpoint should apply role scope, not just module action.
- Unknown authenticated routes under protected module folders should fail closed.

## Button Visibility Gaps

Many list pages render action buttons unconditionally:

- `classes/index.php`: Add/Edit/Delete always visible.
- Similar pattern exists in sections, students, teachers, subjects, teacher assignments, period templates, periods, and timetables.
- Some pages check `canCreate()` in dashboard quick actions, but CRUD modules are inconsistent.

Impact: direct backend guards may block later, but UI still exposes unauthorized actions and creates a broken UX.

Required fix:

- Wrap Add buttons in `canCreate(module)`.
- Wrap Edit buttons in `canEdit(module)`.
- Wrap Delete buttons in `canDelete(module)`.
- Hide or disable action columns when no actions are available.

## CRUD Restriction Gaps

- Backend action restrictions mostly rely on route mapping rather than local `requirePermission()` calls.
- GET deletes are used across modules. Even with delete permission, this is unsafe and should move to POST with CSRF.
- Root `profile/index.php` checks `canEdit('profile')` only on POST, but edit UI is still shown to users without edit permission.

Required fix:

- Add local `requirePermission()` to all add/edit/delete entry files.
- Convert deletes to POST.
- Add CSRF tokens for every mutating request.
- Render profile edit form only when `canEdit('profile')` is true.

## Role-Specific Findings

### Admin

Admin currently bypasses all checks. That is acceptable, but admin account creation must be controlled.

### Teacher

Teacher should be allowed to:

- View dashboard/profile.
- View assigned students.
- View timetable.
- Mark student attendance only for assigned timetable periods.
- View own teacher attendance.
- Enter marks later under Results/Exams permissions.

Current gaps:

- Can manage teacher attendance by default.
- Can access root attendance marking if granted `attendance.create`.
- Assigned-period validation is missing server-side.
- Teacher reports redirect to root attendance report, which can expose institution-wide attendance unless filters are scoped.

### Student

Student should be allowed to:

- View dashboard/profile.
- View own attendance.
- View own timetable.
- View own results.
- View digital ID.

Current gaps:

- Student result uses `exams.view` permission.
- Self-registered students may have missing `class_id` and `section_id`, breaking scoped pages.
- QR data is not permission-backed by verification page yet.

### Parent

Parent should be read-only.

Current gaps:

- Parent profile module links to root edit profile and may allow edit if profile edit permission is granted.
- Parent child linkage supports one `student_id` code per parent row, not robust multi-child normalized access.
- Parent dashboard notification count is broken by incorrect column name.

## Recommended Permission Modules

Add or normalize these module keys:

- `results`
- `exams`
- `transport`
- `student_attendance`
- `teacher_attendance_admin`
- `my_teacher_attendance`
- `qr_verification`
- `reports`

Avoid using `exams.view` as a proxy for results.

## Required Acceptance Checklist

- Sidebar hides unauthorized modules.
- Direct URLs deny unauthorized access.
- Buttons hide unauthorized actions.
- POST handlers enforce permissions locally.
- AJAX endpoints enforce scope.
- Teacher cannot create, edit, or delete teacher attendance.
- Teacher can only mark assigned timetable periods.
- Student cannot access admin or teacher URLs.
- Parent cannot mutate ERP records.
- Permission tables are guaranteed in every install.
