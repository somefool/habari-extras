<?php

class PhotoSlide extends Plugin
{
	public function action_plugin_activation( $plugin_file )
	{
		Post::add_new_type( 'photo' );
	}

	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'photo' );
	}
	

	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'photo' => array(
				'singular' => _t( 'Photo', 'photoslide' ),
				'plural' => _t( 'Photos', 'photoslide' ),
			)
		); 
		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	public function action_form_publish( $form, $post )
	{
		if( $form->content_type->value == Post::type( 'photo' ) ) {
			
			$src = '';
			if($post->content != '') {
				$asset = Media::get($post->content);
				$src = $asset->thumbnail_url;
			}

			$imagepreview = $form->insert('content', 'static', 'imagepreview', '');
			$imagepreview->caption = <<< CAPTION_SCRIPT
<div class="container transparent">
	<fieldset>
		<legend>Preview</legend>
		<img id="imagepreview" src="{$src}" style="width:100px;">
	</fieldset>
</div>
<script type="text/javascript">
$(function(){
	$.extend(habari.media.output.image_jpeg, {
		insert_image: function(fileindex, fileobj) {
			$('#content').val(fileobj.path);
			$('#imagepreview').attr('src', fileobj.thumbnail_url);
		}
	});
	$.extend(habari.media.output.flickr, {
		embed_photo: function(fileindex, fileobj) {
			$('#content').val(fileobj.path);
			$('#imagepreview').attr('src', fileobj.url);
			console.log(fileindex, fileobj);
		}
	});
});
</script>
CAPTION_SCRIPT;
						
			// Re-create the Content field
			//$form->content->remove();
			$content = $form->append( 'hidden', 'content', 'null:null' );
			//$content = $form->append( 'text', 'content', 'null:null', _t( 'Content' ), 'admincontrol_text' );
			$content->value = $post->content;
			$content->raw = true;
			$content->id = 'content';
			
//			$postfields = $form->publish_controls->append( 'fieldset', 'enclosures', _t( 'Podcasting', 'podcast'  ) );
		}
	}
	
	public function filter_post_content_media($content, $post)
	{
		return '<img src="' . Media::get($content)->url . '">';
	}

	public function filter_post_content_mediaurl($content, $post)
	{
		return Media::get($content)->url;
	}
	
	
}


?>