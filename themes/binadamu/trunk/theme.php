<?php

/**
 * BinadamuTheme is a custom Theme class.
 *
 * @package Habari
 */



// We must tell Habari to use BinadamuTheme as the custom theme class:
define('THEME_CLASS', 'BinadamuTheme');

/**
 * A custom theme for Binadamu output
 */
class BinadamuTheme extends Theme
{
	private $handler_vars = array();

	/**
	 * On theme initialization
	 */
	public function action_init_theme()
	{
		/**
		 * Apply these filters to output
		 */
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

		$this->load_text_domain('binadamu');
	}

	public function add_template_vars()
	{
		$this->assign('home_tab', 'Blog'); //Set to whatever you want your first tab text to be.

		if (!$this->assigned('pages')) {
			$this->assign('pages', Posts::get(array('content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1)));
		}
		if (!$this->assigned('user')) {
			$this->assign('user', User::identify());
		}
		if (!$this->assigned('recent_entries')) {
			$this->assign('recent_entries', Posts::get(array('limit' => 10, 'content_type' => 'entry', 'status' => Post::status('published'), 'orderby' => 'pubdate DESC')));
		}

		$this->add_template( 'binadamu_submit', dirname(__FILE__) . '/formcontrol_submit.php' );

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

	public function filter_post_tags_class($tags)
	{
		if (!$tags)
			return;
		$rt = array();
		foreach ($tags as $t)
			$rt[] = 'tag-' . $t->term;
		return count($rt) > 0 ? implode(' ', $rt) : 'no-tags';
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
			$title = sprintf(_t('%1$s &raquo; Chronological Archives of %2$s', 'binadamu'), $date_string, Options::get('title'));
		}
		else
		if ($this->request->display_entries_by_tag && isset($this->handler_vars['tag'])) {
			$tag = (count($this->posts) > 0) ? Tags::get_by_slug($this->handler_vars['tag'])->term_display : $this->handler_vars['tag'] ;
			$title = sprintf(_t('%1$s &raquo; Taxonomic Archives of %2$s', 'binadamu'), htmlspecialchars($tag), Options::get('title'));
		}
		else
		if (($this->request->display_entry || $this->request->display_page || $this->request->display_post) && isset($this->posts)) {
			$title = sprintf(_t('%1$s ¶ %2$s', 'binadamu'), strip_tags($this->posts->title_out), Options::get('title'));
		}
		else
		if ($this->request->display_search && isset($this->handler_vars['criteria'])) {
			$title = sprintf(_t('%1$s &raquo; Search Results of %2$s', 'binadamu'), htmlspecialchars($this->handler_vars['criteria']), Options::get('title'));
		}
		else
		{
			$title = Options::get('title');
		}

		if ($this->page > 1) {
			$title = sprintf(_t('%1$s &rsaquo; Page %2$s', 'binadamu'), $title, $this->page);
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
			$h1 = '<h1>' . sprintf(_t('Posts written in %s', 'binadamu'), $date_string) . '</h1>';
		}
		else
		if ($this->request->display_entries_by_tag && isset($this->handler_vars['tag'])) {
			$tag = (count($this->posts) > 0) ? Tags::get_by_slug($this->handler_vars['tag'])->term_display : $this->handler_vars['tag'] ;
			$h1 = '<h1>' . sprintf(_t('Posts tagged with %s', 'binadamu'), htmlspecialchars($tag)) . '</h1>';
		}
		else
		if ($this->request->display_search && isset($this->handler_vars['criteria'])) {
			$h1 = '<h1>' . sprintf(_t('Search results for “%s”', 'binadamu'), htmlspecialchars($this->handler_vars['criteria'])) . '</h1>';
		}
		return $h1;
	}

	public function action_form_comment($form) {
		$form->append('static', 'cf_header', '<h2>' . _t('Leave a Reply', 'binadamu') . '</h2>');

		$form->append('wrapper', 'cf_commenter_info');
		$form->append('wrapper', 'cf_response');

		$form->cf_commenter->move_into($form->cf_commenter_info);
		$form->cf_commenter->caption = _t('Name', 'binadamu');

		$form->cf_email->move_into($form->cf_commenter_info);
		$form->cf_email->caption = _t('E-mail', 'binadamu');

		$form->cf_url->move_into($form->cf_commenter_info);
		$form->cf_url->caption = _t('Website', 'binadamu');

		$form->cf_content->move_into($form->cf_response);
		$form->cf_content->caption = _t('Your Comments', 'binadamu');

		$form->cf_submit->move_into($form->cf_response);
		$form->cf_submit->caption = _t('Send', 'binadamu');
		$form->cf_submit->template = 'binadamu_submit';

		$form->append('static', 'cf_notice', '<p id="cf_notice">' . _t('Your comment may not display immediately due to spam filtering. Please wait for moderation.', 'binadamu') . '</p>');
	}
}
?>
