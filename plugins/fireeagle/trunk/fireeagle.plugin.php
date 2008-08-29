<?php
/**
 * Fire Eagle
 * Fire Eagle for Habari
 *
 * @package fireeagle
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-fireeagle
 */
require('lib/fireeagle.php');
class FireEagle extends Plugin
{
    private $consumer_key = 'GKDcUJOEuDvX';
    private $consumer_secret = 'r4MCmNPKhXbf7tRlsXu7dbsgislL6uns';
	private $level_zoom_map = array(
				0 => 16,	// exact
				1 => 14,	// postal
				3 => 11,	// city
				4 => 8, 	// region
				5 => 5, 	// state
				6 => 2, 	// country
	);

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Fire Eagle',
			'version' => '0.01-alpha',
			'url' => 'http://ayu.commun.jp/habari-fireeagle',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'Fire Eagle for Habari',
			'guid' => '84708e24-6de5-11dd-b14a-001b210f913f'
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

		Options::set('fireeagle__refresh_interval', 3600);
		Modules::add(_t('Fire Eagle', 'fireeagle'));
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('fireeagle');

		$this->add_template('fireeagle', dirname(__FILE__) . '/templates/fireeagle.php');
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add('Fire Eagle', $this->info->guid, $this->info->version);
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
            $form->on_success(array($this, 'on_success'));
			$refresh_interval = $form->append('text', 'refresh_interval', 'fireeagle__refresh_interval', _t('Refresh Interval (sec): ', 'fireeagle'));
            $refresh_interval->add_validator('validate_regex', '/^[0-9]+$/');
            $form->append('submit', 'save', _t('Save'));
			$form->out();
		} elseif ($action == _t('Authorize', 'fireeagle')) {
			// get request token
            $fireeagle = new FireEagleAPI($this->consumer_key, $this->consumer_secret);
            $token = $fireeagle->getRequestToken();

			if (!$token || empty($token['oauth_token'])) {
				echo 'Invalid Response';
				return;
			}

			$_SESSION['fireeagle']['req_token'] = $token['oauth_token'];
			$_SESSION['fireeagle']['req_token_secret'] = $token['oauth_token_secret'];
			$_SESSION['fireeagle']['state'] = 1;

            $oauth_callback = URL::get('admin', array('page' => 'plugins', 'configure' => $plugin_id, 'configaction' => '_callback')) . '#plugin_' . $plugin_id;
            ob_end_clean();
			header('Location: ' . $fireeagle->getAuthorizeURL($token) . '&oauth_callback=' . urlencode($oauth_callback));
            exit;
		} elseif ($action == _t('De-Authorize', 'fireeagle')) {
			Options::set('fireeagle__access_token_' . User::identify()->id, '');
			Options::set('fireeagle__access_token_secret_' . User::identify()->id, '');

			echo 'Fire Eagle De-authorization successfully.';
		} elseif ($action == '_callback') {
            if (empty($_GET['oauth_token']) || $_GET['oauth_token'] != $_SESSION['fireeagle']['req_token']) {
				echo 'Invalid Token';
				return;
			}

			// get access token
			$fireeagle = new FireEagleAPI($this->consumer_key, $this->consumer_secret,
				$_SESSION['fireeagle']['req_token'], $_SESSION['fireeagle']['req_token_secret']);
			$token = $fireeagle->getAccessToken();
			if (!$token || empty($token['oauth_token'])) {
				echo 'Invalid Response';
				return;
			}

			Options::set('fireeagle__access_token_' . User::identify()->id, $token['oauth_token']);
			Options::set('fireeagle__access_token_secret_' . User::identify()->id, $token['oauth_token_secret']);

			echo 'Fire Eagle Authorization successfully.';
        }
	}

    public function on_success($form)
    {
        $form->save();

		$params = array(
			'name' => 'fireeagle:refresh',
			'callback' => 'fireeagle_refresh',
			'increment' => Options::get('fireeagle__refresh_interval'),
			'description' => 'Refreshing Fire Eagle Location'
		);
        CronTab::delete_cronjob($params['name']);
        CronTab::add_cron($params);

		return false;
    }

	/**
	 * action: admin_header
	 *
	 * @access public
	 * @param object $theme
	 * @return void
	 */
	public function action_admin_header($theme)
	{
		if ($theme->page != 1) return; // why dashboard is 1?
		Stack::add('admin_header_javascript', $this->get_url() . '/js/admin.js');
		Stack::add('admin_stylesheet', array($this->get_url() . '/css/admin.css', 'screen'));
	}

	/**
	 * action: before_act_admin_ajax
	 *
	 * @access public
	 * @return void
	 */
	public function action_before_act_admin_ajax()
	{
		$handler_vars = Controller::get_handler_vars();
		switch ($handler_vars['context']) {
		case 'fireeagle_update':
			$user = User::identify();
			$access_token = Options::get('fireeagle__access_token_' . $user->id);
			$access_token_secret = Options::get('fireeagle__access_token_secret_' . $user->id);
			if (empty($access_token) || empty($access_token_secret)) {
				echo json_encode(array('errorMessage' => _t('Authorize is not done!', 'fireeagle')));
				die();
			}

			$fireeagle = new FireEagleAPI($this->consumer_key, $this->consumer_secret, $access_token, $access_token_secret);
			try {
				$result = $fireeagle->update(array('address' => $handler_vars['location']));
			} catch (FireEagleException $e) {
				echo json_encode(array('errorMessage' => $e->getMessage()));
				die();
			}
			if (!is_object($result) || $result->stat != 'ok') {
				echo json_encode(array('errorMessage' => _t('Update failed.', 'fireeagle')));
				die();
			}

			// refresh location
			if (!$this->update((int)$user->id)) {
				echo json_encode(array('errorMessage' => _t('Update failed.', 'fireeagle')));
				die();
			}

			$location = '';
			if (isset($user->info->fireeagle_location)) {
				$location = $user->info->fireeagle_location;
			}

			echo json_encode(array('location' => $location, 'message' => sprintf(_t('Your present place was updated to "%s".', 'fireeagle'), $location)));
			die();
		default:
			break;
		}
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
			$access_token = Options::get('fireeagle__access_token_' . User::identify()->id);
			$access_token_secret = Options::get('fireeagle__access_token_secret_' . User::identify()->id);
			if (empty($access_token) || empty($access_token_secret)) {
	            $actions[] = _t('Authorize', 'fireeagle');
			} else {
	            $actions[] = _t('De-Authorize', 'fireeagle');
			}
		}
		return $actions;
	}

	/**
	 * filter: dash_modules
	 *
	 * @access public
	 * @param array $modules
	 * @return array
	 */
	public function filter_dash_modules($modules)
	{
		$modules[] = _t('Fire Eagle', 'fireeagle');
		$this->add_template('dash_fireeagle', dirname(__FILE__) . '/dash_fireeagle.php');
		return $modules;
	}

	/**
	 * filter: dash_module_fire_eagle
	 *
	 * @access public
	 * @param array $module
	 * @param string $module_id
	 * @param object $theme
	 * @return array
	 */
	public function filter_dash_module_fire_eagle($module, $module_id, $theme)
	{
		$module['title'] = _t('Fire Eagle', 'fireeagle') . '<img src="' . $this->get_url() . '/img/fireeagle.png" alt= "Fire Eagle" />';

		$form = new FormUI('dash_fireeagle');
		$form->append('text', 'location', 'null:unused', _t('Location: ', 'fireeagle'));
		$user = User::identify();
		if (isset($user->info->fireeagle_location)) {
			$form->location->value = $user->info->fireeagle_location;
		}
		$form->append('submit', 'submit', _t('Update', 'fireeagle'));
		$form->properties['onsubmit'] = 'fireeagle.update(); return false;';
		$theme->fireeagle_form = $form->get();

		$module['content'] = $theme->fetch('dash_fireeagle');
		return $module;
	}

	/**
	 * filter: fireeagle_refresh
	 *
	 * @access public
	 * @param boolean $result
	 * @return boolean
	 */
	public function filter_fireeagle_refresh($result)
	{
		$users = Users::get_all();
		foreach ($users as $user) {
			$location = $this->update((int)$user->id);
			if (!$location) continue;
		}
		$result = true;

		return $result;
	}

	/**
	 * theme: show_fireeagle
	 *
	 * @access public
	 * @param object $theme
	 * @param mixed $who user ID, username, or e-mail address
	 * @return string
	 */
	public function theme_show_fireeagle($theme, $who)
	{
		$user = User::get($who);
		if (!$user) return '';

		$theme->fireeagle_longitude = $user->info->fireeagle_longitude;
		$theme->fireeagle_latitude = $user->info->fireeagle_latitude;
		$theme->fireeagle_level = $user->info->fireeagle_level;

		if (isset($user->info->fireeagle_location)) {
			$theme->fireeagle_location = $user->info->fireeagle_location;
		}

		$theme->zoom = 1;
		if (isset($this->level_zoom_map[$user->info->fireeagle_level])) {
			$theme->zoom = $this->level_zoom_map[$user->info->fireeagle_level];
		}

		return $theme->fetch('fireeagle');
	}

	/**
	 * refresh location
	 *
	 * @access private
	 * @param mixed $who user ID, username, or e-mail address
	 * @return boolean
	 */
	private function update($who)
	{
		$user = User::get($who);
		if (!$user) return false;

		$access_token = Options::get('fireeagle__access_token_' . $user->id);
		$access_token_secret = Options::get('fireeagle__access_token_secret_' . $user->id);
		if (empty($access_token) || empty($access_token_secret)) return;

		$fireeagle = new FireEagleAPI($this->consumer_key, $this->consumer_secret, $access_token, $access_token_secret);
		try {
			$result = $fireeagle->user();
		} catch (FireEagleException $e) {
			return false;
		}

		if (!isset($result->user->best_guess)) return false;
		$location = $result->user->best_guess;

		$user->info->fireeagle_longitude = $location->longitude;
		$user->info->fireeagle_latitude = $location->latitude;
		$user->info->fireeagle_level = $location->level;
		if (isset($location->name)) {
			$user->info->fireeagle_location = $location->name;
		} else {
			$user->info->fireeagle_location = '';
		}
		$user->info->commit();
		Plugins::act('fireeagle_after_update', $user);

		return true;
    }
}
?>