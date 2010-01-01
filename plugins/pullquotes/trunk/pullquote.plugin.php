<?php

/**
 * Pullquote Plugin Class
 *
 **/

class Pullquote extends Plugin
{
	/**
	 * Return information about this plugin
	 * @return array Plugin info array
	 **/
	
	
	public function action_init()
	{
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', $this->get_url() . '/pullquote.js', 'pullquote-js', 'jquery' );
		Stack::add( 'template_stylesheet', array( $this->get_url() . '/pullquote.css', 'screen,projector'), 'pullquote-css' );
	}
	
	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t( 'Pull quotes does not require any configuration.  Simply wrap the the desired content in a span of either pquote-l or pquote-r (l and r for floated left and right respectively).  The quotes are styled with the included pullquotes.css.  To customize the CSS, make sure your theme calls <code>$theme->header();</code> before the call for the style sheet.  You can then add span.pull-left and span.pull-right to your theme\'s style sheet to override the default style.');
		
		return $help;
	}
}
?>