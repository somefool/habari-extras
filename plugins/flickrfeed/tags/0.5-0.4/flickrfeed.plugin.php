<?php
/**
 * FlickrFeed Plugin: Show the images from Flickr feed
 * Usage: <?php $theme->flickrfeed(); ?>
 */

class FlickrFeed extends Plugin
{
	private $config = array();
	private $class_name = '';
	private $default_options = array(
		'type' => 'user',
		'user_id' => '',
		'num_item' => '6',
		'size' => 'square',
		'tags' => '',
		'cache_expiry' => '1800',
	);

	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'FlickrFeed',
			'version' => '0.5-0.4',
			'url' => 'http://code.google.com/p/bcse/wiki/FlickrFeed',
			'author' => 'Joel Lee',
			'authorurl' => 'http://blog.bcse.info/',
			'license' => 'Apache License 2.0',
			'description' => 'Display your latest photos on your blog.',
			'copyright' => '2008'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add('FlickrFeed', '165a2150-59ae-11dd-ae16-0800200c9a66', $this->info->version);
	}

	/**
	 * Add actions to the plugin page for this plugin
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The string id of a plugin, generated by the system
	 * @return array The array of actions to attach to the specified $plugin_id
	 **/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id === $this->plugin_id()) {
			$actions[] = _t('Configure', $this->class_name);
		}

		return $actions;
	}

	/**
	 * Respond to the user selecting an action on the plugin page
	 * @param string $plugin_id The string id of the acted-upon plugin
	 * @param string $action The action string supplied via the filter_plugin_config hook
	 **/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id === $this->plugin_id()) {
			switch ($action) {
				case _t('Configure', $this->class_name):
					$ui = new FormUI($this->class_name);

					$type = $ui->append('select', 'type', 'option:' . $this->class_name . '__type', _t('Photostream Type', $this->class_name));
					$type->options = array(
						'public' => _t('Public photos & video', $this->class_name),
						'user' => _t('Public photos & video from you', $this->class_name),
						'friends' => _t('Your friends’ photostream', $this->class_name),
						'faves' => _t('Public favorites from you', $this->class_name),
						'group' => _t('Group pool', $this->class_name)
					);
					$type->add_validator('validate_required');

					$user_id = $ui->append('text', 'user_id', 'option:' . $this->class_name . '__user_id', _t('Flickr ID (You can get it from <a href="http://idgettr.com">idGettr</a>)', $this->class_name));
					$user_id->add_validator('validate_flickr_id');

					$num_item = $ui->append('text', 'num_item', 'option:' . $this->class_name . '__num_item', _t('&#8470; of Photos', $this->class_name));
					$num_item->add_validator('validate_uint');
					$num_item->add_validator('validate_required');

					$size = $ui->append('select', 'size', 'option:' . $this->class_name . '__size', _t('Photo Size', $this->class_name));
					$size->options = array(
						'square' => _t('Square', $this->class_name),
						'thumbnail' => _t('Thumbnail', $this->class_name),
						'small' => _t('Small', $this->class_name),
						'medium' => _t('Medium', $this->class_name),
						'large' => _t('Large', $this->class_name),
						'original' => _t('Original', $this->class_name)
					);
					$size->add_validator('validate_required');

					$tags = $ui->append('text', 'tags', 'option:' . $this->class_name . '__tags', _t('Tags (comma separated, no space)', $this->class_name));

					$cache_expiry = $ui->append('text', 'cache_expiry', 'option:' . $this->class_name . '__cache_expiry', _t('Cache Expiry (in seconds)', $this->class_name));
					$cache_expiry->add_validator('validate_uint');
					$cache_expiry->add_validator('validate_required');

					// When the form is successfully completed, call $this->updated_config()
					$ui->append('submit', 'save', _t('Save', $this->class_name));
					$ui->set_option('success_message', _t('Options saved', $this->class_name));
					$ui->out();
					break;
			}
		}
	}

	public function validate_uint($value)
	{
		if (!ctype_digit($value) || strstr($value, '.') || $value < 0) {
			return array(_t('This field must be positive integer.', $this->class_name));
		}
		return array();
	}

	public function validate_flickr_id($value)
	{
		if (empty($value) && in_array($this->config['type'], array('user', 'group'))) {
			return array(_t('A value for this field is required while type is not ‘Public’.', $this->class_name));
		}
		return array();
	}

	private function plugin_configured($params = array())
	{
		if (empty($params['type']) ||
			empty($params['user_id']) ||
			empty($params['num_item']) ||
			empty($params['size']) ||
			empty($params['cache_expiry'])) {
			return false;
		}
		return true;
	}

	private function load_feeds($params = array())
	{
		$cache_name = $this->class_name . '__' . md5(serialize($params));

		if (Cache::has($cache_name)) {
			// Read from cache
			return Cache::get($cache_name);
		}
		else {
			switch ($params['type']) {
				case 'user':
					$url = 'http://api.flickr.com/services/feeds/photos_public.gne?id=' . $params['user_id'] . '&tags=' . $params['tags'] . '&format=php_serial';
					break;
				case 'friends':
					$url = 'http://api.flickr.com/services/feeds/photos_friends.gne?user_id=' . $params['user_id'] . '&format=php_serial';
					break;
				case 'faves':
					$url = 'http://api.flickr.com/services/feeds/photos_faves.gne?id=' . $params['user_id'] . '&format=php_serial';
					break;
				case 'group':
					$url = 'http://api.flickr.com/services/feeds/groups_pool.gne?id=' . $params['user_id']. '&format=php_serial';
					break;
				default:
					$url = 'http://api.flickr.com/services/feeds/photos_public.gne?tags=' . $params['tags'] . '&format=php_serial';
					break;
			}

			try {
				// Get PHP serialized object from Flickr
				$call = new RemoteRequest($url);
				$call->set_timeout(5);
				$result = $call->execute();
				if (Error::is_error($result)) {
					throw Error::raise(_t('Unable to contact Flickr.', $this->class_name));
				}

				// Unserialize and manipulate the data
				$flickrfeed = unserialize($call->get_response_body());
				$flickrfeed = array_slice($flickrfeed['items'], 0, $params['num_item']);

				// Photo size
				for ($i = 0; $i < $params['num_item']; $i++) {
					switch ($params['size']) {
						case 'thumbnail':
							$flickrfeed[$i]['image_url'] = str_replace('_m.jpg', '_t.jpg', $flickrfeed[$i]['m_url']);
							break;
						case 'small':
							$flickrfeed[$i]['image_url'] = $flickrfeed[$i]['m_url'];
							break;
						case 'medium':
							$flickrfeed[$i]['image_url'] = $flickrfeed[$i]['l_url'];
							break;
						case 'large':
							$flickrfeed[$i]['image_url'] = str_replace('_m.jpg', '_b.jpg', $flickrfeed[$i]['m_url']);
							break;
						case 'original':
							$flickrfeed[$i]['image_url'] = $flickrfeed[$i]['photo_url'];
							break;
						default:
							$flickrfeed[$i]['image_url'] = $flickrfeed[$i]['t_url'];
							break;
					}
				}

				// Do cache
				Cache::set($cache_name, $flickrfeed, $params['cache_expiry']);

				return $flickrfeed;
			}
			catch (Exception $e) {
				return $e->getMessage();
			}
		}
	}

	/**
	 * Add Flickr images to the available template vars
	 * @param Theme $theme The theme that will display the template
	 **/
	public function theme_flickrfeed($theme, $params = array())
	{
		$params = array_merge($this->config, $params);

		if ($this->plugin_configured($params)) {
			$theme->flickrfeed = $this->load_feeds($params);
		}
		else {
			$theme->flickrfeed = _t('FlickrFeed Plugin is not configured properly.', $this->class_name);
		}

		return $theme->fetch('flickrfeed');
	}

	/**
	 * On plugin activation, set the default options
	 */
	public function action_plugin_activation($file)
	{
		if (realpath($file) === __FILE__) {
			$this->class_name = strtolower(get_class($this));
			foreach ($this->default_options as $name => $value) {
				$current_value = Options::get($this->class_name . '__' . $name);
				if (is_null($current_value)) {
					Options::set($this->class_name . '__' . $name, $value);
				}
			}
		}
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->class_name = strtolower(get_class($this));
		foreach ($this->default_options as $name => $value) {
			$this->config[$name] = Options::get($this->class_name . '__' . $name);
		}
		$this->load_text_domain($this->class_name);
		$this->add_template('flickrfeed', dirname(__FILE__) . '/flickrfeed.php');
	}
}
?>
