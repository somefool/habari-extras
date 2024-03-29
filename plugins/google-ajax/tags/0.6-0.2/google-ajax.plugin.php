<?php
class GoogleAjax extends Plugin
{
	
	/**
	 * Required plugin information
	 * @return array The array of information
	 */
	public function info()
	{
		return array(
			'name' => 'GoogleAjax',
			'version' => '0.2',
			'url' => 'http://habariproject.org/',
			'author' => 'Colin Seymour',
			'authorurl' => 'http://colinseymour.co.uk/',
			'license' => 'Apache License 2.0',
			'description' => 'Overwrites local paths to common Javascript libraries, like jQuery, MooTool etc, with the paths hosted by Google using either direct links or Google\'s google.load() method.',
			'copyright' => '2009'
			);
	}
	
	/**
	 * The help message - it provides a larger explanation of what this plugin
	 * does
	 *
	 * @return string
	 */
	public function help()
	{
		$help  = '<p>' . _t( 'The GoogleAjax plugin overwrites any local paths to common Javascript libraries, with the paths hosted by Google. ') . '</p>';
		$help .= '<p>' . _t( 'A full list of libraries hosted by Google can be found' ) . ' <a href="http://code.google.com/apis/ajaxlibs/documentation/">here</a></p>';
		$help .= '<p>' . _t( 'By default, this plugin uses the latest version of each library, and loads the libraries using Google\'s preferred') . ' <a href="http://code.google.com/apis/ajaxlibs/documentation/">google.load()</a> ' . _t ( 'method, however you can choose to link directly to the files if you prefer.' ) . '</p>';
		$help .= '<p>' . _t( 'In order for this plugin to work, you need to ensure your plugins and theme are using Habari\'s Stack methods, ie Stack::add(), to add the libraries to either the "template_header_javascript" or "template_footer_javascript" stacks and that they are using the generic names that correspond with the generic library names offered by Google, eg jquery.') . '<br><br>';
		$help .= _t( 'For example:' ) . '<br><br>';
		$help .= '<code>Stack::add( "template_header_javascript",  "http://example.com/scripts/jquery.js", "jquery" );</code></p>';
		return $help;
	}

	/**
	 * Beacon Support for Update checking
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add( 'GoogleAjax', 'DDB34CAA-ECBE-11DE-A151-593F56D89593', $this->info->version );
	}

	/**
	 * Add the Configure option for the plugin
	 *
	 * @access public
	 * @param array $actions
	 * @param string $plugin_id
	 * @return array
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}
		return $actions;
	}

	/**
	 * Plugin UI
	 *
	 * @access public
	 * @param string $plugin_id
	 * @param string $action
	 * @return void
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$ui = new FormUI( strtolower( __CLASS__ ) );
					$ui->append( 'static', 'insert_desc', _t('There are two methods in which you can link to the Google hosts libraries. Here you can choose your preferred method.' ) );
					$ui->append( 'radio', 'direct_link', __CLASS__ . '__direct_link', _t( 'Insert Method' ), array( TRUE => _t( 'Direct links' ), FALSE => _t( 'google.load()' ) ) );
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->on_success ( array( $this, 'storeOpts' ) );
					$ui->out();
					break;
			}
		}
	}

	/**
	 * Save our options and display a session message to confirm the save.
	 *
	 * @access public
	 * @param object $ui FormUI object
	 * @return FALSE
	 */
	public static function storeOpts ( $ui )
	{
		$ui->save();
		Session::notice( _t( 'Options saved.' ) );
		return FALSE;
	}

	/**
	 * Process the stack and replace links to Google hosted Javascript libraries.
	 *
	 * @param array $stack
	 * @param string $stack_name
	 * @param $filter
	 * @return array
	 */
	public function filter_stack_out( $stack, $stack_name, $filter )
	{
		if ( count( $stack ) == 0 ) {
		return $stack;
	}

	// Array of direct links - defaults to latest version
	// TODO: Google doesn't offer a method to programatically determine the available versions, so these are hard-coded.
	$googleHosts = array(
			'jquery'		=> 'http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js',
			'jqueryui'		=> 'http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js',
			'prototype'		=> 'http://ajax.googleapis.com/ajax/libs/prototype/1/prototype.js',
			'scriptaculous'		=> 'http://ajax.googleapis.com/ajax/libs/scriptaculous/1/scriptaculous.js',
			'mootools'		=> 'http://ajax.googleapis.com/ajax/libs/mootools/1/mootools-yui-compressed.js',
			'dojo'			=> 'http://ajax.googleapis.com/ajax/libs/dojo/1/dojo/dojo.xd.js',
			'swfobject'		=> 'http://ajax.googleapis.com/ajax/libs/swfobject/2/swfobject.js',
			'yui'			=> 'http://ajax.googleapis.com/ajax/libs/yui/2/build/yuiloader/yuiloader-min.js',
			'ext-core'		=> 'http://ajax.googleapis.com/ajax/libs/ext-core/3/ext-core.js',
			'chrome-frame'		=> 'http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js'
			);

		switch ( $stack_name ) {
			case 'template_footer_javascript':
			case 'template_header_javascript':
				// First we remove the duplicates that occur in the header stack if we're processing the footer stack - Habari doesn't do this, so we do.  This is all about performance after all.
				$header_stack = Stack::get_named_stack( 'template_header_javascript' );
				if ( $stack_name == 'template_footer_javascript' ) {
					$stack = array_diff_key( $stack, $header_stack );
				}
				if ( Options::get( __CLASS__ . '__direct_link' ) ) {
					$int = array_intersect_key( $googleHosts, $stack );
					return array_merge( $stack, $int );
				} else {
					// We only need this in the footer if it's not already in the header
					$newstack['jsapi'] = 'http://www.google.com/jsapi';
					if ( ( $stack_name == 'template_footer_javascript' ) && array_intersect_key( $googleHosts, $header_stack ) ) {
						unset( $newstack['jsapi'] );
					}
					$newstack['jsapi_load'] = '';
					foreach ( $stack as $key => $value ) {
						if ( array_key_exists( $key, $googleHosts ) ) {
							$ver = explode( '/', $googleHosts[$key] );
							$newstack['jsapi_load'] .= 'google.load("'.$key.'", "'.$ver[6].'");'; // This assumes Google keeps a consistent URL structure with the version in the 6th field.
							unset( $stack[$key] );	// Remove replaced key from original stack
						}
					}
					return ( $newstack['jsapi_load'] != '' ) ? array_merge( $newstack, $stack ) : $stack; // Merge stacks, putting the Google API calls first.
				}
				break;
			default:
				return $stack;
		}
	}
}
?>