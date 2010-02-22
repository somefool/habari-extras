<?php

/**
* Post Fields - A plugin to display additional fields on the publish page
**/
class postfields extends Plugin
{
	/**
	* Required Plugin Information
	*/
	public function info()
	{
		return array(
			'name' => 'Post Fields',
			'version' => '1.1',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'license' => 'Apache License 2.0',
			'description' => 'Display additional fields on the post page in the tabs to let authors add additional metadata to their posts.',
			'copyright' => '2008'
		);
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
			$types = Post::list_active_post_types();
			unset($types['any']);
			foreach($types as $type => $id) {
				$actions['config-' . $id] = _t('%s fields', array($type));
			}
			$actions['plugin'] = _t('Build Plugin');
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
		if ($plugin_id == $this->plugin_id()) {
			switch($action) {
				case 'plugin':

					$ui = new FormUI('postfields');
					$ui->append('static', 'typelabel', _t('Add this code to a plugin to implement the currently configured fields.'));
					$ui->append('textarea', 'plugincode', 'null:null', _t('Plugin code:'))->value = $this->get_code();
					$ui->out();
					
					break;

				default:
					$types = array_flip(Post::list_active_post_types());
					$key = substr($action, 7);
			
					$ui = new FormUI('postfields');
					$ui->append('static', 'typelabel', _t('Adding fields to the "%s" post type.', array($types[$key])));
					$ui->append('textmulti', 'fields', 'postfields__fields_' . $key, 'Additional Fields:');
					$ui->append('submit', 'submit', 'Submit');
					$ui->out();
			}
		}
	}

	/**
	* Add additional controls to the publish page tab
	*
	* @param FormUI $form The form that is used on the publish page
	* @param Post $post The post being edited
	**/
	public function action_form_publish($form, $post)
	{
		$fields = Options::get('postfields__fields_' . $post->content_type);
		if(!is_array($fields) || count($fields) == 0) {
			return;
		}
		$output = '';
		$control_id = 0;
		$postfields = $form->publish_controls->append('fieldset', 'postfields', 'Additional Fields');
		foreach($fields as $field) {
			$control_id = md5($field);
			$fieldname = "postfield_{$control_id}";
			$customfield = $postfields->append('text', $fieldname, 'null:null', $field);
			$customfield->value = isset($post->info->{$field}) ? $post->info->{$field} : '';
			$customfield->template = 'tabcontrol_text';
		}
	}
	

	/**
	* Modify a post before it is updated
	*
	* @param Post $post The post being saved, by reference
	* @param FormUI $form The form that was submitted on the publish page
	*/
	public function action_publish_post($post, $form)
	{
		$fields = Options::get('postfields__fields_' . $post->content_type);
		if(!is_array($fields) || count($fields) == 0) {
			return;
		}
		foreach($fields as $field) {
			$control_id = md5($field);
			$fieldname = "postfield_{$control_id}";
			$customfield = $form->$fieldname;
			$post->info->{$field} = $customfield->value;
		}
	}

	public function get_code()
	{
		$cases_form = '';

		$types = Post::list_active_post_types();
		unset($types['any']);
		foreach($types as $type => $id) {
			$fields = Options::get('postfields__fields_' . $id);
			if(!is_array($fields) || count($fields) == 0) {
				continue;
			}
			$fieldlist = array();
			foreach($fields as $field) {
				$fieldlist[] = "'" . addslashes($field) . "'";
			}
			$fieldlist = implode(', ', $fieldlist);
			$cases_form .= "\t\t\tcase {$id}:\n\t\t\t\t\$fields = array({$fieldlist});\n\t\t\t\tbreak;\n";
		}		

		$code = <<< PLUGIN_CODE_1

	/**
	* Add additional controls to the publish page tab
	*
	* @param FormUI \$form The form that is used on the publish page
	* @param Post \$post The post being edited
	**/
	public function action_form_publish(\$form, \$post)
	{
		switch(\$post->content_type) {
			{$cases_form}
			default:
				return;
		}
		foreach(\$fields as \$field) {
			\$control_id = md5(\$field);
			\$fieldname = "postfield_{\$control_id}";
			\$customfield = \$postfields->append('text', \$fieldname, 'null:null', \$field);
			\$customfield->value = isset(\$post->info->{\$field}) ? \$post->info->{\$field} : '';
			\$customfield->template = 'tabcontrol_text';
		}
	}
	

	/**
	* Modify a post before it is updated
	*
	* @param Post \$post The post being saved, by reference
	* @param FormUI \$form The form that was submitted on the publish page
	*/
	public function action_publish_post(\$post, \$form)
	{
		switch(\$post->content_type) {
			{$cases_form}
			default:
				return;
		}
		foreach(\$fields as \$field) {
			\$control_id = md5(\$field);
			\$fieldname = "postfield_{\$control_id}";
			\$customfield = \$form->\$fieldname;
			\$post->info->{\$field} = \$customfield->value;
		}
	}

PLUGIN_CODE_1;

		return $code;		
	}
	
		
  /**
   * Add update beacon support
   **/
  public function action_update_check()
  {
		Update::add( 'Postfields', '228D6060-38F0-11DD-AE16-0800200C9A66', $this->info->version );
  }

}
?>