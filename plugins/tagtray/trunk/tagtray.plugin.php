<?php
class TagTray extends Plugin
{
	private $theme;

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'Tag Tray',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Displays a tray of tags on the publich page to click on and add.',
			'license' => 'Apache License 2.0',
		);
	}
	
	public function action_form_publish($form, $post) 
	{
		// Create the tags selector
		$tagselector = $form->publish_controls->append('fieldset', 'tagselector', _t('Tags'));

		$tags_buttons = $tagselector->append('wrapper', 'tags_buttons');
		$tags_buttons->class = 'container';
		$tags_buttons->append('static', 'clearbutton', '<p class="span-5"><input type="button" value="'._t('Clear').'" id="clear"></p>');

		$tags_list = $tagselector->append('wrapper', 'tags_list');
		$tags_list->class = ' container';
		$tags_list->append('static', 'tagsliststart', '<ul id="tag-list" class="span-19">');

		$tags = Tags::get();
		$max = Tags::max_count();
		foreach ($tags as $tag) {
			$tags_list->append('tag', 'tag_'.$tag->slug, $tag, 'tabcontrol_text');
		}

		$tags_list->append('static', 'tagslistend', '</ul>');
	}
	
	public function action_admin_header($theme)
	{
	Utils::debug('tagtray');
		Stack::add('admin_header_javascript', $this->get_url(true) . 'tagtray.js', 'tagtray');
	}
	
}
?>