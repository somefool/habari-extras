commentPreview= {
	init: function() {
		commentPreview.preview= $('#comment-preview');
		commentPreview.form= $('#commentform');
		commentPreview.fields= $('input[type=text], textarea', commentPreview.form);
		commentPreview.time= 300;
		
		if(commentPreview.preview.length == 0) return;
		
		commentPreview.fields.keyup(function() {
			commentPreview.test();
		});
		
		commentPreview.preview.hide();
		
	},
	test: function() {
		show= false;
				
		commentPreview.fields.each(function() {
			el= $(this);
			
			holder= $('.' + el.attr('id') + 'holder');
			container= $('.' + el.attr('id') + 'container');
						
			if(el.val() != '') {
				
				holder.html($(this).val());
				container.slideDown(commentPreview.time / 2);
				
				if(holder.hasClass('urlholder')) {
					holder.attr('href', $(this).val())
				}
				
				show= true;
			} else {
				container.slideUp(commentPreview.time / 2);
			}
		});
		
		if(show) {
			commentPreview.preview.slideDown(commentPreview.time);
		} else {
			commentPreview.preview.slideUp(commentPreview.time);
		}
		
		return;
	}
}

$(document).ready(function() {
	commentPreview.init();
});