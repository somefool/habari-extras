<?php

class KeyNavigation extends Plugin
{

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'KeyNavigation', '', $this->info->version );
	}

	/**
	 * Add help text to plugin configuration page
	 **/
	public function help()
	{
		$help = _t('Allow users to navigate between posts on a page by pressing j (down) and k (up). Handles paging when on the first or last post.');
		return $help;
	}

	/**
	 * Set default options
	 **/
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Options::set( 'key_nav_paging_error', '100' );
			Options::set( 'key_nav_delay', '100' );
			Options::set( 'key_nav_selector', 'div.post' );
		}
	}

	/**
	 * Add appropriate javascript.
	 **/
	public function action_template_header( $theme )
	{
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', Site::get_url('habari') . "/3rdparty/hotkeys/jquery.hotkeys.js", 'jquery.hotkeys', 'jquery' );
		Stack::add( 'template_header_javascript', $this->js($theme), 'key_navigation', array('jquery, jquery.hotkeys') );
	}

	/**
	 * Create plugin configuration
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$form = new FormUI(strtolower(get_class($this)));
					// You can't go past the first/last post message
					$form->append( 'text', 'paging_error', 'option:key_nav_paging_error', _t('Message to display when a user tries to go past the first or last post') );
					// Selector for posts
					$form->append( 'text', 'selector', 'option:key_nav_selector', _t('jQuery selector to identify posts.') );
					// Scroll delay
					// TODO Validate as a number, or perhaps a selector
					$form->append( 'text', 'delay', 'option:key_nav_delay', _t('Scroll delay (ms)') );

					$form->append( 'submit', 'save', _t('Save') );
					$form->set_option( 'success_message', _t( 'Configuration saved') );
					$form->out();
					break;
			}
		}
	}

	private function js($theme)
	{
		// If there's a single post, ascend or descend appropriately
		if ( $theme->posts instanceOf Post) {
			$next = Posts::ascend($theme->posts)->permalink;
			$previous = Posts::descend($theme->posts)->permalink;
		}
		// If there are multiple posts, page appropriately
		else {
			$page = $theme->page;
			$items_per_page = isset($theme->posts->get_param_cache['limit']) ?
				$theme->posts->get_param_cache['limit'] :
				Options::get('pagination');
			$total = Utils::archive_pages( $theme->posts->count_all(), $items_per_page );
			if ( $page + 1 > $total ) {
				$next = '';
			}
			else {
				$next = URL::get(null, array('page' => $page + 1));
			}

			if ( $page - 1 < 1 ) {
				$previous = '';
			}
			else {
				$previous = URL::get(null, array('page' => $page - 1));
			}
		}
		$delay = Options::get( 'key_nav_delay' );
		$selector = Options::get( 'key_nav_selector' );
		return <<<KEYNAV
$(document).ready(function() {
	current = 0;
	$.hotkeys.add('j', {propagate:true, disableInInput: true}, function(){
		if (current == $('$selector').length-1) {
			if ('$next' == '') {
				// TODO Show the no more posts message
			} else {
				// go to next page
				window.location = '$next';
			}

		} else {
			target = $('$selector').eq(current+1).offset().top
			$('html,body').animate({scrollTop: target}, $delay);
			current++;
		}
	});
	$.hotkeys.add('k', {propagate:true, disableInInput: true}, function(){
		if (current == 0) {
			if ('$previous' == '') {
				// Show the no more posts message
			} else {
				// go to previous page
				window.location = '$previous';
			}
		} else {
			target = $('$selector').eq(current-1).offset().top
			$('html,body').animate({scrollTop: target}, $delay);
			current--;
		}
	});
});
KEYNAV;

	}
}

?>
