<?php

	include("opentape_common.php");
	
	if (!is_logged_in()) { header("Location: " . $REL_PATH . "code/login.php"); }
	
	check_cookie();
	
	$prefs_struct = get_opentape_prefs();

	if (  
		 (!isset($prefs_struct['check_updates']) || $prefs_struct['check_updates'] == 1 ) &&
	 	 ((!isset($prefs_struct['last_update_check']) || (time() - $prefs_struct['last_update_check']) > 604800))
	 	) {
		$prefs_struct = check_for_update();
		if ($prefs_struct===false) { header("Location: " . $REL_PATH . "code/warning.php"); }
	}
		
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Opentape / Settings</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="imagetoolbar" content="no" />		
		<meta name="viewport" content="width = 680" />
		<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/style.css" />
			</head>
	<body>
		<div class="container">
<div class="banner">
				<h1>OPENTAPE</h1>
			    <ul class="nav">
					<li><a href="<?php echo $REL_PATH; ?>code/edit.php">Edit Tape</a></li>
					<li id="active"><a href="<?php echo $REL_PATH; ?>code/settings.php">Settings</a></li>
					<?php
					if ( $prefs_struct['latest_opentape'] > constant("VERSION") )
				 	{ ?><li><a href="http://opentape.fm/download">Update to <?php echo $prefs_struct['latest_opentape']; ?></a></li><?php } ?>
					<li id="user">
					   <a id="home" href="<?php echo $REL_PATH; ?>">YOUR TAPE &rarr;</a>
					   <a id="logout" href="<?php echo $REL_PATH; ?>code/logout.php">LOG-OUT &rarr;</a>
				    </li>
                </ul>
                <div class="ajax_status"></div>
			</div>			
			<div class="content">

                <div class="section">
                <h2>Sharing / Network Features</h2>
                    <a name="togglesharing"></a>
                    <form name="sharing_settings" onsubmit="return false">
                    
                    <input type=checkbox name="display_mp3" id="display_mp3" <?php if( isset($prefs_struct['display_mp3']) && $prefs_struct['display_mp3']==1 ) { echo 'checked="true"';} ?>>Display direct MP3 links on mixtape<br />
                    <input type=checkbox name="check_updates" id="check_updates" <?php if( (isset($prefs_struct['check_updates']) && $prefs_struct['check_updates']==1) || !isset($prefs_struct['check_updates']) ) { echo 'checked="true"'; } ?>>Automatically check for updates to <a href="http://opentape.fm/download">Opentape</a> (weekly)<br />
<!--
                    <input type=checkbox name="announce_songs" id="announce_songs" <?php if( isset($prefs_struct['announce_songs']) && $prefs_struct['announce_songs']==1 ) { echo 'checked="true"';} ?>>Announce songs in this mixtape to the <a href="http://opentape.fm/network">Opentape Discovery Network</a>.<br />
-->
                    </form>
                </div>
                
                <div class="section">
                    <h2>Embed Your Mix</h2>
                    <textarea scrolbars="no" name="codebox" cols="70" rows="4" readonly="readonly" id="codebox">
<object width="300" height="160">
<param name="allowscriptaccess" value="always" />	
<param name="movie" value="<?php echo get_base_url(); ?>res/jw_player.swf?playlist=bottom&displayheight=0&thumbsinplaylist=false&file=<?php echo get_base_url(); ?>code/xspf.php" />	
<embed src="<?php echo get_base_url(); ?>res/jw_player.swf?playlist=bottom&displayheight=0&thumbsinplaylist=false&file=<?php echo get_base_url(); ?>code/xspf.php" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="300" height="160"></embed>
</object>
</textarea>
<object width="300" height="160">
                        <param name="allowscriptaccess" value="always" />	
                        <param name="movie" value="<?php echo get_base_url(); ?>res/jw_player.swf?playlist=bottom&displayheight=0&thumbsinplaylist=false&file=<?php echo get_base_url(); ?>code/xspf.php" />	
                        <embed src="<?php echo get_base_url(); ?>res/jw_player.swf?playlist=bottom&displayheight=0&thumbsinplaylist=false&file=<?php echo get_base_url(); ?>code/xspf.php" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="300" height="160"></embed>
                    </object>

</div>
                <div class="section">
                <h2>Change Your Password</h2>
                    <a name="changepassword"></a>
                    <form name="password_form" onsubmit="return false">
                    <label for="password1">New PW:</label>
                        <input type="password" id="password1" maxlength="255" size="20" /><br />
                    <label for="password2">Re-type:</label>
                        <input type="password" id="password2" maxlength="255" size="20" /><br />
                        <input type="button" class="small_button" id="password_button" value="Save New Password" />
                    </form>
                </div>

                <div class="footer">
					<?php get_version_banner(); ?>
			</div>
        </div>
        
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-core-yc.js"></script>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-more-yc.js"></script>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/statusfader.js"></script>

			<script type="text/javascript">
				var first = $(document).getElement('input');
				if(first) first.focus();
			</script>
			
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
					fader.set(resp['status'] ? 'success' : 'failure');
					$(document.body).setStyle('cursor','default');
				},
				onFailure: function() {
					fader.set('failure');
					$(document.body).setStyle('cursor','default');
				}
			});

			
			if($('password1') && $('password2')) {
				$('password_button').addEvent('click',function(){
					ajax.send('command=change_password&args='+JSON.encode({password1:$('password1').value,password2:$('password2').value}));
				});
			}

			if($('display_mp3')) {
				$('display_mp3').addEvent('change',function(){
					ajax.send('command=set_option&args='+JSON.encode({display_mp3:$('display_mp3').checked}));
				});
			}

			if($('check_updates')) {
				$('check_updates').addEvent('change',function(){
					ajax.send('command=set_option&args='+JSON.encode({check_updates:$('check_updates').checked}));
				});
			}
			
			//if($('announce_songs')) {
			//	$('announce_songs').addEvent('change',function(){
			//		ajax.send('command=set_option&args='+JSON.encode({announce_songs:$('announce_songs').checked}));
			//	});
			//}
			
			</script>
		</body>
</html>