$(document).ready(function() {
	var loupe = $('#timeline');
	loupe.hover(
		function() {
			$('#timeline, #timeline .years, #timeline a.item').animate({ 
				height: "50"
			}, 500 );
			$('#timeline .years .months div span').animate({ 
				top: "33"
			}, 500 );
		},
		function() {
			$('#timeline, #timeline .years, #timeline a.item').animate({ 
				height: "15"
			}, 500 );
			$('#timeline .years .months div span').animate({ 
				top: "-2"
			}, 500 );
		}
	);
});