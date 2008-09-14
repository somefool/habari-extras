<?php

/**
 * DeMorganTheme is a custom Theme class.
 *
 * @package Habari
 */



// We must tell Habari to use DeMorganTheme as the custom theme class:
define('THEME_CLASS', 'DeMorganTheme');

/**
 * A custom theme for DeMorgan output
 */
class DeMorganTheme extends Theme
{
	private $handler_vars = array();

	/**
	 * Execute on theme init to apply these filters to output
	 */
	public function action_init_theme()
	{
		if (!Plugins::is_loaded('HabariMarkdown')) {
			// Apply Format::autop() to post content...
			Format::apply('autop', 'post_content_out');
		}
		// Truncate content excerpt at "<!--more-->"...
		Format::apply_with_hook_params('more', 'post_content_out');
		// Apply Format::autop() to comment content...
		Format::apply('autop', 'comment_content_out');
		// Apply Format::tag_and_list() to post tags...
		Format::apply('tag_and_list', 'post_tags_out');

		$this->load_text_domain('demorgan');
	}

	public function add_template_vars()
	{
		//Theme Options
		$this->assign('home_tab', 'Blog'); //Set to whatever you want your first tab text to be.

		if (!$this->assigned('pages')) {
			$this->assign('pages', Posts::get(array('content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1)));
		}
		if (!$this->assigned('user')) {
			$this->assign('user', User::identify());
		}
		if (!$this->assigned('recent_comments')) {
			$this->assign('recent_comments', Comments::get(array('limit'=>10, 'status'=>Comment::STATUS_APPROVED, 'type'=>Comment::COMMENT, 'orderby'=>'date DESC')));
		}
		if (!$this->assigned('recent_entries')) {
			$this->assign('recent_entries', Posts::get(array('limit'=>10, 'content_type'=>1, 'status'=>2, 'orderby'=>'pubdate DESC')));
		}

		parent::add_template_vars();
	}

	public function filter_theme_call_header($return, $theme)
	{
		if ($this->request->display_search) {
			echo '<meta name="robots" content="noindex,nofollow">';
		} elseif ($this->request->display_entries_by_date
			|| $this->request->display_entries_by_tag) {
			echo '<meta name="robots" content="noindex,follow">';
		}
		return $return;
	}
/*
	public function filter_theme_call_footer($return, $theme)
	{
		var_dump($this);
		return $return;
	}
*/
	public function filter_post_tags_class($tags)
	{
		if (!is_array($tags))
			$tags = array($tags);
		return count($tags) > 0 ? 'tag-' . implode(' tag-', array_keys($tags)) : 'no-tags';
	}

	public function theme_body_class($theme)
	{
		// Assigning <body> class(es)
		$body_class = array();
		if ($this->request->display_home) {
			$body_class[] = 'home';
			$body_class[] = 'multiple';
		}
		else
		if ($this->request->display_entries) {
			$body_class[] = 'multiple';
		}
		else
		if ($this->request->display_entries_by_date) {
			$body_class[] = 'date-archive';
			$body_class[] = 'archive';
			$body_class[] = 'multiple';
		}
		else
		if ($this->request->display_entries_by_tag) {
			$body_class[] = 'tag-archive';
			$body_class[] = 'archive';
			$body_class[] = 'multiple';
		}
		else
		if ($this->request->display_entry || $this->request->display_page) {
			$post_type_name = Post::type_name($this->posts->content_type);
			$body_class[] =  $post_type_name . '-' . $this->posts->slug;
			$body_class[] =  $post_type_name;
			$body_class[] = 'single';
		}
		else
		if ($this->request->display_search) {
			$body_class[] = 'search';
			$body_class[] = 'multiple';
		}
		else
		if ($this->request->display_404) {
			$body_class[] = 'four04';
		}

		//Get unique items
		$body_class = array_flip(array_flip($body_class));

		return count($body_class) > 0 ? ' class="' . implode(' ', $body_class) . '"' : '';
	}

	public function theme_title($theme)
	{
		$title = '';

		if (count($this->handler_vars) === 0) {
			$this->handler_vars = Controller::get_handler()->handler_vars;
		}
		if ($this->request->display_entries_by_date && count($this->handler_vars) > 0) {
			$date_string = '';
			$date_string .= isset($this->handler_vars['year']) ? $this->handler_vars['year'] : '' ;
			$date_string .= isset($this->handler_vars['month']) ? '‒' . $this->handler_vars['month'] : '' ;
			$date_string .= isset($this->handler_vars['day']) ? '‒' . $this->handler_vars['day'] : '' ;
			$title = sprintf(_t('%1$s &raquo; Chronological Archives of %2$s', 'demorgan'), $date_string, Options::get('title'));
		}
		else
		if ($this->request->display_entries_by_tag && isset($this->handler_vars['tag'])) {
			$tag = (count($this->posts) > 0) ? $this->posts[0]->tags[$this->handler_vars['tag']] : $this->handler_vars['tag'] ;
			$title = sprintf(_t('%1$s &raquo; Taxonomic Archives of %2$s', 'demorgan'), htmlspecialchars($tag), Options::get('title'));
		}
		else
		if (($this->request->display_entry || $this->request->display_page) && isset($this->posts)) {
			$title = sprintf(_t('%1$s ¶ %2$s', 'demorgan'), strip_tags($this->posts->title), Options::get('title'));
		}
		else
		if ($this->request->display_search && isset($this->handler_vars['criteria'])) {
			$title = sprintf(_t('%1$s &raquo; Search Results of %2$s', 'demorgan'), htmlspecialchars($this->handler_vars['criteria']), Options::get('title'));
		}
		else
		{
			$title = Options::get('title');
		}

		if ($this->page > 1) {
			$title = sprintf(_t('%1$s &rsaquo; Page %2$s', 'demorgan'), $title, $this->page);
		}

		return $title;
	}

	public function theme_mutiple_h1($theme)
	{
		$h1 = '';

		if (count($this->handler_vars) === 0) {
			$this->handler_vars = Controller::get_handler()->handler_vars;
		}
		if ($this->request->display_entries_by_date && count($this->handler_vars) > 0) {
			$date_string = '';
			$date_string .= isset($this->handler_vars['year']) ? $this->handler_vars['year'] : '' ;
			$date_string .= isset($this->handler_vars['month']) ? '‒' . $this->handler_vars['month'] : '' ;
			$date_string .= isset($this->handler_vars['day']) ? '‒' . $this->handler_vars['day'] : '' ;
			$h1 = '<h1>' . sprintf(_t('Posts written in %s', 'demorgan'), $date_string) . '</h1>';
		}
		else
		if ($this->request->display_entries_by_tag && isset($this->handler_vars['tag'])) {
			$tag = (count($this->posts) > 0) ? $this->posts[0]->tags[$this->handler_vars['tag']] : $this->handler_vars['tag'] ;
			$h1 = '<h1>' . sprintf(_t('Posts tagged with %s', 'demorgan'), htmlspecialchars($tag)) . '</h1>';
		}
		else
		if ($this->request->display_search && isset($this->handler_vars['criteria'])) {
			$h1 = '<h1>' . sprintf(_t('Search results for “%s”', 'demorgan'), htmlspecialchars($this->handler_vars['criteria'])) . '</h1>';
		}
		return $h1;
	}
}

?>
