var autoSave = {
	lastSaved: null,
	lastDiffed: null,
	saveTimer: null,
	diffTimer: null,
	saveClicked: false,
	
	// Initiates timers, prepares placeholder and hijacks buttons/form
	init: function() {
		// Autosave placeholder to display errors or last save timestamp
		$('#save').append('<span id="autosave" style="float:right;"><span id="autosave_type"></span> at <span id="autosave_last"></span> (<span id="autosave_diff"></span> ago)</span>');
		$('#autosave').hide();
		
		/* Let's hook everything we need to provide best autosaving */
		$('#save > input').val('Save now').click(function(){
			// Call our own handler via ajax
			autoSave.save();
			return false;
		});
		
		// Reset the "Save now" button if changes happen on any field and unset timers
		$('#create-content input[type="text"], #create-content input[type="password"], #create-content textarea').keypress(autoSave.formChangeAndUnset);
		$('#create-content input[type="checkbox"], #create-content input[type="radio"], #create-content select').change(autoSave.formChangeAndUnset);
		// Save after interval if changes happen upon changing title or content
		$('#title, #content').unbind('keypress', autoSave.formChangeAndUnset).keypress(autoSave.formChangeAndSet);
	},
	
	// Displays difference in time (X minutes ago)
	diff: function() {
		dateSaved = autoSave.lastSaved != null ? autoSave.lastSaved : new Date();
		dateDiffed = autoSave.lastDiffed != null ? autoSave.lastDiffed : new Date();
		dateDiff = Math.round(( dateDiffed - dateSaved ) / 60000);
		diffMinutes = dateDiff == 1 ? ' minute' : ' minutes';
		$('#autosave_diff').html(dateDiff + diffMinutes);
		autoSave.lastDiffed = new Date();
	},
	
	// Saves the current form as a revision
	save: function() {
		// Time to send form home
		var publishForm = $('#create-content').serializeArray();
		$.post(autoSave.url, publishForm, autoSave.handleResponse, 'json');
		autoSave.saveTimer = null;
	},
	
	handleResponse: function(data) {
		$('#autosave').show();
		autoSave.lastSaved = new Date();
		var lastHours = (autoSave.lastSaved.getHours() < 10 ? '0' : '') + autoSave.lastSaved.getHours();
		var lastMinutes = (autoSave.lastSaved.getMinutes() < 10 ? '0' : '') + autoSave.lastSaved.getMinutes();
		if (autoSave.isSet(data.post_id) && autoSave.isSet(data.post_slug)) {
			// Prevent a new ID to be generated if title is changed
			$('input[name="id"]').val(data.post_id);
			$('#newslug').val(data.post_slug);
			// Advise user we just saved the form
			$('#save > input').attr('disabled', 'disabled').val('Saved').css('color', '#555');
			if (autoSave.lastFailed) {
				autoSave.lastFailed = false;
				$('#autosave').html('<span id="autosave_type"></span> at <span id="autosave_last"></span> (<span id="autosave_diff"></span> ago)');
			}
			$('#autosave_last').html(lastHours + ':' + lastMinutes);
			$('#autosave_type').html(autoSave.saveClicked ? 'Saved' : 'Autosaved');
			autoSave.diff();
			autoSave.diffTimer = setInterval("autoSave.diff()", 60000); // 1 minute
			
			// Don't prompt users with annoying messages about not being saved
			initialCrc32 = crc32($('#content').val(), crc32($('#title').val()));
		}
		else {
			autoSave.lastFailed = true;
			$('#autosave').html('Unexpected behavior, post may not have been saved. (' + lastHours + ':' + lastMinutes + ')').css('color', '#A30000');
		}
		
		$.each( data.messages, function( i ) {
			humanMsg.displayMsg( data.messages[i] );
		});
	},
	
	formChange: function() {
		// If "save now" button isn't already enabled,
		// Reset "save" button if changes happen after autosaving
		if ($('#save > input').attr('disabled')) {
			autoSave.saveCicked = false;
			$('#save > input').attr('disabled', null).val('Save now').css('color', '#333');
		}
	},
	
	formChangeAndUnset: function() {
		autoSave.formChange();
		// Prevent unwanted savings (incomplete tags or new slugs, for example)
		autoSave.unload();
	},
	
	formChangeAndSet: function() {
		autoSave.formChange();
		// Set a timer to save form after 10 seconds after starting to type
		if (autoSave.saveTimer == null) {
			autoSave.saveTimer = setTimeout('autoSave.save()', 10000);
		}
	},
	
	isSet: function (varname) {
		try {
			var type = typeof(varname);
		} catch(e) { return false; }

		if (type !== 'undefined') { return true; }
		else { return false; }
	},
	
	unload: function() {
		clearTimeout(autoSave.saveTimer);
		clearInterval(autoSave.diffTimer);
	}
}

$(document).ready(function() {
	autoSave.init();
});

$(document).unload(function() {
	autoSave.unload();
});