<?php
require_once("../../includes/database.php");
require_once("../../includes/functions.php");
require_once("../../includes/session.php");
require_once("../../includes/database.php");
require_once("../../includes/photograph.php");
require_once("../../includes/user.php");

if(!$session->is_logged_in()) { redirect_to("login.php"); }
    require_once(".././layouts/admin_header.php");
    $max_file_size=1048576;
    				//expressed in Bytes
    				//10240		=10 KB
    				//102400	=100 KB
    				//1048576	= 1 MB
    				//10485760  = 10 MB
    $message="";
    if(isset($_POST['submit'])){
    	$photo = new Photograph();
    	$photo->caption=$_POST['caption'];
    	$photo->attach_file($_FILE['file_upload']);
    	if($photo->save()){
    		//Success
    		$message="$photograph uploaded successfully";
    	}
    	else{
    		//Failure
    		$message=join("<br />",$photo->errors);
    	}
    }

?>

<h2>Photo Upload </h2>
<form action="photo_upload.php" enctype="multipart/form-data" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>" />
	<p><input type="file" name="file_upload" /></p>
	<p>caption: <input type="text" name="caption" value="" /></p>
	<input type="submit" name="submit" value="Upload" />
</form>


<?php  
//this code is for footer which is admin_footer.php
require_once(".././layouts/admin_footer.php");
?>
