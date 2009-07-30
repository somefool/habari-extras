<?php

/**
 * Google Analytics Dashboard Modules Plugin
 *
 * @todo figure out why the dropdown doesn't show the first time you hit save.  have to hit it twice to make it show up.
 * @todo clean up documentation.  It's sad, I know.
 * 
 **/

include 'class.analytics.php';

class ga_dashboard extends Plugin
{
	
	private $class_name = '';
	private $config = array();
	private $reports = array();
	private $funcs = array();
	
	// Default options
	private $default_options = array(
										'user_id' => '',
										'user_pw' => '',
										'cache_expiry' => 3600,
										'profile' => ''
									);
	
	/**
	 * On plugin init
	 **/
	public function action_init()
	{
		$this->class_name = strtolower( get_class( $this ) );
		
		foreach ( $this->default_options as $name => $value ) {
			$this->config[$name] = Options::get( $this->class_name . '__' . $name );
		}
		
		if ( $this->plugin_configured( $this->config ) ) {
			
			$this->validate_reports( $this->load_available_reports() );
			
			// Add our template files for valid reports and register our functions
			foreach ( $this->reports as $rpt => $data ) {
				$this->add_template( $data['slug'], $data['template'] );
				// This is janky.  dynamic_dash_filter() is too.
				$this->funcs[$data['module']] = "$rpt";
				Plugins::register( array( $this, 'dynamic_dash_filter' ), 'filter', 'dash_module_' . $data['slug'] );
			}
		}
	}
	
	/**
	 * action_plugin_activation
	 * Registers the core modules with the Modules class. Add these modules to the
	 * dashboard if the dashboard is currently empty.
	 * @param string $file plugin file
	 */
	public function action_plugin_activation( $file )
	{
		foreach ( $this->default_options as $name => $value ) {
			$current_value = Options::get( $this->class_name . '__' . $name );
			if ( is_null( $current_value ) ) {
				Options::set( $this->class_name . '__' . $name, $value );
			}
		}
		
		if( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			foreach ( $this->reports as $rpt => $data ) {
				Modules::add( $data['module'] );
			}
		}
	}
	
	/**
	 * action_plugin_deactivation
	 * Unregisters the core modules.
	 * @param string $file plugin file
	 */
	public function action_plugin_deactivation( $file )
	{
		if( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			foreach ( $this->reports as $rpt => $data ) {
				Modules::remove_by_name( $data['module'] );
			}
		}
	}
	
	public function action_admin_header( $theme )
	{
		Stack::add( 'admin_header_javascript', "http://www.google.com/jsapi");
		Stack::add( 'admin_header_javascript', $this->get_url(true) . 'ga_base.js');
	}
	
	/**
	 * filter_dash_modules
	 * Registers the core modules with the Modules class. 
	 */
	public function filter_dash_modules( $modules )
	{
		foreach ( $this->reports as $rpt => $data ) {
			array_push( $modules, $data['module'] );
		}
		return $modules;
	}
	
	/**
	 * Add actions to the plugin page
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The string id of a plugin, generated by the system
	 * @return array The array of actions to attach to the specified $plugin_id
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id === $this->plugin_id() ) {
			$actions[] = _t( 'Configure', $this->class_name );
		}
		return $actions;
	}
	
	/**
	 * Respond to the user selecting an action on the plugin page
	 * @param string $plugin_id The string id of the acted-upon plugin
	 * @param string $action The action string supplied via the filter_plugin_config hook
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id === $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure', $this->class_name ):
					
					$ui = new FormUI( $this->class_name );
					
					$user_id = $ui->append( 'text', 'user_id', 'option:' . $this->class_name . '__user_id', _t( 'GA username', $this->class_name ) );
					$user_pw = $ui->append( 'password', 'user_pw', 'option:' . $this->class_name . '__user_pw', _t( 'GA password', $this->class_name ) );
					$user_pw->value = base64_decode( Options::get( $this->class_name . '__user_pw' ) );
					
					$cache_expiry = $ui->append( 'text', 'cache_expiry', 'option:' . $this->class_name . '__cache_expiry', _t( 'Cache Expiry (in seconds)', $this->class_name ) );
					$cache_expiry->add_validator( 'validate_uint' );
					$cache_expiry->add_validator( 'validate_required' );
					
					if ( $this->plugin_configured( $this->config ) ) {
						$ga = new Google_Analytics( $this->config['user_id'], base64_decode( $this->config['user_pw'] ) );
						// get an array with profiles (profileId => profileName)
						$gprofs = $ga->get_profiles();
						$profile = $ui->append( 'select', 'profile', 'option:' . $this->class_name . '__profile', _t( 'Profile' ) );
						$profile->options = $gprofs;
					}
					// When the form is successfully completed, call $this->updated_config()
					$ui->append( 'submit', 'save', _t( 'Save', $this->class_name ) );
					$ui->set_option( 'success_message', _t( 'Options saved', $this->class_name ) );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					
					break;
			}
		}
	}
	
	public function updated_config( $ui )
	{
		$b64_pw = base64_encode( $ui->user_pw->value );
		
		$ui->save();
		
		Options::set( $this->class_name . '__user_pw', $b64_pw );
		
		foreach ( $this->reports as $rpt => $data ) {
			$cache = $this->class_name . '__' . $rpt;
			Cache::expire( $cache );
		}
		return false;
	}
	
	public function validate_uint( $value )
	{
		if ( ! ctype_digit( $value ) || strstr( $value, '.' ) || $value < 0 ) {
			return array( _t( 'This field must be a positive integer.', $this->class_name ) );
		}
		return array();
	}
	
	public function plugin_configured( $params = array() )
	{
		if ( empty( $params['user_id'] ) || empty( $params['user_pw'] ) || empty( $params['cache_expiry'] ) ) {
			return false;
		}
		return true;
	}
	
	
	/**
	 * Load in all available reports
	 * @return array array of available report files
	 **/
	private function load_available_reports()
	{
		$valid_reports = array();
		$reports = array();
		$templates = array();
		$searchdirs = array( dirname( __FILE__ ) . '/default', dirname( __FILE__ ) . '/custom' );
		
		$dirs = array();
		foreach ( $searchdirs as $searchdir ) {
			if ( file_exists( $searchdir ) ) {
				$dirs = array_merge( $dirs, Utils::glob( $searchdir . '*', GLOB_ONLYDIR | GLOB_MARK ) );
			}
		}
		
		// Load in the available reports and templates
		foreach ( $dirs as $dir ) {
			$reportfiles = Utils::glob( $dir . '*.xml' );
			$templatefiles = Utils::glob( $dir . '*.php' );
			
			if ( ! empty ( $reportfiles ) ) {
				$reportfiles = array_combine(
						// Use the basename of the file as the index to use the named report from the last directory in $dirs
						array_map( 'basename', $reportfiles ),
						// massage the filenames so that this works on Windows
						array_map( create_function( '$s', 'return str_replace(\'\\\\\', \'/\', $s);' ), $reportfiles )
					);
				$reports = array_merge( $reports, $reportfiles );
			}
			
			if ( ! empty ( $templatefiles ) ) {
				$templatefiles = array_combine(
						// Use the basename of the file as the index to use the named report from the last directory in $dirs
						array_map( 'basename', $templatefiles ),
						// massage the filenames so that this works on Windows
						array_map( create_function( '$s', 'return str_replace(\'\\\\\', \'/\', $s);' ), $templatefiles )
					);
				$templates = array_merge( $templates, $templatefiles );
			}
		}
		
		// Now go through and generate our valid_reports()
		foreach ( $reports as $rptKey => $rptVal ) {
			$tmpKey = array_search( str_replace( '.xml', '.php', $rptVal ), $templates );
			if ( $tmpKey ) {
				$key = basename( $rptVal, ".xml" );
				$valid_reports[$key] = array( 'xml' => $rptVal, 'template' => $templates[$tmpKey] );
			}
		}
		
		ksort( $valid_reports );
		return $valid_reports;
	}
	
	
	/**
	 * validate_reports()
	 * Reads in the xml from each available report
	 * If valid, will load it to the $this->reports array
	 **/
	private function validate_reports( $reports )
	{
		// Charts that require js options to be built
		$jCharts = array( "piechart", "geomap", "linechart", "areachart" );
		// All support chart types
		$sCharts = array_merge( (array) "none", $jCharts );
		
		foreach ( $reports as $rpt => $data ) {
			$report = array();
			
			$xml = simplexml_load_file( $data['xml'] );
			if ( $xml ) {
				
				// load in the single options
				$display  = (string) $xml->name;
				$opts     = (string) $xml->opts;
				$ctype     = strtolower( (string) $xml->type );
				$sort     = (string) $xml->sort;
				
				// Make sure that the display name has been set
				if ( $display === "" ) return;
				
				// Set a few others based off of base option.
				$module   = 'GA ' . $display;
				$slug     = Utils::slugify( $module, '_' );
				$template = $data['template'];
				
				// Make sure this is a supported chart type
				if ( ! in_array( $ctype, $sCharts ) ) return;
				
				// Make sure we have a valid sort type
				if ( ! in_array( $sort, array( "none", "numeric", "key" ) ) ) {
					$sort = "";
				}
				
				// Check our dataRefs
				$dataRefs = array();
				foreach ( $xml->xpath('dataReference') as $dataRef ) {
					if ( $dataRef['order'] ) {
						// if we have order, use that as the key
						$key = (int) $dataRef['order'];
					}
					else {
						// create them as we find them.
						$key = count( $dataRefs ) + 1;
					}
					
					$dimensions = (string) $dataRef->dimensions;
					$metrics    = (string) $dataRef->metrics;
					$sort       = (string) $dataRef->sort;
					
					/**
					 * @todo maybe add some additional checks here to make sure that we have a correct
					 *       set of options.  For now if they are an incorrect combination, it will break
					 *       all of the charts as JS will kick up an error and not run the rest of them.
					 *       We're just checking that the sort isn't blank right now.
					 */
					
					if ( $sort ) {
						// Default sort to metrics if it's not specified
						$sort = $metrics;
					}
					
					// Store them in a temp array
					$dataRefs[ $key ] = array(
							'd' => $dimensions,
							'm' => $metrics,
							's' => $sort
						);
				}
				
				// Get limit information for our data
				$lMax = intval( (string) $xml->limit->max );
				
				if ( $lMax > 0 ) {
					$rlTotal = intval( (string) $xml->limit->totalDropped );
					$lTotal = ( $rlTotal === 1 ) ? true : false;
				}
				
				// Get information for JS on js chart types
				if ( in_array( $ctype, $jCharts ) ) {
					
					if ( $opts === "" ) {
						// set the default js options
						$opts = 'height: 200';
					}
					
					// Load and validate the dataTypes
					$dataTypes = array();
					foreach( $xml->xpath( '//dataTypes/row' ) as $dataType ) {
						$dtype = (string) $dataType['type'];
						$dname = (string) $dataType;
						
						if ( $dtype === "" ) {
							// default type to number
							$dtype = 'number';
						}
						
						if ( $dname === "" ) {
							// default name to column_x
							$dname = 'Column_' . (string) count( $dataTypes );
						}
						
						// Store them in a temp array
						$dataTypes[] = array(
							't' => $dtype,
							'n' => $dname,
							);
					}
					
					// Verify that we have count( $dataRefs) + 1 for our js dataTable.
					if ( ! count( $dataTypes ) == ( count( $dataRefs) + 1 ) ) return;
				}
				
				// Seems to be valid, so let's add it to our self::reports
				$this->reports[ $rpt ] = array(
					'name'      => $display,
					'module'    => $module,
					'slug'      => $slug,
					'template'  => $template,
					'type'      => $ctype,
					'opts'      => $opts,
					'sort'      => $sort,
					'limit'     => $lMax,
					'ltotal'    => $lTotal,
					'dataRefs'  => $dataRefs
					);
				
				if ( in_array( $ctype, $jCharts ) ) {
					$this->reports[$rpt]['dataTypes'] = $dataTypes;
				}
			}
		}
	}
	
	/**
	 * dynamic_dash_filter()
	 * There's just got to be a better way of doing this....
	 **/
	public function dynamic_dash_filter( $module, $module_id, $theme )
	{
		// Pull a little magic sauce out of the params
		$searchVal = (string)$module['name'];
		// $rpt is set to the key of our reports array...
		$rpt = $this->funcs["$searchVal"];
		
		// call to load our data here.  have that func do the cache checking.
		//$this->load_report_data( $rpt );
		
		$module['title'] = ( $this->reports["$rpt"]['name'] );
		
		if ( $this->plugin_configured( $this->config ) ) {
			// call to load our data here.
			$data = $this->load_report_data( $rpt );
			
			$data_total = 0;
			$dkeys = array_keys( $data );
			$sort = $this->reports[$rpt]['sort'];
			
			/**
			 * @todo fix sort to actually support different types.
			 * For now we default to ksort the array.
			 * if sort is set, we arsort it.
			 */

			foreach ( $dkeys as $dk ) {
				foreach ( $data[$dk] as $k => $v ) {
					$data_total += $v;
				}
				ksort( $data[$dk] );
			}
			
			if ( $this->reports[$rpt]['limit'] != 0 ) {
				foreach ( $dkeys as $dk ) {
					if ( $sort ) {
						arsort( $data[$dk] );
					}
					
					// Generate the totals for the other items that we're dropping
					$ototals = 0;
					$tmpArr = array_slice( $data[$dk], $this->reports[$rpt]['limit'] );
					foreach ( $tmpArr as $key => $val ) $ototals += $val;
					
					// Actually drop the other items for limit
					array_splice( $data[$dk], $this->reports[$rpt]['limit'] );
					
					if ( ( $this->reports[$rpt]['type'] != "none" ) && $this->reports[$rpt]['ltotal'] ) {
						// Add the (others) total to the array
						$data[$dk]["(others)"] = $ototals;
					}
				}
			}

			// Set some template variables
			$theme->slug = $this->reports["$rpt"]['slug'];
			$theme->data = $data;
			$theme->data_total = $data_total;
			$theme->js_opts = $this->reports["$rpt"]['opts'];
			$theme->js_data = $this->create_js_data( $rpt, $data );
			$theme->js_draw = $this->create_js_draw( $rpt );
			
			$module['content'] = $theme->fetch( $this->reports["$rpt"]['slug'] );
		}
		else {
			$module['content'] = '<div class="message pct90"><p><b>Plugin is not configured!</b></p></div>';
		}
		
		return $module;
	}
	
	/**
	 * create_js_data()
	 * Generates a js string for a named report
	 * @return string javascript string for chart data.
	 **/
	private function create_js_data( $report, $data )
	{
		if ( $this->reports[$report]['type'] === 'none' ) return '';
		
		// init the DataTable()
		$slug = $this->reports[$report]['slug'];
		$js = "var $slug = new google.visualization.DataTable();";
		
		// Add our column headers
		$dataTypes = $this->reports[$report]['dataTypes'];
		foreach ( $dataTypes as $dataType ) {
			$dtype = $dataType['t'];
			$dname = $dataType['n'];
			
			$js .= "$slug.addColumn('$dtype', '$dname');";
		}
		
		$js .= "$slug.addRows([";
		
		// Add our column data
		$colcount = count( $data );
		// we should always have $data[1]
		$colkeys = array_keys( $data[1] );
		$combined = array();
		
		// Load all of our arrays into one single by key
		foreach ( $colkeys as $key ) {
			$tmp = array();
			for ( $i = 1; $i <= $colcount; $i++ ) {
				$tmp[] = $data[$i][$key];
			}
			$combined[$key] = $tmp;
		}
		
		foreach ( $combined as $key => $val ) {
			if ( preg_match( '/\d\s\d/', $key ) ) {
				$key = str_replace( ' ', '/', $key );
			}
			$js .= "['$key', ";
			
			$cnt = 1;
			foreach ( $val as $v ) {
				if ( is_numeric( $v ) ) {
					$js .= intval( $v );
				} else {
					$js .= "'$v'";
				}
				if ( $cnt == count( $val ) ) {
					$js .= "],";
				} else {
					$js .= ", ";
					$cnt++;
				}
			}
		}
		
		$js .= "]);";
		
		return $js;
	}
	
	private function create_js_draw( $report )
	{
		if ( $this->reports[$report]['type'] === 'none' ) return '';
		
		$slug = $this->reports[$report]['slug'];
		$type = $this->reports[$report]['type'];
		
		$js = "do$type( $slug, opts, 'div_$slug');";
		return $js;
	}
	
	/**
	 * load_report_data()
	 * Loads GA report data for a named report
	 * @return array data from GA
	 **/
	private function load_report_data( $report )
	{
		$cache_name = $this->class_name . '__' . $report;
		
		if ( Cache::has( $cache_name ) ) {
			// Read from the cache
			return Cache::get( $cache_name );
		}
		else {
			// Time to fetch it.
			try {
				// Call out to get the data here.
				$gObj = new Google_Analytics( $this->config['user_id'], base64_decode( $this->config['user_pw'] ) );
				
				// get an array with profiles (profileId => profileName)
				$prof = $gObj->get_profiles();
				$profkeys = array_keys( $prof );
				
				if ( $this->config['profile'] != '' ) {
					$gObj->set_profile( $this->config['profile'] );
				} else {
					// set the profile to the first account.
					$gObj->set_profile( $profkeys[0] );
				}
				
				$startDate = date('Y-m-d', strtotime("-1 month") );
				$stopDate  = date('Y-m-d');
				
				$gObj->set_date_range( $startDate, $stopDate );
				
				$gadata = array();
				$refcount = count( $this->reports[$report]['dataRefs'] );
				
				// We may have multiple data references, so we need to fetch them all.
				$dataref = $this->reports[$report]['dataRefs'];
				
				// loop through in order.
				for ( $i = 1; $i <= $refcount; $i++ ) {
					$gadata[$i] = $gObj->getData( $dataref[$i]['d'], $dataref[$i]['m'], $dataref[$i]['s'] );
				}
				
				// Store the cache
				Cache::set( $cache_name, $gadata, $this->config['cache_expiry'] );
				return $gadata;
			}
			catch ( Exception $e ) {
				return $e->getMessage();
			}
		}
	}
	
} // class ga_dashboards

?>