$(document).ready(function() {
	$('.spoiler-message').bind( 'click', function() {
		$(this).parent().find('.spoiler-text').show();
		$(this).hide();
		return false;
	});
});
