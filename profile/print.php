<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../shared/services/ProfileService.php';
require_once __DIR__ . '/../shared/services/TeacherScopeService.php';

$user_id = (int) $_SESSION['user']['id'];
$role = strtolower($_SESSION['user']['role'] ?? '');

$profileService = new ProfileService($conn);
$data = $profileService->getProfileData($user_id, $role);
$photoUrl = $profileService->getPhotoUrl($data['user']);
$teacherScope = $role === 'teacher' ? new TeacherScopeService($conn, $user_id) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile — Print | MindMerge</title>
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/portals.css">
<style>
.print-profile { max-width: 800px; margin: 40px auto; padding: 20px; }
.print-profile h1 { font-size: 22px; margin-bottom: 8px; }
.print-profile hr { margin: 16px 0; border: none; border-top: 1px solid #e2e8f0; }
@media print { body { margin: 0; } }
</style>
</head>
<body onload="window.print()">
<div class="print-profile">
<h1>MindMerge SmartCampus — <?php echo ucfirst($role); ?> Profile</h1>
<hr>
<div style="display:flex;gap:24px;align-items:flex-start;">
<?php if ($photoUrl) { ?>
<img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Photo" style="width:100px;height:100px;border-radius:8px;object-fit:cover;">
<?php } ?>
<div>
<h2><?php echo htmlspecialchars($data['user']['full_name'] ?? ''); ?></h2>
<p><strong>Role:</strong> <?php echo ucfirst($role); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($data['user']['email'] ?? ''); ?></p>
<p><strong>Phone:</strong> <?php echo htmlspecialchars($data['user']['phone'] ?? '-'); ?></p>

<?php if ($role === 'student' && !empty($data['role_data'])) { ?>
<p><strong>Student ID:</strong> <?php echo htmlspecialchars($data['role_data']['student_id'] ?? '-'); ?></p>
<p><strong>Class:</strong> <?php echo htmlspecialchars($data['role_data']['class_name'] ?? '-'); ?></p>
<p><strong>Section:</strong> <?php echo htmlspecialchars($data['role_data']['section_name'] ?? '-'); ?></p>
<p><strong>Address:</strong> <?php echo htmlspecialchars($data['role_data']['address'] ?? '-'); ?></p>
<?php } ?>

<?php if ($role === 'teacher' && !empty($data['role_data'])) { ?>
<p><strong>Teacher ID:</strong> <?php echo htmlspecialchars($data['role_data']['teacher_id'] ?? '-'); ?></p>
<p><strong>Qualification:</strong> <?php echo htmlspecialchars($data['role_data']['qualification'] ?? '-'); ?></p>
<h3>Subjects</h3>
<p><?php echo htmlspecialchars(implode(', ', $data['extra']['subjects'] ?? []) ?: '-'); ?></p>
<h3>Assigned Classes</h3>
<ul><?php foreach ($teacherScope?->getAssignedClassSectionPairs() ?? [] as $p) { ?>
<li><?php echo htmlspecialchars($p['class_name'] . ' — ' . $p['section_name']); ?></li>
<?php } ?></ul>
<?php } ?>

<?php if ($role === 'parent' && !empty($data['role_data'])) { ?>
<p><strong>Relationship:</strong> <?php echo htmlspecialchars($data['role_data']['relationship_name'] ?? '-'); ?></p>
<h3>Children</h3>
<ul><?php foreach ($data['extra']['children'] ?? [] as $child) { ?>
<li><?php echo htmlspecialchars($child['full_name'] . ' (' . $child['student_id'] . ')'); ?></li>
<?php } ?></ul>
<?php } ?>
</div>
</div>
<p style="margin-top:40px;font-size:12px;color:#64748b;">Printed on <?php echo date('F j, Y g:i A'); ?></p>
</div>
</body>
</html>
