var tagSuggest = { 
	suggestBox: null, 
	init: function() { 
		if($('.create').length == 0) {
			return; 
		}; 
		
		tagSuggest.suggestBox= $('<ol id="tagsuggestions"></ol>').insertAfter('#tags'); 

		$('input, textarea').blur(function() {
			if($('#content').val().length > 5 ) {
				var content = $('#content').val(); 

				tagSuggest.fetch(content);
			}
		}); 
		
		if($('#content').val().length > 5 ) {
			tagSuggest.fetch($('#content').val());
		}
		
	}, 
	fetch: function(content) {

		spinner.start(); 

		var query = {}; 
		query['text'] = content; 
		query['view'] = 'json'; 

		$.ajax({ 
			type: 'POST', 
			url: habari.url.habari + '/ajax/tag_suggest', 
			data: query, 
			dataType: 'json', 
			success: function(json){
			   spinner.stop(); 
			   var response = {}; 
			   if(json.count == 0) { 
			       tagSuggest.suggestBox.addClass('none'); 
			       tagSuggest.suggestBox.html(''); 
        
			       $('<li></li>').text(json.message).prependTo(tagSuggest.suggestBox); 
        
			   } else { 
        
			       tagSuggest.suggestBox.html(''); 
			       tagSuggest.suggestBox.removeClass('none'); 
        
			       for (var index in json.tags)    { 
			           tag= json.tags[index]; 
            
			           $('<li></li>').text(tag).prependTo(tagSuggest.suggestBox); 
            
			       } 
			   }
		
				tagSuggest.clickable();
			
			} 
		});
	},
	clickable: function() {
		$('#tagsuggestions li').each(function() {
			var searchstr = '\\s*"?' + $( this ).text() + '"?\\s*';
			
			if($('#tags').val().search(searchstr) != -1) {
				$(this).addClass('clicked');
			}
		});
		$('#tagsuggestions li').click(function() {
			// here we set the current text of #tags to current for later examination
			var current = $('#tags').val();

			// create a regex that finds the clicked tag in the input field
			var replstr = new RegExp('\\s*"?' + $( this ).text() + '"?\\s*', "gi");

			// check to see if the tag item we clicked has been clicked before...
			if( $( this ).hasClass('clicked') ) {
				// remove that tag from the input field
				$( '#tags' ).val( current.replace(replstr, '') );
				// unhighlight that tag
				$(this).removeClass( 'clicked' );
			}
			else {
				// if it hasn't been clicked, go ahead and add the clicked class
				$(this).addClass( 'clicked' );
				// be sure that the option wasn't already in the input field
				if(!current.match(replstr) || $( '#tags.islabeled' ).size() > 0) {
					// check to see if current is the default text
					if( $( '#tags.islabeled' ).size() > 0 ) {
						// and if it is, replace it with whatever we clicked
						$( '#tags' ).removeClass('islabeled').val( $( this ).text() );
					} else {
						// else if we already have tag content, just append the new tag
						if( $('#tags' ).val() != '' ) {
							$( '#tags' ).val( current + "," + $( this ).text() );
						} else {
							$( '#tags' ).val( $( this ).text() );
						}
					}
				}
			}

			// replace unneccessary commas
			$( '#tags' ).val( $( '#tags' ).val().replace(new RegExp('^\\s*,\\s*|\\s*,\\s*$', "gi"), ''));
			$( '#tags' ).val( $( '#tags' ).val().replace(new RegExp('\\s*,(\\s*,)+\\s*', "gi"), ','));

		});
	}
}

$(document).ready(function(){
	tagSuggest.init();
});