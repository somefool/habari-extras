<?php
class PageSubtitle extends Plugin
{
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
}
?>
