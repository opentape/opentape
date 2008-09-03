<?php
 
  // in progress
 	
	require_once("opentape_common.php");
	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;
	
	$prefs_struct = get_opentape_prefs();
	// Yes, this is the wrong way to build an XML file.
	// But you know what? It works, and we still have time
	// to go out for drinks after this is built.
	
	header("Content-type: application/xspf+xml; charset=UTF-8");
	
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
	echo '<playlist version="0" xmlns="http://xspf.org/ns/0/">' . "\n";

	echo '<title>';
	if (isset($prefs_struct['banner'])) { 
		echo $prefs_struct['banner'];
	} else {
		echo 'Opentape';
	}
	echo '</title>' . "\n";

	echo '<annotation>';
	if (isset($prefs_struct['caption'])) { 
	echo $prefs_struct['caption'];
	} else {
	echo count($songlist_struct) . "songs, " . get_total_runtime_string();
	}
	echo '</annotation>' . "\n";

	echo '<creator>' . 'Opentape ' . get_version() . '</creator>' . "\n";
	echo '<info>' . get_base_url() . '</info>' . "\n";
	echo '<location>' . get_base_url() . 'code/xspf.php</location>' . "\n";
	
	echo '<trackList>' . "\n";
	
	foreach ($songlist_struct as $pos => $row) { 
	
		if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
			unset($songlist_struct[$pos]);
			continue;
		}
	
		echo '<track>' . "\n";
		echo '<location>' . get_base_url() . constant("SONGS_PATH") . rawurlencode($row['filename']) . '</location>' . "\n";
		echo '<meta rel="type">mp3</meta>' . "\n";
		
		echo '<creator>';
		if (isset($row['opentape_artist'])) { echo $row['opentape_artist']; } 
		else { echo htmlentities($row['artist']); } 
		echo '</creator>' . "\n"; 
		
		echo '<title>'; 
		if (isset($row['opentape_title'])) { echo $row['opentape_title']; }
		else { echo htmlentities($row['title']); }
		echo '</title>' . "\n";
		
		echo '<duration>' . floor($row['playtime_seconds']) . '</duration>' . "\n";		
		echo '<info>' . get_base_url() . '</info>' . "\n";
		
		echo '</track>' . "\n";
		
	}
		
	echo '</trackList>' . "\n";
	echo '</playlist>' . "\n";		
    
?>