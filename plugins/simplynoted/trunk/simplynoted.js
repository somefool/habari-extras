habari.media.preview.note = function(fileindex, fileobj) {
	return '<div class="mediatitle">' + fileobj.title + '</div><p class="mediasummary" style="width: 100px; height: 100px; color: #555555;">' + fileobj.summary + '</p>';
}

habari.media.output.note = {
	view: function(fileindex, fileobj) {
		notes.key.val( fileobj.key );
		notes.input.val( fileobj.content ).focus();
		
		notes.show();
		
		// $('#mediatabs').parent().tabs('select', -1);
	},
	insert: function(fileindex, fileobj) {
		habari.editor.insertSelection(fileobj.content);
	}
}

var notes = {
	init: function() {
		notes.input = $('#notes');
		notes.key = $('input[name=note_key]');
		notes.container = notes.input.parents('.container');
		notes.tabs = $('#mediatabs').parent();
		
		notes.container.hide();
		
		notes.tabs.bind('tabsselect', function(event, ui) {
			notes.hide();
		});
		
		notes.tabs.bind('tabsshow', function(event, ui) {
			// console.log(notes.tabs.tabs('option', 'selected'));
			if( $(ui.panel).children('#silo_simplenote').length > 0 ) {
				notes.show();
			}
		});
	},
	show: function() {
		notes.container.slideDown('slow');
	},
	hide: function() {
		notes.container.slideUp('slow');
	}
}

$(document).ready(function() {
	notes.init();
});