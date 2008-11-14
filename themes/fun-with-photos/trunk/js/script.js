/**
 * @author Andrew Rickmann
 */
jQuery(document).ready(function($){
	
	//make sure it doesn't affect the home page
	
	if ( $('#post_details').length > 0 ){
	
		//get the show details link
		$('#post_details_link a').removeAttr('href').click(fwpbShowDetails);
		
		//get the show photos link
		$('#post_content_link a').removeAttr('href').click(fwpbShowPhoto);
	
	}
	
	function fwpbShowDetails(){
		
		//find wa bits what need hiding and hide them
		$('#post_content').hide();
		$('#post_details_link').hide();
		
		//find wa bits what need showing and show them
		$('#post_content_link').show();
		$('#post_details').show();
		
	}
	
	
	
	function fwpbShowPhoto(){
		
		//find wa bits what need hiding and hide them
		$('#post_content_link').hide();
		$('#post_details').hide();
		
		//find wa bits what need showing and show them
		$('#post_content').show();
		$('#post_details_link').show();
		
		
	}
	
});