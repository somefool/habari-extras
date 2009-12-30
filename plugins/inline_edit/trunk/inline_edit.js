// Inline edit
var inEdit = {
	init: function() {
		inEdit.editables = '.date a.edit-date, .title a.author.edit-author, .authorinfo a.edit-url, .authorinfo a.edit-email, .time span.edit-time, .content.edit-content';

		if ($('#comments').length === 0) { // Only works for comments, presently
			return;
		}

		$(inEdit.editables, $('.item')).filter(':not(a)').each(function() {
			$(this).addClass('editable');
			$(this).click(function() {
				if (inEdit.activated != $(this).parents('.item').attr('id').substring(8)) {
					inEdit.deactivate();
					inEdit.activate($(this).parents('.item'));
				}
				return false;
			});
		});
	},
	activated: false,
	editables: null,
	getDestination: function( classes ) {
		classes = classes.split(" ");

		var clas = null;

		for (var key in classes) {
			if (classes.hasOwnProperty(key)) {
				clas = classes[key];
				if (clas.search('edit-') != -1) {
					destination = clas.substring(clas.search('edit-') + 5);
					return destination;
				}
			}
		}

		return false;

	},
	activate: function( parent ) {
		$(parent).hide().addClass('ignore');

		parent = $(parent).clone().addClass('clone').removeClass('ignore').show().insertAfter(parent);

		editables = $(inEdit.editables, parent);

		inEdit.activated = $(parent).attr('id').substring(8);
		var form = $('<form action="#"></form>').addClass('inEdit');
		parent.wrap(form);

		editables.each(function() {
			var classes = $(this).attr('class');
			destination = inEdit.getDestination(classes);
			var val = $(this).text();
			var width = $(this).width();
			var field;

			$(this).hide();

			if ($(this).hasClass('area')) {
				field = $('<textarea></textarea>');
				field.height(100)
					.attr('class', classes)
					.removeClass('pct75')
					.width(width - 13);
			} else {
				field = $('<input></input>');
				field.attr('class', classes)
					.width(width + 5);
			}
			field.addClass('editor').removeClass('editable')
				.val(val)
				.insertAfter($(this));
		});

		$('ul.dropbutton li:not(.cancel):not(.submit)', parent).remove();
		$('ul.dropbutton li.cancel, ul.dropbutton li.submit', parent).removeClass('nodisplay');
		$('ul.dropbutton li.submit', parent).addClass('first-child');
		$('ul.dropbutton li.cancel', parent).addClass('last-child');
		dropButton.init();

		dropButton.init();
		var submit = $('<input type="submit"></input>')
						.addClass('inEditSubmit')
						.val('Update')
						.hide()
						.appendTo(parent);

		$("form").submit(function() {
			inEdit.update();
			return false;
		});

		itemManage.initItems();
		itemManage.changeItem();

	},
	update: function() {
		spinner.start();

		query = {};

		$('.editor').each(function() {
			query[inEdit.getDestination($(this).attr('class'))]= $(this).val();
		});

		query.id = inEdit.activated;
		query.timestamp = $('input#timestamp').attr('value');
		query.nonce = $('input#nonce').attr('value');
		query.digest = $('input#PasswordDigest').attr('value');

		$.ajax({
			type: 'POST',
				url: habari.url.ajaxInEdit,
				data: query,
				dataType: 'json',
				success: function( result ){
					spinner.stop();
					jQuery.each( result, function( index, value) {
						humanMsg.displayMsg( value );
					} );
					inEdit.deactivate();

					loupeInfo = timeline.getLoupeInfo();
					itemManage.fetch( loupeInfo.offset, loupeInfo.limit, false );
				}
		});

	},
	deactivate: function() {
		inEdit.activated = false;

		$('.item').show().removeClass('ignore');
		$('form.inEdit').remove();

		itemManage.changeItem();

	}
};

$(document).ready(function(){
	$('a.author').addClass('edit-author');
	$('a.url').addClass('edit-url');
	$('a.email').addClass('edit-email');
	$('span.content').addClass('edit-content area');

	itemManage.inEdit = true;
	inEdit.init();
});
