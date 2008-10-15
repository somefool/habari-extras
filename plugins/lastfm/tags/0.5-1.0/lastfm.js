habari.media.output.lastfm = {
	display: function(fileindex, fileobj) {
		habari.editor.insertSelection('<a href="' + fileobj.url + '"><img src="' + fileobj.image_url + '"></a>');
	}
}