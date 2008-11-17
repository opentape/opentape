<?php
	
	require_once("opentape_common.php");

	check_cookie();
	
	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;
	$songlist_hash= md5(serialize($songlist_struct));

	$prefs_struct = get_opentape_prefs();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--

	Liberating taste.
	
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<title><?php if(!empty($prefs_struct['banner'])) { echo $prefs_struct['banner']; } else { echo "Opentape / " . count($songlist_struct) . " songs, " . get_total_runtime_string(); } ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/tape.css" />
	<link rel="alternate" type="application/rss+xml" href="<?php echo $REL_PATH; ?>code/rss.php" />
	<style type="text/css">
		div.banner { background: #<?php if(!empty($prefs_struct['color'])) { echo $prefs_struct['color']; } else { echo constant("DEFAULT_COLOR"); } ?>; }									
	</style>
		
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools.js"></script>
	<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/swfobject.js"></script>
	<script type="text/javascript">
		if(!navigator.userAgent.match(/iPhone|iPod/i)) {			
			var flashvars = {
				type: "xml",
				shuffle: "false",
				repeat: "list",
				file: "<?php echo get_base_url(); ?>code/xspf.php<?php echo "?" . $songlist_hash; ?>"		
			}
			var params = {
				allowscriptaccess: "always"
			}
			var attributes = {
			  id: "openplayer",
			  name: "openplayer",
			  styleclass: "flash_player"
			}
			swfobject.embedSWF('<?php echo get_base_url(); ?>res/mediaplayer.swf', "openplayer", "0", "0", "8.0.0", false, flashvars, params, attributes);
		}
	 </script>
	</head>
	
	<body>
		<div class="container">
			<div class="banner">
				<div class="flag">
					<h1><?php if(!empty($prefs_struct['banner'])) { echo $prefs_struct['banner']; } else { echo "OPENTAPE"; } ?></h1>
					<h2><?php if(!empty($prefs_struct['caption'])) { echo $prefs_struct['caption']; } else { echo count($songlist_struct); ?> songs, <?php echo get_total_runtime_string(); } ?></h2>
					
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
	
?>
	</ul>				
		
		<div class="footer">
			<?php get_version_banner(); ?> &infin; <a href="<?php echo $REL_PATH; ?>code/edit.php">Admin</a>
		</div>
		
		<div id="openplayer" class="flash_player"></div>
		
		</div>
		
	<script type="text/javascript">
		
		var openPlaylist=new Array();
		openPlaylist.push(<?php
			$list_str = "";
			foreach ($songlist_struct as $pos => $row) { 
				$list_str .= "'" . preg_replace('/=/', '', $pos) . "',";				
			}
			$list_str = preg_replace('/,$/','',$list_str);
			echo $list_str;
			?>);
			
		// assign all the right events
		for(i = 0; i < openPlaylist.length; i++) {
			var trackEntry = $('song'+i);
			if(trackEntry) {
			
				trackEntry.addEvent('mouseover',function() {
					trackEntry.addClass('hover');
				});
				
				trackEntry.addEvent('mouseout',function() {
					trackEntry.removeClass('hover');
				});
				
		
				trackEntry.addEvent('click',function(e) {
						targ = e.target || e.srcElement;
						// because of the numerous subelements one can click, we need to do this ugly thing
						if (targ.id.indexOf("song")!=-1) { togglePlayback(targ.id); }
						else if (targ.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.id); }
						else if (targ.parentNode.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.parentNode.id); }
						else if (targ.parentNode.parentNode.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.parentNode.parentNode.id); }
				});
				
				
			}
		}	
	
		// Player management code //
	
		var currentTrack = 0;
		var isReady = 0;
		var playerStatus = "";
		var currentPos;
		var player;
		
		function playerReady(obj) {
			var id = obj['id'];
			var version = obj['version'];
			var client = obj['client'];
			isReady = 1;
			sendEvent('ITEM',currentTrack); // sets the playback to item 0
			player = document.getElementById(id);
			player.addModelListener('STATE','updatePlayerState');
			player.addModelListener('TIME','updateCurrentPos');
			player.addControllerListener('ITEM','updateCurrentTrack');
		}
		
		function updatePlayerState(obj) {
			playerStatus = obj['newstate'];
			//console.log("status: " + obj['newstate'] + " currentTrack: " + currentTrack);
		}
		
		
		function updateCurrentTrack(obj) {
			cleanTrackDisplay(currentTrack);
			currentTrack = obj['index'];
			setupTrackDisplay(obj['index']);
			//console.log("currentTrack changed to: " + obj['index']);
		}

		
		function updateCurrentPos(obj) {
			pos = Math.round(obj['position']);
			if ( pos==currentPos ) { return false; }
			else {
				var string = '';
				var sec = pos % 60;
				var min = (pos - sec) / 60;
				var min_formatted = min ? min+':' : '';
				var sec_formatted = min ? (sec < 10 ? '0'+sec : sec) : sec;
				string = min_formatted + sec_formatted;
			
				songClock.setHTML(string);
				currentPos = pos;
			}
		
		}
		
		function playTrack() {
				//console.log("Executing playTrack: " + currentTrack);
				setupTrackDisplay(currentTrack);
				sendEvent('ITEM',currentTrack);
				sendEvent('PLAY',true);
		}
	
		function stopTrack() {
			sendEvent('STOP');
			cleanTrackDisplay(currentTrack);
		}
		
		function cleanTrackDisplay(id) {
			//console.log("Executing cleanTrackDisplay: " + id);

			songClock = $E('#song'+id+' .clock');
			songItem = $E('#song'+id);

			songItem.removeClass('hilite');		
			songClock.setHTML('');
		}
		
		function setupTrackDisplay(id) {
			//console.log("Executing setupTrackDisplay: " + id);

			songClock = $E('#song'+id+' .clock');
			songItem = $E('#song'+id);
		
			songClock.removeClass('grey');
			songClock.addClass('green');
			songClock.setHTML('&mdash;');
			songItem.addClass('hilite');
						
			var name = $E('#song'+ id +' .name').getHTML().replace('&amp;','&');
			document.title = name.trim() + " / <?php if(!empty($prefs_struct['banner'])) { echo escape_for_json($prefs_struct['banner']); } else { echo "OPENTAPE"; } ?>";		
		}
	
		function togglePlayback(id) {
			id = id.replace(/song/,'');
			songClock = $E('#song'+currentTrack+' .clock');
			// songItem = $E('#song'+currentTrack); 
			// console.log("togglePlayback called with: " + id + " currentTrack is: " + currentTrack);
			
			if (id == currentTrack || id == null) { 
				if(playerStatus == "PAUSED"|| playerStatus=="IDLE") {
					songClock.removeClass('grey');
					songClock.addClass('green');
					sendEvent('PLAY', true);
				} else {
					songClock.removeClass('green');
					songClock.addClass('grey');	
					sendEvent('PLAY', false);
				}
			} else {
				stopTrack();
				currentTrack = id;
				playTrack();
			}
		}
		
		// Player maintenance functions
		function sendEvent(typ,prm) { 
			if( isReady ) {	thisMovie('openplayer').sendEvent(typ,prm); }
		}

		function thisMovie(movieName) {
			if(navigator.appName.indexOf("Microsoft") != -1) { return window[movieName]; }
			else { return document[movieName]; }
		}
		
	</script>
				
	</body>
</html>
<?php

	if ($songlist_struct != $songlist_struct_original) {
    	write_songlist_struct($songlist_struct);
    }
    
?>