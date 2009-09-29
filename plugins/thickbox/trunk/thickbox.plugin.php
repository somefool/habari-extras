<?php
class Thickbox extends Plugin {
		
	/**
	 * Adds needed files to the theme stacks (javascript and stylesheet)
	 */
	public function action_init()
	{
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', Site::get_url('user') . '/plugins/thickbox/thickbox-compressed.js', 'thickbox-js' );
		Stack::add( 'template_stylesheet', array( Site::get_url('user') . '/plugins/thickbox/thickbox.css', 'screen,projector'), 'thickbox-css' );
	}
	
	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t( 'There is no configuration for this plugin. Once this plugin is activated, add <code>class="thickbox"</code> to the link of an image file to open it in a hybrid modal box. More information and advanced instructions are available at the  <a href="http://jquery.com/demo/thickbox/" title="ThickBox 3.1 Documentation and Examples">jQuery Demo page</a>.' 
	);
		return $help;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Thickbox', '5e276e9e-c7ce-4010-a979-42580feefdd9', $this->info->version );
	}
}
?>
