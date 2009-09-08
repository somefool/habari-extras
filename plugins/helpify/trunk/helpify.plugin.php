<?php

class Helpify extends Plugin
{
	
	public function action_update_check()
	{
		Update::add( $this->info->name, 'a1477d5c-dc2d-42dd-91e8-d341723466b7', $this->info->version );
	}
	
	/**
	 * Add template
	 **/
	public function action_init()
	{
		$this->add_template( 'help', dirname(__FILE__) . '/help.php' );
	}
	
	/**
	 * Add media files 
	 **/
	public function action_admin_header()
	{
		Stack::add('admin_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/helpify.css', 'screen'), 'helpify');
		Stack::add( 'admin_header_javascript', URL::get_from_filesystem(__FILE__) . '/helpify.js', 'helpify', array('jquery', 'jquery.hotkeys') );
	}
	
	/**
	 * Create plugin configuration
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create configuration panel
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
				
					$form = new FormUI( strtolower( get_class( $this ) ) );

					$form->append( 'textarea', 'help', strtolower( get_class( $this ) ) . '__help', _t('Help') );
					$form->help->raw = true;

					$form->append( 'submit', 'save', _t('Save') );
					$form->out();
					
					break;
			}
		}
	}
	
	/**
	 * Add the help to the publish form
	 */
	public function action_form_publish($form, $post) {
		$selector = $form->append('wrapper', 'help_container');
		$selector->class = 'container';
				
		$theme = Themes::create();
		$theme->help = Options::get( strtolower( get_class( $this ) ) . '__help' );
		
		$content = $theme->fetch( 'help' );
		
		
		$selector->append( 'static', 'help', $content );
				
		
		$form->move_after($selector, $form->silos);
		
		return $form;
	}
	
	
}

?>