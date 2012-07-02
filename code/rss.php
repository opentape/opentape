<?php
 
  // in progress
 	
	require_once("opentape_common.php");
	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;
	
	$prefs_struct = get_opentape_prefs();
	// Yes, this is the wrong way to build an XML file.
	// But you know what? It works, and we still have time
	// to go out for drinks after this is built.
	
	header("Content-type: application/rss+xml; charset=UTF-8");
	
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
	echo '<rss version="2.0">' . "\n";
	echo '<channel>' . "\n";

	echo '<title>';
	if (isset($prefs_struct['banner'])) { 
		echo $prefs_struct['banner'];
	} else {
		echo 'Opentape!';
	}
	echo '</title>' . "\n";

	echo '<description>' . "\n";
	if (isset($prefs_struct['caption'])) { 
	echo ' ' . htmlentities($prefs_struct['caption']);
	} else {
	echo ' ' . count($songlist_struct) . "songs, " . get_total_runtime_string();
	}
	echo '</description>' . "\n";

	echo '<link>' . get_base_url() . '</link>' . "\n";
		
	foreach ($songlist_struct as $pos => $row) { 
	
		if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
			unset($songlist_struct[$pos]);
			continue;
		}
	
		echo '<item>' . "\n";
		
		echo '<title>';
		if (isset($row['opentape_artist'])) { echo $row['opentape_artist'] . ' - '; } 
		else { echo htmlentities($row['artist']) . ' - '; } 
		
		if (isset($row['opentape_title'])) { echo $row['opentape_title']; }
		else { echo htmlentities($row['title']); }
		echo '</title>' . "\n";

		echo '<link>' . get_base_url() . constant("SONGS_PATH") . rawurlencode($row['filename']) . '</link>' . "\n";
		echo '<enclosure url="' . get_base_url() . constant("SONGS_PATH") . rawurlencode($row['filename']) . '" length="' . $row['size'] . '" type="audio/mp3">' . '</enclosure>' . "\n";
		echo '<guid isPermaLink="false">' . $pos . '</guid>' . "\n";
		
		echo '<description>' . $row['playtime_string'] . '</description>' . "\n";		
		
		echo '</item>' . "\n";
		
	}
		
	echo '</channel>' . "\n";
	echo '</rss>' . "\n";		
    
?>