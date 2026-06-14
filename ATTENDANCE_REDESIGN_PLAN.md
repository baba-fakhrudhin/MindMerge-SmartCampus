# Attendance Redesign Plan

## Goals

- One Attendance module with clear sub-areas:
  - Student Attendance
  - Teacher Attendance
  - Reports
  - Analytics
- Student attendance must be period-wise and timetable-based.
- Teacher attendance must be admin-managed only.
- Teachers can only view their own teacher attendance.
- Teachers can mark student attendance only for assigned timetable periods.

## Database Changes

Update `attendance` to support:

- `teacher_id`
- `teacher_name`
- `period_id`
- `subject_id`
- `teacher_assignment_id`
- `attendance_date`
- `attendance_time`
- `attendance_mode`, retained during transition but target should be `period`

Recommended constraints:

- Unique student attendance session by class, section, date, period.
- Unique attendance record by attendance session and student.
- FK from `attendance.teacher_assignment_id` to `teacher_assignments.assignment_id`.
- FK from `attendance.teacher_id` to `teachers.id`.

## Admin Flow

Admin can:

- Mark/edit/delete student attendance.
- Mark/edit/delete teacher attendance.
- View all reports and analytics.
- Export reports.

## Teacher Flow

Teacher can:

- See assigned periods from timetable.
- Mark attendance only for current or selected assigned period, depending policy.
- View own attendance records.
- View scoped reports for assigned classes/sections/subjects.

Teacher cannot:

- Mark own teacher attendance.
- Mark student attendance for unassigned periods.
- Edit/delete teacher attendance.
- Access institution-wide attendance reports.

## Student Flow

Student can:

- View own period-wise attendance.
- Filter by subject/date.
- See attendance percentage by subject and overall.

## Parent Flow

Parent can:

- View linked child period-wise attendance.
- See summary by child, subject, and month.

## Analytics

Build four report modes:

- Class attendance trends.
- Section attendance trends.
- Subject attendance trends.
- Teacher attendance trends.

Exports:

- CSV first.
- Excel next.
- PDF after report layout stabilizes.

## Refactor Steps

1. Add schema migration.
2. Create `AttendanceService`.
3. Create `TeacherAttendanceService`.
4. Replace teacher portal redirect with scoped teacher attendance UI.
5. Enforce assigned-faculty validation server-side.
6. Convert daily attendance to legacy read-only or migration path.
7. Add analytics and exports.
