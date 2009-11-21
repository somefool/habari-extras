(function($){
	$(function(){
		// Energize Breezy Archives
		$("#breezyarchives").addClass("energized");

		// Create spinner
		$(document.body).append('<div id="breezyarchives-indicator"></div>');
		var spinner = {
			start: function() {
				$("#breezyarchives-indicator").html('<img src="<?php echo Site::get_url( 'admin_theme' ); ?>/images/spin.gif">');
				$("#breezyarchives-indicator").show();
			},
			stop: function() {
				$("#breezyarchives-indicator").hide();
			}
		}
		var bapos = $("#breezyarchives").position();
		$("#breezyarchives-indicator").css({position:"absolute",top:bapos.top,left:bapos.left+$("#breezyarchives").width()-32})

		// Do this when click Next/Previous
		function pagination(){
			var ul = $(this).parent().parent();
			$.ajax({
				url: $(this).attr("href"),
				beforeSend: function(){
					spinner.start();
				},
				success: function(response){
					ul.replaceWith(response);
				},
				complete: function(){
					spinner.stop();
					$("#breezyarchives li.pagination a").click(pagination);
				}
			});
			return false;
		}

		$("#breezyarchives li.type > h3,#breezy-chronology-archive li.year > a,#breezy-chronology-archive li.month > a,#breezy-taxonomy-archive li.tag > a").click(function(){
			$(this).parent().siblings().removeClass("selected").find("ul:first").hide();
			$(this).parent().addClass("selected").find("ul:first").show();
			return false;
		});

		$("#breezy-chronology-archive li.month > a,#breezy-taxonomy-archive li.tag > a").one("click", function(){
			var link = $(this);
			$.ajax({
				url: link.attr("href").replace("<?php echo $habari_url; ?>", "<?php echo $habari_url . $class_name; ?>/"),
				beforeSend: function(){
					spinner.start();
				},
				success: function(response){
					link.parent().append(response);
				},
				complete: function(){
					spinner.stop();
					$("#breezyarchives li.pagination a").click(pagination);
				}
			});
		});
		$("#breezyarchives li.type > h3:first,#breezy-chronology-archive li.year > a:first,#breezy-chronology-archive li.month > a:first").click();
	});
})(jQuery);
