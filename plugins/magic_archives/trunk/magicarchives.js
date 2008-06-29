var magicArchives = {
	archives: null,
	month: null,
	year: null,
	tag: null,
	content_type: null,
	posts: null,
	init: function() {
		magicArchives.archives = $('#magicArchives');
		magicArchives.month = $('#archiveControls .month ol', magicArchives.archives);
		magicArchives.year = $('#archiveControls .year ol', magicArchives.archives);
		magicArchives.tag = $('#archiveControls .tags ol', magicArchives.archives);
		magicArchives.content_type = $('#archiveControls .type ol', magicArchives.archives);
		magicArchives.posts = $('ol#archiveItems li:not(.headings)', magicArchives.archives);
		magicArchives.search = $('#archiveSearch', magicArchives.archives);
		
		magicArchives.posts.each(function() {
			$(this).addClass('searched').addClass('filtered');
		});
		
		magicArchives.search.keyup(function() {
			magicArchives.filter();
		});
		
		magicArchives.createFilters();
	},
	createFilters: function() {
		$('li:not(.allofthestuff)', $('#archiveControls')).hide().addClass('hidden');
		
		magicArchives.posts.each(function() {
			var month = $('.month', $(this)).text().toLowerCase();
			var year = $('.year', $(this)).text();
			var type = $('.type', $(this)).text();
			var tags = $('.tags .tag', $(this));
			
			tags.each(function() {
				$('li.' + $(this).text(), magicArchives.tag).show().removeClass('hidden');
			});
			
			$('li.' + month, magicArchives.month).show().removeClass('hidden');
			$('li.y' + year, magicArchives.year).show().removeClass('hidden');
			$('li.' + type, magicArchives.content_type).show().removeClass('hidden');
		});
		
		$('li.allofthestuff span',  magicArchives.month).text($('li:not(.hidden):not(.allofthestuff)', magicArchives.month).length);
		$('li.allofthestuff span',  magicArchives.year).text($('li:not(.hidden):not(.allofthestuff)', magicArchives.year).length);
		$('li.allofthestuff span',  magicArchives.tag).text($('li:not(.hidden):not(.allofthestuff)', magicArchives.tag).length);
		$('li.allofthestuff span',  magicArchives.content_type).text($('li:not(.hidden):not(.allofthestuff)', magicArchives.content_type).length);
		
		$('li', $('#archiveControls')).click(function() {
			$('li', $(this).parent()).removeClass('active');
			$(this).addClass('active');
			magicArchives.filter();
		});
	},
	filter: function() {
		var month = $('.active', magicArchives.month);
		var year = $('.active', magicArchives.year);
		var type = $('.active', magicArchives.content_type);
		var tag = $('.active', magicArchives.tag);
		
		magicArchives.posts.show();
		
		var i = 0;
		
		magicArchives.posts.each(function() {
			if(month.hasClass('allofthestuff') == false) {
				if(month.text() != $('.month', $(this)).text()) {
					$(this).hide();
				}
			}
			if(year.hasClass('allofthestuff') == false) {
				if(year.text() != $('.year', $(this)).text()) {
					$(this).hide();
				}
			}
			if(type.hasClass('allofthestuff') == false) {
				if(type.text() != $('.type', $(this)).text()) {
					$(this).hide();
				}
			}
			
			if(tag.hasClass('allofthestuff') == false) {
				hide = true;
				
				$('.tags .tag', $(this)).each(function() {
					if(tag.text() == $(this).text()) {
						hide = false;
					}
				});
				
				if(hide) {
					$(this).hide();
				}
			}
			
			var score = 0;
			
			$('.tags .tag', $(this)).each(function() {
				score = score + $(this).text().score(magicArchives.search.val());
			});
			
			score = score + $('.month', $(this)).text().toLowerCase().score(magicArchives.search.val());
			score = score + $('.title', $(this)).text().toLowerCase().score(magicArchives.search.val());
			score = score + $('.year', $(this)).text().toLowerCase().score(magicArchives.search.val());
						
			if(score == 0) {
				$(this).hide();
			}
		});
		
		
	}
};

$(document).ready(function() {

	if($('#magicArchives').length != 0) {
		magicArchives.init();
	}
	
});