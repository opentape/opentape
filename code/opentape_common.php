<?php

// --- CONFIGURABLE ADVANCED SETTINGS
// Be sure to have an ending /.  These are relative to the dir
// where you installed opentape.

define("SETTINGS_PATH", "settings/");
define("SONGS_PATH", "songs/");
define("DEFAULT_COLOR", "EC660F");
// --- END OF CONFIGURABLE ADVANCED SETTINGS ---- //

require_once ('JSON.php');
ini_set("track_errors","on");

global $REL_PATH;
$REL_PATH = preg_replace('|settings/[^/]*?$|', '', $_SERVER['REQUEST_URI']);
$REL_PATH = preg_replace('|songs/[^/]*?$|', '', $REL_PATH);
$REL_PATH = preg_replace('|code/[^/]*?$|', '', $REL_PATH);
$REL_PATH = preg_replace('|res/[^/]*?$|', '', $REL_PATH);
$REL_PATH = preg_replace('|/[^/]+?$|', '/', $REL_PATH);
$REL_PATH = preg_replace('|/+|', '/', $REL_PATH);
define("VERSION", "0.12");
define("VERSION_CHECK_URL", "http://opentape.fm/public/latest_version.php");
define("ANNOUNCE_SONGS_URL", "http://opentape.fm/public/announce_songs.php");
define("ANNOUNCE_JS_URL", "http://opentape.fm/public/announce.js");

// this may fix certain win32 issues, thanks fusen
define("GETID3_HELPERAPPSDIR", "/");

// Change dir to the main install dir for consistency
$cwd = getcwd();
if (preg_match('/code\/?$/', $cwd) || preg_match('|' . constant("SETTINGS_PATH") . '?$|', $cwd) || preg_match('/res\/?$/', $cwd)) {
	chdir('..');
}

//This function transforms the php.ini notation numbers (like '2M') to an integer (2*1024*1024 in this case)
function let_to_num($v){ 
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}

function get_max_upload_mb () {
	$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
	return round(($max_upload_size / (1024*1024)),2);
}

function get_max_upload_bytes () {
	$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
	return $max_upload_size;
}

// returns microtime as a long number
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function escape_for_inputs($string) {
	return preg_replace("/'/", "&#39;", $string);
}

function escape_for_json($string) {
	return preg_replace('/"/', '\"', $string);
}

function clean_titles($string) {
	return preg_replace('/\\000/', '', $string);
}

function get_base_url() {

	global $REL_PATH;

	//if ( ($_SERVER['SERVER_PORT']==80 && (empty($_SERVER['HTTPS']) || !strcasecmp($_SERVER['HTTPS'],"off"))) ) {
	return 'http://' . $_SERVER['HTTP_HOST'] . $REL_PATH;
	/*
	} elseif ( ($_SERVER['SERVER_PORT']!=80 && (empty($_SERVER['HTTPS']) || !strcasecmp($_SERVER['HTTPS'],"off"))) ) {
		return 'http://' . $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'] . $REL_PATH;
	} elseif ($_SERVER['SERVER_PORT']==443 && (!empty($_SERVER['HTTPS']) || strcasecmp($_SERVER['HTTPS'],"off"))) {
		return 'https://' . $_SERVER['HTTP_HOST'] . $REL_PATH;	
	} else {
		return 'https://' . $_SERVER['HTTP_HOST'] . ":" . $_SERVER['SERVER_PORT'] . $REL_PATH;	
	}*/

}

function is_logged_in() {

	$session_struct = get_session_struct();
		
	if (is_array($session_struct)) {
		foreach ($session_struct as $pos => $item) { 
			if(!strcmp($_COOKIE["opentape_session"], $item['key'])) {
				return true;
			}
		}
	}
				
	return false;
	
}

function check_password($password) {

/*	
	$password_struct = array();

	if (file_exists(constant("SETTINGS_PATH") . ".opentape_password.array")) {
		
		$password_struct_data = file_get_contents( constant("SETTINGS_PATH") . ".opentape_password.array" );
		if(empty($password_struct_data) || $password_struct_data===false) {
			return -1;
		}
	
		$password_struct = unserialize($password_struct_data); 
		if(empty($password_struct_data) || $password_struct_data===false) {
			return -1;
		}
	
	} else {
		return -1;
	}
*/

	$password_struct = get_password_struct();		
		
	if (!strcmp( md5("MIXTAPESFORLIFE" . $password), $password_struct['hash']) ) {
		return true;
	} else {
		return false;
	}
	
}

function is_password_set() {
	
	$password_struct = get_password_struct();

	if (is_array($password_struct) && !empty($password_struct) && $password_struct !== false) {
		return true;
	} else {
		return false;
	}
	
/*
	if (file_exists(constant("SETTINGS_PATH") . ".opentape_password.array")) {
	
		$password_struct_data = file_get_contents( constant("SETTINGS_PATH") . ".opentape_password.array" );
		if(empty($password_struct_data)) {
			return false;
		} elseif ($password_struct_data===false) {
			return -1; // this is a read error
		} else {
			return true; // password exists
		}
	
	} else {
		return false;
	}
*/
	
}

function set_password($password) {

	$password_struct = array();
	$password_struct['hash'] = md5("MIXTAPESFORLIFE" . $password);
	
	return write_password_struct($password_struct);
	
	/*
	$bytes_written = file_put_contents( constant("SETTINGS_PATH") . ".opentape_password.array", serialize($password_struct));
	
	if ($bytes_written===false) {
		return false;
	} else {
		return true;
	}
	*/

}

// Here we give all users cookies, then we add/remove these session id's
// into the session file
function check_cookie() {

	if (!isset($_COOKIE["opentape_session"])) {
		if (!strcasecmp($_SERVER['HTTP_HOST'],"localhost")) {
			setcookie("opentape_session", md5("MIXTAPESFORLIFE" .  microtime_float()), (time() + (86400*365)), "/", "");
		} elseif (preg_match('/:\d+$/', $_SERVER['HTTP_HOST'])) {
			setcookie("opentape_session", md5("MIXTAPESFORLIFE" .  microtime_float()), (time() + (86400*365)), "/", "");
		} else {
			setcookie("opentape_session", md5("MIXTAPESFORLIFE" .  microtime_float()), (time() + (86400*365)), "/", $_SERVER['HTTP_HOST']);
		}
	}

}

function create_session() {
	
	$session_struct = get_session_struct();
	$session_item = array();

	if($session_struct===false) { $session_struct=array(); }	

	if (isset($_COOKIE["opentape_session"])) {

		$session_item['key'] = $_COOKIE["opentape_session"];
		$session_item['ts'] = time();
		array_push($session_struct, $session_item);
						
		return write_session_struct($session_struct);	
	
	} else {
	
		return false;
		
	}

}

function remove_session() {

	$session_struct = get_session_struct();
	$session_struct_new = array();
	$session_item = array();
	
	if (is_array($session_struct)) {
	
		// rewrite the session struct without the one we are removing
		foreach ($session_struct as $pos => $item) { 
			
			if(strcmp($_COOKIE["opentape_session"], $item['key'])) {
				array_push($session_struct_new,$session_item);
			}
		
		}
		
		return write_session_struct($session_struct_new);
					
	} else {
		// if the session file doesn't exist, there's nothing to remove the session from
		// so we just say ok.
	}
	
	return true;

}



function scan_songs() {

	require_once('getid3.php');
	
	$dir_handle = opendir(constant("SONGS_PATH"));
	
	// Initialize getID3 engine
	$getID3 = new getID3;
		
	$songlist_struct = get_songlist_struct();
	$songlist_struct_original = $songlist_struct;
	$songlist_new_items = array();
	
	// List all the files
    while (false !== ($file = readdir($dir_handle)) ) {

		if ( strcmp($file, ".") && strcmp($file, "..") && !strcasecmp(end(explode(".", $file)), "mp3")) {

			// error_log("Analyzing: " . constant("SONGS_PATH") . $file . " file_exists=" . file_exists(constant("SONGS_PATH") . $file));
			// error_log("id3_structure: " . print_r($id3_info,1) . "\nID3v2:" . $id3_info['id3v2']['comments']['artist'][0] . " - " . $id3_info['id3v2']['comments']['title'][0]);
			// error_log("ID3v1:" . $id3_info['id3v1']['artist'] . " - " . $id3_info['id3v1']['title']);
			
			if ( !isset($songlist_struct[ base64_encode(rawurlencode($file)) ]) ) {
		
				$id3_info = $getID3->analyze(constant("SONGS_PATH") . $file);
		
				$song_item = array();

				// Check id3 v2 tags, 
				if (!empty($id3_info['id3v2']['comments']['artist'][0])) {
					$song_item['artist'] = clean_titles($id3_info['id3v2']['comments']['artist'][0]);
				} elseif (!empty($id3_info['id3v1']['artist'])) {
					$song_item['artist'] = clean_titles($id3_info['id3v1']['artist']);
				} /*else { 
					$song_item['artist'] = "Unknown artist";
				} */

				if (!empty($id3_info['id3v2']['comments']['title'][0])) {
					$song_item['title'] = clean_titles($id3_info['id3v2']['comments']['title'][0]);
				} elseif (!empty($id3_info['id3v1']['title'])) {
					$song_item['title'] = clean_titles($id3_info['id3v1']['title']);
				} /*else { 
					$song_item['title'] = "Unknown title";
				} */
				
				// if we are missing tags, set the title to the filename, sans ".mp3"
				if (!isset($song_item['artist']) && !isset($song_item['title'])) {
					$song_item['artist'] = "";
					$song_item['title'] = preg_replace('/\.mp3$/i', '', $id_info['filename']);
				} elseif (!isset($song_item['artist'])) { // fill in some of the blanks otherwise
					$song_item['artist'] = "Unknown artist";
				} elseif (!isset($song_item['title'])) {
					$song_item['title'] = "Unknown track";
				}
									
				$song_item['filename'] = $id3_info['filename'];
				$song_item['playtime_seconds'] = $id3_info['playtime_seconds'];
				$song_item['playtime_string'] = $id3_info['playtime_string'];
				$song_item['mtime'] = filemtime(constant("SONGS_PATH") . $file);
				$song_item['size'] = filesize(constant("SONGS_PATH") . $file);
				$songlist_new_items[ base64_encode(rawurlencode($id3_info['filename'])) ] = $song_item;
			
			}
				
		}
		
    }
    
    // if changed, save it
    if (!empty($songlist_new_items)) {
    	
    	$songlist_struct = array_merge($songlist_new_items, $songlist_struct);
    
    	announce_songs($songlist_struct);
    	write_songlist_struct($songlist_struct);
    	
    }
    
    return $songlist_struct;

}

// Renames songs in the songlist structure
function rename_song($song_key, $artist, $title) {
	
	if (empty($song_key)) { 
		error_log("rename_song called with insufficient arguments: song_key=$song_key, artist=$artist, title=$title");
		return false; 
	}
	
	$songlist_struct = get_songlist_struct();
	
	$songlist_struct[$song_key]['opentape_artist'] = $artist;
	$songlist_struct[$song_key]['opentape_title'] = $title;
	
	return write_songlist_struct($songlist_struct);
	
}

// Reorders songs in the songlist structure
function reorder_songs($args) {

	if (empty($args)) {
		error_log("reorder_songs called with insufficient arguments: args=$args");
		return false;
	}
	
	$songlist_struct = get_songlist_struct();
	$songlist_struct_new = array();
	foreach ($args as $pos => $row) { 
		
		$songlist_struct_new[$row] = $songlist_struct[$row];

	}

	return write_songlist_struct($songlist_struct_new);

}

// Deletes song from the disk and the songlist struct
function delete_song($song_key) {
	
	if (empty($song_key)) {
		error_log("delete_song called with insufficient arguments: song_key=$song_key");
		return false;
	}
	
	$songlist_struct = get_songlist_struct();
	
	if (unlink(constant("SONGS_PATH") . $songlist_struct[$song_key]['filename'])) {
	
		unset($songlist_struct[$song_key]);
	
	} else {
		return false;
	}
	
	return write_songlist_struct($songlist_struct);

}

// Get total tape runtime from songlist_struct in seconds
function get_total_runtime_seconds() {

	$songlist_struct = get_songlist_struct();
	
	$total_secs = 0;
	foreach ($songlist_struct as $pos => $row) { 
		$total_secs += $row['playtime_seconds'];
	}
	
	return $total_secs;
	
}

// Return pretty and formatted runtime
function get_total_runtime_string() {
	
	$seconds = get_total_runtime_seconds();
	$string = "";
	
	$mins = round($seconds / 60);
	$secs = $seconds % 60;
	
	if ($mins==1) { $string .= "$mins min"; }
	else { $string .= "$mins mins"; }
	
	if ($secs==1) { $string .= " $secs sec"; }
	else { $string .= " $secs secs"; }
	
	return $string;

}

// Retrieves songlist struct from the disk file
function get_songlist_struct() {

	$songlist_struct = array();
	$filename_base = ".opentape_songlist";
	
	if (file_exists( constant("SETTINGS_PATH") . $filename_base . ".php" ) &&
		is_readable( constant("SETTINGS_PATH") . $filename_base . ".php" )  ) {
	
		// this is the more secure way of storing this data, as 
		// the web users can't fetch it
		include( constant("SETTINGS_PATH") . $filename_base . ".php" );
		$songlist_struct = unserialize(base64_decode($songlist_struct_data));
	
	} elseif (file_exists( constant("SETTINGS_PATH") . $filename_base . ".array" ) &&
			is_readable( constant("SETTINGS_PATH") . $filename_base . ".array" )  ) {

		$songlist_struct_data = file_get_contents  ( constant("SETTINGS_PATH") . $filename_base .".array" );
		$songlist_struct = unserialize($songlist_struct_data); 

		if ($songlist_struct === false || !is_array($songlist_struct)) {
			error_log ("Songlist currently empty");
			$songlist_struct = array();
		} else {
			// upgrade to the new method of storing the data quietly
			write_songlist_struct($songlist_struct);	
		}
		
	}

	return $songlist_struct;
	
}

// Saves the songlist data to disk.  Checks if all items in list are actualyl accessible
function write_songlist_struct($songlist_struct) {

	// before we write, lets verify that all the files in here are really working files, that they exist at least...
	foreach ($songlist_struct as $pos => $row) { 
		if (! is_file(constant("SONGS_PATH") . $row['filename']) ) {
			error_log($row['filename'] . " is not accessible, removing it from the songlist");
			unset($songlist_struct[$pos]);
		}
	}
	
	$songlist_struct_data = '<?php $songlist_struct_data = "' . base64_encode(serialize($songlist_struct)) . '"; ?>';
	
	$bytes_written = file_put_contents( constant("SETTINGS_PATH") . ".opentape_songlist.php", $songlist_struct_data);
	if ($bytes_written===false) {
		error_log("Unable to write songlist array");
		return false;
	} else {
		return true;	
	}
	
}

function get_opentape_prefs() {

	$prefs_struct = array();
	$filename_base = ".opentape_prefs";

	if (file_exists( constant("SETTINGS_PATH") . $filename_base . ".php" ) &&
		is_readable( constant("SETTINGS_PATH") . $filename_base . ".php" )  ) {
	
		// this is the more secure way of storing this data, as 
		// the web users can't fetch it
		include( constant("SETTINGS_PATH") . $filename_base . ".php" );
		$prefs_struct = unserialize(base64_decode($prefs_struct_data));

	} elseif (file_exists( constant("SETTINGS_PATH") . $filename_base . ".array" ) &&
			is_readable( constant("SETTINGS_PATH") . $filename_base . ".array" )  ) {
	
		$prefs_struct_data = file_get_contents  ( constant("SETTINGS_PATH") . $filename_base . ".array" );
		$prefs_struct = unserialize($prefs_struct_data); 

		if ($prefs_struct === false || !is_array($prefs_struct)) {
			error_log ("prefs file currently empty");
			$prefs_struct = array();
		} else {
			// we need to upgrade the file type to the new version by writing it over again here.
			write_opentape_prefs($prefs_struct);
		}
		
	}

	return $prefs_struct;

}

function write_opentape_prefs($prefs_struct) {

	$prefs_struct_data = '<?php $prefs_struct_data = "' . base64_encode(serialize($prefs_struct)) . '"; ?>';

	$bytes_written = file_put_contents( constant("SETTINGS_PATH") . ".opentape_prefs.php", $prefs_struct_data);
	if ($bytes_written===false) {
		error_log("Unable to write prefs php data in " . constant("SETTINGS_PATH") . ".opentape_prefs.php");
		return false;
	} else {
		return true;	
	}


}

function get_password_struct() {

	$password_struct = array();
	$filename_base = ".opentape_password";

	if (file_exists( constant("SETTINGS_PATH") . $filename_base . ".php" ) &&
		is_readable( constant("SETTINGS_PATH") . $filename_base .".php" )  ) {
		// this is the more secure way of storing this data, as 
		// the web users can't fetch it
		include( constant("SETTINGS_PATH") . $filename_base . ".php" );
		$password_struct = unserialize(base64_decode($password_struct_data));

	} elseif (file_exists( constant("SETTINGS_PATH") . $filename_base . ".array" ) &&
			is_readable( constant("SETTINGS_PATH") . $filename_base . ".array" )  ) {
	
		$password_struct_data = file_get_contents  ( constant("SETTINGS_PATH") . $filename_base . ".array" );
		$password_struct = unserialize($password_struct_data); 

		if ($password_struct === false || !is_array($password_struct)) {
			error_log ("password file currently empty");
			$password_struct = array();
		} else {
			// we need to upgrade the file type to the new version by writing it over again here.
			write_password_struct($password_struct);
		}
		
	} else {
		return false;
	}

	return $password_struct;

}

function write_password_struct($password_struct) {

	$password_struct_data = '<?php $password_struct_data = "' . base64_encode(serialize($password_struct)) . '"; ?>';

	$bytes_written = file_put_contents( constant("SETTINGS_PATH") . ".opentape_password.php", $password_struct_data);
	if ($bytes_written===false) {
		error_log("Unable to write password php data in " . constant("SETTINGS_PATH") . ".opentape_password.php");
		return false;
	} else {
		return true;	
	}

}

function get_session_struct() {

	$session_struct = array();
	$filename_base = ".opentape_session";

	if (file_exists( constant("SETTINGS_PATH") . $filename_base . ".php" ) &&
		is_readable( constant("SETTINGS_PATH") . $filename_base . ".php" )  ) {
		
		// this is the more secure way of storing this data, as 
		// the web users can't fetch it
		include( constant("SETTINGS_PATH") . $filename_base . ".php" );
		$session_struct = unserialize(base64_decode($session_struct_data));

	} elseif (file_exists( constant("SETTINGS_PATH") . $filename_base . ".array" ) &&
			is_readable( constant("SETTINGS_PATH") . $filename_base . ".array" )  ) {
	
		$session_struct_data = file_get_contents  ( constant("SETTINGS_PATH") . $filename_base . ".array" );
		$session_struct = unserialize($session_struct_data); 

		if ($session_struct === false || !is_array($session_struct)) {
			error_log ("password file currently empty");
			$session_struct = array();
		} else {
			// we need to upgrade the file type to the new version by writing it over again here.
			write_session_struct($session_struct);
		}
		
	} else {
		return false;
	}

	return $session_struct;

}

function write_session_struct($session_struct) {

	$session_struct_data = '<?php $session_struct_data = "' . base64_encode(serialize($session_struct)) . '"; ?>';

	$bytes_written = file_put_contents( constant("SETTINGS_PATH") . ".opentape_session.php", $session_struct_data);
	if ($bytes_written===false) {
		error_log("Unable to write session php data in " . constant("SETTINGS_PATH") . ".opentape_session.php");
		return false;
	} else {
		return true;	
	}

}

function get_version() {
	return constant('VERSION');
}

function get_version_banner() {
	echo 'This is <a href="http://opentape.fm">Opentape ' . constant('VERSION') . '</a>.';
}

function check_for_update() {
	
	$ts = time();
	
	$prefs_struct = get_opentape_prefs();
	
	$result = do_post_request(constant("VERSION_CHECK_URL"), http_build_query(array('version'=>constant("VERSION"))), null);

	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);	
	$version_struct = $json->decode(stripslashes($result));

	if ($version_struct!==false && is_array($version_struct)) {
		$prefs_struct['latest_opentape'] = $version_struct['version'];
		$prefs_struct['latest_opentape_update'] = $version_struct['ts'];
		$prefs_struct['last_update_check'] = $ts;
		//error_log($result);
	} else {
		error_log("Version check returned incorrect result");
		$prefs_struct['latest_opentape'] = -1;
		$prefs_struct['latest_opentape_update'] = -1;
		$prefs_struct['last_update_check'] = $ts;
	} 
	
	if(write_opentape_prefs($prefs_struct)) {
		return $prefs_struct;
	} else {
		false;
	}
	
}

function announce_songs($songlist_struct) {
	
	global $REL_PATH;

	$ts = time();
	
	$prefs_struct = get_opentape_prefs();

	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);	
	$version_struct = $json->encode($songlist_struct);

	$data = http_build_query(
			array(
				'version' => constant("VERSION"),
				'url' => get_base_url(),
				'songs' => $version_struct 
			)	
		);

	$result = do_post_request(constant("ANNOUNCE_SONGS_URL"), $data, null);
	
	if (!strcmp($result,"OK")) {
		
	} else {
		error_log("Failed to announce songs to " . constant("ANNOUNCE_SONGS_URL") . " result was: " . $result);
	}
	
	$prefs_struct['last_announce_songs'] = $ts;
	if(write_opentape_prefs($prefs_struct)) {
		return $prefs_struct;
	} else {
		false;
	}
	
	
}

function do_post_request($url, $data, $optional_headers = null) {
	
	// People with curl use curl
	if (extension_loaded("curl")) {
	
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_handle, CURLOPT_URL, "$url");
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, "Opentape " . constant("VERSION"));
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
		
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
			
		// check for success or failure
		if (empty($buffer)) {
			
			return false;		
		
		} else {
		
			return $buffer;
		
		}
	// everyone else is via the stream
	// from here: http://netevil.org/blog/2006/nov/http-post-from-php-without-curl
	} else {
	
	     $params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
               ));
               
		 if ($optional_headers !== null) {
		 	array_push($optional_headers, "User-Agent: Opentape " . constant("VERSION"));		
		 } else {
		 	$optional_headers = array();
		 	array_push($optional_headers, "User-Agent: Opentape " . constant("VERSION"));
		 }	
		
		$params['http']['header'] = $optional_headers;
		
		
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			return false;
			//throw new Exception("Problem with $url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			return false;
			//throw new Exception("Problem reading data from $url, $php_errormsg");
		}
		return $response;

	}
	
}


?>
