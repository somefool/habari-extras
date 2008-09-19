<?php

class SuperTypes extends Plugin
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
			'name' => 'Super Types',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Extends Habari to allow for more advanced content types',
			'license' => 'Apache License 2.0',
		);
	}
	
	public function action_admin_header() {
		Stack::add( 'admin_stylesheet', array(URL::get_from_filesystem(__FILE__) . '/supertypes.css', 'screen'), 'supertypes' );
	}
	
	public function action_update_check() {
		Update::add( 'Super Types', '2e78ca0b-e6b6-4c2d-87cd-4b9dc902ea87', $this->info->version ); 
	}
	
	public function action_form_publish($form, $post) {
		$selector = $form->append('wrapper', 'type_selector');
		$selector->class = 'container';
		

		if(Controller::get_var('to_type') != NULL && $post->content_type != Controller::get_var('to_type')) { /* set type */
			$post->content_type = Post::type(Controller::get_var('to_type'));
			
			$post->update();
						
			Utils::redirect(URL::get('admin', 'page=publish&slug=' . $post->slug)); // Refresh view
		}
				
		foreach(Post::list_active_post_types() as $type) {
			if($type != 0) {
				if($post->slug == '') {
					$url = URL::get('admin', 'page=publish&content_type=' . Post::type_name($type));
				} else {
					$url = URL::get('admin', 'page=publish&to_type=' . Post::type_name($type) . '&slug=' . $post->slug);
				}
				
				$html = '<a href="' . $url . '"';
								
				if(Post::type_name($type) == $post->content_type || $type == $post->content_type) {
					$html.= ' class="active"';
				}
				$html.= '>' . Post::type_name($type) . '</a>';
				
				$selector->append('static', 'type_selector_' . $type, $html);
			}
		}
				
		$selector->move_before($selector, $form);
		
		return $form;
	}
}

?>
