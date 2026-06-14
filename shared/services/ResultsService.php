<?php

class ResultsService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function isReady(): bool
    {
        return $this->tableExists('results') && $this->tableExists('result_entries');
    }

    public function getResultSets(array $scope = []): array
    {
        if (!$this->isReady()) {
            return [];
        }

        $where = ['1=1'];

        if (!empty($scope['class_id'])) {
            $where[] = 'r.class_id = ' . (int) $scope['class_id'];
        }

        if (!empty($scope['section_id'])) {
            $where[] = 'r.section_id = ' . (int) $scope['section_id'];
        }

        if (!empty($scope['published_only'])) {
            $where[] = "r.status = 'published'";
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.*, c.class_name, s.section_name
             FROM results r
             INNER JOIN classes c ON c.class_id = r.class_id
             INNER JOIN sections s ON s.section_id = r.section_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY r.created_at DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getStudentResults(int $student_db_id): array
    {
        if (!$this->isReady() || $student_db_id <= 0) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.academic_year, r.semester, r.result_type, r.status,
                    sub.subject_name, re.total_marks, re.grade, re.grade_point
             FROM result_entries re
             INNER JOIN results r ON r.result_id = re.result_id
             INNER JOIN subjects sub ON sub.subject_id = re.subject_id
             WHERE re.student_id = '$student_db_id'
               AND r.status = 'published'
             ORDER BY r.academic_year DESC, r.semester DESC, sub.subject_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    private function tableExists(string $table): bool
    {
        $table = mysqli_real_escape_string($this->conn, $table);
        $result = mysqli_query($this->conn, "SHOW TABLES LIKE '$table'");

        return $result && mysqli_num_rows($result) > 0;
    }
}
