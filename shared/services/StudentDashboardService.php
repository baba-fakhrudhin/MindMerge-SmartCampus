<?php

class StudentDashboardService
{
    private mysqli $conn;
    private ?array $student = null;

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $this->student = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT st.*, u.full_name, u.email, u.profile_photo,
                    c.class_name, s.section_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN classes c ON c.class_id = st.class_id
             INNER JOIN sections s ON s.section_id = st.section_id
             WHERE st.user_id = '$user_id'
             LIMIT 1"
        ));
    }

    public function getStudent(): ?array
    {
        return $this->student;
    }

    public function getStudentDbId(): int
    {
        return (int) ($this->student['id'] ?? 0);
    }

    public function getStats(): array
    {
        $sid = $this->getStudentDbId();
        $attendance_pct = 0;

        if ($sid > 0) {
            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid' AND status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid'"
            );
            $attendance_pct = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $unread = $this->scalar(
            "SELECT COUNT(DISTINCT n.id)
             FROM notifications n
             LEFT JOIN notification_reads nr
               ON nr.notification_id = n.id AND nr.user_id = '$uid'
             WHERE nr.read_id IS NULL"
        );

        $subjects = $this->scalar(
            "SELECT COUNT(DISTINCT te.subject_id)
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             WHERE tt.class_id = '" . (int) ($this->student['class_id'] ?? 0) . "'
               AND tt.section_id = '" . (int) ($this->student['section_id'] ?? 0) . "'"
        );

        return [
            'attendance_pct'      => $attendance_pct,
            'subjects_count'      => $subjects,
            'upcoming_exams'      => 0,
            'unread_notifications'=> $unread,
        ];
    }

    public function getAttendanceTrend(int $days = 30): array
    {
        $sid = $this->getStudentDbId();
        $labels = [];
        $values = [];

        if ($sid <= 0) {
            return ['labels' => $labels, 'values' => $values];
        }

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $row = mysqli_fetch_assoc(mysqli_query(
                $this->conn,
                "SELECT ar.status FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id = '$sid' AND a.attendance_date = '$date'
                 LIMIT 1"
            ));

            if (!$row) {
                $values[] = null;
            } else {
                $values[] = $row['status'] === 'absent' ? 0 : 100;
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getTodayTimetable(): array
    {
        $class_id   = (int) ($this->student['class_id'] ?? 0);
        $section_id = (int) ($this->student['section_id'] ?? 0);
        $day        = strtolower(date('l'));
        $items      = [];

        if ($class_id <= 0) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT te.*, p.period_name, p.start_time, p.end_time,
                    sub.subject_name, u.full_name AS teacher_name
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             INNER JOIN periods p ON p.period_id = te.period_id
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             LEFT JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE tt.class_id = '$class_id'
               AND tt.section_id = '$section_id'
               AND te.day_of_week = '$day'
             ORDER BY p.start_time ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getAttendanceStatus(): string
    {
        $pct = $this->getStats()['attendance_pct'];

        if ($pct >= 85) {
            return 'Excellent';
        }

        if ($pct >= 75) {
            return 'Good';
        }

        if ($pct >= 60) {
            return 'Needs Improvement';
        }

        return 'Critical';
    }

    public function getRecentAttendance(int $limit = 10): array
    {
        $sid = $this->getStudentDbId();
        $items = [];

        if ($sid <= 0) {
            return $items;
        }

        $query = mysqli_query(
            $this->conn,
            "SELECT a.attendance_date, ar.status, sub.subject_name, p.period_name
             FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             LEFT JOIN subjects sub ON sub.subject_id = a.subject_id
             LEFT JOIN periods p ON p.period_id = a.period_id
             WHERE ar.student_id = '$sid'
             ORDER BY a.attendance_date DESC
             LIMIT $limit"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    private function scalar(string $sql): int
    {
        $result = mysqli_query($this->conn, $sql);

        if (!$result) {
            return 0;
        }

        $row = mysqli_fetch_row($result);

        return (int) ($row[0] ?? 0);
    }
}
