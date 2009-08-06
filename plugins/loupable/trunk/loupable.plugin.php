<?php

class Loupable extends Plugin
{
	
	public function action_init()
	{
		$this->add_template('loupe.public', dirname(__FILE__) . '/loupe.public.php');
	}
	
	public function action_init_theme() {
		Stack::add( 'template_stylesheet', array( URL::get_from_filesystem(__FILE__) . '/loupable.css', 'screen' ), 'loupable' );

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/loupable.js', 'loupable' );
	}
	
	public function action_add_template_vars($theme, $handler_vars) {
		$items = array();
		if(isset($theme->request->display_home) && $theme->request->display_home) {
			$posts = Posts::get(array('content_type' => Post::type('entry'), 'status' => Post::status('published'), 'nolimit' => true, 'orderby' => 'pubdate ASC'));
			foreach($posts as $post) {
				$item = array();
				$item['url'] = $post->permalink;
				$item['title'] = $post->title;
				$item['time'] = strtotime($post->pubdate);
				$items[] = $item;
			}
		}
		
		$theme->timeline_items = $items;
	}
}

?>