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

$(document).ready(function() {

	if($('#magicArchives').length != 0) {
		magicArchives.init();
	}
	
});