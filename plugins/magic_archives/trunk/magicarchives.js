var magicArchives = {
	search: null,
	init: function() {
		magicArchives.archives = $('#magicArchives');
		magicArchives.controller= $('#archive_controller', magicArchives.archives);
		magicArchives.searcher= $('.section.search input', magicArchives.archives);
		magicArchives.posts= $('#archive_posts', magicArchives.archives);
		
		// Toggle tags
		$('a#archive_toggle_tags', magicArchives.controller).click(function() {
			magicArchives.toggle($('#archive_tags'));
			return false;
		});
		
		// Set up our AJAX
		magicArchives.ajax = $.manageAjax({manageType: 'abortOld', maxReq: 1}); 
		
		// Set up the search label
		magicArchives.searcher
			.val($('.section.search label').text())
			.focus(function() {
				if(magicArchives.searcher.val() == $('.section.search label').text()) {
					magicArchives.searcher.val('');
				}
			})
			.blur(function() {
				if(magicArchives.searcher.val() == '') {
					magicArchives.searcher.val($('.section.search label').text());
				}
			});
		
		
		// Update on search
		magicArchives.searcher.keyup(function() {
			if(magicArchives.searcher.val() != magicArchives.search) {
				magicArchives.fetch();
			}
		});
		
		tagManage.init();
	},
	fetch: function() {
		magicArchives.search= magicArchives.searcher.val();
		
		params= {
			search: magicArchives.search
		}
		
		magicSpinner.start();
		
		magicArchives.ajax.add({ 
			type: "GET",
			url: magicArchives.endpoint,
			data: params,
			success: function(response){
				magicSpinner.stop();
				magicArchives.posts.html(response);
			}
		});
	 
	},
	toggle: function(section) {
		if(section.hasClass('open')) {
			section.slideUp();
			section.removeClass('open');
		} else {
			section.slideDown();
			section.addClass('open');
		}
	}
};

var magicSpinner = {
	start: function() {
		magicArchives.archives.addClass('spinner');
	},
	stop: function () {
		magicArchives.archives.removeClass('spinner');
	}
}

var tagManage = {
	init: function() {
		
		tagManage.initItems();
		
		$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls .clear').click(function() {
			tagManage.uncheckAll();
			return false;
		});
		
		$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls input[type=checkbox]').change(function () {
			if($('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls label.selectedtext').hasClass('all')) {
				tagManage.uncheckAll();
			} else {
				tagManage.checkAll();
			}
		});
		
	},
	initItems: function() {
		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.ignore) .checkbox input[type=checkbox]').change(function () {
			tagManage.changeItem();
		});
		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.ignore) .checkbox input[type=checkbox]').each(function() {
			id = $(this).attr('id');
			id = id.replace(/.*\[(.*)\]/, "$1" ); // checkbox ids have the form name[id]
			if(tagManage.selected['p' + id] == 1) {
				this.checked = 1;
			}
		});
		tagManage.changeItem();
	},
	selected: [],
	searchCache: [],
	searchRows: [],
	changeItem: function() {
		var selected = {};

		if(tagManage.selected.length != 0) {
			selected = tagManage.selected;
		}

		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.ignore) .checkbox input[type=checkbox]:checked').each(function() {
			check= $(this);
			id = check.attr('id');
			id = id.replace(/.*\[(.*)\]/, "$1" );
			selected['p' + id] = 1;
			check.parent().parent().addClass('selected');
		});
		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.ignore) .checkbox input[type=checkbox]:not(:checked)').each(function() {
			check= $(this);
			id = check.attr('id');
			id = id.replace(/.*\[(.*)\]/, "$1" );
			selected['p' + id] = 0;
			check.parent().parent().removeClass('selected');
		});

		tagManage.selected = selected;

		visible = $('#magicArchives #archive_controls .section#archive_tags .tag:not(.hidden):not(.ignore) .checkbox input[type=checkbox]:checked').length;
				
		count = 0;
		for (var id in tagManage.selected)	{
			if(tagManage.selected[id] == 1) {
				count = count + 1;
			}
		}
				
		if(count == 0) {
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls input[type=checkbox]').each(function() {
				this.checked = 0;
			});
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls label.selectedtext').addClass('none').removeClass('all').text('None selected');
		} else if(count == $('#magicArchives #archive_controls .section#archive_tags .tag:not(.hidden):not(.ignore) .checkbox input[type=checkbox]').length) {
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls input[type=checkbox]').each(function() {
				this.checked = 1;
			});
			
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls label.selectedtext').removeClass('none').addClass('all').html('All ' + count + ' selected');
		} else {
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls input[type=checkbox]').each(function() {
				this.checked = 0;
			});
			$('#magicArchives #archive_controls .section#archive_tags #archive_tags_controls label.selectedtext').removeClass('none').removeClass('all').text(count + ' selected');
			
		}
	},
	uncheckAll: function() {
		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.hidden):not(.ignore) .checkbox input[type=checkbox]').each(function() {
			this.checked = 0;
		});
		tagManage.selected = [];
		tagManage.changeItem();
	},
	checkAll: function() {
		$('#magicArchives #archive_controls .section#archive_tags .tag:not(.hidden):not(.ignore) .checkbox input[type=checkbox]').each(function() {
			this.checked = 1;
		});
		tagManage.changeItem();
	}
}


$(document).ready(function() {

	if($('#magicArchives').length != 0) {
		magicArchives.init();
	}
	
});