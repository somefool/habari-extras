$(document).ready(function() {
	var preview = $('#comment-preview');
	
	$('#commentform input[type=text], #commentform textarea').keyup(function() {
		var data = false;
		$('#commentform input[type=text], #commentform textarea').each(function() {
			if($(this).val() != '') {
				data = true;
				$('.' + $(this).attr('id') + 'holder').text($(this).val());
				$('.' + $(this).attr('id') + 'container').show();
				
				if($('.' + $(this).attr('id') + 'holder').hasClass('urlholder')) {
					$('.' + $(this).attr('id') + 'holder').attr('href', $(this).val())
				}
				
			} else {
				$('.' + $(this).attr('id') + 'container').hide();
			}
		});
		
		if(data) {
			preview.slideDown(300);
		} else {
			preview.slideUp(300);
		}
	});
});