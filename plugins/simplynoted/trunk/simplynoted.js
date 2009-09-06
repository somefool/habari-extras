habari.media.preview.note = function(fileindex, fileobj) {
	return '<div class="mediatitle">' + fileobj.title + '</div><p class="mediasummary" style="width: 100px; height: 100px; color: #555555;">' + fileobj.summary + '</p>';
}

habari.media.output.note = {
	view: function(fileindex, fileobj) {
		$('input[name=note_key]').val( fileobj.key );
		$('textarea#notes').val( fileobj.content ).focus();
		
		console.log( $('#mediatabs').parent() );
		$('#mediatabs').parent().tabs('select', -1);
		// habari.media.clearSelections();
	},
	insert: function(fileindex, fileobj) {
		habari.editor.insertSelection(fileobj.content);
	}
}