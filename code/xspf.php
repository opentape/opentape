<?
 
  // in progress
 	
	require_once("opentape_common.php");
	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;

	// Yes, this is the wrong way to build an XML file.
	// But you know what? It works, and we still have time
	// to go out for drinks after this is built.
	
	header("Content-type: text/xml; charset=UTF-8");
	
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
	echo '<playlist version="0" xmlns="http://xspf.org/ns/0/">' . "\n";
	echo '<title>' . 'Opentape: ' . count($songlist_struct) . ", " . get_total_runtime_string() . '</title>' . "\n";
	echo '<creator>' . 'Opentape ' . get_version() . '</creator>' . "\n";
	echo '<info>' . 'http://' . $_SERVER[HTTP_HOST] . $REL_PATH . '</info>' . "\n";
	echo '<location>' . 'http://' . $_SERVER[HTTP_HOST] . $REL_PATH . '</location>' . "\n";
	
	echo '<trackList>' . "\n";
	
	foreach ($songlist_struct as $pos => $row) { 
	
		if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
			unset($songlist_struct[$pos]);
			continue;
		}
	
		echo '<track>' . "\n";
		echo '<location>' . "http://" . $_SERVER[HTTP_HOST] . $REL_PATH . constant("SONGS_PATH") . rawurlencode($row['filename']) . '</location>' . "\n";
		echo '<meta rel="type">mp3</meta>' . "\n";
		
		echo '<title>';
		if ($row['opentape_artist']) { echo $row['opentape_artist']; } 
		else { echo htmlentities($row['artist']); } 
		echo ' - '; 
		if ($row['opentape_title']) { echo $row['opentape_title']; }
		else { echo htmlentities($row['title']); }
		echo '</title>';
		
		echo '<info>' . "http://" . $_SERVER[HTTP_HOST] . $REL_PATH . '</info>' . "\n";
		
		echo '</track>' . "\n";
		
	}
		
	echo '</trackList>' . "\n";
	echo '</playlist>';		
    
?>