<?php

require_once __DIR__ . '/../../config/constants.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied | MindMerge SmartCampus</title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/global.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="access-denied-page">
<div class="access-denied-card">
<div class="access-denied-icon">
<i class="fa-solid fa-shield-halved"></i>
</div>
<h1>Access Denied</h1>
<p>You do not have permission to view this page. Contact your administrator if you believe this is an error.</p>
<div class="access-denied-actions">
<a href="<?php echo BASE_URL; ?>dashboard/index.php" class="btn btn-primary">
<i class="fa-solid fa-house"></i>
Go to Dashboard
</a>
<a href="<?php echo BASE_URL; ?>profile/index.php" class="btn btn-secondary">
<i class="fa-solid fa-user"></i>
My Profile
</a>
</div>
</div>
</div>

</body>
</html>
