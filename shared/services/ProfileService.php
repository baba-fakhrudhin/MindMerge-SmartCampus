<?php

class ProfileService
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getProfileData(int $user_id, string $role): array
    {
        $user = mysqli_fetch_assoc(mysqli_query(
            $this->conn,
            "SELECT * FROM users WHERE id = '$user_id' LIMIT 1"
        ));

        $data = [
            'user'      => $user,
            'role'      => $role,
            'role_data' => null,
            'extra'     => [],
        ];

        switch ($role) {
            case 'student':
                $data['role_data'] = mysqli_fetch_assoc(mysqli_query(
                    $this->conn,
                    "SELECT st.*, c.class_name, s.section_name
                     FROM students st
                     LEFT JOIN classes c ON c.class_id = st.class_id
                     LEFT JOIN sections s ON s.section_id = st.section_id
                     WHERE st.user_id = '$user_id'"
                ));
                break;

            case 'teacher':
                $data['role_data'] = mysqli_fetch_assoc(mysqli_query(
                    $this->conn,
                    "SELECT * FROM teachers WHERE user_id = '$user_id'"
                ));

                if ($data['role_data']) {
                    $tid = (int) $data['role_data']['id'];
                    $data['extra']['assignments'] = [];
                    $data['extra']['subjects'] = [];

                    $query = mysqli_query(
                        $this->conn,
                        "SELECT ta.*, c.class_name, s.section_name, sub.subject_name
                         FROM teacher_assignments ta
                         INNER JOIN classes c ON c.class_id = ta.class_id
                         INNER JOIN sections s ON s.section_id = ta.section_id
                         INNER JOIN subjects sub ON sub.subject_id = ta.subject_id
                         WHERE ta.teacher_id = '$tid'"
                    );

                    while ($row = mysqli_fetch_assoc($query)) {
                        $data['extra']['assignments'][] = $row;
                        $data['extra']['subjects'][$row['subject_name']] = $row['subject_name'];
                    }
                }
                break;

            case 'parent':
                $data['role_data'] = mysqli_fetch_assoc(mysqli_query(
                    $this->conn,
                    "SELECT * FROM parents WHERE user_id = '$user_id'"
                ));

                $parent_links = [];
                $parent_query = mysqli_query(
                    $this->conn,
                    "SELECT * FROM parents WHERE user_id = '$user_id' ORDER BY id ASC"
                );

                while ($parent_query && $row = mysqli_fetch_assoc($parent_query)) {
                    $parent_links[] = $row;
                }

                $data['role_data'] = $parent_links[0] ?? $data['role_data'];
                $data['extra']['parent_links'] = $parent_links;
                $student_codes = [];

                foreach ($parent_links as $link) {
                    if (!empty($link['student_id'])) {
                        $student_codes[] = "'" . mysqli_real_escape_string($this->conn, $link['student_id']) . "'";
                    }
                }

                if (!empty($student_codes)) {
                    $student_list = implode(',', array_unique($student_codes));
                    $data['extra']['children'] = [];
                    $query = mysqli_query(
                        $this->conn,
                        "SELECT st.*, u.full_name, c.class_name, s.section_name
                         FROM students st
                         INNER JOIN users u ON u.id = st.user_id
                         LEFT JOIN classes c ON c.class_id = st.class_id
                         LEFT JOIN sections s ON s.section_id = st.section_id
                         WHERE st.student_id IN ($student_list)
                         ORDER BY u.full_name ASC"
                    );

                    while ($row = mysqli_fetch_assoc($query)) {
                        $data['extra']['children'][] = $row;
                    }
                }
                break;
                case 'driver':

                $result = mysqli_query(

                $this->conn,

                "SELECT *
                FROM transport_staff
                WHERE user_id = '$user_id'
                AND staff_type='driver'
                LIMIT 1"

                );

                $roleData = mysqli_fetch_assoc($result);

                break;
        }

        return $data;
    }

    public function getPhotoUrl(?array $user): string
    {
        if (!$user) {
            return '';
        }

        $photo = $user['profile_photo'] ?? 'default.svg';

        if ($photo !== 'default.svg' && file_exists(
            dirname(__DIR__, 2) . '/assets/uploads/profile/' . $photo
        )) {
            return BASE_URL . 'assets/uploads/profile/' . $photo;
        }

        return '';
    }
}
