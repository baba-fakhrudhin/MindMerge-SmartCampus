# MindMerge SmartCampus ERP Audit

Audit date: 2026-06-13

## Scope Reviewed

- Core PHP modules: admin dashboard, classes, sections, students, teachers, subjects, teacher assignments, periods, period templates, timetables, attendance, notifications, profile, exams placeholder, portal dashboards.
- Portal folders: `admin/`, `teacher/`, `student/`, `parent/`, plus legacy/root modules.
- Database files: `database/schema.sql`, `database/migrations/permissions_system.sql`, `database/migrations/notifications_improvements.sql`.
- CSS architecture: `assets/css/global.css`, `assets/css/layout.css`, `assets/css/components.css`, `assets/css/portals.css`.
- JS architecture: `assets/js/common.js`, `assets/js/notifications.js`; requested `assets/js/sidebar.js` is missing.
- Shared services/helpers: `shared/services/*`, `shared/helpers/portal.php`.
- Authentication, role routing, and route-level permission resolution.

## Current Architecture

MindMerge is a file-based PHP 8.x application using MariaDB/MySQL and `mysqli`. There is no front controller or framework. Pages include `config/auth.php`, which starts the session, loads `config/db.php`, loads permissions, and calls `permission_guard_request()`.

The application now has a hybrid architecture:

- Admin CRUD modules live at root paths such as `classes/`, `students/`, `teachers/`, `attendance/`, `timetables/`, and `notifications/`.
- Role portals live under `teacher/`, `student/`, and `parent/`.
- Shared services exist for dashboard/profile/scope logic under `shared/services/`.
- Shared UI shell exists in `partials/sidebar.php` and `partials/topbar.php`.
- Role routing is handled by `shared/helpers/portal.php` and `permission_role_dashboard_url()`.

## Module Findings

### Admin Dashboard

- `admin/dashboard/index.php` uses `AdminDashboardService`.
- Stats and charts are centralized, but several values are placeholders, especially exams and critical teachers.
- `AdminDashboardService::getInsights()` uses a grouped scalar query for critical students; because `scalar()` returns the first row only, this can undercount students below threshold.

### Classes

- CRUD exists in `classes/`.
- Route guards map add/edit/delete to permission actions.
- UI buttons in `classes/index.php` always show Add/Edit/Delete instead of checking `canCreate()`, `canEdit()`, and `canDelete()`.
- Delete is performed by GET URL, not POST.

### Sections

- CRUD exists in `sections/`.
- Same UI/action concerns as classes.
- Sections depend on classes and are deleted with FK cascade from classes, but manual in-use checks are inconsistent across modules.

### Students

- Admin CRUD exists in `students/`.
- `students/add.php` correctly stores `class_id` and `section_id`.
- `auth/verify-otp.php` student self-registration stores `class_name` and `section_name` but does not store `class_id` and `section_id`; this breaks dashboard, timetable, attendance, and scoping logic for self-registered students.
- Student ID generation differs between admin add (`STU00001`) and self-registration (`STU0001` style), creating inconsistent identifier formats.

### Teachers

- Admin CRUD exists in `teachers/`.
- Teacher assignments are split into `teacher_assignments/`.
- Teacher dashboard and teacher portal pages use `TeacherScopeService`, but attendance marking still does not fully enforce timetable-period assignment server-side.

### Attendance

- Admin student attendance exists under `attendance/`.
- Teacher attendance exists under `attendance/teacher/`.
- Teacher portal redirects `teacher/attendance/index.php` to root `attendance/mark.php`.
- Teacher "My Attendance" redirects to `attendance/teacher/index.php`, which is the admin teacher-attendance management dashboard rather than a teacher-scoped read-only view.
- Student attendance supports daily and period modes, but the new requirement is period-wise based on timetable only.
- Attendance stores `period_id`, `subject_id`, and `teacher_assignment_id`, but not `teacher_id`, `teacher_name`, or `attendance_time`.
- Teacher assignment validation checks that a timetable entry exists, but it does not enforce that the logged-in teacher is assigned to the selected period when used from the teacher portal.

### Notifications

- Core notification tables and UI exist.
- Targeting helper exists in `config/notifications.php`.
- `notifications_improvements.sql` has indexes and optional source columns, but these are not present in the main schema dump.
- Parent dashboard uses `n.notification_id`, but the `notifications` table primary key is `id`; this breaks unread notification counts for parents.
- `notifications/reports.php` is empty.

### Permissions

- Permission logic exists in `config/permissions.php`.
- Permission tables are not included as created tables in `database/schema.sql`; the schema only points to the migration at the end.
- If permission tables are missing, guards return early and the whole app becomes permissive.
- See `PERMISSION_AUDIT.md` for detailed gaps.

### Profile

- Root `profile/index.php` contains the actual edit/avatar/profile UI.
- Portal profile pages under `teacher/profile/`, `student/profile/`, and `parent/profile/` are separate thin profile views with print links and edit links back to root profile.
- This partially follows reuse, but still duplicates profile display layouts instead of using one shared renderer/component.
- Profile upload permits SVG, which is dangerous for user-uploaded avatars.

### Timetables and Schedules

- Admin timetable renderer in `timetables/view.php` is a professional grid with periods, subject boxes, teacher names, rooms, colors, and non-teaching period handling.
- Teacher and student timetable views are plain tables and do not reuse the admin renderer.
- Period templates and periods exist and support schedule structure.

### Results

- Student and parent result pages exist only as placeholders.
- No database tables exist for `results`, `result_entries`, or `grading_system`.
- No GPA, publishing, teacher marks entry, or reports flow exists.

### Exams

- `exams/index.php` is a placeholder only.
- No exam tables exist for exam type, schedule, halls, hall allocation, invigilators, or marks integration.

### Bus/Transport

- No transport module exists.
- No tables exist for buses, drivers, routes, stops, route stops, or bus assignments.

### Digital ID and QR

- `student/digital-id/index.php` exists.
- QR currently uses an external QR API and encodes raw student ID plus name.
- There is no verification page and no signed/opaque token.
- QR verification would expose data without server-side validation if expanded as-is.

## Duplicate Functionality

- Portal-specific profile pages duplicate profile display concerns instead of reusing one shared profile view layer.
- Teacher attendance is exposed both as admin management under `attendance/teacher/` and teacher portal redirects under `teacher/my-attendance/`.
- Student attendance marking is reachable through root `attendance/mark.php` and teacher portal redirect, with insufficient distinction between admin and assigned faculty workflows.
- CSS has overlapping dashboard/card/table rules in `layout.css`, `components.css`, and `portals.css`.
- Notification unread logic is duplicated in dashboard services and notification helpers.

## Broken Functionality

- Parent dashboard unread notifications query references `n.notification_id`; actual column is `n.id`.
- `assets/js/sidebar.js` is referenced by `attendance/teacher/mark.php` but does not exist.
- Student self-registration omits `class_id` and `section_id`.
- Main schema does not include permission tables, so a fresh install from `schema.sql` lacks the active permission system.
- `notifications/reports.php`, result pages, and exam pages are placeholders or empty.
- Teacher "My Attendance" redirects to admin teacher attendance dashboard instead of a teacher-scoped read-only record list.

## Missing Features

- ERP-grade Results module.
- Exam scheduling, halls, invigilators, and teacher mark entry.
- Bus/transport module.
- QR verification page with privacy-safe verification tokens.
- Attendance analytics by class, section, subject, and teacher with export.
- Period-wise-only student attendance flow.
- Server-side assigned-faculty validation for attendance marking.
- Export to PDF, Excel, and CSV for attendance/results reports.
- Central timetable renderer reusable by admin, teacher, and student views.

## Security Issues

- No CSRF protection on mutating POST or GET delete actions.
- Destructive deletes use GET URLs across multiple modules.
- User self-registration allows choosing `admin`.
- Many SQL statements use string interpolation. Some are escaped, many are integer-cast, but the codebase is not consistently using prepared statements.
- `auth/verify-otp.php` inserts several session-sourced fields without escaping.
- SVG avatar uploads are allowed.
- File upload validation checks extension only, not MIME type or image decode.
- Permission guard fails open if permission tables are absent.
- Login has no rate limiting or account lockout.
- OTP has no expiry, resend control, or attempt limit.

## Database Inconsistencies

- Permission tables are migration-only and absent from the main schema.
- Notifications migration adds optional columns/indexes not reflected in `schema.sql`.
- Students store both normalized class/section IDs and denormalized names; self-registration only fills names.
- Repeated unique keys exist on `students.student_id`, `teachers.teacher_id`, and `parents.parent_id`.
- Attendance does not store `teacher_id`, `teacher_name`, or `attendance_time` as required for the redesigned flow.
- Timetable entries lack a unique constraint for timetable/day/period.
- `parents.student_id` links by student code instead of a normalized student row FK, limiting multi-child support.
- Results, exams, and transport schemas are absent.

## Recommended Phase Order

1. Normalize schema baseline and migration status.
2. Fix fail-open permission behavior and button/action gating.
3. Consolidate portal profile views around shared profile service/rendering.
4. Rebuild sidebar parent-child awareness and collapsed/expanded persistence.
5. Rebuild attendance with explicit admin, assigned faculty, student, and parent read-only flows.
6. Reuse timetable grid renderer across admin, teacher, and student views.
7. Add QR verification with signed opaque tokens.
8. Build Results before Exams.
9. Build Exams against Results architecture.
10. Build Transport module with future GPS fields.
