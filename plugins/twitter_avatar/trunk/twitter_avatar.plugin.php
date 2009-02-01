<?php
/**
 * Twitter Avatar
 * Twitter avatar plugin
 *
 * @package twitter_avatar
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://twitter.com/
 * @link http://gravater.com/
 */
class DropioSilo extends Plugin
{

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Twitter Avatar',
			'version' => '0.01-alpha',
			'url' => 'http://ayu.commun.jp/habari-twitter_avatar',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'Twitter avatar plugin',
			'guid' => '3d327504-f01b-11dd-bd4c-001b210f913f'
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

		Options::set('twitter_avatar__cache_expire', 24);
		Options::set('twitter_avatar__default_icon', '');
		Options::set('twitter_avatar__fallback_gravatar', true);
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('twitter_avatar');
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
			$form->append('text', 'cache_expire', 'twitter_avatar__cache_expire', _t('Cache Expire (hour):', 'twitter_avatar'));
			$form->append('text', 'default_icon', 'twitter_avatar__default_icon', _t('Default Icon URL:', 'twitter_avatar'));
			$form->append('checkbox', 'fallback_gravater', 'twitter_avatar__fallback_gravatar', _t('Fallback to Gravater: ', 'twitter_avatar'));
			$form->append('submit', 'save', _t('Save'));
			$form->out();
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
		}
		return $actions;
	}

	/**
	 * filter: commenct_twitter_avatar
	 *
	 * @param 
	 * @return string
	 */
	public function filter_comment_twitter_avatar($out, $comment)
	{
		if (empty($comment->email)) {
			$twitter_user = false;
		} elseif (Cache::has('twitter_avatar_' . $comment->email)) {
			$twitter_user = Cache::get('twitter_avatar_' . $comment->email);
		} else {
			$request = new RemoteRequest('http://twitter.com/users/show.json?email=' . $comment->email, 'GET', 5);
			$result = $request->execute();
			if ($result !== true) {
				$twitter_user = false;
			} else {
				$twitter_user = json_decode($request->get_response_body());
			}
			Cache::set('twitter_avatar_' . $comment->email, $twitter_user, Options::get('twitter_avatar__cache_expire') * 3600);
		}

		if ($twitter_user !== false) {
			return '<a href="http://twitter.com/' . $twitter_user->screen_name . '"><img src="' . $twitter_user->profile_image_url . '" class="twitter_avatar" alt="' . $twitter_user->screen_name . '" /></a>';
		} elseif (Options::get('twitter_avatar__fallback_gravatar')) {
			$query = array();
			$query['gravatar_id'] = md5(strtolower($comment->email));
			$default_icon = Options::get('twitter_avatar__default_icon');
			if (!empty($default_icon)) $query['default'] = $default_icon;

			return '<img src="http://www.gravatar.com/avatar.php?' . http_build_query($query) . '" class="twitter_avatar" alt="' . $comment->name . '" />';
		} else {
			$default_icon = Options::get('twitter_avatar__default_icon');
			if (!empty($default_icon)) {
				return '<img src="' . $default_icon . '" class="twitter_avatar" alt="' . $comment->name . '" />';
			} else {
				return '';
			}
		}
	}
}
?>