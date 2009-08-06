spamview = {
	init: function() {
		spamview.button = $('#deleteallspam');
		spamview.body = $('body');
		
		if(spamview.button.length > 0) {
			$.hotkeys.add('Ctrl+d', {propagate:true, disableInInput: true}, function(){
					spamview.trash();
			});
		}
		
		// Started work on a chaining system...
		// $.hotkeys.add('s', {propagate:true, disableInInput: true}, function(){
		// 	if(spamview.button.hasClass('active')) {
		// 		spamview.delete();
		// 	}
		// });
		
		spamview.button.click(function() {
			spamview.trash();
			
			return;
		});
		// 
		// spamview.button.focus(function() {
		// 	spamview.button.addClass('active');
		// });
		// 
		// spamview.button.blur(function() {
		// 	spamview.button.removeClass('active');
		// });
		
	},
	trash: function() {

		query = {};

		if(spamview.body.hasClass('page-dashboard')) {
			query.page = 'dashboard';
		}
		else {
			query.page = 'comments';
		}

		$.ajax({
			url: habari.url.habari + '/auth_ajax/deleteallspam',
			type: "POST",
			dataType: "json",
			data: query,

			success: function( json ) {
				if(query.page == 'dashboard') {
					// Reload the page, though we should ideally just refresh with AJAX
					location.reload(true);
				}
				else {
					jQuery.each( json.messages, function( index, value) {
						humanMsg.displayMsg( value );
					} );

					itemManage.fetch();
				}	
			},

		});

	}
};

$(document).ready(function() {
	spamview.init();
});
