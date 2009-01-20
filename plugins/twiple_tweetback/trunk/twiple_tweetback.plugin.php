<?php
/**
 * Twiple! Tweetback
 * adding Twiple! Tweetback to your posts.
 *
 * @package twiple_tweetback
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-twiple-tweetback
 * @link http://twiple.jp/
 */
class TwipleTweetback extends Plugin
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
			'name' => 'Twiple! Tweetback',
			'version' => '0.01',
			'url' => 'http://ayu.commun.jp/habari-twiple-tweetback',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'adding Twiple! Tweetback to your posts.',
			'guid' => '42604d65-e6ea-11dd-bd4c-001b210f913f',
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

		Options::set('twiple_tweetback__auto_insert', 1);
		Options::set('twiple_tweetback__default_style', 1);
		Options::set('twiple_tweetback__limit', 30);
		Options::set('twiple_tweetback__load_message', '');
		Options::set('twiple_tweetback__notweets_message', '');
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('twiple_tweetback');
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
			$form->append('checkbox', 'auto_insert', 'twiple_tweetback__auto_insert', _t('Auto Insert:', 'twiple_tweetback'));
			$form->append('checkbox', 'default_style', 'twiple_tweetback__default_style', _t('Default Style:', 'twiple_tweetback'));
			$limit = $form->append('text', 'limit', 'twiple_tweetback__limit', _t('Limit: ', 'twiple_tweetback'));
			$limit->add_validator('validate_regex', '/^[0-9]+$/');
			$form->append('text', 'load_message', 'twiple_tweetback__load_message', _t('Loading Message: ', 'twiple_tweetback'));
			$form->append('text', 'notweets_message', 'twiple_tweetback__notweets_message', _t('No Tweets Message: ', 'twiple_tweetback'));
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
			$actions[]= _t('Configure');
		}
		return $actions;
	}

	/**
	 * action: template_header
	 *
	 * @access public
	 * @return void
	 */
	public function action_template_header($theme)
	{
		if ($theme->request->display_entry == true || $theme->request->display_page == true) {
			$load_message = Options::get('twiple_tweetback__load_message');
			$notweets_message = Options::get('twiple_tweetback__notweets_message');
?>
<script type="text/javascript" src="http://static.twiple.jp/js/tweetback.js"></script>
<script type="text/javascript">
tweetback.options.default_style = <?php Options::out('twiple_tweetback__default_style'); ?>;
tweetback.options.limit = <?php Options::out('twiple_tweetback__limit'); ?>;
<?php if (!empty($load_message)): ?>
tweetback.options.load_message = '<?php echo htmlspecialchars($load_message, ENT_QUOTES); ?>';
<?php endif; ?>
<?php if (!empty($notweets_message)): ?>
tweetback.options.notweets_message = '<?php echo htmlspecialchars($notweets_message, ENT_QUOTES); ?>';
<?php endif; ?>
</script>
<?php
		}
	}

	/**
	 * filter: post_content_out
	 *
	 * @access public
	 * @return string
	 */
	public function filter_post_content_out($content, $post)
	{
		if (Options::get('twiple_tweetback__auto_insert') == 1) $content .= '<div id="tweetback"></div>';
		return $content;
	}
}
?>