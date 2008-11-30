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
			'version' => '0.01-beta',
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
		habari.editor.insertSelection('<a href="' + fileobj.dropiosilo_url + '"><img src="' + fileobj.thumbnail_url + '" alt="' + fileobj.title + '" /></a>');
	}
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
			$props['dropiosilo_url'] = $assets[$i]->converted;
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
}
?>