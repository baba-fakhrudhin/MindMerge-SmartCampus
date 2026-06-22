<?php

require_once __DIR__ . '/../../config/notifications.php';

class ParentDashboardService
{
    private mysqli $conn;
    private ?array $parent = null;
    private array $parentLinks = [];
    private array $children = [];

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $query = mysqli_query(
            $conn,
            "SELECT p.*, u.full_name, u.email, u.phone, u.profile_photo
             FROM parents p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.user_id = '$user_id'
             ORDER BY p.id ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $this->parentLinks[] = $row;
        }

        $this->parent = $this->parentLinks[0] ?? null;

        $this->loadChildren();
    }

    public function getParent(): ?array
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getParentLinks(): array
    {
        return $this->parentLinks;
    }

    public function getStats(): array
    {
        $count = count($this->children);
        $avg_attendance = 0;
        $avg_performance = 0;

        if ($count > 0) {
            $total_att = 0;

            foreach ($this->children as $child) {
                $sid = (int) $child['id'];
                $present = $this->scalar(
                    "SELECT COUNT(*) FROM attendance_records
                     WHERE student_id = '$sid' AND status != 'absent'"
                );
                $total = $this->scalar(
                    "SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid'"
                );
                $total_att += $total > 0 ? ($present / $total) * 100 : 0;
            }

            $avg_attendance = round($total_att / $count, 1);
        }

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'parent';
        $context = notification_user_context($this->conn, $uid, $role);
        $unread = notification_unread_count($this->conn, $context);

        return [
            'children_count'       => $count,
            'average_attendance'   => $avg_attendance,
            'average_performance'  => $avg_performance,
            'unread_notifications' => $unread,
        ];
    }

    public function getAttendanceTrend(int $days = 30): array
    {
        $labels = [];
        $values = [];

        if (empty($this->children)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $student_ids = array_map(fn($c) => (int) $c['id'], $this->children);
        $id_list = implode(',', $student_ids);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id IN ($id_list)
                   AND a.attendance_date = '$date'
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE ar.student_id IN ($id_list)
                   AND a.attendance_date = '$date'"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 1) : null;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $alerts = [];
        $performance_alerts = [];

        foreach ($this->children as $child) {
            $sid = (int) $child['id'];
            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records
                 WHERE student_id = '$sid' AND status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records WHERE student_id = '$sid'"
            );
            $rate = $total > 0 ? round(($present / $total) * 100, 1) : 100;

            if ($rate < 75) {
                $alerts[] = [
                    'name' => $child['full_name'],
                    'rate' => $rate,
                ];
            }
        }

        return [
            'attendance_alerts'    => $alerts,
            'performance_alerts'   => $performance_alerts,
            'upcoming_exams'       => [],
        ];
    }

    public function getChildAttendance(int $student_db_id): array
    {
        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT a.attendance_date, ar.status, sub.subject_name
             FROM attendance_records ar
             INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
             LEFT JOIN subjects sub ON sub.subject_id = a.subject_id
             WHERE ar.student_id = '$student_db_id'
             ORDER BY a.attendance_date DESC
             LIMIT 30"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    private function loadChildren(): void
    {
        if (empty($this->parentLinks)) {
            return;
        }

        $student_codes = [];

        foreach ($this->parentLinks as $link) {
            if (!empty($link['student_id'])) {
                $student_codes[] = "'" . mysqli_real_escape_string($this->conn, $link['student_id']) . "'";
            }
        }

        if (empty($student_codes)) {
            return;
        }

        $student_list = implode(',', array_unique($student_codes));

        $query = mysqli_query(
            $this->conn,
            "SELECT st.*, u.full_name, u.profile_photo,
                    c.class_name, s.section_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN classes c ON c.class_id = st.class_id
             INNER JOIN sections s ON s.section_id = st.section_id
             WHERE st.student_id IN ($student_list)
             ORDER BY u.full_name ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $this->children[] = $row;
        }
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
