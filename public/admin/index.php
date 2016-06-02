<?php
require_once("../../includes/database.php");
require_once("../../includes/functions.php");
require_once("../../includes/session.php");
require_once("../../includes/database.php");
require_once("../../includes/user.php");

if (!$session->is_logged_in()) { redirect_to("login.php"); }
    require_once(".././layouts/admin_header.php");

?>

<h2>Menu</h2>
<ul>
    <li><a href="logfile.php">View Log file</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>

<?php
require_once(".././layouts/admin_footer.php");
?>
