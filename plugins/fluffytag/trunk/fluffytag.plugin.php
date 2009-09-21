<?php

class fluffytag extends Plugin
{
	private $cache_name = 'fluffytag';

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'FluffyTag', 'F1AC0F36-246D-11DE-B61E-0D8056D89593', $this->info->version );
	}

	/**
	 * Makes sure everything is setup proper.
	 **/
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			if ( Options::get( 'fluffytag__num' ) == null ) {
				Options::set( 'fluffytag__num', 'true' );
			}
			if ( Options::get( 'fluffytag__prefix' ) == null ) {
				Options::set( 'fluffytag__prefix', '@' );
			}  
			if ( Options::get( 'fluffytag__steps' ) == null ) {
				Options::set( 'fluffytag__steps', '10' );
			}           
			if ( Options::get( 'fluffytag__expire' ) == null ) {
				Options::set( 'fluffytag__expire', '36000000' ); // We're updating the cache everytime a post change...
            }
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Cache::expire( $this->cache_name );
		}
	}

	/**
	 * Plugin init action, executed when plugins are initialized.
	 */
	public function action_init()
	{
		$this->add_template( 'fluffytag', dirname(__FILE__) . '/fluffytag.php' );
	}

	/**
	* Add actions to the plugin page for this plugin
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	**/
	public function filter_plugin_config( $actions, $plugin_id )
	{
	if ( $plugin_id == $this->plugin_id() ) {
		$actions[] = 'Configure';
	}

		return $actions;
	}
	
	/**
	 * Executes when the admin plugins page wants to display the UI for a particular plugin action.
	 * Displays the plugin's UI.
	 *
	 * @param string $plugin_id The unique id of a plugin
	 * @param string $action The action to display
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		// Display the UI for this plugin?
		if ( $plugin_id == $this->plugin_id() ) {
			// Depending on the action specified, do different things
			switch ( $action ) {
				case _t( 'Configure' ):
					$ui = new FormUI( get_class( $this ) );
					$ui->append( 'text', 'hide_tags', 'option:' . 'fluffytag__hide', _t( 'Tag(s) to be hidden (seperate with ",")' ) );
					$ui->append( 'text', 'steps_tag', 'option:' . 'fluffytag__steps', _t( 'No. of steps (if you change this you will also need to change fluffytag.css accordingly)' ) );


					$ui->append( 'text', 'hide_prefix', 'option:' . 'fluffytag__prefix', _t( 'Other plugin might use prefixed tags for different reasons. If you want to hide those from the tag cloud add the prefixes here. (seperate with ",")' ) );



					$ui->append( 'text', 'cache_expire', 'option:' . 'fluffytag__expire', _t( 'Time the cache should save result (sec)' ) );

					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved' ) );

					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
				case _t( 'Clear Cache' ):
					Cache::expire( $this->cache_name );
					echo '<p>' . _t( 'Cache has been cleared.' ) . '</p>';
					break;
			}
		}
	}

	public function updated_config( $ui )
	{
		$ui->save();
		Cache::expire( $this->cache_name );
		return false;
	}

	public function theme_fluffytag($theme)
	{
		/*if ( Cache::has( $this->cache_name ) ) {
			$fluffy = Cache::get( $this->cache_name );
		}
		else {*/
			$fluffy = $this->build_cloud();
			Cache::set( $this->cache_name, $fluffy, Options::get( 'fluffytag__expire' ) );
		//}

		$theme->fluffy = $fluffy;
		return $theme->fetch( 'fluffytag' );
	}

	private function get_hide_tag_list()
	{
		if ( '' != ( Options::get( 'fluffytag__hide' ) ) ) {
			return explode( ',', Options::get( 'fluffytag__hide' ) );
		}
		else {
			return array();
		}
	}

	public function action_template_header()
	{
		Stack::add( 'template_stylesheet', array( $this->get_url(true) . 'fluffytag.css', 'screen' ) , 'fluffytag' );
	}

	/*
	 * Based on the number of steps and tag max count do some magic.
	 */
	private function build_cloud()
	{
		$tags = Tag::get();
		$max = Tags::max_count();
		$hide = $this->get_hide_tag_list();
		$tag_array = array();
		
		
		$tags = array_filter($tags, create_function('$tag', 'return (Posts::count_by_tag($tag->slug, "published") > 0);'));
		
		
		
		if( Options::get( 'fluffytag__prefix' ) != "" ) {
			foreach( explode(',', Options::get( 'fluffytag__prefix' ) ) as $prefix  ) {
				$tags= array_filter( $tags, create_function( '$a', 'return $a{0} != "' . $prefix . '";' ) );
			}
		}
		
		$step = $max / Options::get( 'fluffytag__steps' );

		foreach($tags as $tag) {		
			if( !( empty( $tag->slug ) || in_array( $tag->slug, $hide ) || in_array( $tag->tag, $hide ) ) ) 
				$tag_array[] = array( 'tag' => $tag->tag, 'slug' => $tag->slug, 'step' => ceil( $tag->count / $step ) );
		}
		//print_r( $tag_array );
		return $tag_array;
	}

	/*
	 * Lets make sure we're always up to date.
	 */
	public function action_post_insert_after( $post )
	{
		if ( Post::status_name( $post->status ) == 'published' ) {
			Cache::expire( $this->cache_name );
		}
	}

	public function action_post_update_after( $post )
	{
		if ( Post::status_name( $post->status ) == 'published' ) {
			Cache::expire( $this->cache_name );
		}
	}

	public function action_post_delete_after( $post )
	{
		if ( Post::status_name( $post->status ) == 'published' ) {
			Cache::expire( $this->cache_name );
		}
	}

	public function action_tag_insert_after( $tag )
	{
		Cache::expire( $this->cache_name );
	}

	public function action_tag_update_after( $tag )
	{
		Cache::expire( $this->cache_name );
	}

	public function action_tag_delete_after( $tag )
	{
		Cache::expire( $this->cache_name );
	}
}
?>
