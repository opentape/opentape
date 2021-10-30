<?php

	require_once ('opentape_common.php');
	
	$command = stripslashes($_POST['command']);
	$args = json_decode(stripslashes($_POST['args']),true);
	
	header("Content-type: application/json; charset=UTF-8");
	
	// create_password is the exception, since you can't be logged in
	if (!is_logged_in() && $command!=="create_password") {
		echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>"You must authenticate."]);
		exit;
	}
	//error_log ("$command - " . print_r($args,1));
	
	if (isset($args['password1']) && $args['password1']===$args['password2'] && $command=="create_password") {
			
		// don't allow people to set password using this method once the file exists
		if(is_password_set()) { 
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>"The password is already configured, login to change it."]);
		}
		
		if (set_password($args['password1'])) {
			// proceed to next step, nothing here really...
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
			exit;
		}
	
		if (create_session()) {
			echo json_encode(['status'=>true, 'command'=>"create_password", 'debug'=>""]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}
				
 	} elseif (isset($args['password1']) && $args['password1']===$args['password2'] && $command==="change_password") {

		if (set_password($args['password1'])) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>""]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}
		
	} elseif ($command==="rename") {
	
		$_POST['artist'] = htmlentities(stripslashes($_POST['artist']));
		$_POST['title'] = htmlentities(stripslashes($_POST['title']));			
			
		if (rename_song($args['song_key'], $_POST['artist'], $_POST['title'])) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>"", 'args' => ['song_key'=>$args['song_key'], 'artist'=>$_POST['artist'], 'title'=>$_POST['title']]]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}


	} elseif ($command==="reorder") {
	
		if (reorder_songs($args)) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>""]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}			

	} elseif ($command==="delete") {
	
		if (delete_song($_POST['args'])) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>"", 'args'=> $_POST['args']]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}
	
	} elseif ($command==="bannercaptioncolor") {
		
		$prefs_struct = get_opentape_prefs();
		
		$_POST['banner'] = htmlentities(stripslashes($_POST['banner']));
		$_POST['caption'] = htmlentities(stripslashes($_POST['caption']));	
		$_POST['color'] = htmlentities(stripslashes($_POST['color']));		
	
		$prefs_struct['banner'] = $_POST['banner'];
		$prefs_struct['caption'] = $_POST['caption'];
		$prefs_struct['color'] = $_POST['color'];
		
		if (write_opentape_prefs($prefs_struct)) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>""]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}
		
	} elseif ($command==="set_option") {
	
		$prefs_struct = get_opentape_prefs();

		foreach ($args as $key => $data) {
			
			if (!strcmp($data,"on") || !strcmp($data,"true") || $data===true || $data==1 ) {
				$prefs_struct[$key] = 1;
			} else {
				$prefs_struct[$key] = 0;
			}
				
		}
		
		if (write_opentape_prefs($prefs_struct)) {
			echo json_encode(['status'=>true, 'command'=>$command, 'debug'=>""]);
		} else {
			echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);
		}
	
	} else {
		echo json_encode(['status'=>false, 'command'=>$command, 'debug'=>""]);	
	}

?>