<?php
/**
 * A plugin to use predefined and user-generated text bricks.
 *
 **/
class Bricks extends Plugin
{
	private $bricks_found;
	private $post;
	private $bricks = array();

	/**
	 * Set up some default options
	 *
	 **/
	public function action_plugin_activation($file)
	{
		
	}

	/**
	 * Create plugin configuration menu entry
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create plugin configuration
	 **/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()) {
			switch($action) {
				case _t('Configure'):
					$ui = new FormUI('postfields');
					$ui->append('static', 'typelabel', _t('Add bricks', array('keine ahnung','unsicher')));
					$ui->append('textmulti', 'bricks', 'bricks_bricks', 'Available bricks:');
					$ui->append('submit', 'submit', 'Submit');
					$ui->out();
			}
		}
	}
	
	// DEBUGGING CRAP
	// Displays the available bricks in the publish form
	// No functionality, only for viewing
	// Will be replaced by something better later
	/*
	public function action_form_publish($form, $post)
	{
		$bricks = Options::get('bricks_bricks');
		if(!is_array($bricks) || count($bricks) == 0) {
			return;
		}
		$output = '';
		$control_id = 0;
		$postbricks = $form->publish_controls->append('fieldset', 'postbricks', 'Available bricks');
		foreach($bricks as $brick)
		{
			//skip invalid bricks
			if(!strpos($brick,'=')) continue;
			list($bricktext, $brickvalue) = explode("=",$brick,2);
			$control_id = md5($bricktext);
			$fieldname = "postbrick_{$control_id}";
			$custombrick = $postbricks->append('text', $fieldname, 'null:null', $bricktext);
			$custombrick->value = $brickvalue;
			$custombrick->template = 'tabcontrol_text';
		}
	}*/

	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		// eeh? How is this used to display sourcecode?
		$help = _t( 'Usage: Enter brickname=brickvalue in one of the fields in the configuration. Brickname can contain a-Z, 0-9, underscores and dashes. Brickvalue can be anything. Be careful with html, Habari sometimes messes it up. In post content, use {brickname} and it will be replaced with brickvalue automatically when the post is displayed. {i} is a predefined brick that inserts your current Habari base path so you can create internal links using {i}slug.', 'bricks' );
		return $help;
	}

	/**
	 * Nothing to do here atm
	 **/
	public function action_init()
	{
		
	}

	/**
	 * This is where the stuff happens.
	 * All bricks in the post content will be replaced with their values.
	 **/
	public function filter_post_content( $content, $post )
	{
		// If we're on the publish page, replacement will be destructive.
		// We don't want that, so return here.
		$handler = Controller::get_handler();
		if ( isset( $handler->action ) && $handler->action == 'admin' && isset($handler->handler_vars['page']) && $handler->handler_vars['page'] == 'publish' ) {
			return $content;
		}
	
		// Get available bricks
		$bricklist = Options::get('bricks_bricks');
		foreach($bricklist as $brickentry)
		{
			//skip invalid bricks (bricks without value)
			if(!strpos($brickentry,'=')) continue;
			list($key, $value) = explode('=', $brickentry, 2);
			$this->bricks[$key] = $value;
		}
		
		// Add predefined bricks
		$this->bricks['i'] = Site::get_url('habari') . "/";
		
		if(!is_array($this->bricks) || count($this->bricks) == 0) {
			return $content;
		}

		$this->post = $post;
		
		// Scan for bricks and replace matches with their values
		$return = preg_replace_callback( '/({[a-zA-Z0-9\-_]+})/Us', array($this, 'brickit'), $content );

		if ( !$this->bricks_found ) {
			return $content;
		}

		return $return;
	} 
	
	// This is called for each found brick in the post
	// $matches contains the brick twice because it is found with a subexp in (), we will only use the direct match
	private function brickit( $matches )
	{
		// Get brickname without {}
		$brickname = substr($matches[1], 1, strlen($matches[1])-2);
		if(array_key_exists($brickname, $this->bricks))
		{
			$this->bricks_found = true;
			return $this->bricks[$brickname];
		}
		else return $matches[1];
	}

	// public function action_update_check () {
		// Update::add( 'bricks', '', $this->info->version );
	// }
}

?>
