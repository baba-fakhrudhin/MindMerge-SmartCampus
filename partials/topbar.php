<?php

$user = $_SESSION['user'];

$full_name =
$user['full_name'];

$role =
ucfirst($user['role']);

$profile_photo =
$user['profile_photo'];

$first_letter =
strtoupper(
substr(
$full_name,
0,
1
)
);

?>

<div class="topbar">

<div class="topbar-left">

<button
type="button"
id="openSidebar"
class="menu-toggle">

<i class="fa-solid fa-bars"></i>

</button>

<div>

<h2>
MindMerge SmartCampus
</h2>

<p class="topbar-subtitle">
Smart School ERP Platform
</p>

</div>

</div>

<div class="topbar-right">

<button
type="button" 
id="themeToggle"
class="topbar-btn"
title="Toggle Theme">

<i class="fa-solid fa-moon"></i>

</button>

<button
class="topbar-btn"
title="Notifications">

<i class="fa-solid fa-bell"></i>

</button>

<div class="topbar-profile">

<?php

if(
$profile_photo != 'default.svg'
&&
file_exists(
__DIR__ .
"/../assets/uploads/profile/" .
$profile_photo
)
){

?>

<img
src="../assets/uploads/profile/<?php echo $profile_photo; ?>"
class="profile-img">

<?php } else { ?>

<div class="profile-letter">

<?php echo $first_letter; ?>

</div>

<?php } ?>

<div class="profile-info">

<h4>

<?php echo $full_name; ?>

</h4>

<p>

<?php

if($role == 'Admin'){
echo "Administrator";
}
else{
echo $role;
}

?>

</p>

</div>

</div>

<a
href="../auth/logout.php"
class="topbar-btn"
title="Logout"
style="
display:flex;
justify-content:center;
align-items:center;
text-decoration:none;
">

<i class="fa-solid fa-right-from-bracket"></i>

</a>

</div>

</div>