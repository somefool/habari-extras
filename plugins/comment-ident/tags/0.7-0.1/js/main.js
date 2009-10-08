/**
 * @author Simon
 */

var comment_ident = function($) {
	
	var $dialog;
	
	return {
		render : function (e) {
			$dialog.html('');
		    if(ident.identities.length > 0){
		         var ul = $('<ul class="profile-list"></ul>'); 
		         for (var x = 0; x < ident.identities.length; x++) {
		            if(ident.identities[x].name != '')
		                $('<li> \
		                    <a href="'+ ident.identities[x].profileUrl  + '">\
		                    <img width="16" class="icon" src="' + ident.identities[x].iconUrl + '" />' 
		                    + ident.identities[x].name + '</a>\
		                    </li>').appendTo(ul);   
		            else
		                $('<li>\
		                <a href="' + ident.identities[x].profileUrl  + '">\
		                <img width="16" class="icon" src="' + ident.identities[x].iconUrl +  '" />'
		                 + ident.identities[x].domain + '</a></li>').appendTo(ul); 
		         }
				 ul.appendTo($dialog);
				 $dialog.dialog('open');
				 return false;
		    }
			return true;
		},
		
		init : function () {
			$(function() {
				$(document).bind('ident:update', comment_ident.render);
				$('a.ident').click(function(){
					var url = $(this).attr('href');
					var result =  ident.search(url);
					return false;
				});
				$dialog = $('<div>').dialog({dialogClass : 'comment-ident', autoOpen : false, title : "Commenter's Profiles", open: function(event, ui) {
						$(document).trigger('dialogopen');
					}
				});
			});
		}
	}
	
}(jQuery);


comment_ident.init();