# Results Module Plan

## Build Before Exams

Results should be built before Exams so marks, grading, GPA, publishing, and portal display rules are stable before exam scheduling is added.

## Database Tables

### `grading_system`

- `grading_id`
- `grade_name`
- `min_marks`
- `max_marks`
- `grade_point`
- `status`

### `results`

- `result_id`
- `class_id`
- `section_id`
- `academic_year`
- `semester`
- `result_type`
- `status` draft/published/archived
- `created_by`
- `published_at`
- timestamps

### `result_entries`

- `entry_id`
- `result_id`
- `student_id`
- `subject_id`
- `teacher_id`
- `internal_marks`
- `external_marks`
- `lab_marks`
- `attendance_marks`
- `total_marks`
- `grade`
- `grade_point`
- timestamps

## Admin Features

- Create result set.
- Assign subjects/teachers.
- Review marks.
- Publish/unpublish results.
- Generate reports.
- Export class/section/student result sheets.

## Teacher Features

- Enter marks only for assigned subjects/classes.
- Save draft marks.
- Submit marks for admin review.
- View submitted marks.

## Student Features

- View published results only.
- Download result sheet.
- See subject-wise GPA, semester GPA, and overall GPA.

## Parent Features

- View published child results.
- Download child result sheet.

## Permission Modules

Add `results.view`, `results.create`, `results.edit`, `results.delete`, and likely `results.publish`.

Because current permission actions are only view/create/edit/delete, either:

- Treat publish as edit initially, or
- Add a fifth action `publish` before implementation.

Recommendation: add `publish` for ERP clarity.
