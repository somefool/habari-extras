<?php

class minification extends Plugin {

	private static $cache_name = 'minify';
	private static $stack;

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Minification', '', $this->info->version );
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ) :
					$ui = new FormUI( get_class( $this ) );
					// Add a text control for the home page description and textmultis for the home page keywords
					
					$ui->append( 'text', 'cache_expire', 'option:' . 'minification__expire', _t( 'Time the cache should save the minified files (sec)' ) );

					//$ui->append( 'checkbox', 'extream_cache', 'minification__extreme', _t( 'ONLY reminify if the filenames have change.' ) );
					
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->out();
					break;
			}
		}
	}

	public function action_template_header() {

		//Cache::expire( self::$cache_name . '_js' );
		//Cache::expire( self::$cache_name . '_css' );

		// try to disable output_compression (may not have an effect)
		ini_set('zlib.output_compression', '0');

		$modified_js = Stack::get_sorted_stack('template_header_javascript');
		foreach( $modified_js as $key => $value ) {			
			Stack::remove('template_header_javascript', $key);
		}
		
		Stack::add('template_header_javascript', Site::get_url('user') . '/files/minified.js', 'Minified');

		$modified_css = Stack::get_sorted_stack('template_stylesheet');
		$css = array();
		foreach( $modified_css as $key => $value ) {			
			$css[] = $value[0];
			Stack::remove('template_stylesheet', $key);
		}
		Stack::add('template_stylesheet', array( Site::get_url('user') . "/files/minified.css", 'screen'), 'style' );
		
		/*
		 * If we have the files or the cache havent expired don't create new files.
		 */
		if ( !file_exists(site::get_dir('user') . '/files/minified.css') || 
			 !file_exists(site::get_dir('user') . '/files/minified.js') ||
			
				( !Cache::has( self::$cache_name . '_js' ) || 
				  !Cache::has( self::$cache_name . '_css' ) )
					
			) {
		
			
			/* Taken from min/index.php */
			define('MINIFY_MIN_DIR', dirname(__FILE__) . '/min/');
			
			// load config
			require MINIFY_MIN_DIR . '/config.php';
			
			// setup include path
			set_include_path($min_libPath . PATH_SEPARATOR . get_include_path());
			
			require 'Minify.php';
			
			Minify::$uploaderHoursBehind = $min_uploaderHoursBehind;
			Minify::setCache(
			    isset($min_cachePath) ? $min_cachePath : ''
			    ,$min_cacheFileLocking
			);
			
			if ($min_documentRoot) {
			    $_SERVER['DOCUMENT_ROOT'] = $min_documentRoot;
			} elseif (0 === stripos(PHP_OS, 'win')) {
			    Minify::setDocRoot(); // IIS may need help
			}

			$min_serveOptions['minifierOptions']['text/css']['symlinks'] = $min_symlinks;

			// Using jsmin+ 1.3
			$min_serveOptions['minifiers']['application/x-javascript'] = array('JSMinPlus', 'minify');

			/* Javascript */
			if ( !Cache::has( self::$cache_name . '_js' ) || !file_exists(site::get_dir('user') . '/files/minified.js') ) {	
				$js_stack = array();
				foreach( $modified_js as $js ) {
					$js_stack[] = Site::get_path('base') . str_replace(Site::get_url('habari') . '/', '', $js);
				}
				$options = array(
				    'files' => $js_stack,
				    'encodeOutput' => false,
		   		    'quiet' => true,
				    
				);
				$result = Minify::serve('Files', $options);
				file_put_contents( site::get_dir('user') . '/files/minified.js', $result['content']);
				Cache::set( self::$cache_name . '_js', 'true', Options::get( 'minification__expire' ) );
			}

			/* CSS */
			if ( !Cache::has( self::$cache_name . '_css' ) || !file_exists(site::get_dir('user') . '/files/minified.css') ) {
				$css_stack = array();
				foreach( $css as $file ) {
					$css_stack[] = Site::get_path('base') . str_replace(Site::get_url('habari') . '/', '', $file);
				}
				$options = array(
				    'files' => $css_stack,
				    'encodeOutput' => false,
		   		    'quiet' => true,
				    
				);
				// handle request
				$result = Minify::serve('Files', $options);
				file_put_contents( site::get_dir('user') . '/files/minified.css', $result['content']);
	
				Cache::set( self::$cache_name . '_css', 'true', Options::get( 'minification__expire' ) );
			}
		}
	}
	
	public function filter_final_output( $buffer )
	{
		set_include_path(dirname(__FILE__). '/min/lib' . PATH_SEPARATOR . get_include_path());
		require_once 'Minify/HTML.php'; 
		return Minify_HTML::minify( $buffer );
	}
}

?>