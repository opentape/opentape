<?php

	include("opentape_common.php");
	
	if (is_logged_in()) {
	
		remove_session();
	
		header("Location: " . $REL_PATH);
		
	} else {
		header("Location: " . $REL_PATH);
	}
	
?>