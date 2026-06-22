<?php

require_once __DIR__ . '/TeacherScopeService.php';
require_once __DIR__ . '/../../config/notifications.php';

class TeacherDashboardService
{
    private mysqli $conn;
    private TeacherScopeService $scope;

    public function __construct(mysqli $conn, TeacherScopeService $scope)
    {
        $this->conn  = $conn;
        $this->scope = $scope;
    }

    public function getStats(): array
    {
        $pairs = $this->scope->getAssignedClassSectionPairs();
        $today_classes = $this->getTodayScheduleCount();
        $pending = $this->getPendingAttendanceCount($pairs);
        $completed = $this->getCompletedAttendanceCount($pairs);

        $uid = (int) ($_SESSION['user']['id'] ?? 0);
        $role = $_SESSION['user']['role'] ?? 'teacher';
        $context = notification_user_context($this->conn, $uid, $role);
        $unread = notification_unread_count($this->conn, $context);

        return [
            'today_classes'       => $today_classes,
            'pending_attendance'  => $pending,
            'completed_attendance'=> $completed,
            'assigned_students'   => $this->scope->getAssignedStudentCount(),
            'unread_notifications'=> $unread,
        ];
    }

    public function getTodaySchedule(): array
    {
        $tid = $this->scope->getTeacherId();
        $day = strtolower(date('l'));

        if ($tid <= 0) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT te.*, p.period_name, p.start_time, p.end_time,
                    sub.subject_name, c.class_name, s.section_name
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             INNER JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             INNER JOIN periods p ON p.period_id = te.period_id
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             INNER JOIN classes c ON c.class_id = tt.class_id
             INNER JOIN sections s ON s.section_id = tt.section_id
             WHERE ta.teacher_id = '$tid'
               AND te.day_of_week = '$day'
             ORDER BY p.start_time ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getCurrentAndNextPeriod(): array
    {
        $schedule = $this->getTodaySchedule();
        $now = date('H:i:s');
        $current = null;
        $next = null;

        foreach ($schedule as $item) {
            if ($now >= $item['start_time'] && $now <= $item['end_time']) {
                $current = $item;
            } elseif ($now < $item['start_time'] && $next === null) {
                $next = $item;
            }
        }

        return ['current' => $current, 'next' => $next];
    }

    public function getAttendanceMarkingTrend(int $days = 14): array
    {
        $labels = [];
        $values = [];
        $pairs = $this->scope->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(a.class_id = {$p['class_id']} AND a.section_id = {$p['section_id']})";
        }

        $scope = '(' . implode(' OR ', $conditions) . ')';

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $values[] = $this->scalar(
                "SELECT COUNT(*) FROM attendance a
                 WHERE a.attendance_date = '$date' AND $scope"
            );
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getStudentAttendanceTrend(int $days = 14): array
    {
        $labels = [];
        $values = [];
        $pairs = $this->scope->getAssignedClassSectionPairs();

        if (empty($pairs)) {
            return ['labels' => $labels, 'values' => $values];
        }

        $conditions = [];

        foreach ($pairs as $p) {
            $conditions[] = "(a.class_id = {$p['class_id']} AND a.section_id = {$p['section_id']})";
        }

        $scope = '(' . implode(' OR ', $conditions) . ')';

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date));

            $present = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE a.attendance_date = '$date' AND $scope
                   AND ar.status != 'absent'"
            );
            $total = $this->scalar(
                "SELECT COUNT(*) FROM attendance_records ar
                 INNER JOIN attendance a ON a.attendance_id = ar.attendance_id
                 WHERE a.attendance_date = '$date' AND $scope"
            );

            $values[] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getInsights(): array
    {
        $pairs = $this->scope->getAssignedClassSectionPairs();
        $attention = [];
        $below_threshold = 0;

        foreach ($pairs as $p) {
            $cid = (int) $p['class_id'];
            $sid = (int) $p['section_id'];

            $has_today = $this->scalar(
                "SELECT COUNT(*) FROM attendance
                 WHERE class_id = '$cid' AND section_id = '$sid'
                   AND attendance_date = CURDATE()"
            );

            if ($has_today === 0) {
                $attention[] = $p['class_name'] . ' - ' . $p['section_name'];
            }
        }

        $total_pairs = count($pairs);
        $completed = $total_pairs - count($attention);
        $completion_pct = $total_pairs > 0
            ? round(($completed / $total_pairs) * 100, 1)
            : 0;

        return [
            'classes_requiring_attention' => $attention,
            'students_below_threshold'    => $below_threshold,
            'attendance_completion_pct'   => $completion_pct,
        ];
    }

    private function getTodayScheduleCount(): int
    {
        return count($this->getTodaySchedule());
    }

    private function getPendingAttendanceCount(array $pairs): int
    {
        $pending = 0;

        foreach ($pairs as $p) {
            $has = $this->scalar(
                "SELECT COUNT(*) FROM attendance
                 WHERE class_id = '{$p['class_id']}'
                   AND section_id = '{$p['section_id']}'
                   AND attendance_date = CURDATE()"
            );

            if ($has === 0) {
                $pending++;
            }
        }

        return $pending;
    }

    private function getCompletedAttendanceCount(array $pairs): int
    {
        return count($pairs) - $this->getPendingAttendanceCount($pairs);
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
