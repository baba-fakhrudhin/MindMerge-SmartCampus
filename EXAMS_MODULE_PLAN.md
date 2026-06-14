# Exams Module Plan

## Dependency

Build after Results architecture is approved. Exams should feed schedules and marks into the Results module rather than duplicating mark storage.

## Database Tables

Recommended:

- `exam_types`
- `exams`
- `exam_schedule`
- `exam_halls`
- `exam_hall_allocations`
- `exam_invigilators`

## Admin Features

- Create exam.
- Manage exam type.
- Build class/section/subject schedule.
- Allocate halls.
- Assign invigilators.
- Publish exam schedule.

## Teacher Features

- View assigned invigilation duties.
- View relevant exam schedules.
- Enter marks through Results/Marks entry workflow.

## Student Features

- View published exam schedule.
- See subject, date, time, room/hall.

## Parent Features

- View linked child exam schedule.

## Permission Modules

- `exams.view`
- `exams.create`
- `exams.edit`
- `exams.delete`
- Optional future action: `exams.publish`

## UI Pattern

Use the same report/filter/export design system as Results and Attendance:

- Filter bar.
- Schedule grid.
- Hall allocation table.
- Invigilator assignment table.
- Publish status badge.
