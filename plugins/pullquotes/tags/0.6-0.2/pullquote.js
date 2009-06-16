$(document).ready(function() { 
	  $('span.pquote-r').each(function() { 
		$(this).clone().addClass('pull-right').prependTo($(this).parent()) 
		});
		
		$('span.pquote-l').each(function() { 
		$(this).clone().addClass('pull-left').prependTo($(this).parent()) 
		});	 

	}); 
