$(document).ready(function() {
	var loupe = $('#timeline');
	loupe.hover(
		function() {
			$('#timeline').animate({ 
				top: "0"
			}, 500 );
		},
		function() {
			$('#timeline').animate({ 
				top: "-35"
			}, 500 );
		}
	);
});