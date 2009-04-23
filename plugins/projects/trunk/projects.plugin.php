<?php

/**
 * Project Management System
 * Not a replacement for the better systems such as Trac or a true SVN,
 * But will help any developer manage their projects, clients, scripts, and more.
 *
 * @author Benjamin Hutchins
 * @copyright 2008
 * @license MIT
 */
class Projects extends Plugin
{

	/**
	* Return plugin metadata for this plugin
	*/
	public function info()
	{
		return array(
			'name'		=> 'Project Management System',
			'version'	=> '1.2',
			'url'		=> 'http://www.benhutchins.com/project/project-management/',
			'author'	=> 'Benjamin Hutchins',
			'authorurl'	=> 'http://www.benhutchins.com/',
			'license'	=> 'MIT',
			'description'	=> 'Not a replacement for the better systems such as Trac or a true SVN,' .
				'but will help any developer manage their projects, clients, scripts, and more.'
		 );
	}

	public function action_update_check()
	{
	 	Update::add( 'Project Management System', '8F079806-AC80-11DD-B377-A48856D89593', $this->info->version );
	}

	/**
	 * On plugin activation
	 */
	public function action_plugin_activation( $file )
	{
		// Don't process other plugins
		if(Plugins::id_from_file($file) != Plugins::id_from_file(__FILE__))
			return;

		// Insert new post content types
		Post::add_new_type( 'project', true );
		Post::add_new_type( 'client', true );
		Post::add_new_type( 'task', true );
		
		if ( DB::exists( DB::table( 'rewrite_rules' ), array(
			'action' => 'display_projects',
			'name' => 'display_projects' ) ) ) {
			return; // do not keep adding the same rules if user disabled then re-enabled plugin
		}

		// Create new rewrite rule for showing a project
		$rule = RewriteRule::create_url_rule('"project"/{$slug}', 'UserThemeHandler', 'display_project');
		$rule->parse_regex = '%project/(?P<slug>[^/]+)/?$%i';
		$rule->build_str   = 'project/{$slug}';
		$rule->description = 'Project Management System - View Project';
		$rule->insert();

		// Create new rewrite rule for showing a client
		$rule = RewriteRule::create_url_rule('"client"/{$slug}', 'UserThemeHandler', 'display_client');
		$rule->parse_regex = '%client/(?P<slug>[^/]+)/?$%i';
		$rule->build_str   = 'client/{$slug}';
		$rule->description = 'Project Management System - View Client';
		$rule->insert();

		// Create new rewrite rule for showing a task
		$rule = RewriteRule::create_url_rule('"task"/{$slug}', 'UserThemeHandler', 'display_task');
		$rule->parse_regex = '%task/(?P<slug>[^/]+)/?$%i';
		$rule->build_str   = 'task/{$slug}';
		$rule->description = 'Project Management System - View Task';
		$rule->insert();
		
		// Create new rewrite rule for showing projects
		$rule = RewriteRule::create_url_rule('"project"/{$slug}', 'UserThemeHandler', 'display_projects');
		$rule->parse_regex = '%projects/?$%i';
		$rule->build_str   = 'projects';
		$rule->description = 'Project Management System - Projects';
		$rule->insert();

		// Create new rewrite rule for showing clients
		$rule = RewriteRule::create_url_rule('"client"/{$slug}', 'UserThemeHandler', 'display_clients');
		$rule->parse_regex = '%clients/?$%i';
		$rule->build_str   = 'clients';
		$rule->description = 'Project Management System - Clients';
		$rule->insert();

		// Create new rewrite rule for showing tasks
		$rule = RewriteRule::create_url_rule('"task"/{$slug}', 'UserThemeHandler', 'display_tasks');
		$rule->parse_regex = '%tasks/?$%i';
		$rule->build_str   = 'task';
		$rule->description = 'Project Management System - Tasks';
		$rule->insert();
	}

	public function filter_post_type_display( $type, $stance )
	{
		if ( $type == 'project' || $type == 'client' || $type == 'task' ) {
			return ucwords( $type . ( $stance == 'plural' ? 's' : '' ) );
		} else {
			return $type;
		}
	}

	/**
	 * Manage Projects
	 */
	public function action_form_publish( &$form, &$post, $context )
	{
		switch( $post->typename ) {
		case 'project':
			// Rename statuses
			$options= array();
			foreach($form->publish_controls->settings->status->options as $value=>$option)
				$options[$value] = ($value == 1 ? _t('Private') : ($value == 2 ? _t('Public') : $option));
			$form->publish_controls->settings->status->options= $options;

			// Add project settings fields
			$settings = $form->publish_controls->append('fieldset', 'project_settings', _t('Project Settings'));

			// Add version entry
			$settings->append('text', 'version', 'null:null', _t('Version'), 'tabcontrol_text');
			$settings->version->value = $post->info->version;

			// Add license entry
			$settings->append('text', 'license', 'null:null', _t('License'), 'tabcontrol_text');
			$settings->license->value = $post->info->license;

			// Add client selector
			$clients = array('' => _t('Personal'));
			foreach( Posts::get( array('content_type' => 'client', 'status'=>'any') ) as $client) {
				$clients[ $client->id ] = $client->title;
			}

			$settings->append('select', 'client', 'null:null', _t('Client'), $clients, 'tabcontrol_select');
			$settings->client->value = $post->info->client;

			break;

		case 'client':
			// Change title to "Name"
			$form->title->caption = _t('Name');

			// Add email field
			$form->append('text', 'email', 'null:null', _t('Email'), 'admincontrol_text');
			$form->email->class = 'important';
			$form->email->value = $post->info->email;

			// Add website field
			$form->append('text', 'website', 'null:null', _t('Website'), 'admincontrol_text');
			$form->website->class = 'important';
			$form->website->value = $post->info->website;

			// Reorder controls
			$controls = array();
			foreach($form->controls as $title => $control) {
				// Before Silos place Email and Website
				if ($title == 'silos') {
					$controls['email'] = $form->controls['email'];
					$controls['website'] = $form->controls['website'];
					
					/* Now continue...
					   We don't show silos for client form */
					//continue;
				}

				// Don't reshow replaced controls
				if ($title == 'email' || $title == 'website') continue;

				// Add control back into array
				$controls[$title] = $control;
			}

			$form->controls = $controls;
			break;

		case 'task':
			// Add project settings fields
			$settings = $form->publish_controls->append('fieldset', 'task_settings', _t('Task Settings'));

			// Add client selector
			$projects = array();
			foreach( Posts::get( array('content_type' => 'project', 'status'=>'any') ) as $project ) {
				$projects[ $project->id ] = $project->title;
			}
			$settings->append('select', 'project', 'null:null', _t('Project'), $projects, 'tabcontrol_select');
			$settings->project->value = $post->info->project;
			break;
		}
	}


	/**
	 * Change how we save project, client, and tasks
	 * Get rid of unneeded items and add to info
	 */
	public function action_publish_post( &$post, &$form )
	{
		// Run action again, content type wasn't set before
		$this->action_form_publish( $form, $post );

		switch( $post->typename ) {
		case 'project':
			// Save settings
			$post->info->version= $form->version->value;
			$post->info->license= $form->license->value;
			$post->info->client= $form->client->value;

			/*
			// Get sections (beta feature)
			$sections = array();

			// Format the content
			$content = $post->content;
			preg_match_all("/\<h[1-6]?.*>(.*)<\/h[1-6]?>/i", $content, $matches);
			for($i = 0; $i < count($matches[0]); $i++) {
				$match = array( $matches[0][$i], $matches[1][$i] );
				
				// process Element as XML/HTML
				$element = new HTML($match[0]);
				$id = $element->getAttribute('id');
				if ( ! $id ) {
					// generate an ID for element
					$id = strtolower(preg_replace("/[^a-zA-Z0-9]/", "_", $match[1]));

					// add ID to element
					$element->addAttribute('id', $id);

					// replace the element
					$content = str_replace($match[0], $element->asHTML(), $content);
				}

				// add to sections
				$sections[ $match[1] ] = $id;
			}

			// Remove empty parts from sections
			//$a = create_function('$a', 'return !empty($a);');
			//$sections = array_filter($sections, $a);

			// Update post
			$post->content = $content;
			$post->info->sections = $sections;
			*/

			break;

		case 'client':
			// Save email
			$post->info->email = $form->email->value;

			// Save website
			$post->info->website = $form->website->value;
			break;

		case 'task':
			// Save project
			$post->info->project= $form->project->value;
			break;
		}
	}

	/**
	 * Handle displays
	 */
	public function filter_theme_act_display_project($handled, &$theme)	{ return $this->act_display( 'project', $theme ); }
	public function filter_theme_act_display_client($handled, &$theme)	{ return $this->act_display( 'client', $theme ); }
	public function filter_theme_act_display_task($handled, &$theme)	{ return $this->act_display( 'task', $theme ); }
	public function filter_theme_act_display_projects($handled, &$theme)	{ return $this->act_display_multiple( 'project', $theme ); }
	public function filter_theme_act_display_clients($handled, &$theme)	{ return $this->act_display_multiple( 'client', $theme ); }
	public function filter_theme_act_display_tasks($handled, &$theme)	{ return $this->act_display_multiple( 'task', $theme ); }

	public function act_display_multiple( $type, &$theme )
	{
		$this->theme= $theme;

		$paramarray['fallback'] = array(
		 	'{$type}.multiple',
			'multiple',
		);

		// Makes sure home displays only entries
		$default_filters = array(
			'content_type' => Post::type( $type ),
		);

		$paramarray['user_filters'] = $default_filters;

		return $theme->act_display( $paramarray );
	}

	public function act_display( $type, &$theme )
	{
		$this->theme = $theme;

		$paramarray['fallback']= array(
		 '{$type}.{$id}',
		 '{$type}.{$slug}',
		 '{$type}.tag.{$posttag}',
		 '{$type}.single',
		 '{$type}.multiple',
		 'single',
		 'multiple',
		);

		// Does the same as a Post::get()
		$default_filters= array(
		 'fetch_fn' => 'get_row',
		 'limit' => 1,
		 'content_type' => $type,
		);

		// Remove the page from filters.
		$page_key= array_search( 'page', $theme->valid_filters );
		unset( $theme->valid_filters[$page_key] );

		$paramarray['user_filters']= $default_filters;

		return $theme->act_display( $paramarray );
	}
}

?>
