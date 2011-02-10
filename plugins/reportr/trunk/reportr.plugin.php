<?php

class Reportr extends Plugin
{
	
	const SILO_NAME = 'Scribd';
	
	static $secret_report_cache = array();
	
	public function action_update_check()
	{
		Update::add( $this->info->name, '14f9f07a-e40d-4275-83b3-403024a00460', $this->info->version );
	}
	
	
	public function action_init() {
		// self::install();
		
		$this->add_template('scribdr', dirname(__FILE__) . '/formcontrol.scribdr.php');
		
		
		$this->add_rule('"reports"', 'display_reports');
		$this->add_rule('"reports"/"tag"/tag', 'display_reports_by_tag');
		
		
		// exit;
		
	}

	/**
	 * Handle register_page action
	 **/
	public function action_plugin_act_display_reports($handler)
	{
		
		$reports = self::get_reports();
		
		$handler->theme->view_tag = false;
		$handler->theme->reports = $reports;
		
		$handler->theme->display('report.multiple');
	}
	
	
	/**
	 * Handle register_page action
	 **/
	public function action_plugin_act_display_reports_by_tag($handler)
	{
		$tag = $handler->handler_vars['tag'];

		$reports = self::get_reports( array( "tag" => $tag ) );
		
		$handler->theme->view_tag = $tag;
		$handler->theme->reports = $reports;
		
		$handler->theme->display('report.multiple');
	}

	
	public function action_plugin_activation( $plugin_file )
	{
		self::install();
	}
	
	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'report' );
	}
	
	public static function get_reports( $params = array() )
	{
		$params['content_type'] = Post::type('report');
		$params['nolimit'] = true;
		
		if( $params['tag'] != NULL )
		{
			$params['vocabulary'] = array( 'tags:term' => $params['tag'] );
			unset( $params['tags'] );
		}
		
		return Posts::get( $params );
	}
	
	/**
	 * install various stuff we need
	 */
	public static function install() {
		/**
		 * Register content type
		 **/
		Post::add_new_type( 'report' );
		
		// Give anonymous users access
		$group = UserGroup::get_by_name('anonymous');
		$group->grant('post_report', 'read');

	}
	
	/**
	 * Create name string
	 **/
	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'report' => array(
				'singular' => _t('Report'),
				'plural' => _t('Reports'),
			)
		); 
 		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	/**
	 * Modify publish form
	 */
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('report')) {
			
			$form->content->caption = _t( 'Summary' );
			
			$file= $form->append('file', 'file', 'null:null', _t('URL'), 'scribdr');
			$form->move_after($file, $form->title);

			$id= $form->append('text', 'report_id', 'null:null', _t('ID'), 'admincontrol_text');
			$form->move_after($id, $form->file);		
	
			if( $post->report != NULL ) {
				
				
				// Scribd revisions seem to be buggy
				$file->remove();
				$id->remove();
				
				// Utils::debug( $post->report );
				
				// $form->append('static', 'overwrite_warning', '<div class="container transparent">' . sprintf( _t( 'If you upload a new file, it will overwrite the <a href="%s">existing</a> one.' ), $post->report->url ) . '</div>');
				// $form->move_before($form->overwrite_warning, $form->file);
				
				
			}
			
			$form->remove($form->silos);
		}
	}
	
	
	/**
	 * Save our report
	 */
	public function action_publish_post( $post, $form )
	{
		
		if ($post->content_type == Post::type('report')) {
			$this->action_form_publish($form, $post);
			
			$api = new ScribdAPI;
			
			// $pathinfo = pathinfo( $_FILES[$form->file->field]['name'] );
			
			if( $form->file->tmp_file != '' ) {
				$pathinfo = pathinfo( $_FILES[$form->file->field]['name'] );					
	// We have an upload
								
				if( $post->report == NULL) {
					// New report
					$results = $api->upload( $form->file->tmp_file, $pathinfo );
			
					if( $results == FALSE ) {
						// Error logic
					}
					else {
						$post->info->report_id = $results['id'];
						$post->info->report_key = $results['key'];
					}
				}
				else {
					// Update existing report;
					$results = $post->report->replace( $form->file->tmp_file, $pathinfo );
						
					if( $results == FALSE ) {
						// Error logic
					}
					else {
						// Utils::debug( $results, $post->report );
						// exit;
					}
				}
			}
			
			// Utils::debug( $form->report_id != '' );

			if( $form->report_id->value != '' )
			{
				$results = $api->get_info ( $form->report_id->value );
				
				$post->info->report_id = $form->report_id->value;
				$post->info->report_key = $results['access_key'];
					
				// Utils::debug( $results );

			}
			// exit;
			
			$post->save();
			
			$post->report->title = $form->title->value;
			$post->report->description = $form->content->value;
			$post->report->tags = $form->tags->value;
									
			$post->report->save();
		}
	}
	
	/**
	 * Create the magic report property
	 **/
	public function filter_post_report($val, $post) {
		if($post->content_type == Post::type('report')) {
			
			
			if( isset( Reportr::$secret_report_cache[$post->id] ) ) {
				return Reportr::$secret_report_cache[$post->id];
			}
			
				
			if( !isset( $post->info->report_id) ) {
				// No associated report
				return null;
			}
			else {
																
				Reportr::$secret_report_cache[$post->id] = new Report( $post->info->report_id, $post->info->report_key);
				
				return Reportr::$secret_report_cache[$post->id];
				
			}
			
			
		}
		else {
			return $val;
		}
	}
	
	// /**
	// * Add rewrite rules to map post urls
	// *
	// * @param array $rules An array of RewriteRules
	// * @return array The array of new and old rules
	// */
	// public function filter_rewrite_rules( $rules )
	// {
	// 	
	// 	Utils::debug( $rules );
	// 	
	// 	// return $rules;
	// 	
	// 	$rules[] = new RewriteRule( array(
	// 		'name' => 'display_reports',
	// 		'parse_regex' => '#^reports(?:/page/(?P<page>\d+))?/?$#i',
	// 		'build_str' => 'reports/{$tag}(/page/{$page})',
	// 		'handler' => 'UserThemeHandler',
	// 		'action' => 'display_reports',
	// 		'priority' => 1,
	// 		'description' => 'Return posts matching specified tag.',
	// 		'parameters' => serialize( array( 'content_type' => Post::type('report') ) )
	// 	) );
	// 	
	// 	// array( 'name' => 'display_entries', 'parse_regex' => '#^(?:page/(?P<page>[2-9]|[1-9][0-9]+))/?$#', 'build_str' => '(page/{$page})', 'handler' => 'UserThemeHandler', 'action' => 'display_entries', 'priority' => 999, 'description' => 'Display multiple entries' ),
	// 
	// 	return $rules;
	// 	return array();
	// }
	
}

class ScribdAPI
{
	function __construct()
	{
		$this->key = '3qjhrs6u1x5h3r2co1mzu';
		$this->secret = 'sec-ba36gcyybz2svc5aqpngd9hjje';
		$this->pubid = 'pub-5725093775857216776';
		$this->endpoint = 'http://api.scribd.com/api';
		$this->conntimeout = 20;
	}
	
	private function get_endpoint( $method ) {
		$url = $this->endpoint;
		$url.= '?method=' . $method;
		$url.= '&api_key=' . $this->key;
		
		return $url;
	}
	
	function fetch($method, $params = array(), $tokenize = false, $debug = false) {
		$url = $this->get_endpoint( $method );
				
		foreach($params as $key => $val) {
			$url.= '&' . $key . '=' . $val;
		}
				
		if($debug) {
			print_r($url);	
		}
		
		
		$contents = RemoteRequest::get_contents($url);
				
		$data = new SimpleXMLElement($contents);
				
		if($data['stat'] == 'ok') {
			return $data;
		} else {
			return FALSE;
		}
	}
	
	function put($method, $params = array(), $tokenize = false, $debug = false) {
		$url = $this->get_endpoint( $method );
				
		if($debug) {
			print_r($url);	
		}
		
		$req = curl_init();
		
		$params['api_key'] = $this->key;

		curl_setopt($req, CURLOPT_URL, $url);
		curl_setopt($req, CURLOPT_TIMEOUT, 0);
		// curl_setopt($req, CURLOPT_INFILESIZE, filesize($photo));
		// Sign and build request parameters
		curl_setopt($req, CURLOPT_POSTFIELDS, $params);
		curl_setopt($req, CURLOPT_CONNECTTIMEOUT, $this->conntimeout);
		curl_setopt($req, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($req, CURLOPT_HEADER, 0);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
		$this->_http_body = curl_exec($req);

		if (curl_errno($req)){
			throw new Exception(curl_error($req));
		}

		curl_close($req);
		$xml = simplexml_load_string($this->_http_body);
		
		if( $xml['stat'] == 'ok') {
			return $xml;
		}
		else {
			Utils::debug( $xml, $params, $url );
			$result = false;
		}
			
		return $result;
	}
	
	/**
	 * Gets a list of documents for an (optionally) specified user 
	 **/
	public function getDocs( $user = NULL )
	{
		
		$xml = $this->fetch('docs.getList');
		
		// Utils::debug( $xml );
		
		$docs = array();
		
		foreach($xml->resultset->result as $doc) {
		
			
			
			// $report = new Report;
		
			$report->id = (string) $doc->doc_id;
			$report->thumbnail = (string) $doc->thumbnail_url;
			$report->title = (string) $doc->title;
			
			// $props['url'] = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}$size.jpg";
			// $props['thumbnail_url'] = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_m.jpg";
			// $props['flickr_url'] = "http://www.flickr.com/photos/{$_SESSION['nsid']}/{$photo['id']}";
			// $props['filetype'] = 'flickr';
			
			// Utils::debug( $doc, $report);
			
			$docs[] = $report;
	
		}
		
		return $docs;
		
	}
	
	/**
	 * Gets the download url of a docuemnt
	 **/
	public function getURL( $doc_id, $format = "original" )
	{
		
		$params = array(
			"doc_id" => $doc_id,
			"doc_type" => $format
		);
		
		$xml = $this->fetch('docs.getDownloadUrl', $params);
		
		// Utils::debug( $xml );
		
		if( $xml ) {
			return (string) $xml->download_link;
		}
		else {
			return false;
		}
		
	}
	
	function changeSettings( $doc_id, $settings ) {
		
		$args = $settings;
		
		$args['doc_ids'] = $doc_id;
		
		$result = $this->put( 'docs.changeSettings', $args );
		
		if( $result != false ) {
			return true;
		}
		else {
			return false;
		}
		
	}
	
	function getSettings( $doc_id ) {
		
		$args = array();
		
		$args['doc_id'] = $doc_id;
		
		$result = $this->fetch( 'docs.getSettings', $args );
		
		if( $result != false ) {
			
			$settings = array(
				"title" => (string) $result->title,
				"description" => (string) $result->description,
			);
			
			$settings["tags"] = explode(",", (string) $result->tags);
			$settings["download_formats"] = explode(",", (string) $result->download_formats);
			$settings["access_key"] = (string) $result->access_key;			

			return $settings;
		}
		else {
			return false;
		}
		
	}

	function get_info( $doc_id ) {
		return $this->getSettings( $doc_id );
	}
	
	function upload( $file, $pathinfo = array(), $replace = FALSE ) {
		
		$args['file'] = '@' . $file;
		
		if( $replace ) {
			$args['rev_id'] = $replace;
		}
		
		if( isset( $pathinfo['extension'] ) ) {
			$args['doc_type'] = $pathinfo['extension'];
		}
		
		$xml = $this->put( 'docs.upload', $args );
		
		if( $xml ) {
			$result = array(
				'id' => (string) $xml->doc_id,
				'key' => (string) $xml->access_key
				);
		}
		else {
			$result = false;
		}
			
		return $result;
	}
	
	
	
}

class Report
{
	public $api;
	private $settings = array();
	private $changed;
	private $download_url;
	
	function __construct( $id, $key ) {
		$this->id = $id;
		$this->key = $key;
		
		$this->api = new ScribdAPI;
	}
	
	public function __set( $name, $value ) {
		switch( $name ) {
			
			case 'description':
			case 'title':
			case 'tags':
				$this->settings[$name] = $value;

				$this->changed = TRUE;
				
				break;
				
			default;
				$this->{$name} = $value;
				
				break;
			
		}
				
	}
	
	public function __get( $name ) {
		switch( $name ) {
			
			case 'pdf':
			case 'text':
			case 'original':
				if( !isset( $this->{$name . "_url"} ) ) {
					$this->{$name . "_url"} = $this->api->getURL( $this->id, $name );
				}
								
				return $this->{$name . "_url"};
				
			
			case 'url':
				return 'http://www.scribd.com/doc/' . $this->id;
				break;
				
			case 'formats':
			
				$formats = array();
				
				foreach( $this->download_formats as $format ) {
					
					if( $format == 'text' ) {
						// buggy
						continue;
					}
					
					switch( $format ) {
						case 'text':
						case 'original':
							$string = ucfirst( $format );
							break;
							
						default:
							$string = strtoupper( $format );
							break;
					}
					
					$formats[$format] = $string;
				}
				
				return $formats;
				
				break;
			
			case "download_formats":
			case 'description':
			case 'title':
			case 'tags':
				if( !isset( $this->settings[$name] ) ) {
					
					$settings = $this->api->getSettings( $this->id );
					$this->settings = $settings;
				}
				
				return $this->settings[$name];
				
				break;
			
			default;
				return $this->{$name};
				
				break;
			
		}
				
	}
	
	public function save() {
		
		if( !$this->changed ) {
			return;
		}
				
		if( $this->api->changeSettings( $this->id, $this->settings) ) {
			$this->changed = false;
			return true;
		}
		else {
			return false;
		}
		
	}
	
	public function replace( $file, $pathinfo = array() ) {
				
		if( $results = $this->api->upload( $file, $pathinfo, $this->id ) ) {
			return true;
		}
		else {
			return false;
		}
		
	}
		
}

?>
