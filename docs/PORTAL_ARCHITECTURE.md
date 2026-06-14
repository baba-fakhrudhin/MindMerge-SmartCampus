# MindMerge SmartCampus — Multi-Role Portal Architecture

## Overview

MindMerge transforms from a single Admin ERP into four distinct role-based portals while preserving existing business logic. Admin CRUD modules remain at the project root; role portals live under dedicated folders with shared services handling data access.

```
MindMerge SmartCampus/
├── admin/                  # Administrator ERP entry
│   └── dashboard/
├── teacher/                # Teacher ERP
│   ├── dashboard/
│   ├── students/
│   ├── attendance/
│   ├── my-attendance/
│   ├── timetable/
│   ├── notifications/
│   ├── exams/
│   ├── reports/
│   └── profile/
├── student/                # Student ERP
│   ├── dashboard/
│   ├── attendance/
│   ├── timetable/
│   ├── results/
│   ├── exams/
│   ├── notifications/
│   ├── digital-id/
│   └── profile/
├── parent/                 # Parent ERP
│   ├── dashboard/
│   ├── children/
│   ├── attendance/
│   ├── results/
│   ├── notifications/
│   └── profile/
├── shared/                 # Reusable services (no UI duplication)
│   ├── helpers/
│   │   └── portal.php
│   └── services/
│       ├── AdminDashboardService.php
│       ├── TeacherDashboardService.php
│       ├── StudentDashboardService.php
│       ├── ParentDashboardService.php
│       ├── TeacherScopeService.php
│       └── ProfileService.php
├── classes/                # Admin modules (unchanged paths)
├── students/
├── teachers/
├── attendance/             # Unified attendance hub
├── notifications/
├── config/
│   ├── auth.php
│   └── permissions.php     # Role menus + route guards
└── partials/
    ├── sidebar.php           # Role-aware grouped navigation
    └── topbar.php
```

---

## Routing Strategy

### Login Redirection

After successful authentication, users are redirected by role:

| Role    | Destination                          |
|---------|--------------------------------------|
| admin   | `admin/dashboard/index.php`          |
| teacher | `teacher/dashboard/index.php`        |
| student | `student/dashboard/index.php`        |
| parent  | `parent/dashboard/index.php`         |

Legacy paths (`dashboard/index.php`, root `index.php`) redirect to the role home via `portal_redirect_home()`.

### URL Model

File-based routing (no front controller). All URLs use `BASE_URL` from `config/constants.php`.

| Portal  | Pattern                          | Example                              |
|---------|----------------------------------|--------------------------------------|
| Admin   | Root modules + `admin/dashboard` | `/classes/index.php`                 |
| Teacher | `teacher/{module}/`              | `/teacher/attendance/index.php`      |
| Student | `student/{module}/`              | `/student/digital-id/index.php`      |
| Parent  | `parent/{module}/`               | `/parent/children/index.php`         |

### Permission Route Guards

`permission_resolve_route()` in `config/permissions.php` maps URIs to `module.action` pairs. Portal paths are registered alongside existing admin routes. Unauthorized direct URL access redirects to `settings/access-denied.php`.

---

## Shared Services Strategy

Business logic lives in `shared/services/`. Portal pages are thin controllers: auth → service → render.

| Service                    | Responsibility                                      |
|----------------------------|-----------------------------------------------------|
| `AdminDashboardService`    | Institution stats, charts, insights                 |
| `TeacherDashboardService`  | Daily console: schedule, attendance tasks           |
| `StudentDashboardService`  | Personal academic overview                          |
| `ParentDashboardService`   | Child monitoring aggregates                         |
| `TeacherScopeService`      | Assigned classes/sections/students for teachers     |
| `ProfileService`           | Role profile data + print layouts                   |

### Rules

1. **No duplicate SQL** — portal pages call services, not inline queries (except trivial one-offs).
2. **Admin modules unchanged** — `classes/`, `students/`, etc. remain the source of truth for CRUD.
3. **Teacher scope** — `TeacherScopeService` filters data by `teacher_assignments`.
4. **Parent scope** — filtered by `parents.student_id` linkage.
5. **Student scope** — filtered by logged-in student's own record.

---

## Dashboard Strategy

Each role receives a purpose-built dashboard answering role-specific questions.

### Admin Dashboard (`admin/dashboard/index.php`)

**Answers:** How many students/teachers/classes/sections? Attendance health? Notifications? Exams?

- **Cards:** Totals, attendance rates, unread notifications, upcoming exams
- **Charts:** Monthly attendance, class comparison, teacher attendance trend, student growth
- **Insights:** Best/lowest class, critical students/teachers, pending sessions
- **Quick Actions:** Add student, add teacher, create timetable, create exam, send notification

### Teacher Dashboard (`teacher/dashboard/index.php`)

**Answers:** What do I teach today? What attendance is pending?

- **Greeting:** Good Morning, {Name}
- **Cards:** Today's classes, pending/completed attendance, assigned students, notifications
- **Charts:** Attendance marking trend, student attendance trend
- **Priority:** Attendance → Timetable → Students → Notifications

### Student Dashboard (`student/dashboard/index.php`)

**Answers:** How is my attendance? What's coming up?

- **Cards:** Attendance %, subjects, upcoming exams, notifications
- **Charts:** Attendance trend, academic trend (placeholder until exams module)
- **Priority:** Attendance → Results → Timetable → Exams

### Parent Dashboard (`parent/dashboard/index.php`)

**Answers:** How are my children performing?

- **Cards:** Children count, average attendance/performance, notifications
- **Charts:** Attendance trend, performance trend
- **Priority:** Attendance monitoring → Academic monitoring → Notifications

---

## Sidebar Strategy

Navigation is role-aware and permission-filtered via `permission_portal_menu($role)` in `config/permissions.php`.

### Admin Sidebar (grouped)

```
GENERAL        → Profile, Dashboard
ACADEMICS      → Classes, Sections, Students, Teachers
SCHEDULING     → Schedules, Timetables
OPERATIONS     → Attendance (unified hub), Notifications, Exams
ADMINISTRATION → Permissions
```

**Teacher Attendance** is NOT a separate sidebar item. It lives inside the Attendance module hub:

```
Attendance
├── Student Attendance  → attendance/mark.php, attendance/report.php
├── Teacher Attendance  → attendance/teacher/
├── Reports             → attendance/report.php
└── Analytics           → attendance/index.php
```

### Teacher Sidebar

```
GENERAL     → Profile, Dashboard
ACADEMICS   → Students (assigned only)
SCHEDULING  → My Timetable
OPERATIONS  → Student Attendance, My Attendance, Notifications, Exams, Reports
```

### Student Sidebar

```
GENERAL     → Profile, Dashboard
ACADEMICS   → Results
SCHEDULING  → Timetable
OPERATIONS  → Attendance, Notifications, Exams
UTILITIES   → Digital ID
```

### Parent Sidebar

```
GENERAL     → Profile, Dashboard
ACADEMICS   → Children, Results
OPERATIONS  → Attendance, Notifications
```

### Visibility Rules

1. `canView(module)` — hide sidebar item
2. `canCreate/canEdit/canDelete` — hide action buttons on pages
3. `permission_guard_request()` — block direct URL access
4. Profile is always visible (`always: true`)

---

## Attendance Integration

The Attendance module is unified under `attendance/index.php` as a hub with sub-modules:

| Sub-module           | Path                        | Permission Module      |
|----------------------|-----------------------------|------------------------|
| Student Attendance   | `attendance/mark.php`       | `attendance`           |
| Teacher Attendance   | `attendance/teacher/`       | `teacher_attendance`   |
| Reports              | `attendance/report.php`     | `attendance.view`      |
| Analytics            | `attendance/index.php`      | `attendance.view`      |

Role-specific attendance views:

| Role    | Portal Path                    | Scope                |
|---------|--------------------------------|----------------------|
| Admin   | `attendance/`                  | Institution-wide     |
| Teacher | `teacher/attendance/`          | Assigned classes     |
| Student | `student/attendance/`          | Own records          |
| Parent  | `parent/attendance/`           | Linked children      |

---

## Profile System

All roles share profile features via `ProfileService`:

- Profile information view/edit
- Change password
- Theme preferences (via `common.js` localStorage)
- **Print Profile** — `*/profile/print.php` with `@media print` layout

---

## CSS & JS Reuse

All portals load the existing design system:

- `assets/css/global.css` — variables, typography, dark mode
- `assets/css/layout.css` — app shell, sidebar, topbar
- `assets/css/components.css` — cards, tables, buttons
- `assets/css/portals.css` — portal dashboards, charts, print, digital ID
- `assets/js/common.js` — sidebar toggle, theme, table sort
- Chart.js CDN for dashboard charts (same pattern as `attendance/report.php`)

---

## Future: Unified Search

Search scopes are designed per role (not yet implemented):

| Role    | Search Scope                    |
|---------|---------------------------------|
| Admin   | Students, teachers, classes     |
| Teacher | Assigned students             |
| Student | Own academic records            |
| Parent  | Child records                   |

---

## Migration Path

1. Existing admin workflows continue at root module paths.
2. Login redirects to role portals; legacy `dashboard/` redirects automatically.
3. Permission tables unchanged; portal routes added to `permission_resolve_route()`.
4. No database schema changes required for Phase 2.
