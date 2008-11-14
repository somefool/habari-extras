<?php
class fun_with_photos_plugin extends Plugin
{
	
	/**
	 * Major Props to Ringmaster for volunteering the code for this plugin.
	 * 
	 */
	
	function info()	{		return array(
		'name' => 'Fun with Photos Plugin',
		'version' => '1',
		'url' => 'http://www.habari-fun.co.uk/fun-with-photos/',		'author' => 'Andrew Rickmann',		'authorurl' => 'http://www.habari-fun.co.uk',		'license' => 'Apache License 2.0',		'description' => 'Adds a photo field to the publish page',		);	}
	
	/**
	 * Add fields to the publish page for photos
	 *
	 * @param FormUI $form The publish form
	 * @param Post $post
	 * @return array
	 */
	public function action_form_publish( $form, $post )
	{
		if( $form->content_type->value == Post::type( 'entry' ) ) {
			$form->insert('content', 'text', 'photo', 'null:null', _t('Photo URL'));
			if(isset($post->info->photo)) {
				$form->photo->value = $post->info->photo;
			}
			$form->photo->template = 'admincontrol_text';
		}
	}
 
 
	/**
	 * Allow the media browser to insert photos into the photo field
	 *
	 * @param Theme $theme The current theme
	 */
	public function action_admin_header( $theme )
	{
		if( $theme->page == 'publish' ) {
			echo <<< PHOTO_JS
<script type="text/javascript">
$.extend(habari.media.output.image_jpeg, {
	use_as_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
});
$.extend(habari.media.output.image_png, {
	use_as_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
});
$.extend(habari.media.output.image_gif, {
	use_as_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
});
$.extend(habari.media.output.flickr, {
	use_as_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
});
function set_photo(fileindex, fileobj) {
	$('#photo').val(fileobj.url);
}
</script>
PHOTO_JS;
		}
	}
 
	/**
	 * Save the photo URL in the post's postinfo
	 *
	 * @param Post $post The post that is being saved
	 * @param FormUI $form The submitted publish form
	 */
	public function action_publish_post($post, $form)
	{
		if( $form->content_type->value == Post::type( 'entry' ) ) {
			if($form->photo->value != '') {
				$post->info->photo = $form->photo->value;
			}
		}
	}
 
 	
}
?>
