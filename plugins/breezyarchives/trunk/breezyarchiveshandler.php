<?php
class BreezyArchivesHandler extends ActionHandler
{
	private $theme = null;

	/**
	 * Constructor for the default theme handler.  Here, we
	 * automatically load the active theme for the installation,
	 * and create a new Theme instance.
	 */
	public function __construct()
	{
		$this->theme = Themes::create();
	}

	/**
	 * Helper function which automatically assigns all handler_vars
	 * into the theme and displays a theme template
	 *
	 * @param template_name Name of template to display (note: not the filename)
	 */
	protected function display($template_name)
	{
		/*
		 * Assign internal variables into the theme (and therefore into the theme's template
		 * engine.  See Theme::assign().
		 */
		foreach ($this->handler_vars as $key => $value) {
			$this->theme->assign($key, $value);
		}
		try {
			$this->theme->display($template_name);
		}
		catch(Error $e) {
			EventLog::log($e->humane_error(), 'error', 'plugin', $this->handler_vars['class_name'], print_r($e, 1));
		}
	}

	private function get_params($user_filters = array())
	{
		$default_filters = array(
			'content_type' => Post::type('entry'),
			'status' => Post::status('published'),
			'limit' => Options::get($this->handler_vars['class_name'] . '__posts_per_page'),
			'page' => 1,
			'orderby' => Options::get($this->handler_vars['class_name'] . '__show_newest_first') ? 'pubdate DESC' : 'pubdate ASC'
		);

		$paramarray = array_merge($default_filters, $user_filters, $this->handler_vars);
		unset($paramarray['entire_match']);
		unset($paramarray['class_name']);

		return $paramarray;
	}

	/**
	 * Helper function: Display the posts for a specific date
	 * @param array $user_filters Additional arguments used to get the page content
	 */
	public function act_display_month($user_filters = array())
	{
		$paramarray = $this->get_params($user_filters);
		$cache_name = array($this->handler_vars['class_name'], 'month_' . $paramarray['year'] . $paramarray['month'] . '_page_' . $paramarray['page']);

		if (Cache::has($cache_name)) {
			echo Cache::get($cache_name);
		} else {
			$posts = Posts::get($paramarray);

			$this->theme->assign('posts', $posts);
			$this->theme->assign('page_total', Utils::archive_pages($posts->count_all(), $paramarray['limit']));
			$this->theme->assign('current_page', $paramarray['page']);
			$this->theme->assign('prev_page_text', Options::get($this->handler_vars['class_name'] . '__prev_page_text'));
			$this->theme->assign('prev_page_link', URL::get('display_breezyarchives_by_month', array('class_name' => $this->handler_vars['class_name'], 'year' => $paramarray['year'], 'month' => $paramarray['month'], 'page' => $paramarray['page'] - 1)));
			$this->theme->assign('next_page_text', Options::get($this->handler_vars['class_name'] . '__next_page_text'));
			$this->theme->assign('next_page_link', URL::get('display_breezyarchives_by_month', array('class_name' => $this->handler_vars['class_name'], 'year' => $paramarray['year'], 'month' => $paramarray['month'], 'page' => $paramarray['page'] + 1)));
			$this->theme->assign('show_comment_count', Options::get($this->handler_vars['class_name'] . '__posts_per_page'));
			$this->theme->assign('year', $paramarray['year']);
			$this->theme->assign('month', $paramarray['month']);

			$ret = $this->theme->fetch('breezyarchives_month');
			Cache::set($cache_name, $ret);
			echo $ret;
		}
	}

	/**
	 * Helper function: Display the posts for a specific tag
	 * @param array $user_filters Additional arguments used to get the page content
	 */
	public function act_display_tag($user_filters = array())
	{
		$paramarray = $this->get_params($user_filters);
		$cache_name = array($this->handler_vars['class_name'], 'tag_' . $this->handler_vars['tag_slug'] . '_page_' . $paramarray['page']);

		if (Cache::has($cache_name)) {
			echo Cache::get($cache_name);
		} else {
			$posts = Posts::get($paramarray);

			$this->theme->assign('posts', $posts);
			$this->theme->assign('page_total', Utils::archive_pages($posts->count_all(), $paramarray['limit']));
			$this->theme->assign('current_page', $paramarray['page']);
			$this->theme->assign('prev_page_text', Options::get($this->handler_vars['class_name'] . '__prev_page_text'));
			$this->theme->assign('prev_page_link', URL::get('display_breezyarchives_by_tag', array('class_name' => $this->handler_vars['class_name'], 'tag_slug' => $this->handler_vars['tag_slug'], 'page' => $paramarray['page'] - 1)));
			$this->theme->assign('next_page_text', Options::get($this->handler_vars['class_name'] . '__next_page_text'));
			$this->theme->assign('next_page_link', URL::get('display_breezyarchives_by_tag', array('class_name' => $this->handler_vars['class_name'], 'tag_slug' => $this->handler_vars['tag_slug'], 'page' => $paramarray['page'] + 1)));
			$this->theme->assign('show_comment_count', Options::get($this->handler_vars['class_name'] . '__posts_per_page'));

			$ret = $this->theme->fetch('breezyarchives_tag');
			Cache::set($cache_name, $ret);
			echo $ret;
		}
	}

	/**
	 * Helper function: Display the CSS for Breezy Archives
	 */
	public function act_display_js()
	{
		ob_clean();
		header('Content-type: text/javascript');
	//	header('ETag: ' . md5($out));
	//	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 315360000) . ' GMT');
	//	header('Cache-Control: max-age=315360000');
		$this->theme->assign('habari_url', Site::get_url('habari', TRUE));
		$this->theme->assign('class_name', $this->handler_vars['class_name']);
		$this->theme->assign('spinner_img', URL::get_from_filesystem(__FILE__, TRUE) . 'breezyarchives_spinner.png');

		$this->display('breezyarchives_js');
	}
}
?>