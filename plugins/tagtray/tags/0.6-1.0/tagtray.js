$(function(){
	// Tag Drawer: Add tag via click
	$('#tag-list li').click(function() {
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
				if( $( '#tags').val().length == 0 ) {
					// and if it is, replace it with whatever we clicked
					$( '#tags' ).removeClass('islabeled').val( $( this ).text() );
					$('label[for=tags]').removeClass('overcontent').addClass('abovecontent').hide();
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
		$( '#tags' ).val( $( '#tags' ).val().replace(new RegExp('\\s*,(\\s*,)+\\s*', "gi"), ', '));
		
		resetTags();
	});
	
	$( '#tags' ).keyup(function(){
		clearTimeout(tagskeyup);
		tagskeyup = setTimeout(resetTags, 500);
	});
	
	$('#tags').focus(function() {
		$('#tags').addClass('focus');
		}).blur(function() {
			$('#tags').removeClass('focus');
		});
	
	// Tag Drawer: Remove all tags.
	$('#clear').click( function() {
		// so we nuke all the tags in the tag text field
		$(' #tags ').val( '' );
		$('label[for=tags]').removeClass('abovecontent').addClass('overcontent').show();
		// and remove the clicked class from the tags in the manager
		$( '#tag-list li' ).removeClass( 'clicked' );
	});
});