<?php
class PagelessHandler extends ActionHandler
{
	public $theme = null;
	private $default_fields = array(
		'slug' => ''
	);

	/**
	 * Constructor for the default theme handler.  Here, we
	 * automatically load the active theme for the installation,
	 * and create a new Theme instance.
	 */
	public function __construct()
	{
		$this->theme = Themes::create();
	}

	public function act_display_pageless()
	{
		$filters = new SuperGlobal($this->default_fields);
		$filters = $filters->merge($this->handler_vars);
		$post = Post::get(array('slug' => $filters['slug']));
		if ($post instanceof Post) {
			// Default params
			$params = array(
				'where' => "(pubdate < '{$post->pubdate->sql}' OR (pubdate = '{$post->pubdate->sql}' AND id < {$post->id})) AND content_type = {$post->content_type} AND status = {$post->status}",
				'limit' => Options::get('pageless__num_item'),
				'orderby' => 'pubdate DESC, id DESC'
				);

			// Additional filters, in other word, handling act_display
			if (isset($filters['type'])) {
				if ($filters['type'] === 'tag') {
					$params['tag_slug'] = $filters['param'];
				} else
				if ($filters['type'] === 'date') {
					$date = explode('/', $filters['param']);
					$params_count = count($date);
					switch ($params_count) {
						case 3:
							$params['day'] = $date[2];
						case 2:
							$params['month'] = $date[1];
						case 1:
							$params['year'] = $date[0];
						default:
							break;
					}
				} else
				if ($filters['type'] === 'search') {
					$params['criteria'] = $filters['param'];
				}
			}

			// Get $posts -> Assign $posts to theme -> Display template
			$posts = Posts::get($params);
			$this->theme->assign('posts', $posts);
			$this->theme->display('pageless');
		}
	}
}
?>