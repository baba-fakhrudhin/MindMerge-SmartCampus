<?php

/**
 * Shared weekly timetable grid builder and renderer.
 */
class TimetableViewService
{
    private mysqli $conn;

    private array $days = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
    ];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Build grid for a class/section timetable (student view).
     */
    public function buildClassSectionGrid(int $class_id, int $section_id): ?array
    {
        if ($class_id <= 0 || $section_id <= 0) {
            return null;
        }

        $timetable = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT t.*, c.class_name, s.section_name,
                    pt.template_name, pt.template_code
             FROM timetables t
             INNER JOIN classes c ON c.class_id = t.class_id
             INNER JOIN sections s ON s.section_id = t.section_id
             INNER JOIN period_templates pt ON pt.template_id = t.template_id
             WHERE t.class_id = '$class_id'
               AND t.section_id = '$section_id'
             ORDER BY t.timetable_id DESC
             LIMIT 1"
        ));

        if (!$timetable) {
            return null;
        }

        $periods = $this->loadPeriods((int) $timetable['template_id']);
        $entries = $this->loadEntries((int) $timetable['timetable_id']);

        return [
            'title'   => ($timetable['class_name'] ?? '') . ' - ' . ($timetable['section_name'] ?? ''),
            'subtitle'=> ($timetable['template_code'] ?? '') . ' • ' . ($timetable['template_name'] ?? ''),
            'periods' => $periods,
            'days'    => $this->days,
            'grid'    => $this->mapEntries($periods, $entries),
        ];
    }

    /**
     * Build consolidated grid for a teacher across assigned classes.
     */
    public function buildTeacherGrid(int $teacher_id): ?array
    {
        if ($teacher_id <= 0) {
            return null;
        }

        $entries = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT te.day_of_week, te.period_id, te.room_no, te.remarks,
                    p.period_name, p.start_time, p.end_time, p.period_type,
                    p.is_teaching_period, p.display_color,
                    sub.subject_name,
                    c.class_name, s.section_name,
                    ta.assignment_role,
                    u.full_name AS teacher_name
             FROM timetable_entries te
             INNER JOIN timetables tt ON tt.timetable_id = te.timetable_id
             INNER JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             INNER JOIN periods p ON p.period_id = te.period_id
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             INNER JOIN classes c ON c.class_id = tt.class_id
             INNER JOIN sections s ON s.section_id = tt.section_id
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE ta.teacher_id = '$teacher_id'
             ORDER BY p.start_time ASC, te.day_of_week ASC"
        );

        $period_map = [];

        while ($row = mysqli_fetch_assoc($query)) {
            $entries[] = $row;
            $period_map[(int) $row['period_id']] = [
                'period_id'         => (int) $row['period_id'],
                'period_name'       => $row['period_name'],
                'start_time'        => $row['start_time'],
                'end_time'          => $row['end_time'],
                'period_type'       => $row['period_type'],
                'is_teaching_period'=> $row['is_teaching_period'],
                'display_color'     => $row['display_color'] ?: '#3b82f6',
            ];
        }

        if (empty($entries)) {
            return null;
        }

        usort($period_map, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
        $periods = array_values($period_map);

        $grid = [];
        foreach ($periods as $period) {
            $pid = (int) $period['period_id'];
            foreach ($this->days as $day) {
                $grid[$pid][$day] = null;
            }
        }

        foreach ($entries as $entry) {
            $pid = (int) $entry['period_id'];
            $day = $entry['day_of_week'];
            $grid[$pid][$day] = $entry;
        }

        return [
            'title'    => 'My Teaching Schedule',
            'subtitle' => 'Weekly view across assigned classes',
            'periods'  => $periods,
            'days'     => $this->days,
            'grid'     => $grid,
        ];
    }

    public function renderGrid(array $data): string
    {
        ob_start();
        $periods = $data['periods'] ?? [];
        $days = $data['days'] ?? $this->days;
        $grid = $data['grid'] ?? [];
        ?>
        <div class="table-responsive timetable-grid-wrap">
        <table class="timetable-table custom-table">
        <thead>
        <tr>
        <th>Period</th>
        <?php foreach ($days as $day) { ?>
        <th><?php echo ucfirst($day); ?></th>
        <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($periods as $period) {
            $pid = (int) $period['period_id'];
            $color = htmlspecialchars($period['display_color'] ?? '#3b82f6');
        ?>
        <tr>
        <td class="period-cell">
        <div><strong><?php echo htmlspecialchars($period['period_name']); ?></strong></div>
        <div class="period-time"><?php echo date('g:i A', strtotime($period['start_time'])); ?> - <?php echo date('g:i A', strtotime($period['end_time'])); ?></div>
        </td>
        <?php foreach ($days as $day) {
            $entry = $grid[$pid][$day] ?? null;
            echo $this->renderCell($period, $entry);
        } ?>
        </tr>
        <?php } ?>
        </tbody>
        </table>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderCell(array $period, ?array $entry): string
    {
        $color = htmlspecialchars($period['display_color'] ?? '#3b82f6');
        $type = $period['period_type'] ?? 'regular';

        ob_start();
        echo '<td>';

        if (in_array($type, ['lunch', 'break', 'exam'], true)) {
            ?>
            <div class="timetable-block" style="background:<?php echo $color; ?>20;border-color:<?php echo $color; ?>55;color:<?php echo $color; ?>;">
            <?php echo htmlspecialchars($period['period_name']); ?>
            </div>
            <?php
        } elseif (($period['is_teaching_period'] ?? 'yes') === 'no') {
            ?>
            <div class="timetable-block non-teaching"><?php echo htmlspecialchars($period['period_name']); ?></div>
            <?php
        } elseif ($entry) {
            $is_lab = ($type === 'lab') || str_contains(strtolower($entry['assignment_role'] ?? ''), 'lab');
            ?>
            <div class="subject-box" style="background:<?php echo $color; ?>20;border-color:<?php echo $color; ?>55;">
            <div class="subject-title" style="color:<?php echo $color; ?>;">
            <?php echo htmlspecialchars($entry['subject_name']); ?>
            <?php if ($is_lab) { ?><span class="lab-badge">Lab</span><?php } ?>
            </div>
            <?php if (!empty($entry['class_name'])) { ?>
            <div class="teacher-name"><?php echo htmlspecialchars($entry['class_name'] . ' • ' . ($entry['section_name'] ?? '')); ?></div>
            <?php } elseif (!empty($entry['teacher_name'])) { ?>
            <div class="teacher-name"><?php echo htmlspecialchars($entry['teacher_name']); ?></div>
            <?php } ?>
            <?php if (!empty($entry['room_no'])) { ?>
            <div class="room-name" style="color:<?php echo $color; ?>;">Room: <?php echo htmlspecialchars($entry['room_no']); ?></div>
            <?php } ?>
            </div>
            <?php
        } else {
            ?>
            <div class="empty-box">—</div>
            <?php
        }

        echo '</td>';
        return ob_get_clean();
    }

    private function loadPeriods(int $template_id): array
    {
        $periods = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT *
             FROM periods
             WHERE template_id = '$template_id'
               AND status = 'active'
             ORDER BY sort_order ASC"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            if (empty($row['display_color'])) {
                $row['display_color'] = '#3b82f6';
            }
            $periods[] = $row;
        }

        return $periods;
    }

    private function loadEntries(int $timetable_id): array
    {
        $entries = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT te.*, sub.subject_name, u.full_name AS teacher_name,
                    ta.assignment_role
             FROM timetable_entries te
             INNER JOIN subjects sub ON sub.subject_id = te.subject_id
             LEFT JOIN teacher_assignments ta ON ta.assignment_id = te.teacher_assignment_id
             LEFT JOIN teachers t ON t.id = ta.teacher_id
             LEFT JOIN users u ON u.id = t.user_id
             WHERE te.timetable_id = '$timetable_id'"
        );

        while ($row = mysqli_fetch_assoc($query)) {
            $entries[] = $row;
        }

        return $entries;
    }

    private function mapEntries(array $periods, array $entries): array
    {
        $grid = [];

        foreach ($periods as $period) {
            $pid = (int) $period['period_id'];
            foreach ($this->days as $day) {
                $grid[$pid][$day] = null;
            }
        }

        foreach ($entries as $entry) {
            $pid = (int) $entry['period_id'];
            $day = $entry['day_of_week'];
            $grid[$pid][$day] = $entry;
        }

        return $grid;
    }
}
