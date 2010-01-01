<?php

/**
 * Linkoid Plugin
 *
 **/

class Linkoid extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Linkoid',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Displays posts that are tagged with a specific tag in a different place using a separate template',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Linkoid', '34257e00-3942-11dd-ae16-0800200c9a66', $this->info->version );
	}

	/**
	* Add actions to the plugin page for this plugin
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()){
			$actions[] = 'Configure';
		}

		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()){
			switch ($action){
				case 'Configure' :
					$ui = new FormUI(strtolower(get_class($this)));
					$links = $ui->append('text', 'count', 'linkoid__count', 'Number of items to be shown');
					//required
					$links->add_validator( 'validate_required' )->add_validator( 'validate_regex', '%^[1-9][0-9]*$%', 'Number of items shown must be a number; 1 or more.' );
					$tag_control = $ui->append('select', 'show', 'linkoid__show', 'Tag that will be shown via linkoid command');
					$tags = DB::get_results( 'SELECT tag_slug, tag_text FROM {tags} ORDER BY tag_text ASC' );
					$options = array();
					foreach($tags as $tag) {
						$options[$tag->tag_slug] = $tag->tag_text;
					}
					$tag_control->options = $options;
					$ui->append( 'submit', 'save', _t('Save') );

					$ui->out();
					break;
			}
		}
	}

	/**
	 * Respond to call to $theme->linkoid() in template
	 *
	 * @param string $return The return value to the template function (passed through other potential plugin calls)
	 * @param Theme $theme The theme object
	 * @param string $tag An optional tag to use instead of the one defined in the plugin options.
	 * @return string The return value to the template function
	 */
	public function theme_linkoid( $theme, $tag = null )
	{
		if(!isset($tag)) {
			$tag = Options::get( 'linkoid__show' );
		}
		$linkoids = Posts::get(array('tag_slug'=>$tag, 'limit'=>Options::get( 'linkoid__count' ) ) );

		$theme->linkoids = $linkoids;
		return $theme->fetch( 'linkoid' );
	}

	/**
	 * On plugin init, add the template included with this plugin to the availalbe templates in the theme
	 */
	public function action_init()
	{
		$this->add_template('linkoid', dirname(__FILE__) . '/linkoid.php');
	}

	/**
	 * Prevent posts with the selected tag from appearing anywhere but the tag listing and on single post requests
	 *
	 * @param array $where_filters An array of parameters being sent to Posts::get()
	 * @return array The potentially altered parameter set.
	 */
	public function filter_template_where_filters( $where_filters )
	{
		if( ! (isset($where_filters['tag']) || isset($where_filters['tag_slug']) || isset($where_filters['slug'])) )  {
			$where_filters['not:tag'] = Options::get('linkoid__show');
		}
		return $where_filters;
	}
}

?>
