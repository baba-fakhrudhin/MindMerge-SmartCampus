    <?php

    include('../config/auth.php');
    include('../config/db.php');

    $created_by = intval($_SESSION['user']['id']);
    $error = '';
    $today = date('Y-m-d');

    if (isset($_POST['save_attendance'])) {

        $class_id = intval($_POST['class_id'] ?? 0);
        $section_id = intval($_POST['section_id'] ?? 0);

        $attendance_date = mysqli_real_escape_string(
            $conn,
            $_POST['attendance_date'] ?? ''
        );

        $attendance_mode = mysqli_real_escape_string(
            $conn,
            $_POST['attendance_mode'] ?? 'daily'
        );

        $remarks = mysqli_real_escape_string(
            $conn,
            trim($_POST['remarks'] ?? '')
        );

        if (
            empty($class_id) ||
            empty($section_id) ||
            empty($attendance_date)
        ) {
            $error = 'Please fill all required fields.';
        }

        $attendance_day = strtolower(
            date(
                'l',
                strtotime($attendance_date)
            )
        );
        $timetable_exists = mysqli_query(

$conn,

"SELECT timetable_id

FROM timetables

WHERE class_id='$class_id'

AND section_id='$section_id'

LIMIT 1"

);

if(
mysqli_num_rows($timetable_exists) == 0
){

$error =
'No timetable exists for the selected class and section.';

}

        $period_id = null;
        $subject_id = null;
        $teacher_assignment_id = null;

        if (
            $attendance_mode === 'period'
        ) {

            $period_id = intval(
                $_POST['period_id'] ?? 0
            );

            $subject_id = intval(
                $_POST['subject_id'] ?? 0
            );

            $teacher_assignment_id = intval(
                $_POST['teacher_assignment_id'] ?? 0
            );

            if ($period_id <= 0) {
                $error = 'Please select a period.';
            }
        }

        if ($error === '') {

            $period_condition = 'period_id IS NULL';

            if ($attendance_mode === 'period') {
                $timetable_check = mysqli_query(

                    $conn,

                    "SELECT te.entry_id

                    FROM timetable_entries te

                    JOIN timetables t
                    ON te.timetable_id=t.timetable_id

                    WHERE

                    t.class_id='$class_id'

                    AND

                    t.section_id='$section_id'

                    AND

                    te.period_id='$period_id'

                    AND

                    te.day_of_week='$attendance_day'

                    LIMIT 1"

                    );

                    if(
                    mysqli_num_rows($timetable_check) == 0
                    ){

                    $error =
                    'No timetable exists for the selected day and period.';

                    }
                $period_condition = "period_id='$period_id'";
            }

            $duplicate_query = mysqli_query(
                $conn,
                "SELECT attendance_id
                FROM attendance
                WHERE class_id='$class_id'
                AND section_id='$section_id'
                AND attendance_date='$attendance_date'
                AND attendance_mode='$attendance_mode'
                AND $period_condition
                LIMIT 1"
            );

            if (
                $duplicate_query &&
                mysqli_num_rows($duplicate_query) > 0
            ) {
                $error = 'Attendance already exists for the selected criteria.';
            }
        }

        if (
            $error === '' &&
            (
                !isset($_POST['student_ids']) ||
                count($_POST['student_ids']) === 0
            )
        ) {
            $error = 'No students found to mark attendance.';
        }

        if ($error === '') {

            mysqli_begin_transaction($conn);

            try {

                $period_sql =
                    $period_id
                    ? "'$period_id'"
                    : "NULL";

                $subject_sql =
                    $subject_id
                    ? "'$subject_id'"
                    : "NULL";

                $teacher_assignment_sql =
                    $teacher_assignment_id
                    ? "'$teacher_assignment_id'"
                    : "NULL";

                $insert_attendance = mysqli_query(
                    $conn,
                    "INSERT INTO attendance (

                        class_id,
                        section_id,
                        attendance_date,
                        attendance_day,
                        attendance_mode,
                        period_id,
                        subject_id,
                        teacher_assignment_id,
                        remarks,
                        created_by

                    ) VALUES (

                        '$class_id',
                        '$section_id',
                        '$attendance_date',
                        '$attendance_day',
                        '$attendance_mode',
                        $period_sql,
                        $subject_sql,
                        $teacher_assignment_sql,
                        '$remarks',
                        '$created_by'

                    )"
                );

                if (!$insert_attendance) {
                    throw new Exception(
                        mysqli_error($conn)
                    );
                }

                $attendance_id = mysqli_insert_id($conn);

                if (!$attendance_id) {
                    throw new Exception(
                        'Failed to create attendance record.'
                    );
                }

                foreach ($_POST['student_ids'] as $student_id) {

                    $student_id = intval($student_id);

                    $status = mysqli_real_escape_string(
                        $conn,
                        $_POST['status_' . $student_id] ?? 'present'
                    );

                    $allowed_statuses = [
                        'present',
                        'absent',
                        'late',
                        'leave',
                        'medical_leave',
                        'od'
                    ];

                    if (
                        !in_array(
                            $status,
                            $allowed_statuses
                        )
                    ) {
                        $status = 'present';
                    }

                    $insert_record = mysqli_query(
                        $conn,
                        "INSERT INTO attendance_records (

                            attendance_id,
                            student_id,
                            status

                        ) VALUES (

                            '$attendance_id',
                            '$student_id',
                            '$status'

                        )"
                    );

                    if (!$insert_record) {
                        throw new Exception(
                            mysqli_error($conn)
                        );
                    }
                }

                mysqli_commit($conn);
                $_SESSION['attendance_success'] = true;

            header(
        'Location:index.php?success=marked'
    );
                exit();

            } catch (Exception $e) {

                mysqli_rollback($conn);

                $error = $e->getMessage();
            }
        }
    }

    $class_query = mysqli_query(
        $conn,
        "SELECT *
        FROM classes
        WHERE status='active'
        ORDER BY class_name ASC"
    );

    $period_query = mysqli_query(
        $conn,
        "SELECT *
        FROM periods
        WHERE attendance_allowed='yes'
        ORDER BY sort_order ASC"
    );
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

    <meta charset="UTF-8">

    <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>
    Mark Attendance | MindMerge SmartCampus
    </title>

    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
    .mode-card{

        display:flex;
        align-items:center;
        gap:12px;

        padding:18px;

        border-radius:14px;

        cursor:pointer;

        border:1px solid rgba(148,163,184,.15);

        transition:.25s ease;

        background:var(--card);

    }

    .mode-card:hover{

        transform:translateY(-2px);

    }

    .mode-card input{

        margin:0;

    }

    .mode-card.active{

        border-color:var(--primary);

        background:rgba(59,130,246,.08);

    }
    .mode-card input[type="radio"]{

        accent-color:var(--primary);

    }

    .hidden{

        display:none !important;

    }

    .student-row{

        display:flex;

        justify-content:space-between;

        align-items:center;

        gap:20px;

        padding:14px;

        border:1px solid rgba(148,163,184,.15);

        border-radius:14px;

        margin-bottom:12px;

        transition:.2s ease;

    }

    .student-row:hover{

        transform:translateY(-1px);

    }

    .student-info{

        display:flex;

        flex-direction:column;

        gap:4px;

    }

    .student-name{

        font-weight:600;

    }

    .student-id{

        font-size:13px;

        color:var(--muted);

    }

    .dashboard-card h4{

        margin-bottom:8px;

    }

    .dashboard-card p{

        margin:0;

    }
    .attendance-summary-grid{

    grid-template-columns:
    repeat(3,1fr);

    gap:16px;

    }

    .attendance-summary-grid .dashboard-card{

    text-align:center;

    padding:22px;

    }

    .attendance-summary-grid p{

    font-size:28px;
    font-weight:700;
    margin-top:8px;

    }

    @media(max-width:768px){

        .student-row{

            flex-direction:column;

            align-items:flex-start;

        }

        .student-row select{

            width:100% !important;

        }

    }

    body.dark-mode .mode-card{

        border-color:#29476d;

    }

    body.dark-mode .student-row{

        border-color:#29476d;

    }
    </style>

    </head>

    <body>

    <div class="app-layout">

    <?php include('../partials/sidebar.php'); ?>

    <div class="main-content">

    <?php include('../partials/topbar.php'); ?>
    <div class="page-content">

        <div class="page-header">

            <div>
                <h1>Student Attendance</h1>
                <p>
                    Record daily and period-wise for students.
                </p>
            </div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">

                <a href="index.php" class="btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>

            </div>

        </div>

        <?php if ($error != '') { ?>

            <div
                style="
                    background:#fee2e2;
                    color:#991b1b;
                    padding:14px 18px;
                    border-radius:14px;
                    margin-bottom:20px;
                    border:1px solid #fecaca;
                "
            >
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>
        <?php if(isset($_GET['success']) && $_GET['success'] === 'marked'){ ?>

    <div
        style="
            background:#dcfce7;
            color:#166534;
            padding:14px 18px;
            border-radius:14px;
            margin-bottom:20px;
            border:1px solid #bbf7d0;
        "
    >

        <i class="fa-solid fa-circle-check"></i>
        Attendance marked successfully.

    </div>

    <?php } ?>

        <form method="POST" id="attendanceForm">

            <div class="dashboard-grid">

                <div class="dashboard-card">

                    <div class="card-header">
                        <h3>Attendance Mode</h3>
                    </div>

                    <div
                        style="
                            display:grid;
                            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                            gap:16px;
                        "
                    >

                        <label class="mode-card active">

                            <input
                                type="radio"
                                name="attendance_mode"
                                value="daily"
                                checked
                            >

                            <div>
                                <strong>Daily Attendance</strong>
                                <div style="font-size:13px;color:var(--muted);margin-top:4px;">
                                    Mark attendance for the whole day.
                                </div>
                            </div>

                        </label>

                        <label class="mode-card">

                            <input
                                type="radio"
                                name="attendance_mode"
                                value="period"
                            >

                            <div>
                                <strong>Period Attendance</strong>
                                <div style="font-size:13px;color:var(--muted);margin-top:4px;">
                                    Mark attendance period-wise using timetable.
                                </div>
                            </div>

                        </label>

                    </div>

                </div>

            </div>

            <div class="dashboard-section">

                <div class="section-header">
                    <h2>Attendance Details</h2>
                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label class="form-label">
                            Class
                        </label>

                        <select
                            name="class_id"
                            id="class_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Class
                            </option>

                            <?php while ($class = mysqli_fetch_assoc($class_query)) { ?>

                                <option
                                    value="<?php echo $class['class_id']; ?>"
                                >
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="form-group">

                        <label class="form-label">
                            Section
                        </label>

                        <select
                            name="section_id"
                            id="section_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Select Class First
                            </option>
                        </select>

                    </div>

                </div>

                <div class="form-grid">

                    <div class="form-group">

                        <label class="form-label">
                            Attendance Date
                        </label>

                        <input
                            type="date"
                            name="attendance_date"
                            id="attendance_date"
                            class="form-input"
                            value="<?php echo $today; ?>"
                            max="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                    <div
                        class="form-group hidden"
                        id="periodContainer"
                    >

                        <label class="form-label">
                            Period
                        </label>

                        <select
                            name="period_id"
                            id="period_id"
                            class="form-select"
                        >

                            <option value="">
                                Select Period
                            </option>

                            <?php while ($period = mysqli_fetch_assoc($period_query)) { ?>

                                <option
                                    value="<?php echo $period['period_id']; ?>"
                                >
                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>

                <input
                    type="hidden"
                    name="subject_id"
                    id="subject_id"
                >

                <input
                    type="hidden"
                    name="teacher_assignment_id"
                    id="teacher_assignment_id"
                >

            </div>

            <div
                id="timetableInfo"
                class="dashboard-section hidden"
            >

                <div class="section-header">
                    <h2>Timetable Information</h2>
                </div>

                <div class="dashboard-grid">

                    <div class="dashboard-card">

                        <h4>Subject</h4>

                        <p id="subjectText">
                            -
                        </p>

                    </div>

                    <div class="dashboard-card">

                        <h4>Teacher</h4>

                        <p id="teacherText">
                            -
                        </p>

                    </div>

                    <div class="dashboard-card">

                        <h4>Room</h4>

                        <p id="roomText">
                            -
                        </p>

                    </div>

                </div>

            </div>

            <div
                id="studentsSection"
                class="dashboard-section hidden"
            >

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:12px;
                        flex-wrap:wrap;
                        margin-bottom:20px;
                    "
                >

                    <div>

                        <h2 style="margin-bottom:5px;">
                            Student Attendance
                        </h2>

                        <p style="color:var(--muted);">
                            All students are marked present by default.
                        </p>

                    </div>

                    <div
                        style="
                            display:flex;
                            gap:10px;
                            flex-wrap:wrap;
                        "
                    >

                        <button
                            type="button"
                            id="markAllPresent"
                            class="btn"
                        >
                            <i class="fa-solid fa-check"></i>
                            Mark All Present
                        </button>

                        <button
                            type="button"
                            id="markAllAbsent"
                            class="btn"
                        >
                            <i class="fa-solid fa-user-xmark"></i>
                            Mark All Absent
                        </button>

                    </div>

                </div>

                <div
                    class="dashboard-card"
                style="
                    background:rgba(59,130,246,.08);
                    border:1px solid rgba(59,130,246,.15);
                    padding:18px;
                    margin-bottom:20px;
                    "
                >

                    <strong>
                        Quick Note:
                    </strong>

                    Change only students who are absent, late, on leave,
                    medical leave, or OD. Everyone else remains present.

                </div>

                <div
                id="attendanceSummary"
                class="dashboard-grid attendance-summary-grid"
                >

                    <div class="dashboard-card">
                        <h4>Total Students</h4>
                        <p id="totalStudents">0</p>
                    </div>

                    <div class="dashboard-card">
                        <h4>Present</h4>
                        <p id="presentStudents">0</p>
                    </div>

                    <div class="dashboard-card">
                        <h4>Absent</h4>
                        <p id="absentStudents">0</p>
                    </div>

                </div>

                <div id="studentsContainer">

                    <div
                        style="
                            text-align:center;
                            padding:50px;
                            color:var(--muted);
                        "
                    >
                        Select Class and Section first.
                    </div>

                </div>

                <div class="form-group" style="margin-top:20px;">

                    <label class="form-label">
                        Attendance Remarks
                    </label>

                    <textarea
                        name="remarks"
                        class="form-textarea"
                        rows="4"
                        placeholder="Optional remarks..."
                    ></textarea>

                </div>

                <div
                    style="
                        display:flex;
                        gap:12px;
                        flex-wrap:wrap;
                        margin-top:25px;
                    "
                >

                    <button
                        type="submit"
                        name="save_attendance"
                        class="btn btn-primary"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Attendance

                    </button>

                    <a
                        href="index.php"
                        class="btn"
                    >
                        Cancel
                    </a>

                </div>

            </div>

        </form>

    </div>

    <script>

    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section_id');
    const periodSelect = document.getElementById('period_id');
    const dateInput = document.getElementById('attendance_date');

    const timetableInfo = document.getElementById('timetableInfo');
    const studentsSection = document.getElementById('studentsSection');
    const studentsContainer = document.getElementById('studentsContainer');
    const periodContainer = document.getElementById('periodContainer');

    const totalStudentsEl = document.getElementById('totalStudents');
    const presentStudentsEl = document.getElementById('presentStudents');
    const absentStudentsEl = document.getElementById('absentStudents');

    const modeInputs = document.querySelectorAll(
        'input[name="attendance_mode"]'
    );

    function getSelectedMode() {

        const selected =
            document.querySelector(
                'input[name="attendance_mode"]:checked'
            );

        return selected ? selected.value : 'daily';
    }

    function showLoading(container) {

        container.innerHTML = `
            <div
                style="
                    text-align:center;
                    padding:50px;
                    color:var(--muted);
                "
            >
                <i class="fa-solid fa-spinner fa-spin"></i>
                Loading...
            </div>
        `;
    }

    function resetSummary() {

        if (totalStudentsEl) totalStudentsEl.innerText = '0';
        if (presentStudentsEl) presentStudentsEl.innerText = '0';
        if (absentStudentsEl) absentStudentsEl.innerText = '0';
    }

    function updateAttendanceSummary() {

        const selects =
            document.querySelectorAll(
                '#studentsContainer select'
            );

        let total = selects.length;
        let present = 0;
        let absent = 0;

        selects.forEach(function(select) {

            if (select.value === 'present') {
                present++;
            }

            if (select.value === 'absent') {
                absent++;
            }

        });

        if (totalStudentsEl) {
            totalStudentsEl.innerText = total;
        }

        if (presentStudentsEl) {
            presentStudentsEl.innerText = present;
        }

        if (absentStudentsEl) {
            absentStudentsEl.innerText = absent;
        }
    }

    function attachStatusListeners() {

        document
            .querySelectorAll(
                '#studentsContainer select'
            )
            .forEach(function(select) {

                select.addEventListener(
                    'change',
                    updateAttendanceSummary
                );

            });

        updateAttendanceSummary();
    }

    modeInputs.forEach(function(input) {

        input.addEventListener(
            'change',
            function() {

                document
                    .querySelectorAll('.mode-card')
                    .forEach(function(card) {

                        card.classList.remove('active');

                    });

                this
                    .closest('.mode-card')
                    .classList.add('active');

                if (this.value === 'period') {

                    periodContainer.classList.remove(
                        'hidden'
                    );

                } else {

                    periodContainer.classList.add(
                        'hidden'
                    );

                    timetableInfo.classList.add(
                        'hidden'
                    );

                    document.getElementById(
                        'subject_id'
                    ).value = '';

                    document.getElementById(
                        'teacher_assignment_id'
                    ).value = '';

                }

                loadStudents();

            }
        );

    });

    classSelect.addEventListener(
        'change',
        loadSections
    );

    sectionSelect.addEventListener(
        'change',
        loadStudents
    );

    periodSelect.addEventListener(
        'change',
        loadTimetableInfo
    );

    dateInput.addEventListener(
        'change',
        function() {

            if (
                getSelectedMode() === 'period'
            ) {
                loadTimetableInfo();
            }

        }
    );

    function loadSections() {

        const classId =
            classSelect.value;

        if (!classId) {

            sectionSelect.innerHTML =
                '<option value="">Select Class First</option>';

            studentsSection.classList.add(
                'hidden'
            );

            return;
        }

        fetch(
            'get_sections.php?class_id=' +
            encodeURIComponent(classId)
        )

        .then(response => response.json())

        .then(data => {

            let html =
                '<option value="">Select Section</option>';

            data.forEach(function(section) {

                html += `
                    <option value="${section.section_id}">
                        ${section.section_name}
                    </option>
                `;

            });

            sectionSelect.innerHTML = html;

            studentsSection.classList.add(
                'hidden'
            );

            resetSummary();

        })

        .catch(() => {

            alert(
                'Unable to load sections.'
            );

        });

    }

    function loadStudents() {

        const classId =
            classSelect.value;

        const sectionId =
            sectionSelect.value;

        if (!classId || !sectionId) {

            studentsSection.classList.add(
                'hidden'
            );

            resetSummary();

            return;
        }

                showLoading(
                studentsContainer
                );

                fetch(
                'check_timetable_exists.php?class_id=' +
                encodeURIComponent(classId) +
                '&section_id=' +
                encodeURIComponent(sectionId)
                )

                .then(response => response.json())

               .then(result => {

                    if(!result.success){

                    studentsSection.classList.remove(
                    'hidden'
                    );

                    studentsContainer.innerHTML = `
                    <div
                    style="
                    text-align:center;
                    padding:50px;
                    color:#dc2626;
                    font-weight:600;
                    "
                    >
                    No timetable found for this class and section.
                    <br><br>
                    Create timetable first before marking attendance.
                    </div>
                    `;

                    resetSummary();

                    return;
                    }

                    studentsSection.classList.remove(
                    'hidden'
                    );

                    fetch(
                    'get_students.php?class_id=' +
                    encodeURIComponent(classId) +
                    '&section_id=' +
                    encodeURIComponent(sectionId)
                    )

                    .then(response => response.json())

                    .then(data => {

                    let html = '';

                    if(!data || data.length === 0){

                    html = `
                    <div
                    style="
                    text-align:center;
                    padding:50px;
                    color:var(--muted);
                    "
                    >
                    No students found.
                    </div>
                    `;

                    }else{

                    data.forEach(function(student){

                    html += `
                    <div class="student-row">

                    <div class="student-info">

                    <div class="student-name">
                    ${student.full_name}
                    </div>

                    <div class="student-id">
                    ${student.student_id}
                    </div>

                    </div>

                    <div>

                    <input
                    type="hidden"
                    name="student_ids[]"
                    value="${student.id}"
                    >

                    <select
                    name="status_${student.id}"
                    class="form-select"
                    style="width:180px;"
                    >

                    <option value="present" selected>
                    Present
                    </option>

                    <option value="absent">
                    Absent
                    </option>

                    <option value="late">
                    Late
                    </option>

                    <option value="leave">
                    Leave
                    </option>

                    <option value="medical_leave">
                    Medical Leave
                    </option>

                    <option value="od">
                    OD
                    </option>

                    </select>

                    </div>

                    </div>
                    `;

                    });

                    }

                    studentsContainer.innerHTML = html;

                    attachStatusListeners();

                    if(getSelectedMode()==='period'){
                    loadTimetableInfo();
                    }

                    });

                    });

    }
    

    function loadTimetableInfo() {


        if (
            getSelectedMode() !== 'period'
        ) {
            return;
        }

        const classId =
            classSelect.value;

        const sectionId =
            sectionSelect.value;

        const periodId =
            periodSelect.value;

        const attendanceDate =
            dateInput.value;

        if (
            !classId ||
            !sectionId ||
            !periodId
        ) {

            timetableInfo.classList.add(
                'hidden'
            );

            return;
        }

        fetch(

            'get_timetable_info.php' +

            '?class_id=' +
            encodeURIComponent(classId) +

            '&section_id=' +
            encodeURIComponent(sectionId) +

            '&period_id=' +
            encodeURIComponent(periodId) +

            '&attendance_date=' +
            encodeURIComponent(attendanceDate)

        )

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                document.getElementById(
                    'subjectText'
                ).innerText =
                    data.subject_name || '-';

                document.getElementById(
                    'teacherText'
                ).innerText =
                    data.teacher_name || '-';

                document.getElementById(
                    'roomText'
                ).innerText =
                    data.room_no || '-';

                document.getElementById(
                    'subject_id'
                ).value =
                    data.subject_id || '';

                document.getElementById(
                    'teacher_assignment_id'
                ).value =
                    data.teacher_assignment_id || '';

                timetableInfo.classList.remove(
                    'hidden'
                );

            } else {

                timetableInfo.classList.add(
                    'hidden'
                );

            }

        })

        .catch(() => {

            timetableInfo.classList.add(
                'hidden'
            );

        });

    }

    document.getElementById(
        'markAllPresent'
    )?.addEventListener(
        'click',
        function() {

            document
                .querySelectorAll(
                    '#studentsContainer select'
                )
                .forEach(function(select) {

                    select.value = 'present';

                });

            updateAttendanceSummary();

        }
    );

    document.getElementById(
        'markAllAbsent'
    )?.addEventListener(
        'click',
        function() {

            document
                .querySelectorAll(
                    '#studentsContainer select'
                )
                .forEach(function(select) {

                    select.value = 'absent';

                });

            updateAttendanceSummary();

        }
    );

    document.getElementById(
        'attendanceForm'
    )?.addEventListener(
        'submit',
        function(e) {

            const total =
                document.querySelectorAll(
                    'input[name="student_ids[]"]'
                ).length;

            if (total === 0) {

                e.preventDefault();

                alert(
                    'No students available to mark attendance.'
                );

                return false;
            }

        }
    );


    </script>



    </div>
    </div>

    </div>
    <script>

    document.addEventListener(
        'DOMContentLoaded',
        function(){

            document
                .querySelectorAll('.sidebar-menu a')
                .forEach(function(link){

                    link.classList.remove('active');

                });

            const attendanceLink =
                document.querySelector(
                    'a[href="../attendance/index.php"], a[href="index.php"]'
                );

            if(attendanceLink){
                attendanceLink.classList.add('active');
            }

        }
    );

    </script>
    <script src="../assets/js/common.js"></script>

    </body>
    </html>