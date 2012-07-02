<?php
	
	require_once("opentape_common.php");

	check_cookie();
	
	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;
	$songlist_hash= md5(serialize($songlist_struct));

	$prefs_struct = get_opentape_prefs();
	
	if(!empty($prefs_struct['banner'])) { $page_title = strip_tags($prefs_struct['banner']); } else { $page_title = "Opentape / " . count($songlist_struct) . " songs, " . get_total_runtime_string(); }
	if(!empty($prefs_struct['color'])) { $header_bg_color = $prefs_struct['color']; } else { $header_bg_color = constant("DEFAULT_COLOR"); }
	if(!empty($prefs_struct['banner'])) { $banner_header_text = $prefs_struct['banner']; } else { $banner_header_text = "OPENTAPE"; }
	if(!empty($prefs_struct['caption'])) { $banner_caption_text = $prefs_struct['caption']; } else { $banner_caption_text = count($songlist_struct) . " songs, " . get_total_runtime_string(); }

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--

	Liberating taste.
	
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<title><?php echo $page_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/tape.css" />
	<link rel="alternate" type="application/rss+xml" href="<?php echo $REL_PATH; ?>code/rss.php" />
	<style type="text/css">
		div.banner { background: #<?php echo $header_bg_color; ?>; }									
	</style>
		
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-core-1.3-yc.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-more-1.3-yc.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/soundmanager2-nodebug-jsmin.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/player.js"></script>

	<script type="text/javascript">
        soundManager.debugMode = false;
        soundManager.url = 'res/';
        if(Browser.Platform.ios) { soundManager.useHTML5Audio = true; }
	</script>
	</head>
	
	<body>
		<div class="container">
			<div class="banner">
				<div class="flag">
					<h1><?php echo $banner_header_text; ?></h1>
					<h2><?php echo $banner_caption_text; ?></h2>
				</div>
			</div>
				
			<ul class="songs">
<?php
		
		$i = 0;
        foreach ($songlist_struct as $pos => $row) { 

        	if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
				unset($songlist_struct[$pos]);
				continue;
			}

?>
			<li class="song" id="song<?php echo $i; ?>">
			<div class="name">
				<?php
					if (isset($row['opentape_artist']) && !empty($row['opentape_artist'])) { echo $row['opentape_artist'] . " - "; } 
					elseif (!isset($row['opentape_artist']) && isset($row['artist'])) { echo $row['artist'] . " - "; } 
					
					if (isset($row['opentape_title'])) { echo $row['opentape_title']; }
					else { echo $row['title']; }
				?>
			</div>

			<?php if (isset($prefs_struct['display_mp3']) && $prefs_struct['display_mp3']==1) { ?>
			<a class="mp3" href="<?php echo $REL_PATH . constant("SONGS_PATH") . rawurlencode($row['filename']); ?>" target="_blank">MP3</a>
			<?php } else { ?>
			&nbsp;
			<?php } ?>

			<div class="info">
			<div class="clock"></div> <strong><?php echo $row['playtime_string']; ?></strong>
			</div>
			
		</li>
<?php 
			
			$i++;
	
		}
		
    	if ($songlist_struct != $songlist_struct_original) { // a song in the db went missing!
        	write_songlist_struct($songlist_struct);
        }
	
?>
	</ul>				
		
		<div class="footer">
			<?php get_version_banner(); ?> &infin; <a href="<?php echo $REL_PATH; ?>code/edit.php">Admin</a>
		</div>
				
		</div>
		
	<script type="text/javascript">
		
		var openPlaylist=new Array(); // build track array, do it in this sequence so files detected as missing in the load-scan are not included
		openPlaylist.push(<?php
			foreach ($songlist_struct as $pos => $row) { $list_str .= "'" . $pos . "',"; }
			$list_str = preg_replace('/,$/','',$list_str);
			echo $list_str;
			?>);

        var pageTitle = "<?php echo $page_title; ?>";
            
        event_init(); // bind events where needed

	</script>
				
	</body>
</html>
