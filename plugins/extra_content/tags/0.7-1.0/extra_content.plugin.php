<?php

class extracontent extends Plugin
{
	/**
	* Add additional controls to the publish page tab
	*
	* @param FormUI $form The form that is used on the publish page
	* @param Post $post The post being edited
	**/
	public function action_form_publish( $form, $post )
	{
		switch( $post->content_type ) {
			case Post::type( 'entry' ):
				$extra = $form->append( 'textarea', 'extra_textarea', 'null:null', _t( 'Extra', 'extra_content' ) );
				$extra->value = $post->info->extra;
				$extra->class[] = 'resizable';
				$extra->rows = 3;
				$extra->template = 'admincontrol_textarea';
				$form->move_after($form->extra_textarea, $form->content);
				break;

			default:
				return;
		}
	}

	/**
	* Modify a post before it is updated
	*
	* @param Post $post The post being saved, by reference
	* @param FormUI $form The form that was submitted on the publish page
	*/
	public function action_publish_post($post, $form)
	{
		switch( $post->content_type ) {
			case Post::type( 'entry' ):
			
				$post->info->extra = $form->extra_textarea->value;
				break;
			default:
				return;
		}
	}

}
?>
