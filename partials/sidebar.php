<?php

$current_page = basename($_SERVER['PHP_SELF']);

?>

<div class="sidebar" id="sidebar">

<div class="sidebar-logo">

<a href="../dashboard/index.php" class="logo-box" style="text-decoration:none;color:white;">

<i class="fa-solid fa-graduation-cap"></i>

<h2>MindMerge</h2>

</a>

<button class="menu-close" id="closeSidebar">

<i class="fa-solid fa-xmark"></i>

</button>

</div>

<ul class="sidebar-menu">

<li>

<a
href="../profile/index.php"
class="<?php if($current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'],'profile')) echo 'active'; ?>"
>

<i class="fa-solid fa-user"></i>

<span>Profile</span>

</a>

</li>

<li>

<a
href="../dashboard/index.php"
class="<?php if($current_page == 'index.php' && strpos($_SERVER['REQUEST_URI'],'dashboard')) echo 'active'; ?>"
>

<i class="fa-solid fa-house"></i>

<span>Dashboard</span>

</a>

</li>

<li>

<a
href="../classes/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/classes/') !== false ? 'active' : ''; ?>">

<i class="fa-solid fa-school"></i>

<span>Classes</span>

</a>

</li>
<li>

<a
href="../sections/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/sections/') !== false ? 'active' : ''; ?>">

<i class="fa-solid fa-layer-group"></i>

<span>Sections</span>

</a>

</li>
<li>


<a
href="../students/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/students/') !== false ? 'active' : ''; ?>"
>

<i class="fa-solid fa-user-graduate"></i>

<span>Students</span>

</a>

</li>

<li>

<a
href="../teachers/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/teachers/') || strpos($_SERVER['REQUEST_URI'],'/subjects/') || strpos($_SERVER['REQUEST_URI'],'/teacher_assignments/') !== false ? 'active' : ''; ?>"
>

<i class="fa-solid fa-chalkboard-user"></i>

<span>Teachers</span>

</a>

</li>
<li>

<a
href="../period_templates/index.php"
class="<?php if(strpos($_SERVER['REQUEST_URI'],'period_templates') || strpos($_SERVER['REQUEST_URI'],'periods')) echo 'active'; ?>">

<i class="fa-solid fa-clock"></i>

<span>Schedules</span>

</a>

</li>
<li>

<a
href="../timetables/index.php"

class="<?php if(strpos($_SERVER['REQUEST_URI'],'timetables') ) echo 'active'; ?>">

<i class="fa-solid fa-calendar-days"></i>

<span>
Timetables
</span>

</a>

</li>
<li>

<a
href="../attendance/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/attendance/') !== false ? 'active' : ''; ?>"
>

<i class="fa-solid fa-calendar-check"></i>

<span>Attendance</span>

</a>

</li>

<li>

<a
href="../exams/index.php"
class="<?php echo strpos($_SERVER['REQUEST_URI'],'/exams/') !== false ? 'active' : ''; ?>"
>

<i class="fa-solid fa-file-lines"></i>

<span>Exams</span>

</a>

</li>

</ul>

</div>