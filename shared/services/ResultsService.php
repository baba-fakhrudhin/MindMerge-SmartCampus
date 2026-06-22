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
        return $this->tableExists('results')
            && $this->tableExists('result_entries')
            && $this->columnExists('results', 'exam_id')
            && $this->tableExists('exams')
            && $this->columnExists('exams', 'exam_code')
            && $this->columnExists('exams', 'class_id');
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

        if (!empty($scope['exam_id'])) {
            $where[] = 'r.exam_id = ' . (int) $scope['exam_id'];
        }

        if (!empty($scope['published_only'])) {
            $where[] = "r.status = 'published'";
        }

        if (!empty($scope['status'])) {
            $status = mysqli_real_escape_string($this->conn, $scope['status']);
            $where[] = "r.status = '$status'";
        }

        if (!empty($scope['teacher_id'])) {
            $where[] = "EXISTS (
                SELECT 1 FROM teacher_assignments ta
                WHERE ta.class_id = r.class_id
                  AND ta.section_id = r.section_id
                  AND ta.teacher_id = " . (int) $scope['teacher_id'] . "
            )";
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.*, c.class_name, s.section_name,
                    e.exam_code, e.exam_name, e.exam_date, e.exam_time,
                    (SELECT COUNT(*) FROM result_entries re WHERE re.result_id = r.result_id) AS entry_count
             FROM results r
             INNER JOIN classes c ON c.class_id = r.class_id
             INNER JOIN sections s ON s.section_id = r.section_id
             LEFT JOIN exams e ON e.exam_id = r.exam_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY COALESCE(e.exam_date, DATE(r.created_at)) DESC, r.created_at DESC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function getResultById(int $result_id): ?array
    {
        if (!$this->isReady() || $result_id <= 0) {
            return null;
        }

        return mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT r.*, c.class_name, s.section_name,
                    e.exam_code, e.exam_name, e.exam_date, e.exam_time
             FROM results r
             INNER JOIN classes c ON c.class_id = r.class_id
             INNER JOIN sections s ON s.section_id = r.section_id
             LEFT JOIN exams e ON e.exam_id = r.exam_id
             WHERE r.result_id = '$result_id'
             LIMIT 1"
        )) ?: null;
    }

    public function createResultFromExam(int $exam_id, int $created_by): int
    {
        if (!$this->isReady() || $exam_id <= 0) {
            return 0;
        }

        $exam = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT * FROM exams WHERE exam_id = '$exam_id' LIMIT 1"
        ));

        if (!$exam) {
            return 0;
        }

        $existing = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT result_id FROM results WHERE exam_id = '$exam_id' LIMIT 1"
        ));

        if ($existing) {
            return (int) $existing['result_id'];
        }

        $class_id = (int) $exam['class_id'];
        $section_id = (int) $exam['section_id'];
        $year = mysqli_real_escape_string($this->conn, $exam['academic_year'] ?? '');

        mysqli_query(
            $this->conn,
            "INSERT INTO results (exam_id, class_id, section_id, academic_year, semester, result_type, status, created_by, created_at)
             VALUES ('$exam_id', '$class_id', '$section_id', '$year', '', 'exam', 'draft', '$created_by', NOW())"
        );

        return (int) mysqli_insert_id($this->conn);
    }

    public function deleteResult(int $result_id): bool
    {
        return (bool) mysqli_query($this->conn, "DELETE FROM results WHERE result_id='$result_id'");
    }

    public function publishResult(int $result_id): bool
    {
        return (bool) mysqli_query(
            $this->conn,
            "UPDATE results SET status='published', published_at=NOW(), updated_at=NOW()
             WHERE result_id='$result_id'"
        );
    }

    public function unpublishResult(int $result_id): bool
    {
        return (bool) mysqli_query(
            $this->conn,
            "UPDATE results SET status='draft', published_at=NULL, updated_at=NOW()
             WHERE result_id='$result_id'"
        );
    }

    public function getStudentsForResult(int $result_id): array
    {
        $result = $this->getResultById($result_id);

        if (!$result) {
            return [];
        }

        $class_id = (int) $result['class_id'];
        $section_id = (int) $result['section_id'];
        $students = [];

        $query = mysqli_query(
            $this->conn,
            "SELECT st.id, st.student_id, u.full_name
             FROM students st
             INNER JOIN users u ON u.id = st.user_id
             WHERE st.class_id = '$class_id' AND st.section_id = '$section_id'
             ORDER BY u.full_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $students[] = $row;
        }

        return $students;
    }

    public function getSubjectsForResult(int $result_id): array
    {
        $result = $this->getResultById($result_id);

        if (!$result) {
            return [];
        }

        $class_id = (int) $result['class_id'];
        $section_id = (int) $result['section_id'];
        $subjects = [];

        $query = mysqli_query(
            $this->conn,
            "SELECT DISTINCT sub.subject_id, sub.subject_name, sub.subject_code
             FROM teacher_assignments ta
             INNER JOIN subjects sub ON sub.subject_id = ta.subject_id
             WHERE ta.class_id = '$class_id' AND ta.section_id = '$section_id'
             ORDER BY sub.subject_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $subjects[] = $row;
        }

        return $subjects;
    }

    public function getEntries(int $result_id, ?int $subject_id = null): array
    {
        if (!$this->isReady() || $result_id <= 0) {
            return [];
        }

        $where = ["re.result_id = '$result_id'"];

        if ($subject_id) {
            $where[] = 're.subject_id = ' . (int) $subject_id;
        }

        $entries = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT re.*, u.full_name AS student_name, st.student_id AS student_code,
                    sub.subject_name, t.teacher_id AS teacher_code
             FROM result_entries re
             INNER JOIN students st ON st.id = re.student_id
             INNER JOIN users u ON u.id = st.user_id
             INNER JOIN subjects sub ON sub.subject_id = re.subject_id
             LEFT JOIN teachers t ON t.id = re.teacher_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY u.full_name ASC, sub.subject_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $entries[] = $row;
        }

        return $entries;
    }

    public function saveEntry(int $result_id, int $student_id, int $subject_id, array $marks, ?int $teacher_id = null): bool
    {
        $internal = max(0, (float) ($marks['internal_marks'] ?? 0));
        $external = max(0, (float) ($marks['external_marks'] ?? 0));
        $lab = max(0, (float) ($marks['lab_marks'] ?? 0));
        $attendance = max(0, (float) ($marks['attendance_marks'] ?? 0));
        $total = $internal + $external + $lab + $attendance;
        $gradeInfo = $this->resolveGrade($total);
        $grade = mysqli_real_escape_string($this->conn, $gradeInfo['grade_name'] ?? '');
        $grade_point = $gradeInfo['grade_point'] ?? null;
        $grade_point_sql = $grade_point !== null ? "'" . (float) $grade_point . "'" : 'NULL';
        $teacher_sql = $teacher_id ? "'" . (int) $teacher_id . "'" : 'NULL';

        $existing = mysqli_query(
            $this->conn,
            "SELECT entry_id FROM result_entries
             WHERE result_id='$result_id' AND student_id='$student_id' AND subject_id='$subject_id'
             LIMIT 1"
        );

        if ($existing && mysqli_num_rows($existing) > 0) {
            $row = mysqli_fetch_assoc($existing);

            return (bool) mysqli_query(
                $this->conn,
                "UPDATE result_entries SET
                    internal_marks='$internal', external_marks='$external',
                    lab_marks='$lab', attendance_marks='$attendance',
                    total_marks='$total', grade='$grade', grade_point=$grade_point_sql,
                    teacher_id=$teacher_sql, updated_at=NOW()
                 WHERE entry_id='" . (int) $row['entry_id'] . "'"
            );
        }

        return (bool) mysqli_query(
            $this->conn,
            "INSERT INTO result_entries (
                result_id, student_id, subject_id, teacher_id,
                internal_marks, external_marks, lab_marks, attendance_marks,
                total_marks, grade, grade_point, created_at
             ) VALUES (
                '$result_id', '$student_id', '$subject_id', $teacher_sql,
                '$internal', '$external', '$lab', '$attendance',
                '$total', '$grade', $grade_point_sql, NOW()
             )"
        );
    }

    public function bulkSaveEntries(int $result_id, array $rows, ?int $teacher_id = null): int
    {
        $saved = 0;

        foreach ($rows as $row) {
            if ($this->saveEntry(
                $result_id,
                (int) ($row['student_id'] ?? 0),
                (int) ($row['subject_id'] ?? 0),
                $row,
                $teacher_id
            )) {
                $saved++;
            }
        }

        return $saved;
    }

    public function resolveGrade(float $total_marks): array
    {
        if (!$this->tableExists('grading_system')) {
            return ['grade_name' => '', 'grade_point' => null];
        }

        $total = (float) $total_marks;
        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT grade_name, grade_point FROM grading_system
             WHERE status='active' AND $total BETWEEN min_marks AND max_marks
             ORDER BY min_marks DESC LIMIT 1"
        ));

        return $row ?: ['grade_name' => '', 'grade_point' => null];
    }

    public function getStudentResults(int $student_db_id): array
    {
        if (!$this->isReady() || $student_db_id <= 0) {
            return [];
        }

        $items = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT r.result_id, r.academic_year, r.semester, r.result_type, r.status,
                    e.exam_code, e.exam_name, e.exam_date,
                    sub.subject_name, re.total_marks, re.grade, re.grade_point,
                    re.internal_marks, re.external_marks, re.lab_marks, re.attendance_marks
             FROM result_entries re
             INNER JOIN results r ON r.result_id = re.result_id
             LEFT JOIN exams e ON e.exam_id = r.exam_id
             INNER JOIN subjects sub ON sub.subject_id = re.subject_id
             WHERE re.student_id = '$student_db_id' AND r.status = 'published'
             ORDER BY COALESCE(e.exam_date, DATE(r.created_at)) DESC, sub.subject_name ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $items[] = $row;
        }

        return $items;
    }

    public function calculateSemesterGpa(int $student_db_id, string $academic_year, string $semester): ?float
    {
        if ($student_db_id <= 0) {
            return null;
        }

        $year = mysqli_real_escape_string($this->conn, $academic_year);
        $sem = mysqli_real_escape_string($this->conn, $semester);

        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT AVG(re.grade_point) AS gpa, COUNT(*) AS cnt
             FROM result_entries re
             INNER JOIN results r ON r.result_id = re.result_id
             WHERE re.student_id = '$student_db_id'
               AND r.status = 'published'
               AND r.academic_year = '$year'
               AND r.semester = '$sem'
               AND re.grade_point IS NOT NULL"
        ));

        if (!$row || (int) $row['cnt'] === 0) {
            return null;
        }

        return round((float) $row['gpa'], 2);
    }

    public function calculateOverallGpa(int $student_db_id): ?float
    {
        if ($student_db_id <= 0) {
            return null;
        }

        $row = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT AVG(re.grade_point) AS gpa, COUNT(*) AS cnt
             FROM result_entries re
             INNER JOIN results r ON r.result_id = re.result_id
             WHERE re.student_id = '$student_db_id'
               AND r.status = 'published'
               AND re.grade_point IS NOT NULL"
        ));

        if (!$row || (int) $row['cnt'] === 0) {
            return null;
        }

        return round((float) $row['gpa'], 2);
    }

    public function getLatestGpa(int $student_db_id): ?array
    {
        $results = $this->getStudentResults($student_db_id);

        if (empty($results)) {
            return null;
        }

        $latest = $results[0];
        $gpa = $this->calculateSemesterGpa(
            $student_db_id,
            $latest['academic_year'],
            $latest['semester'] ?? ''
        );

        return [
            'academic_year' => $latest['academic_year'],
            'semester'      => $latest['semester'] ?? '',
            'gpa'           => $gpa,
            'overall_gpa'   => $this->calculateOverallGpa($student_db_id),
        ];
    }

    public function getPerformanceTrend(int $student_db_id): array
    {
        if ($student_db_id <= 0) {
            return ['labels' => [], 'values' => []];
        }

        $labels = [];
        $values = [];
        $query = mysqli_query(
            $this->conn,
            "SELECT COALESCE(e.exam_name, CONCAT(r.academic_year, ' ', COALESCE(r.semester, 'Exam'))) AS label,
                    AVG(re.grade_point) AS avg_gpa,
                    COALESCE(e.exam_date, DATE(r.created_at)) AS sort_date
             FROM result_entries re
             INNER JOIN results r ON r.result_id = re.result_id
             LEFT JOIN exams e ON e.exam_id = r.exam_id
             WHERE re.student_id = '$student_db_id' AND r.status = 'published'
               AND re.grade_point IS NOT NULL
             GROUP BY r.result_id, label, sort_date
             ORDER BY sort_date ASC"
        );

        while ($query && $row = mysqli_fetch_assoc($query)) {
            $labels[] = $row['label'];
            $values[] = round((float) $row['avg_gpa'], 2);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function exportEntriesCsv(int $result_id): string
    {
        $entries = $this->getEntries($result_id);
        $lines = ['Student ID,Student Name,Subject,Internal,External,Lab,Attendance,Total,Grade,GPA'];

        foreach ($entries as $e) {
            $lines[] = implode(',', [
                '"' . str_replace('"', '""', $e['student_code'] ?? '') . '"',
                '"' . str_replace('"', '""', $e['student_name'] ?? '') . '"',
                '"' . str_replace('"', '""', $e['subject_name'] ?? '') . '"',
                $e['internal_marks'],
                $e['external_marks'],
                $e['lab_marks'],
                $e['attendance_marks'],
                $e['total_marks'],
                '"' . ($e['grade'] ?? '') . '"',
                $e['grade_point'] ?? '',
            ]);
        }

        return implode("\n", $lines);
    }

    public function exportEntriesExcelHtml(int $result_id): string
    {
        $result = $this->getResultById($result_id);
        $entries = $this->getEntries($result_id);
        $title = htmlspecialchars(($result['exam_name'] ?? 'Result') . ' Result Sheet');
        $html = '<table border="1"><thead><tr><th colspan="10">' . $title . '</th></tr>';
        $html .= '<tr><th>Student ID</th><th>Student Name</th><th>Subject</th><th>Internal</th><th>External</th><th>Lab</th><th>Attendance</th><th>Total</th><th>Grade</th><th>GPA</th></tr></thead><tbody>';

        foreach ($entries as $e) {
            $html .= '<tr>';
            foreach (['student_code', 'student_name', 'subject_name', 'internal_marks', 'external_marks', 'lab_marks', 'attendance_marks', 'total_marks', 'grade', 'grade_point'] as $key) {
                $html .= '<td>' . htmlspecialchars((string) ($e[$key] ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table>';
    }

    public function exportEntriesPdf(int $result_id): string
    {
        $result = $this->getResultById($result_id);
        $entries = $this->getEntries($result_id);
        $lines = [
            ($result['exam_name'] ?? 'Result Sheet') . ' - ' . ($result['exam_code'] ?? ('Result #' . $result_id)),
            ($result['class_name'] ?? '') . ' / ' . ($result['section_name'] ?? ''),
            '',
        ];

        foreach ($entries as $e) {
            $lines[] = ($e['student_code'] ?? '') . '  ' . ($e['student_name'] ?? '') . '  ' . ($e['subject_name'] ?? '') . '  Total: ' . ($e['total_marks'] ?? '0') . '  Grade: ' . ($e['grade'] ?? '-');
        }

        return $this->buildSimplePdf($lines);
    }

    public function getResultsForTeacher(int $teacher_db_id): array
    {
        if ($teacher_db_id <= 0) {
            return [];
        }

        return $this->getResultSets(['teacher_id' => $teacher_db_id]);
    }

    private function buildSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 790 Td\n";
        $first = true;

        foreach ($lines as $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr((string) $line, 0, 110));
            if (!$first) {
                $content .= "0 -16 Td\n";
            }
            $content .= '(' . $safe . ") Tj\n";
            $first = false;
        }

        $content .= "ET\n";
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
            "5 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "endstream endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $pdf . "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
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
