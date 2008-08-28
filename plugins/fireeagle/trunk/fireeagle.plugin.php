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

		Options::set('fireeagle__refresh_interval', 600);
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
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add('Fire Eagle', '84708e24-6de5-11dd-b14a-001b210f913f', $this->info->version);
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
			$location = Plugins::filter('fireeagle_user', (int)$user->id);
			if (!$location) continue;

			$user->info->fireeagle_longitude = $location->longitude;
			$user->info->fireeagle_latitude = $location->latitude;
			$user->info->commit();
		}
		$result = true;

		return $result;
	}

	/**
	 * filter: fireeagle_user
	 *
	 * @access public
	 * @param mixed $who user ID, username, or e-mail address
	 * @return mixed
	 */
	public function filter_fireeagle_user($who)
	{
		$user = User::get($who);
		if (!$user) return false;

		$access_token = Options::get('fireeagle__access_token_' . $user->id);
		$access_token_secret = Options::get('fireeagle__access_token_secret_' . $user->id);
		if (empty($access_token) || empty($access_token_secret)) return;

		$fireeagle = new FireEagleAPI($this->consumer_key, $this->consumer_secret, $access_token, $access_token_secret);
		try {
			$result = $fireeagle->user();
			return $result->user->best_guess;
		} catch (FireEagleException $e) {
			return false;
		}
    }
}
?>