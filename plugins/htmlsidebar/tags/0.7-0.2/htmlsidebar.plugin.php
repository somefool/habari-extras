<?php
/*
 * HTMLSidebar Plugin
 * Usage: <?php $theme->sidebar(); ?>
 * A simple plugin to include HTML code directly in your theme sidebar.
 * 
 */

class HTMLSidebar extends Plugin
{
	/*Create empty option in the DB*/
	public function action_plugin_activation( $file )
	{
		Options::set( 'htmlsidebar' );
	}
	
	/*Clean DB stored htmlcode*/
	public function action_plugin_deactivation( $file )
	{
		Options::delete( 'htmlsidebar' );
	}

	/*Return stored html code when theme call $theme->sidebar()*/
	function theme_sidebar()
	{
		//returned text is auto escaped during theme render
		return Options::get( 'htmlsidebar' );
	}

		
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					  $ui = new FormUI( strtolower( get_class( $this ) ) );
					  $ui->append( 'textarea', 'htmlsidebar', 'option:htmlsidebar', _t( 'Your code:', 'htmlsidebar' ) );
					  $ui->append( 'submit', 'savecode', _t( 'Save code', 'htmlsidebar' ) );
					  $ui->on_success( array($this, 'formui_submit') );
					  $ui->out();
			}
		}
	}

	/*Action available for this plugin*/
	public function filter_plugin_config( $actions, $plugin_id )
	{
	  if ( $plugin_id == $this->plugin_id() ) {
		$actions[] = _t( 'Configure' );
	  }
	  return $actions;
	}

	/*Save button was pressed*/
	public function formui_submit( FormUI $form )
	{
		Session::notice( _t( 'Sidebar HTML code saved', 'htmlsidebar' ) );
		$form->save();
	}

	/*Check for updates*/
	function action_update_check() 
	{
	  Update::add( 'HTML Sidebar', '2f88a495-f56b-4f45-9d76-909a653134e0', $this->info->version ); 
	}

}
?>
