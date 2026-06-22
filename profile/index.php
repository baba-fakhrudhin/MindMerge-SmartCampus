<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../shared/services/ProfileService.php';
require_once __DIR__ . '/../shared/services/TeacherScopeService.php';

$user = $_SESSION['user'];
$user_id = (int) $user['id'];
$role = strtolower($user['role'] ?? '');
$edit_mode = isset($_GET['edit']) && canEdit('profile');

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData($user_id, $role);
$photoUrl = $profileService->getPhotoUrl($data['user']);
$teacherScope = $role === 'teacher' ? new TeacherScopeService($conn, $user_id) : null;

if ($edit_mode && isset($_POST['save_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $profile_photo = $user['profile_photo'];

    if (!empty($_FILES['profile_photo']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed, true)) {
            $file_name = 'time_' . time() . '.' . $ext;
            $upload_path = __DIR__ . '/../assets/uploads/profile/' . $file_name;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                $profile_photo = $file_name;
            }
        }
    }

    mysqli_query(
        $conn,
        "UPDATE users SET full_name='$full_name', phone='$phone', profile_photo='$profile_photo' WHERE id='$user_id'"
    );

    if ($role === 'student' && !empty($data['role_data'])) {
        $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
        mysqli_query($conn, "UPDATE students SET address='$address' WHERE user_id='$user_id'");
    }

    if ($role === 'teacher' && !empty($data['role_data'])) {
        $qualification = mysqli_real_escape_string($conn, $_POST['qualification'] ?? '');
        mysqli_query($conn, "UPDATE teachers SET qualification='$qualification' WHERE user_id='$user_id'");
    }

    if ($role === 'parent' && !empty($data['role_data'])) {
        $relationship = mysqli_real_escape_string($conn, $_POST['relationship_name'] ?? '');
        mysqli_query($conn, "UPDATE parents SET relationship_name='$relationship' WHERE user_id='$user_id'");
    }
    
    if ($role === 'driver' && !empty($data['role_data'])) {

    $license_number = mysqli_real_escape_string(
        $conn,
        $_POST['license_number'] ?? ''
    );

    $emergency_contact = mysqli_real_escape_string(
        $conn,
        $_POST['emergency_contact'] ?? ''
    );

    $address = mysqli_real_escape_string(
        $conn,
        $_POST['address'] ?? ''
    );

    mysqli_query(

        $conn,

        "UPDATE transport_staff
         SET
         license_number='$license_number',
         emergency_contact='$emergency_contact',
         address='$address'
         WHERE user_id='$user_id'
         AND staff_type='driver'"

    );
    }
    header('Location: index.php?updated=1');
    exit();
}


    $updated = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $_SESSION['user'] = mysqli_fetch_assoc($updated);
    $user = $_SESSION['user'];
    $data = $profileService->getProfileData($user_id, $role);
    $photoUrl = $profileService->getPhotoUrl($data['user']);

    

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/layout.css">
<link rel="stylesheet" href="../assets/css/components.css">
<link rel="stylesheet" href="../assets/css/portals.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app-layout">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/../partials/topbar.php'; ?>
<div class="page-content">

<?php if (isset($_GET['updated'])) { ?>
<div class="success-alert"><i class="fa-solid fa-circle-check"></i> Profile updated successfully.</div>
<?php } ?>

<div class="page-header">
<div>
<h1><?php echo $edit_mode ? 'Edit Profile' : 'My Profile'; ?></h1>
<p><?php echo ucfirst($role); ?> account information<?php echo $edit_mode ? '' : ' and details'; ?>.</p>
</div>
<?php if (!$edit_mode) { ?>
<div class="quick-actions">
<?php if (canEdit('profile')) { ?>
<a href="index.php?edit=1" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit Profile</a>
<?php } ?>
<a href="print.php" class="btn btn-secondary" target="_blank"><i class="fa-solid fa-print"></i> Print Profile</a>
</div>
<?php } else { ?>
<a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
<?php } ?>
</div>

<?php if ($edit_mode) { ?>

<form method="POST" enctype="multipart/form-data" class="dashboard-section">
<div class="form-grid">
<div class="form-group">
<label class="form-label">Full Name</label>
<input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
</div>
<div class="form-group">
<label class="form-label">Email</label>
<input type="email" class="form-input disabled-field" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
</div>
<div class="form-group">
<label class="form-label">Phone</label>
<input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
</div>
<div class="form-group">
<label class="form-label">Profile Photo</label>
<input type="file" name="profile_photo" class="form-input" accept=".png,.jpg,.jpeg,.webp">
</div>

<?php if ($role === 'student' && !empty($data['role_data'])) { ?>
<div class="form-group">
<label class="form-label">Student ID</label>
<input type="text" class="form-input disabled-field" value="<?php echo htmlspecialchars($data['role_data']['student_id'] ?? ''); ?>" disabled>
</div>
<div class="form-group">
<label class="form-label">Class</label>
<input type="text" class="form-input disabled-field" value="<?php echo htmlspecialchars($data['role_data']['class_name'] ?? ''); ?>" disabled>
</div>
<div class="form-group">
<label class="form-label">Section</label>
<input type="text" class="form-input disabled-field" value="<?php echo htmlspecialchars($data['role_data']['section_name'] ?? ''); ?>" disabled>
</div>
<div class="form-group">
<label class="form-label">Address</label>
<textarea name="address" class="form-textarea"><?php echo htmlspecialchars($data['role_data']['address'] ?? ''); ?></textarea>
</div>
<?php } ?>

<?php if ($role === 'driver' && !empty($data['role_data'])) { ?>

<div class="form-group">
<label class="form-label">License Number</label>
<input
type="text"
name="license_number"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['license_number'] ?? ''); ?>">
</div>

<div class="form-group">
<label class="form-label">Emergency Contact</label>
<input
type="text"
name="emergency_contact"
class="form-input"
value="<?php echo htmlspecialchars($data['role_data']['emergency_contact'] ?? ''); ?>">
</div>

<div class="form-group">
<label class="form-label">Address</label>
<textarea
name="address"
class="form-textarea"><?php echo htmlspecialchars($data['role_data']['address'] ?? ''); ?></textarea>
</div>

<div class="form-group">
<label class="form-label">Status</label>
<input
type="text"
class="form-input disabled-field"
value="<?php echo ucfirst($data['role_data']['status'] ?? ''); ?>"
disabled>
</div>

<?php } ?>
<?php if ($role === 'teacher' && !empty($data['role_data'])) { ?>
<div class="form-group">
<label class="form-label">Teacher ID</label>
<input type="text" class="form-input disabled-field" value="<?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? ''); ?>" disabled>
</div>
<div class="form-group">
<label class="form-label">Qualification</label>
<input type="text" name="qualification" class="form-input" value="<?php echo htmlspecialchars($data['role_data']['qualification'] ?? ''); ?>">
</div>
<?php } ?>

<?php if ($role === 'parent' && !empty($data['role_data'])) { ?>
<div class="form-group">
<label class="form-label">Relationship</label>
<input type="text" name="relationship_name" class="form-input" value="<?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? ''); ?>">
</div>
<?php } ?>
</div>
<button type="submit" name="save_profile" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
</form>

<?php } else { ?>

<div class="dashboard-grid" style="grid-template-columns:320px 1fr;">
<div class="dashboard-card" style="text-align:center;">
<?php if ($photoUrl) { ?>
<img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Profile" style="width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 16px;display:block;">
<?php } else { ?>
<div class="profile-letter" style="width:80px;height:80px;margin:0 auto 16px;font-size:32px;"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
<?php } ?>
<h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
<span class="status primary"><?php echo ucfirst($role); ?></span>
</div>

<div class="dashboard-card">
<h3>Account Details</h3>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>

<?php if ($role === 'student' && !empty($data['role_data'])) { ?>
<p><strong>Student ID:</strong> <?php echo htmlspecialchars($data['role_data']['student_id'] ?? '-'); ?></p>
<p><strong>Class:</strong> <?php echo htmlspecialchars($data['role_data']['class_name'] ?? '-'); ?></p>
<p><strong>Section:</strong> <?php echo htmlspecialchars($data['role_data']['section_name'] ?? '-'); ?></p>
<p><strong>Address:</strong> <?php echo htmlspecialchars($data['role_data']['address'] ?? '-'); ?></p>
<?php } ?>

<?php if ($role === 'teacher' && !empty($data['role_data'])) { ?>
<p><strong>Teacher ID:</strong> <?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? '-'); ?></p>
<p><strong>Qualification:</strong> <?php echo htmlspecialchars($data['role_data']['qualification'] ?? '-'); ?></p>
<h4 style="margin-top:20px;">Subjects</h4>
<p><?php echo htmlspecialchars(implode(', ', $data['extra']['subjects'] ?? []) ?: '-'); ?></p>
<h4 style="margin-top:20px;">Assigned Classes</h4>
<ul><?php foreach ($teacherScope?->getAssignedClassSectionPairs() ?? [] as $p) { ?>
<li><?php echo htmlspecialchars($p['class_name'] . ' — ' . $p['section_name']); ?></li>
<?php } ?></ul>
<?php } ?>

<?php if ($role === 'parent' && !empty($data['role_data'])) { ?>
<p><strong>Relationship:</strong> <?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? '-'); ?></p>
<h4 style="margin-top:20px;">Children</h4>
<ul><?php foreach ($data['extra']['children'] ?? [] as $child) { ?>
<li><?php echo htmlspecialchars($child['full_name'] . ' (' . $child['student_id'] . ') — ' . ($child['class_name'] ?? '') . ' ' . ($child['section_name'] ?? '')); ?></li>
<?php } ?></ul>
<?php } ?>
<?php if ($role === 'driver' && !empty($data['role_data'])) { ?>

<p><strong>Staff Type:</strong>
<?php echo ucfirst($data['role_data']['staff_type'] ?? '-'); ?>
</p>

<p><strong>License Number:</strong>
<?php echo htmlspecialchars($data['role_data']['license_number'] ?? '-'); ?>
</p>

<p><strong>Emergency Contact:</strong>
<?php echo htmlspecialchars($data['role_data']['emergency_contact'] ?? '-'); ?>
</p>

<p><strong>Address:</strong>
<?php echo htmlspecialchars($data['role_data']['address'] ?? '-'); ?>
</p>

<p><strong>Status:</strong>
<?php echo ucfirst($data['role_data']['status'] ?? '-'); ?>
</p>

<?php } ?>
<?php if ($role === 'admin') { ?>
<p><strong>Admin ID:</strong> <?php echo htmlspecialchars($user['admin_id'] ?? '-'); ?></p>
<?php } ?>
</div>
</div>

<?php } ?>

</div>
</div>
</div>
<script src="../assets/js/common.js"></script>
</body>
</html>
