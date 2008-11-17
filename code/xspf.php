<?php

// in progress

require_once("opentape_common.php");
$songlist_struct = scan_songs();
$songlist_struct_original = $songlist_struct;

$prefs_struct = get_opentape_prefs();

// now via the DOM/SimpleXML	
$doc = new DomDocument('1.0', 'UTF-8');

$root = $doc->createElement('playlist');
$root->setAttribute('xmlns', 'http://xspf.org/ns/0/');
$root->setAttribute('version', '0');
$doc->appendChild($root);

$title = $doc->createElement('title');
if (isset($prefs_struct['banner'])) { 
 	$title->appendChild( $doc->createTextNode($prefs_struct['banner']) );
} else {
 	$title->appendChild( $doc->createTextNode('Opentape') );
}
$root->appendChild($title);

$annotation = $doc->createElement('annotation');
if (isset($prefs_struct['caption'])) {
 	$annotation->appendChild( $doc->createTextNode($prefs_struct['caption']) );
} else {
 	$annotation->appendChild( $doc->createTextNode(count($songlist_struct) . "songs, " . get_total_runtime_string()) );
}
$root->appendChild($annotation);

$creator = $doc->createElement('creator');
$creator->appendChild( $doc->createTextNode("Opentape " . get_version()) );
$root->appendChild($creator);

$info = $doc->createElement('info');
$info->appendChild( $doc->createTextNode(get_base_url()) );
$root->appendChild($info);

$location = $doc->createElement('location');
$location->appendChild( $doc->createTextNode(get_base_url() . 'code/xspf.php') );
$root->appendChild($location);

$tracklist = $doc->createElement('tracklist');
$root->appendChild($tracklist);

foreach ($songlist_struct as $pos => $row) { 

	if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
		unset($songlist_struct[$pos]);
		continue;
	}
	
	$trackitem = $doc->createElement('track');
	$tracklist->appendChild($trackitem);
	
	$location = $doc->createElement('location');
	$location->appendChild( $doc->createTextNode( get_base_url() . constant("SONGS_PATH") . rawurlencode($row['filename']) ));
	$trackitem->appendChild($location);

	$meta = $doc->createElement('meta');
	$meta->appendChild( $doc->createTextNode("mp3"));
	$meta->setAttribute("rel", "type");
	$trackitem->appendChild($meta);
	
	$creator = $doc->createElement('creator');
	if (isset($row['opentape_artist'])) { $creator->appendChild( $doc->createTextNode(  $row['opentape_artist'] )); } 
	else { $creator->appendChild( $doc->createTextNode( $row['artist'] )); } 
	$trackitem->appendChild($creator);

	$title = $doc->createElement('title');
	if (isset($row['opentape_title'])) { $creator->appendChild( $doc->createTextNode( $row['opentape_title'] )); }
	else { $creator->appendChild( $doc->createTextNode( $row['title'] )); }
	$trackitem->appendChild($title);

	$duration = $doc->createElement('duration');
	$duration->appendChild( $doc->createTextNode( floor($row['playtime_seconds']) ));
	$trackitem->appendChild($duration);

	$info = $doc->createElement('info');
	$info->appendChild( $doc->createTextNode( get_base_url() ));
	$trackitem->appendChild($info);
		
}


// Set type and output XML info
header("Content-type: application/xspf+xml; charset=UTF-8");
echo $doc->saveXML();	
	
/*	

	
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
*/
?>