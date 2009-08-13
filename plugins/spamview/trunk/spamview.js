spamview = {
	init: function() {
		spamview.button = $('#deleteallspam, #deletealllogs');
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
			
			return false;
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
			query.target = 'spam';
		}
		else if(spamview.body.hasClass('page-logs')) {
			query.page = 'logs';
			query.target = 'logs';
		}
		else {
			query.page = 'comments';
			query.target = 'spam';
		}

		$.ajax({
			url: habari.url.habari + '/auth_ajax/deleteall',
			type: "POST",
			dataType: "json",
			data: query,

			success: function( json ) {
				if(query.page == 'dashboard') {
					dashboard.fetch();
				}
				else {
					itemManage.fetch();
				}
				
				jQuery.each( json.messages, function( index, value) {
					humanMsg.displayMsg( value );
				} );
				
			},

		});

	}
};

$(document).ready(function() {
	spamview.init();
});
