var form = $('upload_form');
var button = $('upload_button');

if(form) {
	form.addEvent('submit',function(event){
		var value = $('upload_input').value;
		if(!value) {
			event.stop();
			return false;
		} else {
			if(!value.contains('.mp3') && !value.contains('.MP3') && !value.contains('.Mp3') && !value.contains('.mP3')) {
				alert('Opentape only accepts MP3s.');
				$('upload_input').value = '';
				event.stop();
				return false;
			}
			
		}
		button.blur();
		button.addClass('deactivated');
		button.addEvent('click',function(event){event.stop()});
		status_on();
	});
}

function status_on() {
	button.setProperty('value','uploading...');
	status_off.delay(1800);
}

function status_off() {
	button.setProperty('value','uploading   ');
	status_on.delay(500);
}