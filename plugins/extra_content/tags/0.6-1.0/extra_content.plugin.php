<?php

class extracontent extends Plugin
{
	public function info()
	{
		return array(
			'name' => 'Extra Content',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'The Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Display one additional textarea on the entry publish page.',
			'copyright' => '2010',
		);
	}

	public function help()
	{
		$help = _t( '<p>Activate, then create a new entry or edit an existing one to see the new textarea. Display it with <code>&lt;?php echo $post->info->extra; ?&gt;</code> wherever you have a Post object.</p><p>This can be formatted, also, as <code>&lt;?php echo Format::autop( $post->info->extra ); ?&gt;</code></p>', "extra_content" );
		return $help;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'Extra Content', '1b39ce6e-3576-49ca-aa45-326688720711', $this->info->version );
	}



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
	public function action_publish_post( $post, $form )
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
