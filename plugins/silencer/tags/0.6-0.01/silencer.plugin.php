<?php
class Silencer extends Plugin
{
	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'Silencer',
			'version' => '0.01',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Disables comments for all newly published posts',
			'copyright' => '2009'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Silencer', 'fdb7e18c-f883-4456-a55d-b8e5c8c9ffaa', $this->info->version );
	}

	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t( 'Once this is enabled, "Comments Allowed" will be deselected in the settings of all newly published posts and pages.' 		);
		return $help;
	}

	/**
	 **/
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			// for now, do nothing. Someday global postinfo changes may go here.
		}
	}

	/**
	 * Update the setting prior to displaying the form.
	 **/
	public function action_form_publish( $form, $post, $context )
	{
		$form->settings->comments_enabled->value = false;
	}

}

?>
