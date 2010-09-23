var InstantSearch = {
	
	running: false,
	request: null,
	
	url: '',
	
	init: function () {
		
		// hook our ajax event when a key is released
		$('input#q').keyup( function ( ) {			
			InstantSearch.search( $(this).val() );
			
			// prevent the browser from doing anything
			return false;
		} );
		
		// also hook our ajax event when the instant search form is submitted (via button or 'enter')
		$('form#instant_search').submit( function ( ) {
			InstantSearch.search( $('input#q').val() );
			
			// prevent the browser from doing anything
			return false;
		} );

	},
	
	search: function ( search_term ) {
		
		// cancel any previous requests
		if ( InstantSearch.running ) {
			InstantSearch.request.abort();
			InstantSearch.running = false;
		}
		
		// if the search is blank, there is no point in making a request, display the blank results
		if ( search_term == '' ) {
			InstantSearch.show_results('', search_term);
		}
		
		this.request = $.ajax({
			method: 'GET',
			url: this.url,
			dataType: 'json',
			data: { q: search_term },
			success: function ( data ) {
				InstantSearch.show_results(data, search_term);
			}
		});
		
	},
	
	show_results: function ( data, search_term ) {
		
		// the request has finished
		InstantSearch.running = false;
		
		var html = '';
		
		$.each( data, function( i, item ) {
			var snip = '';
			
			// build the html for this post
			snip += '<div class="result">';
			snip += '	<h2><a href="' + item.url + '">' + item.title + '</a></h2>';
			snip += '	<p>' + item.content.replace( search_term, '<span class="highlight">' + search_term + '</span>' ) + '</p>';
			snip += '	<a href="' + item.url + '" class="read_more">Read more...</a>';
			snip += '</div>';
			
			// append it to the main html block we're building
			html += snip;
		} );
		
		// and stick the html we built into the results div
		$('div#results').html( html );
		
	}
		
};

$(document).ready( InstantSearch.init );