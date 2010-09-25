<?php
/*
 * HTMLSidebar Plugin
 * Usage: <?php $theme->show_HTMLSidebar(); ?>
 * A simple plugin to include HTML code directly in your theme sidebar.
 * @todo: add a new block type
 */

class HTMLSidebar extends Plugin
{
	
	function theme_sidebar()
	{
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
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
	  if ( $plugin_id == $this->plugin_id() ) {
		$actions[] = _t( 'Configure' );
	  }
	  return $actions;
	}
	
	public function formui_submit( FormUI $form )
	{
		Session::notice( _t( 'Sidebar HTML code saved', 'htmlsidebar' ) );
		$form->save();
	}
	
	function action_update_check() 
	{
	  Update::add( 'Download Plugin', '2f88a495-f56b-4f45-9d76-909a653134e0', $this->info->version ); 
	}

}
?>
