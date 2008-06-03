(function () {
	$(document).ready(function () {
		$('#pubdate').after('<input type="text" id="pubdatejs" name="pubdatejs" class="styledformelement">');
		$('#pubdatejs').after('<span id="pubdate_preview" type="text" class="empty" style="margin-left: 10px;"></span>');
		$('#pubdate').hide();
		var messages = '';
		var input = $("#pubdatejs");
		var date_string = $("#pubdate_preview");
		var date_parsed = $("#pubdate");
		var date = null;
		var input_empty = "Enter your pubdate date";
		var empty_string = "";
		
		input.val(input_empty);
		date_string.text(empty_string);
		date_parsed.text(empty_string);
		
		input.keyup( 
			function (e) {
				date_string.removeClass();
				date_parsed.removeClass();
				if (input.val().length > 0) {
					date = Date.parse(input.val());
					if (date !== null) {
						input.removeClass();
						date_string.text(date.toString("dddd, MMMM dd, yyyy h:mm:ss tt"));
						date_parsed.val(date.toString("yyyy-MM-dd HH:mm:ss"));
					} else {
						//
					}
				} else {
					date_string.text(empty_string).addClass("empty");
					date_parsed.text(empty_string).addClass("empty");
				}
			}
		);
		
		input.focus( 
			function (e) {
				if (input.val() === input_empty) {
					input.val("");
				}
			}
		);
		
		input.blur( 
			function (e) {
				if (input.val() === "") {
					input.val(input_empty).removeClass();
				}
			}
		);
	});
}());