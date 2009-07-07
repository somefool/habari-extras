// Fire Eagle
var fireeagle = {
	update: function() {
		spinner.start();
		var query = {};
		query['location'] = $('#dash_fireeagle input:text').val();
		$.post(
			habari.url.habari + '/admin_ajax/fireeagle_update',
			query,
			function(result) {
		     	spinner.stop();
				if (result.errorMessage) {
					humanMsg.displayMsg(result.errorMessage);
					return;
				}
				$('#dash_fireeagle input:text').val(result.location);
				humanMsg.displayMsg(result.message);
			}, 'json');
	}
};
