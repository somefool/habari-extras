<?php
class GoogleAjax extends Plugin
{
	/**
     * Beacon Support for Update checking
     *
     * @access public
     * @return void
     **/
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
				'scriptaculous' => 'http://ajax.googleapis.com/ajax/libs/scriptaculous/1/scriptaculous.js',
				'mootools'		=> 'http://ajax.googleapis.com/ajax/libs/mootools/1/mootools-yui-compressed.js',
				'dojo'			=> 'http://ajax.googleapis.com/ajax/libs/dojo/1/dojo/dojo.xd.js',
				'swfobject'		=> 'http://ajax.googleapis.com/ajax/libs/swfobject/2/swfobject.js',
				'yui'			=> 'http://ajax.googleapis.com/ajax/libs/yui/2/build/yuiloader/yuiloader-min.js',
				'ext-core'		=> 'http://ajax.googleapis.com/ajax/libs/ext-core/3/ext-core.js',
				'chrome-frame'	=> 'http://ajax.googleapis.com/ajax/libs/chrome-frame/1/CFInstall.min.js'
			);

		switch ( $stack_name ) {
            case 'template_footer_javascript':
            case 'template_header_javascript':
				if ( Options::get( __CLASS__ . '__direct_link') ) {
					$int = array_intersect_key( $googleHosts, $stack );
					return array_merge( $stack, $int );
				} else {
					$newstack['jsapi'] = 'http://www.google.com/jsapi';
					$newstack['jsapi_load'] = '';
					foreach ( $stack as $key => $value ) {
						if ( array_key_exists( $key, $googleHosts ) ) {
							$ver = explode( '/', $googleHosts[$key] );
							$newstack['jsapi_load'] .= 'google.load("'.$key.'", "'.$ver[6].'");'; // This assumes Google keeps a consistent URL structure with the version in the 6th field.
							unset( $stack[$key] );	// Remove replaced key from original stack
						} 
					}
					return array_merge( $newstack, $stack ); // Merge stacks, putting the Google API calls first.
				}
				break;
            default:
                return $stack;
        }
	}
}
?>