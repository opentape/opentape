<?php
	
	// Welcome to Opentape!
	// This file is kept intentionally simple.  If you are seeing it in your web browser, you probably do not have PHP installed :(
	
	set_include_path(get_include_path() . PATH_SEPARATOR . "./code/");

	// check if critical directories are writeable
	if ( !is_writeable('settings') || !is_writeable('songs') ) {
		include ("code/warning.php");
	
	// Current version designed for PHP5+, though we may update to support 4.X.X
	} elseif (phpversion() < '5.0.0') {
		include ("code/warning.php");
	
	// If no password is set, show password creation screen
	} elseif ( file_exists ('settings/.opentape_password.php') === false || filesize ('settings/.opentape_password.php') == 0) {
		include ("code/welcome.php");
	
	// otherwise, all is well!
	} else {
		include("code/mixtape.php");	
	
	}

?>