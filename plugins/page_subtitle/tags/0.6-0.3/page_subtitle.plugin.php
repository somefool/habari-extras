<?php
class PageSubtitle extends Plugin
{
	/**
	 * Required plugin information
	 **/
	function info() {
		return array(
			'name' => 'Page Subtitle',
			'version' => '0.3',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Allows a user to add a subtitle to pages.'
		);
	}

	/**
	 * Add the subtitle control to the publish page
	 *
	 * @param FormUI $form The publish page form
	 * @param Post $post The post being edited
	 **/
	public function action_form_publish ( $form, $post )
	{
		if ( $form->content_type->value == Post::type( 'page' ) ) {
			$subtitle = $form->settings->append( 'text', 'subtitle', 'null:null', _t( 'Subtitle: '), 'tabcontrol_text' );
			$subtitle->value = $post->info->subtitle;
			$subtitle->move_before( $form->settings->status );
		}
	}

	/**
	 * Handle update of subtitle
	 * @param Post $post The post being updated
	 * @param FormUI $form. The form from the publish page
	 **/
	public function action_publish_post( $post, $form )
	{
		if ( $post->content_type == Post::type( 'page' ) ) {
			$post->info->subtitle = $form->subtitle->value;
		}
	}

	/**
	 * Enable update notices to be sent using the Habari beacon
	 **/
	public function action_update_check()
	{
		Update::add( 'PageSubtitle', 'f5afa528-3b71-422f-bfd7-f361e3d54bda',  $this->info->version );
	}

}
?>
