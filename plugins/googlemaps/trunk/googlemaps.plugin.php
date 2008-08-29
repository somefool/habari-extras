<?php
/**
 * GoogleMaps
 * easily/quickly insert Google Maps into your posts.
 *
 * @package googlemaps
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://ayu.commun.jp/habari-googlemaps
 */
class GoogleMaps extends Plugin
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
			'name' => 'Google Maps',
			'version' => '0.01-alpha',
			'url' => 'http://ayu.commun.jp/habari-googlemaps',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => 'easily/quickly insert Google Maps into your posts.',
			'guid' => '14c8414f-6cdf-11dd-b14a-001b210f913f'
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

		Options::set('googlemaps__api_key', '');
		Options::set('googlemaps__map_width', 500);
		Options::set('googlemaps__map_height', 300);
		Options::set('googlemaps__streetview_width', 500);
		Options::set('googlemaps__streetview_height', 200);
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('googlemaps');

		$api_key = Options::get('googlemaps__api_key');
		if (empty($api_key)) return;
		Stack::add('template_header_javascript', 'http://www.google.com/jsapi?key=' . $api_key);
        Stack::add('template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery');
		Stack::add('template_header_javascript', $this->get_url() . '/js/googlemaps.js');
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add('Google Maps', $this->info->guid, $this->info->version);
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
			$form= new FormUI( strtolower(get_class($this)));
			$form->append('text', 'api_key', 'googlemaps__api_key', _t('API Key: ', 'googlemaps'));
            $form->append('static', 'static1', 'don\'t have API Key? <a href="http://code.google.com/apis/maps/signup.html" target="_blank">Signup!</a>');
			$map_width = $form->append('text', 'map_width', 'googlemaps__map_width', _t('Map Width: ', 'googlemaps'));
			$map_width->add_validator('validate_regex', '/^[0-9]+$/');
			$map_height = $form->append('text', 'map_height', 'googlemaps__map_height', _t('Map Height: ', 'googlemaps'));
			$map_height->add_validator('validate_regex', '/^[0-9]+$/');
			$streetview_width = $form->append('text', 'streetview_width', 'googlemaps__streetview_width', _t('Streetview Width: ', 'googlemaps'));
			$streetview_width->add_validator('validate_regex', '/^[0-9]+$/');
			$streetview_height = $form->append('text', 'streetview_height', 'googlemaps__streetview_height', _t('Streetview Height: ', 'googlemaps'));
			$streetview_height->add_validator('validate_regex', '/^[0-9]+$/');
			$form->append('submit', 'save', _t('Save'));
			$form->out();
		}
	}

	/**
	 * action: admin_header
	 *
	 * @access public
	 * @param object $theme
	 * @return void
	 */
	public function action_admin_header( $theme )
	{
		$handler_vars = Controller::get_handler_vars();
		if (!isset($handler_vars['page']) || $handler_vars['page'] != 'publish') return;
		$api_key = Options::get('googlemaps__api_key');
		if (empty($api_key)) return;
		Stack::add('admin_header_javascript', 'http://www.google.com/jsapi?key=' . $api_key);
		Stack::add('admin_header_javascript', $this->get_url() . '/js/googlemaps_admin.js');
	}

	/**
	 * action: template_header
	 *
	 * @access public
	 * @param object $theme
	 * @return void
	 */
	public function action_template_header($theme)
	{
?>
<script type="text/javascript">
var habari_googlemaps = {
	map_width: <?php Options::out('googlemaps__map_width'); ?>,
	map_height: <?php Options::out('googlemaps__map_height'); ?>,
	streetview_width: <?php Options::out('googlemaps__streetview_width'); ?>,
	streetview_height: <?php Options::out('googlemaps__streetview_height'); ?>
};
</script>
<?php
	}

	/**
	 * action: form_publish
	 *
	 * @access public
	 * @param object $form
	 * @return void
	 */
	public function action_form_publish($form)
	{
		$container = $form->publish_controls->append('fieldset', 'googlemaps', _t('Google Maps'));
		ob_start();
?>
<div id="googlemaps" class="container" style="width: 600px;">
<p>
<input type="text" id="googlemaps_address" name="googlemaps_address" />
<input type="button" id="googlemaps_search" value="<?php echo _t('Search'); ?>" />
<input type="button" id="googlemaps_streetview_toggle" value="<?php echo _t('Street View', 'googlemaps'); ?>" />
<input type="button" id="googlemaps_insert" value="<?php echo _t('Insert Map', 'googlemaps'); ?>" />
</p>
<div id="googlemaps_canvas"></div>
<div id="googlemaps_streetview_canvas" style="width: 600px; height: 300px;"></div>
</div>
<?php
		$container->append('static', 'static1', ob_get_contents());
		ob_end_clean();
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @param array $actions
	 * @param integer $plugin_id
	 * @return array
	 */
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}
}
?>