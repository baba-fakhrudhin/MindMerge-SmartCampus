<?php

class TeacherScopeService
{
    private mysqli $conn;
    private ?array $teacher = null;
    private ?array $assignments = null;

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $this->loadTeacher($user_id);
    }

    public function getTeacher(): ?array
    {
        return $this->teacher;
    }

    public function getTeacherId(): int
    {
        return (int) ($this->teacher['id'] ?? 0);
    }

    public function getAssignments(): array
    {
        if ($this->assignments !== null) {
            return $this->assignments;
        }

        $this->assignments = [];
        $tid = $this->getTeacherId();

        if ($tid <= 0) {
            return $this->assignments;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT ta.*, c.class_name, s.section_name, sub.subject_name, sub.subject_code
             FROM teacher_assignments ta
             INNER JOIN classes c ON c.class_id = ta.class_id
             INNER JOIN sections s ON s.section_id = ta.section_id
             INNER JOIN subjects sub ON sub.subject_id = ta.subject_id
             WHERE ta.teacher_id = '$tid'
             ORDER BY c.class_name, s.section_name, sub.subject_name"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $this->assignments[] = $row;
        }

        return $this->assignments;
    }

    public function getAssignedClassSectionPairs(): array
    {
        $pairs = [];

        foreach ($this->getAssignments() as $a) {
            $key = $a['class_id'] . '-' . $a['section_id'];
            $pairs[$key] = [
                'class_id'     => (int) $a['class_id'],
                'section_id'   => (int) $a['section_id'],
                'class_name'   => $a['class_name'],
                'section_name' => $a['section_name'],
            ];
        }

        return array_values($pairs);
    }

    public function getAssignedStudentCount(): int
    {
        $pairs = $this->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return 0;
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(st.class_id = {$p['class_id']} AND st.section_id = {$p['section_id']})";
        }

        $sql = "SELECT COUNT(*) FROM students st WHERE " . implode(' OR ', $conditions);

        $row = mysqli_fetch_row(mysqli_query($this->conn, $sql));

        return (int) ($row[0] ?? 0);
    }

    public function getAssignedStudents(string $search = ''): array
    {
        $pairs = $this->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return [];
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(st.class_id = {$p['class_id']} AND st.section_id = {$p['section_id']})";
        }

        $where = '(' . implode(' OR ', $conditions) . ')';

        if ($search !== '') {
            $esc = mysqli_real_escape_string($this->conn, $search);
            $where .= " AND (u.full_name LIKE '%$esc%' OR st.student_id LIKE '%$esc%')";
        }

        $students = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT st.*, u.full_name, u.email, u.phone, u.profile_photo,
                    c.class_name, s.section_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN classes c ON c.class_id = st.class_id
             INNER JOIN sections s ON s.section_id = st.section_id
             WHERE $where
             ORDER BY c.class_name, s.section_name, u.full_name"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $students[] = $row;
        }

        return $students;
    }

    public function canAccessClassSection(int $class_id, int $section_id): bool
    {
        foreach ($this->getAssignedClassSectionPairs() as $p) {
            if ((int) $p['class_id'] === $class_id && (int) $p['section_id'] === $section_id) {
                return true;
            }
        }

        return false;
    }

    public function getSubjects(): array
    {
        $subjects = [];

        foreach ($this->getAssignments() as $a) {
            $subjects[$a['subject_id']] = $a['subject_name'];
        }

        return $subjects;
    }

    public function canAccessTeacherAssignment(int $teacher_assignment_id): bool
    {
        if ($teacher_assignment_id <= 0) {
            return false;
        }

        foreach ($this->getAssignments() as $a) {
            if ((int) $a['assignment_id'] === $teacher_assignment_id) {
                return true;
            }
        }

        return false;
    }

    public function canMarkPeriodAttendance(
        int $class_id,
        int $section_id,
        int $period_id,
        string $day_of_week,
        int $teacher_assignment_id = 0
    ): bool {
        if (!$this->canAccessClassSection($class_id, $section_id)) {
            return false;
        }

        if ($teacher_assignment_id > 0) {
            return $this->canAccessTeacherAssignment($teacher_assignment_id);
        }

        $tid = $this->getTeacherId();

        if ($tid <= 0 || $period_id <= 0) {
            return false;
        }

        $day = mysqli_real_escape_string($this->conn, strtolower($day_of_week));
        $query = mysqli_query(
            $this->conn,
            "SELECT te.entry_id
             FROM timetable_entries te
             INNER JOIN timetables t ON t.timetable_id = te.timetable_id
             INNER JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             WHERE t.class_id = '$class_id'
               AND t.section_id = '$section_id'
               AND te.period_id = '$period_id'
               AND te.day_of_week = '$day'
               AND ta.teacher_id = '$tid'
             LIMIT 1"
        );

        return $query && mysqli_num_rows($query) > 0;
    }

    private function loadTeacher(int $user_id): void
    {
        $this->teacher = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT t.*, u.full_name, u.email, u.phone, u.profile_photo
             FROM teachers t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.user_id = '$user_id'
             LIMIT 1"
        ));
    }
}
