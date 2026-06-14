# MindMerge SmartCampus

## Stack

- PHP 8.x
- MariaDB
- XAMPP

## Architecture

- Admin ERP
- Teacher ERP
- Student ERP
- Parent ERP

## Existing Core Modules

- Dashboard
- Profile
- Classes
- Sections
- Students
- Teachers
- Attendance
- Notifications
- Timetables
- Permissions

## UI Architecture

Use only:

- assets/css/global.css
- assets/css/layout.css
- assets/css/components.css
- assets/css/portals.css

Reuse existing:

- dashboard-card
- dashboard-section
- quick-actions
- custom-table

Do not create duplicate UI systems.

## Rules

- Reuse existing modules
- Do not duplicate profile modules
- Admin manages teacher attendance
- Teachers manage student attendance
- All pages must respect permissions
- Preserve existing database data
- PHP 8.x compatible
- MariaDB compatible