<?php

class Projectr extends Plugin
{
		
	static $secret_report_cache = array();
	
	public function action_update_check()
	{
		Update::add( $this->info->name, '8d5a4403-33a5-430e-8674-ae4a1b1a09d3', $this->info->version );
	}
	
	
	public function action_init() {
		
		
		// gotta be an easier way of doing this
		$theme_dir = Plugins::filter( 'admin_theme_dir', Site::get_dir( 'admin_theme', TRUE ) );
		$theme = Themes::create( 'admin', 'RawPHPEngine', $theme_dir );
		
		if( !$theme->template_exists( 'admincontrol_select') )
		{
			$this->add_template('admincontrol_select', dirname(__FILE__) . '/admincontrol_select.php');
		}

	}

	
	public function action_plugin_activation( $plugin_file )
	{
		self::install();
	}
	
	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'project' );
	}
	
	public static function get_projects( $params = array() )
	{
		$params['content_type'] = Post::type('project');
		$params['nolimit'] = true;
		
		return Posts::get( $params );
	}
	
	/**
	 * Gets a simple list of all projects 
	 **/
	public static function get_projects_simple()
	{
		
		$posts = self::get_projects();
		
		$projects = array();
		
		foreach( $posts as $project )
		{
			$projects[ $project->slug ] = $project->title;
		}
		
		return $projects;
		
	}
	
	/**
	 * install various stuff we need
	 */
	public static function install() {
		/**
		 * Register content type
		 **/
		Post::add_new_type( 'project' );
		
		// Give anonymous users access
		$group = UserGroup::get_by_name('anonymous');
		$group->grant('post_project', 'read');

	}
	
	/**
	 * Create name string
	 **/
	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'project' => array(
				'singular' => _t('Project'),
				'plural' => _t('Projects'),
			)
		); 
 		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	/**
	 * Modify publish form
	 */
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('project'))
		{
			
			$form->title->caption = 'Project Name';
			
		}
		else
		{
			
			$projects = self::get_projects_simple();
 			$projects['other'] = "Other";
			$projects = array_reverse( $projects );
			
			$project_selector= $form->append('select', 'project', 'null:null', _t('Project'), $projects, 'admincontrol_select');
			
			foreach( $projects as $key => $name )
			{
				if( array_key_exists( $key, $post->tags ) )
				{
					$project_selector->value = $key;
				}
			}
			
			$form->move_after($project_selector, $form->tags);
		}
	}
	
	
	/**
	 * Save our report
	 */
	public function action_publish_post( $post, $form )
	{		
		// Delete all project tags
		$projects = self::get_projects_simple();
		$tags = array();
		foreach( $post->tags as $tag )
		{
			if( !array_key_exists( $tag, $projects ) )
			{
				$tags[] = $tag;
			}
		}
		
		// Add the project tag
		if( $form->project->value != 'other' )
		{
			$tags[] = $form->project->value;
		}
		
		$post->tags = $tags;
		
		// exit;
	}
	
	
}

?>