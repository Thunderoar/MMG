<?php
require 'include/db_conn.php';
page_protect();

// Assuming you have the user ID stored in the session after login
$userId = $_SESSION['userid'];

// Check if the user is approved
$query = "SELECT hasApproved FROM users WHERE userid = '$userId'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$isApproved = $row['hasApproved'];

?>

<ul id="main-menu">
    <?php if ($isApproved == 'No'): ?>
        <li id="dash"><a href="index.php"><i class="entypo-gauge"></i><span>Dashboard</span></a></li>	
	    <li id="adminprofile"><a href="more-userprofile.php"><i class="entypo-folder"></i><span>Profile</span></a></li>	
        <li class="approval-message" style="color: grey; font-size: 24px; text-align: center;">
            Further Access Restricted for the time being.
        </li>
	
    <?php else: ?>
        <li id="dash"><a href="index.php"><i class="entypo-gauge"></i><span>Dashboard</span></a></li>
        <li id="paymnt"><a href="payments.php"><i class="entypo-star"></i><span>Payments</span></a></li>
        <!-- <li id="health_status"><a href="new_health_status.php"><i class="entypo-user-add"></i><span>Health Status</span></a></li> -->
		<li><a href="view_plan.php"><i class="entypo-quote"></i><span>Event Planning</span></a></li>
        <!-- <li id="planhassubopen">
            <a href="#" onclick="memberExpand(2)"><i class="entypo-quote"></i><span>Planning</span></a>
            <ul id="planExpand">

            </ul>
        </li> -->
		<!--<li><a href="viewroutine.php"><i class="entypo-alert"></i><span>View Timetable</span></a></li>
         <li id="routinehassubopen">
            <a href="#" onclick="memberExpand(4)"><i class="entypo-alert"></i><span>Timetable</span></a>
            <ul id="routineExpand">

            </ul>
        </li> -->
        <li id="adminprofile"><a href="more-userprofile.php"><i class="entypo-folder"></i><span>Profile</span></a></li>
        <li><a href="logout.php"><i class="entypo-logout"></i><span>Logout</span></a></li>
    <?php endif; ?>
</ul>
