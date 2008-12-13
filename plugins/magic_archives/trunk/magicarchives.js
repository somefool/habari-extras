var magicArchives = {
	archives: null,
	sections: null,
	init: function() {
		magicArchives.archives = $('#magicArchives');
		magicArchives.labels = $('#archive_controlbar .section', magicArchives.archives);
		magicArchives.sections = $('#archive_controls .section', magicArchives.archives);
		
		// console.log(magicArchives.sections);
		
		$('a.toggle', magicArchives.labels).click(function() {
			magicArchives.toggle(magicArchives.labels.index($(this).parent()));
			return false;
		});
		
		$('.controls .close', magicArchives.sections).click(function() {
			magicArchives.toggle(magicArchives.sections.index($(this).parent().parent()));
			return false;
		});
		
		tagManage.init();
	},
	toggle: function(index) {
		section= magicArchives.sections.eq(index);
		label= magicArchives.labels.eq(index);
		if(section.hasClass('open')) {
			section.slideUp();
			section.add(label).removeClass('open');
		} else {
			section.slideDown();
			section.add(label).addClass('open');
		}
	}
};

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