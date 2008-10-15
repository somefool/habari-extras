<?php
/*
* Colophon Habari Plugin
* This plugin allows the blog owner to include an about/colophon somewhere on the blog
* without having to rely on a page or theme rewrite, since the about option got killed
*
* Example usage in PHP template:
*
* <?php if (Plugins::is_loaded('Colophon Plugin')) { ?>
* 	<h2><?php $theme->colophon_title; ?></h2>
* 	<?php $theme->colophon; ?>
* <?php } ?>
*
*/

class Colophon extends Plugin
{
	const VERSION = '0.4.1';

	/**
	* Required plugin information
	* @return array The array of information
	*/
	function info()
	{
		return array(
			'name'		=>	'Colophon Plugin',
			'version'	=>	self::VERSION,
			'url'		=>	'http://habariproject.org/dist/plugins',
			'author'	=>	'stanislas mazurek',
			'authorurl'	=>	'http://stanbar.jp',
			'licence'	=>	'Apache licence 2.0',
			'description'	=>	'Adds an About / Colophon to your blog'
		);
	}

	/**
	* Add actions to the plugin page for this plugin
	* @param array $actions An array of actions to apply to this plugin
	* @param string $plugin_id The string with the plugin id, generated by the system
	* @return array $actions Array of actions to atach to the specified $plugin_id
	*/
	public function  filter_plugin_config($actions,$plugin_id)
	{
		if( $plugin_id == $this->plugin_id()) {
			$actions[]	=	_t('Configure');
		}
		return $actions;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Colophon', 'c3eaec26-0bde-44db-b1f6-baaa8aedefea', $this->info->version );
	}

	/**
	* Method that responds to the user selecting an action on the plugin page
	* @param string $plugin_id String containning the id of the plugin
	* @param string $action The action string suplied via the filter_plugin_config hook
	**/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch( $action ) {
				case _t('Configure'):
					$ui	=	new FormUI ( strtolower( get_class( $this ) ) );
					$ui->append( 'text', 'title', 'colophon__title', _t('Enter your Title:') );
					$ui->append( 'textarea', 'text', 'colophon__text', _t('Enter your Text:') );
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->out();
				break;
			}
		}

	}

	/**
	* Assigns output code to the template variables
	* @param Theme $theme The theme that will display the template
	*/
	function action_add_template_vars( $theme )
	{
		$theme->colophon = Format::autop( Options::get( 'colophon__text' ) );
		$theme->colophon_title = Options::get( 'colophon__title' );
	}

}
?>
