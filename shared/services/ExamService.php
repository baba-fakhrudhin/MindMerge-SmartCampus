<?php

class ExamService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        return $this->tableExists('exams')
            && $this->columnExists('exams', 'exam_code')
            && $this->columnExists('exams', 'class_id')
            && $this->columnExists('exams', 'section_id')
            && $this->columnExists('exams', 'exam_date')
            && $this->columnExists('exams', 'exam_time');
    }

    public function getExams(array $scope = []): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $where = ['1=1'];

        if (!empty($scope['class_id'])) {
            $where[] = 'e.class_id = ' . (int) $scope['class_id'];
        }

        if (!empty($scope['section_id'])) {
            $where[] = 'e.section_id = ' . (int) $scope['section_id'];
        }

        if (!empty($scope['status'])) {
            $status = mysqli_real_escape_string($this->conn, $scope['status']);
            $where[] = "e.status = '$status'";
        }

        if (!empty($scope['active_only'])) {
            $where[] = "e.status = 'active'";
        }

        if (!empty($scope['teacher_id'])) {
            $where[] = "EXISTS (
                SELECT 1 FROM teacher_assignments ta
                WHERE ta.class_id = e.class_id
                  AND ta.section_id = e.section_id
                  AND ta.teacher_id = " . (int) $scope['teacher_id'] . "
            )";
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT e.*, c.class_name, s.section_name,
                    r.result_id, r.status AS result_status,
                    (SELECT COUNT(*) FROM students st WHERE st.class_id = e.class_id AND st.section_id = e.section_id) AS student_count
             FROM exams e
             INNER JOIN classes c ON c.class_id = e.class_id
             INNER JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN results r ON r.exam_id = e.exam_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY e.exam_date DESC, e.exam_time DESC, e.created_at DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getExamById(int $exam_id): ?array
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return null;
        }

        return mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT e.*, c.class_name, s.section_name,
                    r.result_id, r.status AS result_status
             FROM exams e
             INNER JOIN classes c ON c.class_id = e.class_id
             INNER JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN results r ON r.exam_id = e.exam_id
             WHERE e.exam_id = '$exam_id'
             LIMIT 1"
        )) ?: null;
    }

    public function createExam(array $data, int $created_by): int
    {
        if (!$this->isReady()) {
            return 0;
        }

        $class_id = (int) ($data['class_id'] ?? 0);
        $section_id = (int) ($data['section_id'] ?? 0);
        $exam_name = mysqli_real_escape_string($this->conn, trim($data['exam_name'] ?? ''));
        $academic_year = mysqli_real_escape_string($this->conn, trim($data['academic_year'] ?? ''));
        $exam_date = mysqli_real_escape_string($this->conn, trim($data['exam_date'] ?? ''));
        $exam_time = mysqli_real_escape_string($this->conn, trim($data['exam_time'] ?? ''));
        $status = $this->normalizeStatus($data['status'] ?? 'active');
        $exam_code = mysqli_real_escape_string($this->conn, $this->generateExamCode($class_id, $section_id));

        mysqli_query(
            $this->conn,
            "INSERT INTO exams (exam_code, class_id, section_id, exam_name, academic_year, exam_date, exam_time, status, created_by, created_at)
             VALUES ('$exam_code', '$class_id', '$section_id', '$exam_name', '$academic_year', '$exam_date', '$exam_time', '$status', '$created_by', NOW())"
        );

        return (int) mysqli_insert_id($this->conn);
    }

    public function updateExam(int $exam_id, array $data): bool
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return false;
        }

        $class_id = (int) ($data['class_id'] ?? 0);
        $section_id = (int) ($data['section_id'] ?? 0);
        $exam_name = mysqli_real_escape_string($this->conn, trim($data['exam_name'] ?? ''));
        $academic_year = mysqli_real_escape_string($this->conn, trim($data['academic_year'] ?? ''));
        $exam_date = mysqli_real_escape_string($this->conn, trim($data['exam_date'] ?? ''));
        $exam_time = mysqli_real_escape_string($this->conn, trim($data['exam_time'] ?? ''));
        $status = $this->normalizeStatus($data['status'] ?? 'active');

        return (bool) mysqli_query(
            $this->conn,
            "UPDATE exams SET
                class_id='$class_id',
                section_id='$section_id',
                exam_name='$exam_name',
                academic_year='$academic_year',
                exam_date='$exam_date',
                exam_time='$exam_time',
                status='$status',
                updated_at=NOW()
             WHERE exam_id='$exam_id'"
        );
    }

    public function deleteExam(int $exam_id): bool
    {
        if ($exam_id <= 0) {
            return false;
        }

        $result = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT result_id FROM results WHERE exam_id = '$exam_id' LIMIT 1"
        ));

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

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT e.*, c.class_name, s.section_name
             FROM exams e
             INNER JOIN classes c ON c.class_id = e.class_id
             INNER JOIN sections s ON s.section_id = e.section_id
             LEFT JOIN results r ON r.exam_id = e.exam_id
             WHERE e.status = 'active' AND r.result_id IS NULL
             ORDER BY e.exam_date DESC, e.exam_time DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    private function generateExamCode(int $class_id, int $section_id): string
    {
        do {
            $code = 'EXM-' . date('Ymd') . '-' . $class_id . $section_id . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $safe = mysqli_real_escape_string($this->conn, $code);
            $exists = mysqli_query($this->conn, "SELECT exam_id FROM exams WHERE exam_code = '$safe' LIMIT 1");
        } while ($exists && mysqli_num_rows($exists) > 0);

        return $code;
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
    }

    private function tableExists(string $table): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$table'");

        return $result && mysqli_num_rows($result) > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $column = mysqli_real_escape_string($this->conn, $column);
        $result = mysqli_query($this->conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");

        return $result && mysqli_num_rows($result) > 0;
    }
}
