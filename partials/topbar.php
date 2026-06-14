<?php

require_once __DIR__ . '/../config/constants.php';

$user = $_SESSION['user'];

$full_name = $user['full_name'];
$role = ucfirst($user['role']);
$profile_photo = $user['profile_photo'];

$first_letter = strtoupper(substr($full_name, 0, 1));

$notif_base = BASE_URL . 'notifications/';

?>

<div class="topbar">

<div class="topbar-left">

<button type="button" id="openSidebar" class="menu-toggle">
<i class="fa-solid fa-bars"></i>
</button>

<div>
<h2>MindMerge SmartCampus</h2>
<p class="topbar-subtitle">Smart School ERP Platform</p>
</div>

</div>

<div class="topbar-right">

<button type="button" id="themeToggle" class="topbar-btn" title="Toggle Theme">
<i class="fa-solid fa-moon"></i>
</button>

<div class="notif-bell-wrap" id="notifBellWrap" data-base="<?php echo htmlspecialchars($notif_base); ?>">

<button type="button" class="topbar-btn notif-bell-btn" id="notifBellBtn" title="Notifications" aria-label="Notifications">
<i class="fa-solid fa-bell"></i>
<span class="notif-bell-badge" id="notifBellBadge"></span>
</button>

<div class="notif-dropdown" id="notifDropdown">

<div class="notif-dropdown-header">
<h4>Notifications</h4>
<div class="notif-dropdown-actions">
<button type="button" id="notifMarkAllBtn">Mark all read</button>
</div>
</div>

<div class="notif-dropdown-list" id="notifDropdownList">
<div class="notif-dropdown-empty">
<i class="fa-solid fa-spinner fa-spin"></i>
<p>Loading...</p>
</div>
</div>

<div class="notif-dropdown-footer">
<a href="<?php echo $notif_base; ?>index.php">View all notifications</a>
</div>

</div>

</div>

<div class="topbar-profile">

<?php if ($profile_photo != 'default.svg' && file_exists(__DIR__ . "/../assets/uploads/profile/" . $profile_photo)) { ?>

<img src="<?php echo BASE_URL; ?>assets/uploads/profile/<?php echo $profile_photo; ?>" class="profile-img" alt="">

<?php } else { ?>

<div class="profile-letter"><?php echo $first_letter; ?></div>

<?php } ?>

<div class="profile-info">
<h4><?php echo htmlspecialchars($full_name); ?></h4>
<p>
<?php echo (strtolower($role) === 'admin') ? 'Administrator' : htmlspecialchars($role); ?>
</p>
</div>

</div>

<a href="<?php echo BASE_URL; ?>auth/logout.php" class="topbar-btn" title="Logout" style="display:flex;justify-content:center;align-items:center;text-decoration:none;">
<i class="fa-solid fa-right-from-bracket"></i>
</a>

</div>

</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/notifications.css">
<script src="<?php echo BASE_URL; ?>assets/js/notifications.js" defer></script>
