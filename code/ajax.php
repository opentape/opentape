<?php

	require_once ('opentape_common.php');
	require_once ('JSON.php');

	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	
	$command = $_POST['command'];
	$args = $json->decode(stripslashes($_POST['args']));
	
	header("Content-type: text/javascript; charset=UTF-8");
	
	// create_password is the exception, since you can't be logged in
	if (!is_logged_in() && strcmp($command, "create_password") ) {
		echo '{"status":false,"command":"' . $command . '","debug":"You must authenticate."}';
		exit;
	}
	//error_log ("$command - " . print_r($args,1));
	
	if (isset($args['password1']) && !strcmp($args['password1'], $args['password2']) && !strcmp($command,"create_password")) {
			
		// don't allow people to set password using this method once the file exists
		if(is_password_set()) { echo '{"status":false,"command":"' . $command . '","debug":"The password is already configured, login to change it."}'; }

		if (set_password($args['password1'])) {
			// proceed to next step, nothing here really...
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
			exit;
		}
	
		if (create_session()) {
			echo '{"status":true,"command":"create_password","debug":""}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}
				
 	} elseif (isset($args['password1']) && !strcmp($args['password1'], $args['password2']) && !strcmp($command,"change_password")) {

		if (set_password($args['password1'])) {
			echo '{"status":true,"command":"' . $command . '","debug":""}';	
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}
		
	} elseif (!strcmp($command, "rename")) {
	
		if(get_magic_quotes_gpc()) {
			$_POST['artist'] = stripslashes($_POST['artist']);
			$_POST['title'] = stripslashes($_POST['title']);			
		}
			
		if (rename_song($args['song_key'], $_POST['artist'], $_POST['title'])) {
			echo '{"status":true,"command":"' . $command . '","debug":"","args":{"song_key":"' . $args['song_key'] . '","artist":"' . escape_for_json($_POST['artist']) . '","title":"' .  escape_for_json($_POST['title']) .'"}}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}


	} elseif (!strcmp($command, "reorder")) {
	
		if (reorder_songs($args)) {
			echo '{"status":true,"command":"' . $command . '","debug":""}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}			

	} elseif (!strcmp($command, "delete")) {
	
		if (delete_song($_POST['args'])) {
			echo '{"status":true,"command":"' . $command . '","debug":"","args":"' . $_POST['args'] . '"}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}
	
	} elseif (!strcmp($command, "bannercaptioncolor")) {
		
		$prefs_struct = get_opentape_prefs();
		
		if(get_magic_quotes_gpc()) {
			$_POST['banner'] = stripslashes($_POST['banner']);
			$_POST['caption'] = stripslashes($_POST['caption']);	
			$_POST['color'] = stripslashes($_POST['color']);		
		}
		
		$prefs_struct['banner'] = $_POST['banner'];
		$prefs_struct['caption'] = $_POST['caption'];
		$prefs_struct['color'] = $_POST['color'];
		
		if (write_opentape_prefs($prefs_struct)) {
			echo '{"status":true,"command":"' . $command . '","debug":""}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}
		
	} elseif (!strcmp($command, "set_option")) {
	
		$prefs_struct = get_opentape_prefs();

		// maybe should check if the key is a valid data item type, to prevent 
		// some kind of sizing attack... though checking for login does well.
		foreach ($args as $key => $data) {
			
			if (!strcmp($data,"on") || !strcmp($data,"true") || $data===true || $data==1 ) {
				$prefs_struct[$key] = 1;
			} else {
				$prefs_struct[$key] = 0;
			}
				
		}
		
		if (write_opentape_prefs($prefs_struct)) {
			echo '{"status":true,"command":"' . $command . '","debug":""}';
		} else {
			echo '{"status":false,"command":"' . $command . '","debug":""}';
		}
	
	} else {
	
		echo '{"status":false,"command":"' . $command . '","debug":""}';
	
	}

?>