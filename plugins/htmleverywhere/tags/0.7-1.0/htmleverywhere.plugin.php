<?php
/*
 * HTMLEverywhere Plugin
 * Usage: 
 * <?php echo $html_sidebar; ?>
 * <?php echo $html_footer; ?>
 * <?php echo $html_header(); ?>
 * A simple plugin to include HTML code directly in your theme header,sidebar and footer.
 * 
 */

class HTMLEverywhere extends Plugin
{
	/*Create empty options in the DB*/
	public function action_plugin_activation( $file )
	{
	  Options::set( 'htmleverywhere_header' );
	  Options::set( 'htmleverywhere_sidebar' );
	  Options::set( 'htmleverywhere_footer' );
	}
	
	/*Delete stored options in the DB*/
	public function action_plugin_deactivation( $file )
	{
		Options::delete( 'htmleverywhere_header' );
		Options::delete( 'htmleverywhere_sidebar' );
		Options::delete( 'htmleverywhere_footer' );
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					  $ui = new FormUI( strtolower( get_class( $this ) ) );
					  
					  $head = $ui->append( 'textarea', 'htmlheader', 'option:htmleverywhere_header', _t( 'Your header code:', 'htmleverywhere' ) );
					  $head->raw = true;
					  
					  $side = $ui->append( 'textarea', 'htmlsidebar', 'option:htmleverywhere_sidebar', _t( 'Your sidebar code:', 'htmleverywhere' ) );
					  $side->raw = true;
					  
					  $foot = $ui->append( 'textarea', 'htmlfooter', 'option:htmleverywhere_footer', _t( 'Your footer code:', 'htmleverywhere' ) );
					  $foot->raw = true;
					  
					  $ui->append( 'submit', 'savecode', _t( 'Save code', 'htmleverywhere' ) );
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
		Session::notice( _t( 'HTML code saved', 'htmleverywhere' ) );
		$form->save();
	}
	
	function action_add_template_vars( $theme )
	{
	  $theme->html_header = Options::get( 'htmleverywhere_header' );
	  $theme->html_sidebar = Options::get( 'htmleverywhere_sidebar' );
	  $theme->html_footer = Options::get( 'htmleverywhere_footer' );
	}

	/*Check for updates*/
	function action_update_check() 
	{
	  Update::add( 'HTML everywhere', '84226c4b-793e-4c11-9873-4e11fe8e8dd5', $this->info->version ); 
	}

}
?>
