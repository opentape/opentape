
var currentTrack;
var isReady = 0;
var playerStatus = "";
var currentPos;
var playerObj = Array();

if (typeof(soundManager)!='undefined') {
	soundManager.onready(function() {
	  if (soundManager.supported()) {
		isReady = 1;
	 } else {
	    // unsupported/error case
	    alert("Unable to load Sound Manager to play audio :(");
	  }
	});
}

window.debug = function(q,w,e,r){  
    try { if (typeof console != 'undefined') { console.log.apply(console,arguments); }} 
    catch(err){ if (typeof console != 'undefined') { console.log(q,w,e,r); }}
};

function event_init() {

    // assign all the right events
    for(i = 0; i < openPlaylist.length; i++) {
    	var trackEntry = $('song'+i);
    	if(trackEntry) {
    	
    		trackEntry.addEvent('mouseover',function() { trackEntry.addClass('hover'); });
    		trackEntry.addEvent('mouseout',function() { trackEntry.removeClass('hover'); });
    		
    		// because of the numerous subelements one can click, we need to do this ugly thing
    		trackEntry.addEvent('click',function(e) {
    				targ = e.target || e.srcElement;
    				if (targ.id.indexOf("song")!=-1) { togglePlayback(targ.id); }
    				else if (targ.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.id); }
    				else if (targ.parentNode.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.parentNode.id); }
    				else if (targ.parentNode.parentNode.parentNode.id.indexOf("song")!=-1) { togglePlayback(targ.parentNode.parentNode.parentNode.id); }
    		});
    						
    	}
    }	

}

// base64 class: http://www.webtoolkit.info //
var Base64 = {

    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = 0;
        var c1 = 0;
        var c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

};


function togglePlayback(id) {

	id = parseInt(id.replace(/song/,''));
	songClock = $$('#song'+currentTrack+' .clock');
	songItem = $('song'+currentTrack); 
	
	if (id == currentTrack && typeof(currentTrack)!='undefined') { 
		if(playerStatus == "PAUSED") {
			songClock.removeClass('grey');
			songClock.addClass('green');
			playerObj[0].resume();
		} else {
			songClock.removeClass('green');
			songClock.addClass('grey');	
			playerObj[0].pause();
		}
	} else {
	    cleanTrackDisplay(currentTrack);
		currentTrack = id;
		playTrack();
	}
}


function playTrack() {

    if(playerObj[0]) { playerObj[0].destruct(); }

    if(playerObj[1]) { 
        tmp = playerObj.shift(); 
        setupTrackDisplay(currentTrack);
        return true;
    } // likely already playing though, so just exit
    			
	try { playerObj[0] = soundManager.createSound({
	  id: 'playerObj' + currentTrack,
	  url: "songs/" + Base64.decode(openPlaylist[currentTrack]),
		autoLoad: true,
		autoPlay: true,
		onload: sm_onload,
		onplay: sm_onplay,
		onresume: sm_onresume,
		onpause: sm_onpause,
		whileplaying: sm_whileplaying,
//		whileloading: sm_whileloading,
		onfinish: sm_onfinish,
	  	volume: 80
        }); 
	}
	catch(err) { debug('Cant create sound: ' + err.description ); }  

    setupTrackDisplay(currentTrack);

}

function loadNextTrack() {

    debug("loadNextTrack() called");

    if(! openPlaylist[(currentTrack+1)]) { debug("This is the last track"); return false; }
    if(playerObj[1]) { playerObj[1].destruct(); }

    try { playerObj[1] = soundManager.createSound({
      id: 'playerObj' + (currentTrack+1),
      url: "songs/" + Base64.decode(openPlaylist[(currentTrack+1)]),
        autoLoad: true,
        autoPlay: false,
        onload: sm_onload,
        onplay: sm_onplay,
        onresume: sm_onresume,
        onpause: sm_onpause,
        whileplaying: sm_whileplaying,
//      whileloading: sm_whileloading,
        onfinish: sm_onfinish,
        volume: 0
        }); 
    }
    catch(err) { debug('Cant create sound: ' + err.description ); }  


}


function beginFadeTransition() {

    debug("beginFadeTransition() called");

    playerObj[1].play();
    fadeOutSound(playerObj[0],-5); // fade a sound out
    fadeInSound(playerObj[1],5); // fade a sound out

}

function fadeInSound(soundObj,amount) {
  var vol = soundObj.volume;
  if (vol == 100) return false;
  soundObj.setVolume(Math.min(100,vol+amount));
  setTimeout(function(){fadeInSound(soundObj,amount)},300);
}

function fadeOutSound(soundObj,amount) {
  var vol = soundObj.volume;
  if (vol == 0) return false;
  soundObj.setVolume(Math.max(0,vol+amount));
  setTimeout(function(){fadeOutSound(soundObj,amount)},300);
}


function nextTrack() {
	
	if (openPlaylist[(currentTrack+1)]) {
	    cleanTrackDisplay(currentTrack);
		currentTrack++; 
		playTrack(); 
		return true;
	} else { 
		return false;
	}

}

function cleanTrackDisplay(id) {

    if (typeof(id)=='undefined') { return false; }
	songClock = $$('#song'+id+' .clock');
	songItem = $('song'+id);

	songItem.removeClass('hilite');		
	songClock.set('html','');

}

function setupTrackDisplay(id) {

	songClock = $$('#song'+id+' .clock');
	songItem = $('song'+id);

	songClock.removeClass('grey');
	songClock.addClass('green');
	songClock.set('html', '&mdash;');
	songItem.addClass('hilite');
				
	var name = String($$('#song'+ id +' .name').get('html'));
	name = name.replace('&amp;','&');
	document.title = '\u25BA ' + name.trim() + " / " + pageTitle;		

}



// auto advance on track load failure
function sm_onload() { if(currentPlayerObj.readyState == 2) { nextTrack(); } }
function sm_onplay() { playerStatus = "PLAYING"; }
function sm_onpause() {	playerStatus = "PAUSED"; document.title = document.title.replace(/\u25BA/, '\u25FC'); }
function sm_onresume() { playerStatus = "PLAYING"; document.title = document.title.replace(/\u25FC/, '\u25BA'); }
function sm_onfinish() { nextTrack(); }

function sm_whileplaying() {
	
    if (this.sID != "playerObj" + currentTrack) { return false; } // ignore timing events for players that are fading in
	player_position = parseInt(this.position/1000);
	player_duration = parseInt(this.duration/1000);	
	
	if ( player_position==currentPos ) { return false; }
	else {
		var string = '';
		var sec = player_position % 60;
		var min = (player_position - sec) / 60;
		var min_formatted = min ? min+':' : '';
		var sec_formatted = min ? (sec < 10 ? '0'+sec : sec) : sec;
		string = min_formatted + sec_formatted;
	
		songClock = $$('#song'+currentTrack+' .clock');
		songClock.set('html', string);
		currentPos = player_position;

        debug ((player_duration - player_position) + "sec remaining");
        if(is_fade_enabled() && (player_duration - player_position) == 10) { loadNextTrack(); }
        else if(is_fade_enabled() && (player_duration - player_position) == 5) { beginFadeTransition(); } 

	}
		
}

function is_fade_enabled() {
    
    if (Browser.Platform.ios) { return false; }
    else { return true; }

}

//function sm_whileloading() {
	
	//debug( this.bytesLoaded + " loaded, " + this.bytesTotal + " total...");
//	percent_loaded = (Math.round((this.bytesLoaded / this.bytesTotal) * 100 ) * 100 / 100) + '%'; // dumb JS way to get decimals XX.YY
//	e = new Effect.Morph( $('player-progress-loading'), { style: { width:percent_loaded }, duration: '0.2' }); 
			
//}