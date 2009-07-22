<?php
/**
 * A plugin to hide spoilers until a conscious action on the part of the user.
 *
 **/
class Spoilers extends Plugin
{
	private $has_spoilers;
	private $current_id;
	private $post;

	/**
	 * Set up some default options
	 *
	 **/
	public function action_plugin_activation($file)
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			Options::set('spoiler__message', 'Reveal spoiler');
		}
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

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$form = new FormUI( strtolower( get_class( $this ) ) );
					$form->append( 'label', 'message_label', _t( 'Default message used as a link in posts and feeds.', 'spoilers') );
					$form->append( 'text', 'message', 'spoiler__message', _t('Spoiler message:', 'spoilers') );
					$form->append( 'submit', 'save', _t('Save') );
					$form->set_option('success_message', _t('Options saved'));
					$form->out();
					break;
			}
		}
	}

	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t( "Inserting &lt;spoiler message=\"Click this to reveal spoiler\"&gt;The butler did it!&lt;/spoiler&gt; would show a link saying 'Click to reveal spoiler' and hide 'The butler did it!' in a post. A default message can be set in the plugin's configuration. In feeds, the spoiler is removed completely, requiring the user to click the link and visit the site to reveal spoilers.", 'spoilers' );
		return $help;
	}

	/**
	 * Add a template for the spoilers
	 **/
	public function action_init()
	{
		$this->add_template('spoiler', dirname(__FILE__) . '/spoiler.php');
	}

	/**
	 * Filter the content of a post, turning <spoiler>foobar</spoiler> into a div that can be hidden
	 *
	 **/
	public function filter_post_content( $content, $post )
	{
		// If we're on the publish page, replacement will be destructive.
		// We don't want that, so return here.
		$handler = Controller::get_handler();
		if ( isset( $handler->action ) && $handler->action == 'admin' && isset($handler->handler_vars['page']) && $handler->handler_vars['page'] == 'publish' ) {
			return $content;
		}

		// If there are no spoilers, save the trouble and just return it as is.
		if ( strpos( $content, '<spoiler' ) === false ) {
			return $content;
		}

		$this->current_id = $post->id;

		$this->post = $post;
		$return = preg_replace_callback( '/(<spoiler(\s+message=[\'"](.*)[\'"])?>)(.*)(<\/spoiler>)/Us', array($this, 'add_spoiler'), $content );

		if ( !$this->has_spoilers ) {
			return $content;
		}

		return $return;
	}

	private function add_spoiler( $matches )
	{
		$this->has_spoilers = true;
		list(,,, $message, $spoiler) = $matches;

		if ( '' == $message ) {
			$message = Options::get('spoiler__message');
		}

		$theme = Controller::get_handler()->theme;

		// If it's a feed, drop the spoiler and link back.
		// This is horribly hacky. Is there a nicer way to check if it's a feed (Atom or RSS)?
		if ( null == $theme ) {
			$link = $this->post->permalink;
			return "<a href='$link'>$message</a>";
		}

		$theme->spoiler = $spoiler;
		$theme->message = $message;

		return $theme->fetch( 'spoiler' );
	}

	public function action_update_check () {
		Update::add( 'Spoilers', 'cb1e23b3-821d-46e5-838d-a8f8cc5e5e26', $this->info->version );
	}

	public function theme_header()
	{
		Stack::add('template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/spoilers.css', 'screen'), 'spoilers', array() );
	}

	public function theme_footer()
	{
		if ( $this->has_spoilers ) {
				// Load jQuery, if it's not already loaded
				if ( !Stack::has( 'template_header_javascript', 'jquery' ) ) {
					Stack::add( 'template_footer_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery', array('jquery') );
				}
				Stack::add( 'template_footer_javascript', URL::get_from_filesystem(__FILE__) . '/spoilers.js', 'spoilers', array('jquery') );
				
				Stack::add('template_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/spoilers.css', 'screen'), 'spoilers', array() );
		}
	}

}

?>
