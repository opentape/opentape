<?php

	include("opentape_common.php");

	check_cookie();

	if(is_password_set()) { 
		header("Location: " . $REL_PATH . "code/login.php");
	}
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Welcome - Set Password / Opentape</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width = 680" />
		<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/style.css" />
	</head>
	<body>
		<div class="container">
			<div class="banner">
				<h1>OPENTAPE</h1>
			    
			    <div class="ajax_status"></div>
			</div>	

        <div class="content">
            <div class="section">
                <h2>Welcome!</h2>
            </div> 
            <div class="section" id="setpassword">
                <h2>Set an admin password on your mixtape</h2>
                <div class="ajax_status">&nbsp;</div>
				<p>Enter the new password twice:</p>
				<form name="password_form" onsubmit="return false">
					<input type="password" id="password1" maxlength="255" size="20" /><br />
					<input type="password" id="password2" maxlength="255" size="20" /><br />
					<input type="button" class="small_button" id="password_button" value="create password" />
				</form>
            </div>
			<div class="section" style="display:none;" id="uploadnext">
				<a style="font-size: 24px; font-weight: bold;" href="<?php echo $REL_PATH; ?>code/edit.php">Now, add songs!</a>
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
	
	var fader = new StatusFader();
	var userFader = new StatusFader($('status'));
	
	var ajax = new Request({
		url: '<?php echo $REL_PATH; ?>code/ajax.php',
		method: 'post',
		autoCancel: 'true',
		debug: "true",
		onRequest: function() {
			fader.set('progress');
			$(document.body).setStyle('cursor','wait');
		},
		onSuccess: function(resp) {
		
			var resp = JSON.decode(resp);
						
			if (resp['status']) {
			
				fader.flash('Password created!', '#008000');
				$('setpassword').setStyle('display', 'none');
				$('uploadnext').setStyle('display', '');

			} else {
				fader.set('failure');
			}
			
			$(document.body).setStyle('cursor','default');
						
		},
		onFailure: function() {
			fader.set('failure');
			$(document.body).setStyle('cursor','default');
		}
	});
	
	if($('password1') && $('password2')) {
		$('password_button').addEvent('click',function(){
			ajax.send('command=create_password&args='+JSON.encode({password1:$('password1').value,password2:$('password2').value}));
		});
	}

	</script>	

	</body>
</html>