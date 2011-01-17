<?php

/**
 * Create a block with arbitrary content.
 *
 */
class PrettyPhoto extends Plugin
{
	public function action_template_header($theme)
	{
		Plugins::act('add_prettyphoto_template');
	}
	
	public function action_add_prettyphoto_template()
	{
		Stack::add('template_stylesheet', array($this->get_url() . '/css/prettyPhoto.css', 'screen'));
		Stack::add('template_header_javascript', $this->get_url() . '/js/jquery.prettyPhoto.js', 'prettyphoto', 'jquery');
	}

	public function action_add_prettyphoto_admin()
	{
		Stack::add('admin_stylesheet', array($this->get_url() . '/css/prettyPhoto.css', 'screen'));
		Stack::add('admin_header_javascript', $this->get_url() . '/js/jquery.prettyPhoto.js', 'prettyphoto', 'jquery');
	}
}

?>
