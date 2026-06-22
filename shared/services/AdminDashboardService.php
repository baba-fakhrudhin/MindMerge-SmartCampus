<?php

require_once __DIR__ . '/../../config/notifications.php';

class AdminDashboardService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getStats(): array
    {
        $total_students = $this->scalar("SELECT COUNT(*) FROM students");
        $total_teachers = $this->scalar("SELECT COUNT(*) FROM teachers");
        $total_classes  = $this->scalar("SELECT COUNT(*) FROM classes WHERE status='active'");
        $total_sections = $this->scalar("SELECT COUNT(*) FROM sections WHERE status='active'");

        $student_rate = $this->attendanceRate(
            "SELECT COUNT(*) FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             WHERE ar.status != 'absent'"
        );

        $teacher_present = $this->scalar(
            "SELECT COUNT(*) FROM teacher_attendance
             WHERE status IN ('present','late','half_day')"
        );
        $teacher_total = $this->scalar("SELECT COUNT(*) FROM teacher_attendance");
        $teacher_rate = $teacher_total > 0
            ? round(($teacher_present / $teacher_total) * 100, 1)
            : 0;

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $context = notification_user_context($this->conn, $uid, 'admin');
        $unread = notification_unread_count($this->conn, $context);

        return [
            'total_students'         => $total_students,
            'total_teachers'         => $total_teachers,
            'total_classes'          => $total_classes,
            'total_sections'         => $total_sections,
            'attendance_rate'        => $student_rate,
            'teacher_attendance_rate'=> $teacher_rate,
            'unread_notifications'   => $unread,
            'upcoming_exams'         => $this->upcomingExamCount(),
        ];
    }

    public function getMonthlyAttendanceTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $month = $date->format('Y-m');
            $labels[] = $date->format('M Y');

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE DATE_FORMAT(a.attendance_date,'%Y-%m') = '$month'
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE DATE_FORMAT(a.attendance_date,'%Y-%m') = '$month'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getClassAttendanceComparison(): array
    {
        $labels = [];
        $values = [];

        $query = mysqli_query(
            $this->conn,
            "SELECT c.class_name,
                    COUNT(ar.record_id) AS total,
                    SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) AS present
             FROM attendance a
             INNER JOIN classes c ON c.class_id = a.class_id
             INNER JOIN attendance_records ar ON ar.attendance_id = a.attendance_id
             GROUP BY c.class_id, c.class_name
             ORDER BY c.class_name ASC
             LIMIT 10"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $labels[] = $row['class_name'];
            $rate = (int) $row['total'] > 0
                ? round(((int) $row['present'] / (int) $row['total']) * 100, 1)
                : 0;
            $values[] = $rate;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getTeacherAttendanceTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $month = $date->format('Y-m');
            $labels[] = $date->format('M Y');

            $present = $this->scalar(
                "SELECT COUNT(*) FROM teacher_attendance
                 WHERE DATE_FORMAT(attendance_date,'%Y-%m') = '$month'
                   AND status IN ('present','late','half_day')"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM teacher_attendance
                 WHERE DATE_FORMAT(attendance_date,'%Y-%m') = '$month'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getStudentGrowthTrend(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("first day of -$i months");
            $end = $date->format('Y-m-t');
            $labels[] = $date->format('M Y');

            $values[] = $this->scalar(
                "SELECT COUNT(*) FROM students
                 WHERE DATE(created_at) <= '$end'"
            );
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $best = null;
        $lowest = null;

        $query = mysqli_query(
            $this->conn,
            "SELECT c.class_name, s.section_name,
                    COUNT(ar.record_id) AS total,
                    SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) AS present
             FROM attendance a
             INNER JOIN classes c ON c.class_id = a.class_id
             INNER JOIN sections s ON s.section_id = a.section_id
             INNER JOIN attendance_records ar ON ar.attendance_id = a.attendance_id
             GROUP BY a.class_id, a.section_id, c.class_name, s.section_name
             HAVING total > 0"
        );

        $classes = [];

        while ($row = mysqli_fetch_assoc($query)) {
            $rate = round(((int) $row['present'] / (int) $row['total']) * 100, 1);
            $name = $row['class_name'] . ' - ' . $row['section_name'];
            $classes[] = ['name' => $name, 'rate' => $rate];
        }

        if (!empty($classes)) {
            usort($classes, fn($a, $b) => $b['rate'] <=> $a['rate']);
            $best = $classes[0];
            $lowest = $classes[count($classes) - 1];
        }

        $critical_students = $this->scalar(
            "SELECT COUNT(DISTINCT st.id)
             FROM students st
             INNER JOIN attendance_records ar ON ar.student_id = st.id
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             GROUP BY st.id
             HAVING (SUM(CASE WHEN ar.status != 'absent' THEN 1 ELSE 0 END) / COUNT(*)) * 100 < 75"
        );

        $pending_sessions = $this->scalar(
            "SELECT COUNT(DISTINCT CONCAT(s.class_id,'-',s.section_id))
             FROM sections s
             WHERE NOT EXISTS (
                SELECT 1 FROM attendance a
                WHERE a.class_id = s.class_id
                  AND a.section_id = s.section_id
                  AND a.attendance_date = CURDATE()
             )"
        );

        return [
            'best_class'          => $best,
            'lowest_class'        => $lowest,
            'critical_students'   => $critical_students,
            'critical_teachers'   => 0,
            'pending_sessions'    => $pending_sessions,
        ];
    }

    public function getRecentNotifications(int $limit = 5): array
    {
        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT id, title,type as notification_type, created_at
             FROM notifications
             ORDER BY created_at DESC
             LIMIT $limit"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    private function attendanceRate(string $present_sql): float
    {
        $present = $this->scalar($present_sql);
        $total   = $this->scalar("SELECT COUNT(*) FROM attendance_records");

        return $total > 0 ? round(($present / $total) * 100, 1) : 0;
    }

    private function upcomingExamCount(): int
    {
        if (!$this->tableHasColumn('exams', 'exam_date')) {
            return 0;
        }

        return $this->scalar(
            "SELECT COUNT(*) FROM exams
             WHERE status = 'active' AND exam_date >= CURDATE()"
        );
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $column = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");

        return $result && mysqli_num_rows($result) > 0;
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
