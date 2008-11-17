<?php

	require_once("opentape_common.php");
	
	if (!is_logged_in()) { header("Location: " . $REL_PATH . "code/login.php"); }
	
	check_cookie();
	
	// Don't allow users to upload non-mp3 files, seriously.
	if (isset($_FILES['file']) && !preg_match('/mp3$/i', basename($_FILES['file']['name'])) ) {
	
		$upload_success = -1;
	
	} elseif (isset($_FILES['file']) && preg_match('/mp3$/i', basename($_FILES['file']['name'])) ) {
		// In PHP versions earlier than 4.1.0, $HTTP_POST_FILES should be used instead
		// of $_FILES.
		$upload_dir = constant("SONGS_PATH");
		$upload_file = $upload_dir . basename($_FILES['file']['name']);
		$upload_success;
		if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
			$upload_success = 1;
		} else {
			$upload_success = 0;	
		}
	}

	$songlist_struct = scan_songs();
	$songlist_struct_original = $songlist_struct;
	$prefs_struct = get_opentape_prefs();

	// 604800 = week in seconds
	if (  
		 (!isset($prefs_struct['check_updates']) || $prefs_struct['check_updates'] == 1 ) &&
	 	 ((!isset($prefs_struct['last_update_check']) || (time() - $prefs_struct['last_update_check']) > 604800))
	 	) {
		$prefs_struct = check_for_update();
		if ($prefs_struct===false) { header("Location: " . $REL_PATH . "code/warning.php"); }
	}
	
	if ( isset($prefs_struct['announce']) && $prefs_struct['announce'] == 1 && !isset($prefs_struct['last_announce_songs']) ) {
		announce_songs($songlist_struct);
	}
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Opentape / Edit Mixtape</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?php echo $REL_PATH; ?>res/style.css" />
	</head>
	<body>
		<div class="container">
			<div class="banner">
				<h1>OPENTAPE</h1>
			    <ul class="nav">
					<li id="active"><a href="<?php echo $REL_PATH; ?>code/edit.php">Edit Tape</a></li>
					<li><a href="<?php echo $REL_PATH; ?>code/settings.php">Settings</a></li>
					<?php
					if ( $prefs_struct['latest_opentape'] > constant("VERSION") )
				 	{ ?><li><a href="http://opentape.fm/download">Update to <?php echo $prefs_struct['latest_opentape']; ?></a></li><?php } ?>
					<li id="user">
					   <a id="home" href="<?php echo $REL_PATH; ?>">YOUR TAPE &rarr;</a>
					   <a id="logout" href="<?php echo $REL_PATH; ?>code/logout.php">LOG-OUT &rarr;</a>
				    </li>
                </ul>
                <div class="ajax_status"></div>
			</div>	
						
			<div class="content">
                <div class="section">
                    <h2>The Basics</h2>
                    <p>HTML welcome. Leaving these blank will restore them to the defaults (Title: OPENTAPE; Caption: number of tracks, total running time of mix).</p>
                    <form name="banner_form" onsubmit="return false">
                        <label for="banner">Title:</label>
                        <input type="text" id="banner" maxlength="255" size="55" value='<?php if (!empty($prefs_struct['banner'])) { echo escape_for_inputs($prefs_struct['banner']); } ?>' /><br />
                        <label for="caption">Caption:</label>
                        <input type="text" id="caption" maxlength="255" size="55" value='<?php if (!empty($prefs_struct['caption'])) { echo escape_for_inputs($prefs_struct['caption']); } ?>' /><br />
                        <label for="moo_color_input">Color:</label>
                        <input type="text" id="color_input" value='<?php if(!empty($prefs_struct['color'])) { echo $prefs_struct['color']; } else { echo constant("DEFAULT_COLOR"); } ?>' style="background:#<?php if(!empty($prefs_struct['color'])) { echo $prefs_struct['color']; } else { echo constant("DEFAULT_COLOR"); } ?>;" size="6" maxlength="6" /> <input type="button" id="color_reset_button" value="Reset" /> <a style="vertical-align:top;" href="http://ficml.org/jemimap/style/color/wheel.html" target="_new">(online color wheel)</a><br />
                        <input  id="banner_button" type="button" class="save" value="Save" />
                    </form>	
                </div>
                <div class="section">
                    <h2>Upload Songs</h2>
                    <p>Choose any <strong>MP3</strong> no larger than <?php echo get_max_upload_mb(); ?> MB (this is the upload_max_filesize set by your web host).</p>
                    <p>For larger files, place them into the <span style="color:#00f">songs/</span> folder via FTP.</p>
                    <form name="upload" id="upload_form" enctype="multipart/form-data" action="<?php echo get_base_url(); ?>code/edit.php" method="post">
                        <input id="upload_input" name="file" type="file" /><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo get_max_upload_bytes(); ?>" /><br />
                        <input type="submit" class="button" id="upload_button" value="Upload" />
                    </form>	
                </div>
                <div class="section">
                    <h2>Rearrange Songs</h2>		
                    <p><strong>Drag &amp; drop</strong> to change the order of your mixtape, it will save automatically.</p>
                    <ul class="sortie">
<?php
    foreach ($songlist_struct as $pos => $row) { 
        	if (! is_file( constant("SONGS_PATH") . $row['filename']) ) {
				unset($songlist_struct[$pos]);
				continue;
			}
?>
                        <li id="<?php echo $pos; ?>">
                            <div class="name">
                                <span class="original_artist"><?php
					       if (isset($row['opentape_artist']) && !empty($row['opentape_artist'])) { echo $row['opentape_artist']; } 
					elseif (!isset($row['opentape_artist']) && isset($row['artist'])) { echo $row['artist']; }
				        ?></span> - <span class="original_title"><?php
					   if (isset($row['opentape_title'])) { echo $row['opentape_title']; } 
					   else { echo $row['title']; } 
				    ?></span> <span class="original_filename">(<?php echo $row['filename']; ?>)</span>
                            </div>
                            <div class="inputs">
                                <input type="text" class="artist field" /> - <input type="text" class="title field" />
                                <input type="button" class="save button" value="Save" /> <input type="button" class="cancel button" value="Cancel" />
                            </div>			
                            <div class="abc">rename</div>
                            <div class="ex">delete</div>
                        </li>
<?php
		}
?>			
                    </ul>
                </div>	
		<div class="footer">
			<?php get_version_banner(); ?>
		</div>	
    </div>
</div>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-core-yc.js"></script>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/mootools-1.2-more-yc.js"></script>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/statusfader.js"></script>
		<script type="text/javascript" src="<?php echo $REL_PATH; ?>res/upload.js"></script>

		<?php
			if(isset($upload_success) && $upload_success==1 && isset($_FILES['file'])) {
		?>
			<script type="text/javascript">
				var fader = new StatusFader();
				fader.stay("Upload OK!", '#008000');
			</script>
		<?php
			} elseif(isset($upload_success) && $upload_success==0 && isset($_FILES['file'])) {
		?>
			<script type="text/javascript">
				var fader = new StatusFader();
				fader.stay("Upload failed.", '#ff0000');
			</script>
		<?php
			} elseif(isset($upload_success) && $upload_success==-1 && isset($_FILES['file'])) {
		?>
			<script type="text/javascript">
				var fader = new StatusFader();
				fader.stay("MP3s only!", '#ff0000');
			</script>	
		<?php } ?>

		<script type="text/javascript">
			var fader = new StatusFader();
			var ajax = new Request({
				url: '<?php echo $REL_PATH; ?>code/ajax.php',
				method: 'post',
				autoCancel: 'true',
				onRequest: function() {
					fader.set('progress');
					$(document.body).setStyle('cursor','wait');
				},
				onSuccess: function(resp) {
					var resp = JSON.decode(resp);
					if(resp['command'] == 'delete' && resp['status']) $(resp['args']).dispose();
					if(resp['command'] == 'rename' && resp['status']) {
						var li = $(resp['args']['song_key']);
						var artist = resp['args']['artist'] ? resp['args']['artist'] : 'Unknown';
						var title = resp['args']['title'] ? resp['args']['title'] : 'Untitled';
						li.getElement('input.artist').value = artist;
						li.getElement('input.title').value = title;			
						li.getElement('span.original_artist').set('html',artist);
						li.getElement('span.original_title').set('html',title);
						rename_close(li);
					}
					if(resp['command'] == 'reorder' && resp['status'] == false) {
						window.location.reload();
						return;
					}
					
					fader.set(resp['status'] ? 'success' : 'failure');
					$(document.body).setStyle('cursor','default');
				
				},
				onFailure: function() {
					fader.set('failure');
					$(document.body).setStyle('cursor','default');
				}
			});
													
			if($('banner_button')) {
				$('banner_button').addEvent('click',function(){
					var string = 'command=bannercaptioncolor&args=' + JSON.encode('none') +
								 '&banner=' + encodeURIComponent($('banner').value) + '&caption=' + encodeURIComponent($('caption').value) + '&color=' + encodeURIComponent($('color_input').value);
					ajax.send(string);
					$('color_input').style.background = "#" + $('color_input').value;
				});
			}
			
			if($('color_reset_button')) {
				$('color_reset_button').addEvent('click',function(){
					$('color_input').value = "<?php echo constant("DEFAULT_COLOR"); ?>";
					$('color_input').style.background = "#<?php echo constant("DEFAULT_COLOR"); ?>";
				});
			}

			function rename_open(li) {
				var key = li.getProperty('id');
				li.addClass('hover');			
				li.getElement('div.name').setStyle('display','none');
				if(Browser.Engine.webkit || Browser.Engine.trident) li.getElement('.inputs').setStyle('padding-top','9px');
				li.getElement('div.inputs').setStyle('display','block');
			}
			
			function rename_close(li) {
				li.getElement('div.inputs').setStyle('display','none');
				li.getElement('div.name').setStyle('display','block');
				li.removeClass('hover');
			}
			
			function renaming(li) {
				return (li.getElement('div.inputs').getStyle('display') != 'none');
			}
			
			if($$('ul.sortie li').length) {
				$$('ul.sortie li').each(function(li){
					li.getElement('input.artist').value = li.getElement('span.original_artist').get('text');
					li.getElement('input.title').value = li.getElement('span.original_title').get('text');		
					li.addEvent('mouseenter',function(e){ this.addClass('hover') });
					li.addEvent('mouseleave',function(e){
						if(!renaming(this)) this.removeClass('hover');
					});
					li.getElement('input.save').addEvent('click',function(){
						var a = li.getElement('input.artist').value;
						var b = li.getElement('input.title').value;
						var c = li.getElement('span.original_artist').get('text');
						var d = li.getElement('span.original_title').get('text');
						
						var key = li.getProperty('id');
						if(a != c || b != d) {
							ajax.send('command=rename&args=' + JSON.encode({'song_key':key}) + '&artist=' + encodeURIComponent(a) + '&title=' + encodeURIComponent(b));
						} else {
							rename_close(li);
						}
					});	
					li.getElement('input.cancel').addEvent('click',function(){
						li.getElement('input.artist').value = li.getElement('span.original_artist').get('text');
						li.getElement('input.title').value = li.getElement('span.original_title').get('text');
						rename_close(li);
					});
				});
				
				$$('ul.sortie li div.abc').addEvents({
					'click': function(e) {
						var li = this.getParent();
						if(renaming(li)) rename_close(li);
						else rename_open(li);
					},
					'mouseenter': function(e) { this.addClass('hover'); },
					'mouseleave': function(e) { this.removeClass('hover'); }	
				});
			
				$$('ul.sortie li div.ex').addEvents({
					'click': function(e) { 
						var name = this.getParent().getElement('div.name').get('text').trim().replace('&amp;','&');
						if(confirm('Are you sure you want to delete "'+name+'"?')) {
							var key = this.getParent().getProperty('id');
							ajax.send('command=delete&args='+key);
						}
					},
					'mouseenter': function(e) { this.addClass('hover'); },
					'mouseleave': function(e) { this.removeClass('hover'); }
				});
			
				var sortie = new Sortables($(document).getElement('ul.sortie'),{
					constrain: true,
					handle: 'div.name',
					opacity: 0.85,
					clone: true,
					onComplete: function(li) {
						var order = this.serialize(null,function(el,i){
							return el.getProperty('id');
						});
						if(!arrayCompare(order,original)) ajax.send('command=reorder&args=' + JSON.encode(order));
						original = order;
					}
				});
				
				var original = sortie.serialize(null,function(el,i){
					return el.getProperty('id');
				});
			}
			
			function arrayCompare(a,b) {
				if(a.length != b.length) return false;
				for(i = 0; i < a.length; i++) {
					if(a[i] !== b[i]) return false;
				}
				return true;
			}
			
			</script>
	</body>
</html>
