helpify = {
	init: function() {
		helpify.container = $('#create-content #help_container');
		helpify.link = $('.head a.help', helpify.container);
		helpify.content = $('.content', helpify.container);
		
		if( helpify.container.length == 0 ) {
			return;
		}
		
		helpify.content.hide();
		
		helpify.link.click( function() {			
			if( helpify.container.hasClass('open') ) {
				helpify.contract();
				
				
			}
			else {
				helpify.expand();
			}
			
			return false;
		});
		
		// helpify.expand();
		
	},
	expand: function() {
		helpify.container.addClass('open');
		helpify.container.removeClass('transparent');
		
		helpify.content.slideDown();
	},
	contract: function() {
		helpify.content.slideUp( 'fast', function() {
			helpify.container.removeClass('open');
			helpify.container.addClass('transparent');
		});
	}
}

$(document).ready(function() {
	setTimeout("helpify.init()", 200);
});
