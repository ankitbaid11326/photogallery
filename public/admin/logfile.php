<!DOCTYPE html>
    <html>
        <head>
            <style>
                .log-entries{
                    border: 2px solid;
                    color: black;
                    background-color: #D4E6F4;
                    display: inline-block;
                    padding: 20px;

                }
            </style>
        </head>
    </html>
<?php
require_once("../../includes/functions.php");
require_once("../../includes/session.php");
require_once("../../includes/database.php");
require_once("../../includes/user.php");
?>

<?php if (!$session->is_logged_in()) { redirect_to("login.php"); } ?>
<?php

$logfile = "../../logs/log.txt";
if(isset($_GET['clear'])){
if($_GET['clear'] == 'true') {
    file_put_contents($logfile, '');
    // Add the first log entry
    log_action('Logs Cleared', "by User ID {$session->user_id}");
    // redirect to this same page so that the URL won't
    // have "clear=true" anymore
    redirect_to('logfile.php');
}}
?>

<?php include_layout_template('admin_header.php'); ?>

<a href="index.php">&laquo; Back</a><br />
<br />

<h2>Log File</h2>

<p><a href="logfile.php?clear=true">Clear log file</a><p>

    <?php

    if(file_exists($logfile) && is_readable($logfile) && $handle = fopen($logfile, 'r')) {  // read
//        echo "<ul class=\"log-entries\">";
        echo "<table id=\"t01\">";
        while(!feof($handle)) {
            $entry = fgets($handle);
            if(trim($entry) != "") {
                echo "<tr>";
                echo "<th>{$entry}</th>";
                echo "</tr>";
            }

        }
        echo "</table>";
        fclose($handle);
    } else {
        echo "Could not read from {$logfile}.";
    }

    ?>

    <?php include_layout_template('admin_footer.php'); ?>
