<?php

class ExamService
{
    private mysqli $conn;
    private array $tableCache = [];
    private array $columnCache = [];

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        return $this->tableExists('exams')
            && $this->columnExists('exams', 'exam_name')
            && $this->columnExists('exams', 'exam_date')
            && $this->columnExists('exams', 'status');
    }

    public function syncCompletedExams(): void
    {
        if (!$this->isReady()) {
            return;
        }

        mysqli_query(
            $this->conn,
            "UPDATE exams
             SET status = 'completed'
             WHERE exam_date IS NOT NULL
               AND exam_date < CURDATE()
               AND status IN ('upcoming', 'ongoing')"
        );
    }

    public function getExams(array $scope = []): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $this->syncCompletedExams();
        $where = ['1=1'];

        if (array_key_exists('class_id', $scope)) {
            $classId = (int) $scope['class_id'];
            $where[] = $classId > 0 ? "(e.class_id = '$classId' OR e.class_id IS NULL OR e.class_id = 0)" : "(e.class_id IS NULL OR e.class_id = 0)";
        }

        if (array_key_exists('section_id', $scope)) {
            $sectionId = (int) $scope['section_id'];
            $where[] = $sectionId > 0 ? "(e.section_id = '$sectionId' OR e.section_id IS NULL OR e.section_id = 0)" : "(e.section_id IS NULL OR e.section_id = 0)";
        }

        if (!empty($scope['exact_scope'])) {
            if (isset($scope['class_id'])) {
                $where[] = 'COALESCE(e.class_id, 0) = ' . (int) $scope['class_id'];
            }
            if (isset($scope['section_id'])) {
                $where[] = 'COALESCE(e.section_id, 0) = ' . (int) $scope['section_id'];
            }
        }

        if (!empty($scope['status'])) {
            $status = mysqli_real_escape_string($this->conn, $scope['status']);
            $where[] = "e.status = '$status'";
        }

        if (!empty($scope['upcoming_only'])) {
            $where[] = "e.exam_date >= CURDATE()";
            $where[] = "e.status IN ('upcoming', 'ongoing')";
        }

        if (!empty($scope['hide_drafts'])) {
            $where[] = "e.status IN ('upcoming', 'ongoing', 'completed')";
        }

        if (!empty($scope['teacher_id'])) {
            $teacherId = (int) $scope['teacher_id'];
            $where[] = "(
                e.class_id IS NULL
                OR e.class_id = 0
                OR EXISTS (
                    SELECT 1 FROM teacher_assignments ta
                    WHERE ta.teacher_id = '$teacherId'
                      AND ta.class_id = e.class_id
                      AND (e.section_id IS NULL OR e.section_id = 0 OR ta.section_id = e.section_id)
                )
            )";
        }

        $limit = !empty($scope['limit']) ? ' LIMIT ' . (int) $scope['limit'] : '';
        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT e.*,
                    COALESCE(c.class_name, 'School Wide') AS class_name,
                    COALESCE(s.section_name, 'All Sections') AS section_name,
                    COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name,
                    r.result_id, r.status AS result_status,
                    (SELECT COUNT(*) FROM students st
                     WHERE (COALESCE(e.class_id, 0) = 0 OR st.class_id = e.class_id)
                       AND (COALESCE(e.section_id, 0) = 0 OR st.section_id = e.section_id)) AS student_count
             FROM exams e
             LEFT JOIN classes c ON c.class_id = e.class_id
             LEFT JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             LEFT JOIN results r ON r.exam_id = e.exam_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY e.exam_date ASC, e.start_time ASC, e.created_at DESC" . $limit
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
            $row['exam_time'] = $row['start_time'] ?? null;
            $items[] = $row;
        }

        return $items;
    }

    public function getUpcomingExamsForStudent(int $class_id, int $section_id, int $limit = 5): array
    {
        if ($class_id <= 0) {
            return [];
        }

        return $this->getExams([
            'class_id' => $class_id,
            'section_id' => $section_id,
            'upcoming_only' => true,
            'hide_drafts' => true,
            'limit' => $limit,
        ]);
    }

    public function getUpcomingExamsForChildren(array $children, int $limit = 6): array
    {
        if (empty($children) || !$this->isReady()) {
            return [];
        }

        $this->syncCompletedExams();
        $conditions = ["(e.class_id IS NULL OR e.class_id = 0)"];
        foreach ($children as $child) {
            $classId = (int) ($child['class_id'] ?? 0);
            $sectionId = (int) ($child['section_id'] ?? 0);
            if ($classId > 0) {
                $conditions[] = "(e.class_id = '$classId' AND (e.section_id IS NULL OR e.section_id = 0 OR e.section_id = '$sectionId'))";
            }
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT e.*,
                    COALESCE(c.class_name, 'School Wide') AS class_name,
                    COALESCE(s.section_name, 'All Sections') AS section_name,
                    COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name
             FROM exams e
             LEFT JOIN classes c ON c.class_id = e.class_id
             LEFT JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             WHERE e.exam_date >= CURDATE()
               AND e.status IN ('upcoming', 'ongoing')
               AND (" . implode(' OR ', array_unique($conditions)) . ")
             ORDER BY e.exam_date ASC, e.start_time ASC
             LIMIT " . (int) $limit
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
            $row['exam_time'] = $row['start_time'] ?? null;
            $items[] = $row;
        }

        return $items;
    }

    public function getUpcomingExamsForTeacher(int $teacher_id, int $limit = 5): array
    {
        if ($teacher_id <= 0) {
            return [];
        }

        return $this->getExams([
            'teacher_id' => $teacher_id,
            'upcoming_only' => true,
            'hide_drafts' => true,
            'limit' => $limit,
        ]);
    }

    public function getUpcomingExamCount(array $scope = []): int
    {
        return count($this->getExams($scope + ['upcoming_only' => true, 'hide_drafts' => true]));
    }

    public function getExamStatistics(array $scope = []): array
    {
        if (!empty($scope['children']) && is_array($scope['children'])) {
            $examMap = [];
            foreach ($scope['children'] as $child) {
                $childExams = $this->getExams([
                    'class_id' => (int) ($child['class_id'] ?? 0),
                    'section_id' => (int) ($child['section_id'] ?? 0),
                    'hide_drafts' => true,
                ]);
                foreach ($childExams as $exam) {
                    $examMap[(int) $exam['exam_id']] = $exam;
                }
            }
            $exams = array_values($examMap);
        } else {
            $exams = $this->getExams($scope + ['hide_drafts' => true]);
        }

        $stats = [
            'total_exams' => count($exams),
            'upcoming_exams' => 0,
            'ongoing_exams' => 0,
            'completed_exams' => 0,
        ];

        foreach ($exams as $exam) {
            $status = strtolower((string) ($exam['status'] ?? ''));
            if ($status === 'upcoming' && !empty($exam['exam_date']) && $exam['exam_date'] >= date('Y-m-d')) {
                $stats['upcoming_exams']++;
            } elseif ($status === 'ongoing' && (empty($exam['exam_date']) || $exam['exam_date'] >= date('Y-m-d'))) {
                $stats['ongoing_exams']++;
            } elseif ($status === 'completed') {
                $stats['completed_exams']++;
            }
        }

        return $stats;
    }

    public function getExamById(int $exam_id): ?array
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return null;
        }

        $this->syncCompletedExams();
        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT e.*,
                    COALESCE(c.class_name, 'School Wide') AS class_name,
                    COALESCE(s.section_name, 'All Sections') AS section_name,
                    COALESCE(sub.subject_name, e.custom_subject, e.exam_name) AS subject_name,
                    r.result_id, r.status AS result_status
             FROM exams e
             LEFT JOIN classes c ON c.class_id = e.class_id
             LEFT JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN subjects sub ON sub.subject_id = e.subject_id
             LEFT JOIN results r ON r.exam_id = e.exam_id
             WHERE e.exam_id = '$exam_id'
             LIMIT 1"
        ));

        if (!$row) {
            return null;
        }

        $row['exam_code'] = 'EXM-' . (int) $row['exam_id'];
        $row['exam_time'] = $row['start_time'] ?? null;

        return $row;
    }

    public function createExam(array $data, int $created_by = 0): int
    {
        if (!$this->isReady()) {
            return 0;
        }

        $class_id = $this->nullableIntSql($data['class_id'] ?? null);
        $section_id = $this->nullableIntSql($data['section_id'] ?? null);
        $subject_id = $this->nullableIntSql($data['subject_id'] ?? null);
        $exam_name = mysqli_real_escape_string($this->conn, trim($data['exam_name'] ?? ''));
        $exam_type = mysqli_real_escape_string($this->conn, trim($data['exam_type'] ?? 'custom'));
        $custom_subject = mysqli_real_escape_string($this->conn, trim($data['custom_subject'] ?? ''));
        $total_marks = max(0, (int) ($data['total_marks'] ?? 100));
        $exam_date = mysqli_real_escape_string($this->conn, trim($data['exam_date'] ?? ''));
        $start_time = mysqli_real_escape_string($this->conn, trim($data['start_time'] ?? $data['exam_time'] ?? ''));
        $end_time = mysqli_real_escape_string($this->conn, trim($data['end_time'] ?? ''));
        $description = mysqli_real_escape_string($this->conn, trim($data['description'] ?? ''));
        $status = $this->normalizeStatus($data['status'] ?? 'upcoming');

        mysqli_query(
            $this->conn,
            "INSERT INTO exams (exam_name, exam_type, class_id, section_id, subject_id, custom_subject, total_marks, exam_date, start_time, end_time, description, status, created_at)
             VALUES ('$exam_name', '$exam_type', $class_id, $section_id, $subject_id, '$custom_subject', '$total_marks', '$exam_date', '$start_time', '$end_time', '$description', '$status', NOW())"
        );

        return (int) mysqli_insert_id($this->conn);
    }

    public function updateExam(int $exam_id, array $data): bool
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return false;
        }

        $class_id = $this->nullableIntSql($data['class_id'] ?? null);
        $section_id = $this->nullableIntSql($data['section_id'] ?? null);
        $subject_id = $this->nullableIntSql($data['subject_id'] ?? null);
        $exam_name = mysqli_real_escape_string($this->conn, trim($data['exam_name'] ?? ''));
        $exam_type = mysqli_real_escape_string($this->conn, trim($data['exam_type'] ?? 'custom'));
        $custom_subject = mysqli_real_escape_string($this->conn, trim($data['custom_subject'] ?? ''));
        $total_marks = max(0, (int) ($data['total_marks'] ?? 100));
        $exam_date = mysqli_real_escape_string($this->conn, trim($data['exam_date'] ?? ''));
        $start_time = mysqli_real_escape_string($this->conn, trim($data['start_time'] ?? $data['exam_time'] ?? ''));
        $end_time = mysqli_real_escape_string($this->conn, trim($data['end_time'] ?? ''));
        $description = mysqli_real_escape_string($this->conn, trim($data['description'] ?? ''));
        $status = $this->normalizeStatus($data['status'] ?? 'upcoming');

        return (bool) mysqli_query(
            $this->conn,
            "UPDATE exams SET
                exam_name='$exam_name',
                exam_type='$exam_type',
                class_id=$class_id,
                section_id=$section_id,
                subject_id=$subject_id,
                custom_subject='$custom_subject',
                total_marks='$total_marks',
                exam_date='$exam_date',
                start_time='$start_time',
                end_time='$end_time',
                description='$description',
                status='$status'
             WHERE exam_id='$exam_id'"
        );
    }

    public function deleteExam(int $exam_id): bool
    {
        if ($exam_id <= 0) {
            return false;
        }

        $result = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT result_id FROM results WHERE exam_id = '$exam_id' LIMIT 1"));
        if ($result) {
            return false;
        }

        return (bool) mysqli_query($this->conn, "DELETE FROM exams WHERE exam_id='$exam_id'");
    }

    public function getAvailableForResult(): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $this->syncCompletedExams();
        return array_values(array_filter($this->getExams(), fn($exam) => empty($exam['result_id'])));
    }

    private function nullableIntSql($value): string
    {
        $int = (int) $value;
        return $int > 0 ? (string) $int : 'NULL';
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['upcoming', 'ongoing', 'completed'], true) ? $status : 'upcoming';
    }

    private function tableExists(string $table): bool
    {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        $safe = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$safe'");

        return $this->tableCache[$table] = ($result && mysqli_num_rows($result) > 0);
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (isset($this->columnCache[$key])) {
            return $this->columnCache[$key];
        }
        if (!$this->tableExists($table)) {
            return $this->columnCache[$key] = false;
        }

        $safeTable = mysqli_real_escape_string($this->conn, $table);
        $safeColumn = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");

        return $this->columnCache[$key] = ($result && mysqli_num_rows($result) > 0);
    }
}
