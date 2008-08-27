<?php

	include("opentape_common.php");
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Warning - Fix issue before proceeding! / Opentape</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/style.css" />
	</head>
	<body>
		<div class="container">
            <div class="banner">
				<h1>OPENTAPE</h1>
			    <div class="ajax_status"></div>
			</div>	

			<div class="content">
            <div class="section">
			<h2 style="color:#f00;">Uh oh!</h2>

			<?php 
				if ( !is_writeable('settings') || !is_writeable('songs') ) {
			?>
			<p>Looks like two directories that need to be writeable, aren't. :(</p>
			<p>Please make <span style="color: #f00;">/songs/</span> and <span style="color: #f00;">/settings/</span> writeable by the web server user.  Your webhost should be able to provide more information on how to quickly accomplish this.</p>			
			<p>Then refresh this page and you'll be all set.</p>
			<?php
				} elseif ( phpversion() < '5.0.0' ) {
			?>
			<p>Looks like you are running PHP version <?php echo phpversion(); ?>.  Opentape currently requires version 5+ to be installed on your webserver.</p>
			<?php
				}
			?>
			</div>
		<div class="footer">
			<?php get_version_banner(); ?>
		</div>		
		</div>
	</div>
		
	</body>
</html>