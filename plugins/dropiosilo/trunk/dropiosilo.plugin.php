<?php
/**
 * drop.io Silo
 * drop.io Silo
 *
 * @package dropiosilo
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-dropiosilo
 */
class DropioSilo extends Plugin implements MediaSilo
{
	const SILO_NAME= 'drop.io';

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'drop.io Silo',
			'version' => '0.02-alpha',
			'url' => 'http://ayu.commun.jp/habari-dropiosilo',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'drop.io silo (http://drop.io/)',
			'guid' => 'e9f30bd4-be96-11dd-aff6-001b210f913f'
			);
	}

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation($file)
	{
		if (Plugins::id_from_file($file) != Plugins::id_from_file(__FILE__)) return;

		Options::set('dropiosilo__api_key', '');
		Options::set('dropiosilo__drop_name', '');
		Options::set('dropiosilo__password', '');
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('dropiosilo');
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add($this->info->name, $this->info->guid, $this->info->version);
	}

	/**
	 * action: plugin_ui
	 *
	 * @access public
	 * @param string $plugin_id
	 * @param string $action
	 * @return void
	 */
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id != $this->plugin_id()) return;
		if ($action == _t('Configure')) {
			$form = new FormUI(strtolower(get_class($this)));
			$form->append('text', 'api_key', 'dropiosilo__api_key', _t('API Key: ', 'dropiosilo'));
			$form->append('label', 'api_key_get_label', '<a href="http://api.drop.io/" target="_blank">doesn\'t have API Key?</a>');
			$form->append('text', 'drop_name', 'dropiosilo__drop_name', _t('Drop Name: ', 'dropiosilo'));
			$form->append('password', 'password', 'dropiosilo__password', _t('Guest Password (optional): ', 'dropiosilo'));
			$form->append('submit', 'save', _t('Save'));
			$form->out();
		}
	}

	/**
	 * actuin: admin_footer
	 *
	 * @access public
	 * @param string $theme
	 * @return void
	 */
	public function action_admin_footer($theme)
	{
		if ($theme->page != 'publish') return;
?>
<script type="text/javascript">
habari.media.output.dropiosilo = {
	display: function(fileindex, fileobj) {
		habari.editor.insertSelection('<a href="' + fileobj.url + '"><img src="' + fileobj.thumbnail_url + '" alt="' + fileobj.title + '" /></a>');
	}
}
habari.media.preview.dropiosilo = function(fileindex, fileobj) {
	return '<div class="mediatitle"><a href="' + fileobj.dropiosilo_url + '" class="medialink">media</a>' + fileobj.title + '</div><img src="' + fileobj.thumbnail_url + '"><div class="mediastats"></div>';
}
</script>
<?php
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @return array
	 */
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * filter: media_controls
	 *
	 * @access public
	 * @param array $controls
	 * @param object $silo
	 * @param string $path
	 * @param string $panelname
	 * @return array
	 */
	public function filter_media_controls($controls, $silo, $path, $panelname)
	{
		$class = __CLASS__;
		if (!($silo instanceof $class)) return $controls;
		if(User::identify()->can('upload_media')) {
			$controls[] = '<a href="#" onclick="habari.media.showpanel(\'' . self::SILO_NAME . '/' . $path . '\', \'upload\');return false;">' . _t('Upload', 'dropiosilo') . '</a>';
		}
		return $controls;
	}

	/**
	 * filter: media_panels
	 *
	 * @access public
	 */
	public function filter_media_panels($panel, $silo, $path, $panelname)
	{
		$class = __CLASS__;
		if (!($silo instanceof $class)) return $panel;

		switch($panelname) {
		case 'upload':
			if (isset($_FILES['file'])) {
				if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
					return _t('File Uploading Attack Detected!', 'dropiosilo');
				}

				$dropio = new DropioAPI(Options::get('dropiosilo__api_key'), Options::get('dropiosilo__drop_name'), Options::get('dropiosilo__password'));
				try {
					$result = $dropio->upload($_FILES['file']['tmp_name'], $_FILES['file']['name']);
				} catch (Exception $e) {
					return sprintf(_t('File Upload Failed: %s'), $e->getMessage());
				}
				$panel .= '<p>' . _t('File upload successfully.', 'dropiosilo') . '</p>';
			} else {
				ob_start();
?>
<div class="container transparent">
<form action="<?php URL::out('admin_ajax', array('context' => 'media_panel')); ?>" method="post" enctype="multipart/form-data" target="dropiosilo_upload_frame">
<input type="hidden" name="path" value="<?php echo self::SILO_NAME . '/' . $path; ?>">
<input type="hidden" name="panel" value="<?php echo $panelname; ?>" />
<?php _e('File:', 'dropiosilo'); ?> <input type="file" name="file" />
<input type="submit" value="<?php _e('Upload', 'dropiosilo'); ?>" />
</form>
<iframe id="dropiosilo_upload_frame" name="dropiosilo_upload_frame" style="width: 1px; height: 1px;" onload="dropiosilo_uploaded();"></iframe>
<script type="text/javascript">
var responsedata;
function dropiosilo_uploaded() {
	if(!$('#dropiosilo_upload_frame')[0].contentWindow) return;
	var response = $($('#dropiosilo_upload_frame')[0].contentWindow.document.body).text();
	if(response) {
		console.log(response);
		eval('responsedata = ' + response);
		window.setTimeout(dropiosilo_uploaded_complete, 500);
	}
}
function dropiosilo_uploaded_complete() {
	habari.media.jsonpanel(responsedata);
}
</script>
</div>
<?php
				$panel = ob_get_clean();
			}
			break;
		}

		return $panel;
	}

	/**
	 * silo info
	 *
	 * @access public
	 * @return string
	 */
	public function silo_info()
	{
		$dropio = new DropioAPI(Options::get('dropiosilo__api_key'), Options::get('dropiosilo__drop_name'), Options::get('dropiosilo__password'));
		try {
			$dropio->check();
			return array(
				'name' => self::SILO_NAME,
				'icon' => $this->get_url() . '/img/icon.png'
				);
		} catch (Exception $e) {
			Session::error(sprintf(_t('drop.io Silo: %s', 'dropiosilo'), $e->getMessage()));
			return array();
		}
	}

	/**
	 * silo dir
	 *
	 * @access public
	 * @return
	 */
	public function silo_dir($path)
	{
		$paths = explode('/', $path);
		$results = array();

		$dropio = new DropioAPI(Options::get('dropiosilo__api_key'), Options::get('dropiosilo__drop_name'), Options::get('dropiosilo__password'));
		try {
			$assets = $dropio->get_assets();
		} catch (Exception $e) {
			return array();
		}

		for ($i = 0; $i < count($assets); $i++) {
			if ($assets[$i]->type != 'image') continue;
			$props = array();
			$props['title'] = $assets[$i]->title;
			$props['url'] = $assets[$i]->converted;
			$props['thumbnail_url'] = $assets[$i]->thumbnail;
			$props['dropiosilo_url'] = 'http://drop.io/' . Options::get('dropiosilo__drop_name') . '/asset/' . $assets[$i]->name;
			$props['filetype'] = 'dropiosilo';
			$results[] = new MediaAsset(self::SILO_NAME . '/' . Options::get('dropiosilo__drop_name') . '/' . $assets[$i]->name, false, $props);
		}
		return $results;
	}

	/**
	 * silo get
	 *
	 * @access public
	 */
	public function silo_get($path, $qualities = null)
	{
	}

	/**
	 * silo_put
	 *
	 * @access public
	 */
	public function silo_put($path, $filedata)
	{
		// TODO: built-in file uploading mechanism is not implemented?
	}

	/**
	 * silo_url
	 *
	 * @access public
	 * @param string $path
	 * @param string $qualities
	 */
	public function silo_url($path, $qualities = null)
	{
	}

	/**
	 * silo_delete
	 *
	 * @access public
	 * @param string $path
	 */
	public function silo_delete($path)
	{
	}

	/**
	 * silo highlights
	 *
	 * @access public
	 */
	public function silo_highlights()
	{
	}

	/**
	 * silo permissions
	 *
	 * @access public
	 * @param string $path
	 */
	public function silo_permissions($path)
	{
	}

	/**
	 * silo contents
	 *
	 * @access public
	 */
	public function silo_contents()
	{
	}
}

class DropioAPI
{
	private $api_key;
	private $drop_name;
	private $token;
	private $base_url = 'http://api.drop.io/drops/';
	private $upload_url = 'http://assets.drop.io/upload';

	/**
	 * constructer
	 *
	 * @access public
	 * @param string $api_key
	 * @param string $drop_name
	 */
	public function __construct($api_key, $drop_name, $token = '')
	{
		$this->api_key = $api_key;
		$this->drop_name = $drop_name;
		$this->token = $token;
	}

	/**
	 * api_key and drop_name check
	 *
	 * @access public
	 */
	public function check()
	{
		$request = new RemoteRequest($this->base_url . $this->drop_name . '?api_key=' . $this->api_key . '&token=' . $this->token . '&version=1.0&format=json', 'GET');
		$result = $request->execute();
		if ($result !== true) throw new Exception('Invalid API Key, Drop Name or Password.');
		$respose = json_decode($request->get_response_body());
		if (isset($response->result) && $response->result == 'Failure') {
			throw new Exception($response->message);
		}
                return true;
	}

	/**
	 * get assets list
	 *
	 * @access public
	 */
	public function get_assets()
	{
		$request = new RemoteRequest($this->base_url . $this->drop_name . '/assets?api_key=' . $this->api_key . '&token=' . $this->token . '&version=1.0&format=json', 'GET');
		$result = $request->execute();
		if ($result !== true) throw new Exception('Invalid API Key, Drop Name or Password.');
                $response = json_decode($request->get_response_body());
		if (isset($response->result) && $response->result == 'Failure') {
			throw new Exception($response->message);
		}
		return $response;
	}

	/**
	 * upload
	 *
	 * @access public
	 */
	public function upload($filename, $override_filename = null)
	{
		$request = new MyRemoteRequest($this->upload_url, 'POST');
		$request->set_postdata('version', '1.0');
		$request->set_postdata('api_key', $this->api_key);
		$request->set_postdata('token', $this->token);
		$request->set_postdata('drop_name', $this->drop_name);
		$request->set_file('file', $filename, null, $override_filename);
		$result = $request->execute();
		if ($result !== true) throw new Exception('Invalid API Key, Drop Name or Password.');
                $response = json_decode($request->get_response_body());
		if (isset($response->result) && $response->result == 'Failure') {
			throw new Exception($response->message);
		}
		return $response;
	}
}

class MyRemoteRequest
{
	private $method = 'GET';
	private $url;
	private $params = array();
	private $headers = array();
	private $postdata = array();
	private $files = array();
	private $body = '';
	private $timeout = 180;
	private $processor = NULL;
	private $executed = FALSE;
	
	private $response_body = '';
	private $response_headers = '';
	
	private $user_agent = 'Habari'; // TODO add version to that (Habari/0.1.4) 
	
	/**
	 * @param string $url URL to request
	 * @param string $method Request method to use (default 'GET')
	 * @param int $timeuot Timeout in seconds (default 180)
	 */
	public function __construct( $url, $method = 'GET', $timeout = 180 )
	{
		$this->method = strtoupper( $method );
		$this->url = $url;
		$this->set_timeout( $timeout );
		$this->add_header( array( 'User-Agent' => $this->user_agent ) );
		
		// can't use curl's followlocation in safe_mode with open_basedir, so
		// fallback to srp for now
		if ( function_exists( 'curl_init' )
			 && ! ( ini_get( 'safe_mode' ) && ini_get( 'open_basedir' ) ) ) {
			$this->processor = new MyCURLRequestProcessor;
		}
		else {
			$this->processor = new SocketRequestProcessor;
		}
	}
	
	/**
	 * DO NOT USE THIS FUNCTION.
	 * This function is only to be used by the test case for RemoteRequest!
	 */
	public function __set_processor( $processor )
	{
		$this->processor = $processor;
	}
	
	/**
	 * Add a request header.
	 * @param mixed $header The header to add, either as a string 'Name: Value' or an associative array 'name'=>'value'
	 */
	public function add_header( $header )
	{
		if ( is_array( $header ) ) {
			$this->headers = array_merge( $this->headers, $header );
		}
		else {
			list( $k, $v )= explode( ': ', $header );
			$this->headers[$k]= $v;
		}
	}
	
	/**
	 * Add a list of headers.
	 * @param array $headers List of headers to add.
	 */
	public function add_headers( $headers )
	{
		foreach ( $headers as $header ) {
			$this->add_header( $header );
		}
	}
	
	/**
	 * Set the request body.
	 * Only used with POST requests, will raise a warning if used with GET.
	 * @param string $body The request body.
	 */
	public function set_body( $body )
	{
		if ( $this->method !== 'POST' )
			return Error::raise( _t('Trying to add a request body to a non-POST request'), E_USER_WARNING );
		
		$this->body = $body;
	}
	
	/**
	 * Set the request query parameters (i.e., the URI's query string).
	 * Will be merged with existing query info from the URL.
	 * @param array $params
	 */
	public function set_params( $params )
	{
		if ( ! is_array( $params ) )
			$params = parse_str( $params );
		
		$this->params = $params;
	}
	
	/**
	 * Set the timeout.
	 * @param int $timeout Timeout in seconds
	 */
	public function set_timeout( $timeout )
	{
		$this->timeout = $timeout;
		return $this->timeout;
	}
	
	/**
	 * set postdata
	 *
	 * @access public
	 * @param mixed $name
	 * @param string $value
	 */
	public function set_postdata($name, $value = null)
	{
		if (is_array($name)) {
			$this->postdata = array_merge($this->postdata, $name);
		} else {
			$this->postdata[$name] = $value;
		}
	}

	/**
	 * set file
	 *
	 * @access public
	 * @param string $name
	 * @param string $filename
	 * @param string $content_type
	 */
	public function set_file($name, $filename, $content_type = null, $override_filename = null)
	{
		if (!file_exists($filename)) {
			return Error::raise(sprintf(_t('File %s not found.'), $filename), E_USER_WARNING);
		}
		if (empty($content_type)) $content_type = 'application/octet-stream';
		$this->files[$name] = array('filename' => $filename, 'content_type' => $content_type, 'override_filename' => $override_filename);
		$this->headers['Content-Type'] = 'multipart/form-data';
	}

	/**
	 * A little housekeeping.
	 */
	private function prepare()
	{
		// remove anchors (#foo) from the URL
		$this->url = $this->strip_anchors( $this->url );
		// merge query params from the URL with params given
		$this->url = $this->merge_query_params( $this->url, $this->params );
		
		if ( $this->method === 'POST' ) {
			if ( ! isset( $this->headers['Content-Type'] ) || $this->headers['Content-Type'] == 'application/x-www-form-urlencoded') {
				// TODO should raise a warning
				$this->add_header( array( 'Content-Type' => 'application/x-www-form-urlencoded' ) );

				$this->body = http_build_query($this->postdata, '', '&');
			} elseif ($this->headers['Content-Type'] == 'multipart/form-data') {
				$boundary = md5(Utils::nonce());
				$this->headers['Content-Type'] .= '; boundary=' . $boundary;

				$parts = array();
				@reset($this->postdata);
				while (list($name, $value) = @each($this->postdata)) {
					$parts[] = "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n{$value}\r\n";
				}

				@reset($this->files);
				while (list($name, $fileinfo) = @each($this->files)) {
					$filename = basename($fileinfo['filename']);
					if (!empty($fileinfo['override_filename'])) $filename = $fileinfo['override_filename'];
					$part = "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n";
					$part .= "Content-Type: {$fileinfo['content_type']}\r\n\r\n";
					$part .= file_get_contents($fileinfo['filename']) . "\r\n";
					$parts[] = $part;
				}
				$this->body = "--{$boundary}\r\n" . join("--{$boundary}\r\n", $parts) . "--{$boundary}--\r\n";
			}
			$this->add_header( array( 'Content-Length' => strlen( $this->body ) ) );
		}
	}
	
	/**
	 * Actually execute the request.
	 * On success, returns TRUE and populates the response_body and response_headers fields.
	 * On failure, throws error.
	 */
	public function execute()
	{
		$this->prepare();
		$result = $this->processor->execute( $this->method, $this->url, $this->headers, $this->body, $this->timeout );
		
		if ( $result && ! Error::is_error( $result ) ) { // XXX exceptions?
			$this->response_headers = $this->processor->get_response_headers();
			$this->response_body = $this->processor->get_response_body();
			$this->executed = TRUE;
			
			return TRUE;
		}
		else {
			// actually, processor->execute should throw an Error which would bubble up
			// we need a new Error class and error handler for that, though
			$this->executed = FALSE;
			
			return $result;
		}
	}
	
	public function executed() {
		return $this->executed;
	}
	
	/**
	 * Return the response headers. Raises a warning and returns '' if the request wasn't executed yet.
	 */
	public function get_response_headers()
	{
		if ( !$this->executed )
			return Error::raise( _t('Trying to fetch response headers for a pending request.'), E_USER_WARNING );
		
		return $this->response_headers;
	}
	
	/**
	 * Return the response body. Raises a warning and returns '' if the request wasn't executed yet.
	 */
	public function get_response_body()
	{
		if ( !$this->executed )
			return Error::raise( _t('Trying to fetch response body for a pending request.'), E_USER_WARNING );
		
		return $this->response_body;
	}
	
	/**
	 * Remove anchors (#foo) from given URL.
	 */
	private function strip_anchors( $url )
	{
		return preg_replace( '/(#.*?)?$/', '', $url );
	}
	
	/**
	 * Call the filter hook.
	 */
	private function __filter( $data, $url )
	{
		return Plugins::filter( 'remoterequest', $data, $url );
	}
	
	/**
	 * Merge query params from the URL with given params.
	 * @param string $url The URL
	 * @param string $params An associative array of parameters.
	 */
	private function merge_query_params( $url, $params )
	{
		$urlparts = InputFilter::parse_url( $url );
		
		if ( ! isset( $urlparts['query'] ) ) {
			$urlparts['query']= '';
		}
		
		if ( ! is_array( $params ) ) {
			parse_str( $params, $params );
		}
		
		$urlparts['query']= http_build_query( array_merge( Utils::get_params( $urlparts['query'] ), $params ), '', '&' );
		
		return InputFilter::glue_url( $urlparts );
	}
	
	/**
	 * Static helper function to quickly fetch an URL, with semantics similar to
	 * PHP's file_get_contents. Does not support 
	 * 
	 * Returns the content on success or FALSE if an error occurred.
	 * 
	 * @param string $url The URL to fetch
	 * @param bool $use_include_path whether to search the PHP include path first (unsupported)
	 * @param resource $context a stream context to use (unsupported)
	 * @param int $offset how many bytes to skip from the beginning of the result
	 * @param int $maxlen how many bytes to return
	 * @return string description
	 */
	public static function get_contents( $url, $use_include_path = FALSE, $context = NULL, $offset =0, $maxlen = -1 )
	{
		$rr = new RemoteRequest( $url );
		if ( $rr->execute() === TRUE) {
			return ( $maxlen != -1
				? substr( $rr->get_response_body(), $offset, $maxlen )
				: substr( $rr->get_response_body(), $offset ) );
		}
		else {
			return FALSE;
		}
	}
	
}

interface MyRequestProcessor
{
	public function execute( $method, $url, $headers, $body, $timeout );

	public function get_response_body();
	public function get_response_headers();
}

class MyCURLRequestProcessor implements MyRequestProcessor
{
	private $response_body = '';
	private $response_headers = '';
	private $executed = false;
	
	private $can_followlocation = true;
	
	/**
	 * Maximum number of redirects to follow.
	 */
	private $max_redirs = 5;
	
	/**
	 * Temporary buffer for headers.
	 */
	private $_headers = '';
	
	public function __construct()
	{
		if ( ini_get( 'safe_mode' ) || ini_get( 'open_basedir' ) ) {
			$this->can_followlocation = false;
		}
	}
	
	public function execute( $method, $url, $headers, $body, $timeout )
	{
		$merged_headers = array();
		foreach ( $headers as $k => $v ) {
			$merged_headers[]= $k . ': ' . $v;
		}
		
		$ch = curl_init();
		
		curl_setopt( $ch, CURLOPT_URL, $url ); // The URL.
		curl_setopt( $ch, CURLOPT_HEADERFUNCTION, array(&$this, '_headerfunction' ) ); // The header of the response.
		curl_setopt( $ch, CURLOPT_MAXREDIRS, $this->max_redirs ); // Maximum number of redirections to follow.
		if ( $this->can_followlocation ) {
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); // Follow 302's and the like.
		}
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // Return the data from the stream.
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $merged_headers ); // headers to send
		
		if ( $method === 'POST' ) {
			curl_setopt( $ch, CURLOPT_POST, true ); // POST mode.
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		} else {
			curl_setopt( $ch, CURLOPT_CRLF, true ); // Convert UNIX newlines to \r\n.
		}
		
		$body = curl_exec( $ch );
		
		if ( curl_errno( $ch ) !== 0 ) {
			return Error::raise( sprintf( _t('%s: CURL Error %d: %s'), __CLASS__, curl_errno( $ch ), curl_error( $ch ) ),
				E_USER_WARNING );
		}
		
		if ( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) !== 200 ) {
			return Error::raise( sprintf( _t('Bad return code (%1$d) for: %2$s'), 
				curl_getinfo( $ch, CURLINFO_HTTP_CODE ), 
				$url ),
				E_USER_WARNING
			);
		}
		
		curl_close( $ch );
		
		// this fixes an E_NOTICE in the array_pop
		$tmp_headers = explode("\r\n\r\n", substr( $this->_headers, 0, -4 ) );
		
		$this->response_headers = array_pop( $tmp_headers );
		$this->response_body = $body;
		$this->executed = true;
		
		return true;
	}
	
	public function _headerfunction( $ch, $str )
	{
		$this->_headers.= $str;
		
		return strlen( $str );
	}
	
	public function get_response_body()
	{
		if ( ! $this->executed ) {
			return Error::raise( _t('Request did not yet execute.') );
		}
		
		return $this->response_body;
	}
	
	public function get_response_headers()
	{
		if ( ! $this->executed ) {
			return Error::raise( _t('Request did not yet execute.') );
		}
		
		return $this->response_headers;
	}
}
?>