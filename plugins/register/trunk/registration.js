// code for making inline labels which then move above form inputs when the inputs have content
var labeler = {
	focus: null,
	init: function() {
		$('label').each( function() {
			labeler.check(this);

			// focus on the input when clicking on the label
			$(this).click(function() {
				$('#' + $(this).attr('for')).focus();
			});
		});

		$('.islabeled').focus( function() {
			labeler.focus = $(this);
			labeler.aboveLabel($(this));
		}).blur(function(){
			labeler.focus = null;
			labeler.check($('label[for='+$(this).attr('id')+']'));
		});
	},
	check: function(label) {
		var target = $('#' + $(label).attr('for'));

		if ( !target ) {return;}

		if ( labeler.focus !== null && labeler.focus.attr('id') == target.attr('id') ) {
			labeler.aboveLabel(target);
		}
		else if ( target.val() === '' ) {
			labeler.overLabel(target);
		}
		else {
			labeler.aboveLabel(target);
		}
	},
	aboveLabel: function(el) {
		$(el).addClass('islabeled');
		$('label[for=' + $(el).attr('id') + ']').removeClass('overcontent').removeClass('hidden').addClass('abovecontent');
	},
	overLabel: function(el) {
		$(el).addClass('islabeled');
		// for Safari only, we can simply hide labels when we have provided a
		// placeholder attribute
		if ($.browser.safari && $(el).attr('placeholder') ) {
			$('label[for=' + $(el).attr('id') + ']').addClass('hidden');
		}
		else {
			$('label[for=' + $(el).attr('id') + ']').addClass('overcontent').removeClass('abovecontent');
		}
	}
};

$(document).ready(function() {
	labeler.init();
});
