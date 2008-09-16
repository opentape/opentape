<?php

	include("opentape_common.php");
	
	check_cookie();
	
	if (is_logged_in()) { header("Location: " . $REL_PATH . "code/edit.php"); }
		
	if (!empty($_POST['pass'])) {
		
		$res = check_password($_POST['pass']);
		
		if($res === true) {

			error_log("Password OK, creating session...");
			$res = create_session();
			
			if($res === true) {
				header("Location: " . $REL_PATH . "code/edit.php");
			} elseif ($res==-1) { // failed to check password due to some filesystem issue
				header("Location: " . $REL_PATH . "code/warning.php");
			}
			
		} elseif ($res===false) {
			$status_msg = 'Bad Password :(';
			
		} elseif ($res==-1) { // failed to check password due to some filesystem issue
			header("Location: " . $REL_PATH . "code/warning.php");
		}
		
		
	}
	
	// check for new versions once a week
	$prefs_struct = get_opentape_prefs();
	// 604800 = week in seconds
	if (  
		 (!isset($prefs_struct['check_updates']) || $prefs_struct['check_updates'] == 1 ) &&
	 	 ((!isset($prefs_struct['last_update_check']) || (time() - $prefs_struct['last_update_check']) > 604800))
	 	) {
		$prefs_struct = check_for_update();
		if ($prefs_struct===false) { header("Location: " . $REL_PATH . "code/warning.php"); }
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Opentape / Admin Login</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/style.css" />
	</head>
	<body>
		<div class="container">
		
			<div class="banner">
				<h1>OPENTAPE</h1>
			    <ul class="nav">
					<li id="user">
					   <a id="home" href="<?php echo $REL_PATH; ?>">YOUR TAPE &rarr;</a>
				    </li>
                </ul>
                
                <div class="ajax_status"></div>
                 
			</div>				
				
		<div class="content">
		
		 <div class="section">
			<form method="post" action="<?php echo get_base_url(); ?>code/login.php" name="login">
			<label for="pass">Password:</label>
			<input name="pass" type="password" size="25" /><br />
			<input type="submit" class="button" value="LOGIN" />
			</form>
        </div>
		<div class="footer">
			<?php get_version_banner(); ?>
		</div>			
		</div>
				
	</div>
	
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-core-yc.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-more-yc.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/statusfader.js"></script>
	<script type="text/javascript">
		var first = $(document).getElement('input');
		if(first) first.focus();
	</script>
	
	<?php
		// say that this is a bad password
		if(isset($status_msg)) {
	?>
		<script type="text/javascript">
			var fader = new StatusFader();
			fader.flash("<?php echo $status_msg; ?>", '#ff0000');
		</script>
	<?php } ?>

	</body>
</html>