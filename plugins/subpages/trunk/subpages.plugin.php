<?php

class SubPagesPlugin extends Plugin
{
	private static $vocabulary = 'subpages';
	private static $content_type = 'page';
	private static $select_none = 'none';

	private $cache_rootPages = null;

	/**
	 * Add the subpage vocabulary
	 *
	 */
	public function action_plugin_activation($file)
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			$params = array(
				'name' => self::$vocabulary,
				'description' => 'A vocabulary for describing hierarchical relationships between pages',
				'features' => array( 'hierarchical' )
			);

			$subpages = new Vocabulary( $params );
			$subpages->insert();
		}
	}

	/**
	 * Remove the subpage vocabulary
	 *
	 */
	public function action_plugin_deactivation($file)
	{
		/* Until deleting a vocabulary deletes its associated terms, this is not going to work well.  */

		// $subpages = Vocabulary::get( self::$vocabulary );
		// $subpages->delete();
	}

	/**
	 * Add the necessary template
	 *
	 */
	public function action_init()
	{
		$this->add_template( self::$vocabulary, dirname(__FILE__) . '/' . self::$vocabulary . '.php' );
	}

	public function action_add_template_vars( $theme )
	{
		if ( !Options::get( 'subpagesplugin__showallpages' ) ) {
			if ( !isset($theme->pages) ) {
				return;
			}
			
			// Caching, since this is called multiple times per page, and memory is cheap.
			if ( $this->cache_rootPages !== null ) {
				$theme->pages = $this->cache_rootPages;
			}

			$pages = $theme->pages;
			$subpage_vocab = Vocabulary::get( self::$vocabulary );

			$rootPages = array();
			foreach ($pages as $p) {
				$term = $subpage_vocab->get_term( $p->slug );
				if ( ( $term == null ) || ( $term->parent() == null ) ) {
					$rootPages[] = $p;
				}
			}

			if ( count( $rootPages ) > 0 ) {
				$theme->pages = $this->cache_rootPages = $rootPages;
			}
			else {
				$theme->pages = $this->cache_rootPages = array();
			}
		}
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$name = strtolower( get_class( $this ) );
			switch ( $action ) {
				case _t( 'Configure' ) :
					$showAll = Options::get( $name . '__showallpages' );

					$ui = new FormUI( $name );
					$customvalue = $ui->append( 'checkbox', 'showallpages', $name.'__showallpages', _t('Show all pages in menus:') );
					$customvalue->value = $showAll;

					$ui->append( 'static', 'Please note', _t( '<p>This setting relies on the theme auto assigning the available pages via the add_template_vars in the templates theme.php.</p>' ) );
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->out();
					break;
			}
		}
	}

	public function theme_subpages($theme, $post)
	{
		if ( $post->content_type == Post::type( self::$content_type ) ) {
			$subpage_vocab = Vocabulary::get( self::$vocabulary );
			$page_term = $subpage_vocab->get_term( $post->slug );

			if ( null != $page_term ) {
				// TODO this should be get_objects etc

				$slugs = array();
				$children = $page_term->children();

				foreach ( $children as $child )
					$slugs[] = $child->term;

				if ( count($slugs) > 0 ) {
					$theme->{self::$vocabulary} = Posts::get( array( 'slug' => $slugs ) );
					return $theme->display( self::$vocabulary );
				}
			}
		}
	}

	public function action_form_publish ( $form, $post )
	{
		if ( $form->content_type->value == Post::type( self::$content_type ) ) {
			$parent_term = null;
			$descendants = null;
			$subpage_vocab = Vocabulary::get( self::$vocabulary );

			// If there's an existing page, see if it has a related term, parent, and descendants
			if ( null != $post->slug ) {
				$page_term = $subpage_vocab->get_term( $post->slug );
				if ( null != $page_term ) {
					$parent_term = $page_term->parent();
					$descendants = $page_term->descendants();
				}
			}

			// If there are pages, work out which ones can be a parent to this page
			$pages = (array)Posts::get( array( 'content_type' => self::$content_type, 'nolimit'=>true ) );

			// Add a parent selector to the page settings
			$parent_select = $form->settings->append( 'text', 'parent', 'null:null', _t( 'Parent: '), 'tabcontrol_select' );

			if ( 0 != count($pages) ) {
				// Descendants of the current page can't be its parent
				if ( null != $descendants ) {
					/* TODO Why doesn't this work ?
					$pages = array_udiff($pages, $descendants,
						create_function( '$a, $b', 'return $a->slug != $b->term;' ) );
					*/
					for ( $i = 0; $i < count( $pages ); $i++ ) {
						foreach ( $descendants as $descendant ) {
							if ( $pages[$i]->slug == $descendant->term ) {
								unset( $pages[$i] );
								break;
							}
						}
					}
				}

				// Create an array of slug => title, appropriate for passing to a select control
				$candidates = array( self::$select_none => 'No parent' );
				foreach ( $pages as $page ) {
					if ( $page->slug != $post->slug ) {
						$candidates[$page->slug] = $page->title;
					}
				}

				$parent_select->value = $parent_term == null ? self::$select_none : $parent_term->term;
				$parent_select->options = $candidates;
			}
		}
	}

	public function action_publish_post( $post, $form )
	{
		if ( $post->content_type == Post::type( self::$content_type ) ) {
			$subpage_vocab = Vocabulary::get( self::$vocabulary );
			$parent_term = null;
			$page_term = $subpage_vocab->get_term( $post->slug );

			if ( null != $page_term ) {
				$parent_term = $page_term->parent();
			}

			$form_parent = $form->settings->parent->value;

			// If the parent has been changed, delete this page from its children
			if ( null != $parent_term && $form_parent != $parent_term->term ) {
				$subpage_vocab->delete_term( $page_term->term );

				// TODO If the parent no longer has descendants, delete it.
				// TODO What to do if the term has descendants, and we change the parent ?
				// There probably should be a flag passed to delete_term() that says
				// whether to delete them or add them as children of the parent
			}

			// If a new term has been set, add it to the subpages vocabulary
			if ( self::$select_none != $form_parent ) {
				// Make sure the parent term exists.
				$parent_term = $subpage_vocab->get_term( $form_parent );

				if ( null == $parent_term ) {
					// There's no term for the parent, add it as a top-level term
					$parent_term = $subpage_vocab->add_term( $form_parent );
				}

				$page_term = $subpage_vocab->add_term( $post->slug, $parent_term );
			}

		}
	}

	/**
	 * Show help text
	 */
	public function help()
	{
		return <<< END_HELP
<p>To output subpages in a page, insert this code where you want them linked from:</p>
<blockquote><code>&lt;?php \$theme-&gt;subpages( \$post ); ?&gt;</code></blockquote>
<p>The default theme inserts a link to each subpage. If you want to alter
this, you should copy the <tt>subpages.php</tt> template included with this
plugin to your current theme directory and make changes to it there.</p>
END_HELP;
	}

	/**
	 * Change the page rewrite rule to validate ancestors
	 * @param Array $rules Current rewrite rules
	 */
	public function filter_rewrite_rules( $rules )
	{
		foreach ( $rules as $rule ) {
			if ( 'display_page' == $rule->name ) {
				$rule->parse_regex = '%^(?P<parentage>.*/)(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
				$rule->description = 'Return page matching specified slug and page hierarchy';
				$rule->parameters = serialize( array( 'require_match' => array('SubPagesPlugin', 'rewrite_match_subpage') ) );
				break;
			}
		}

		return $rules;
	}

	/**
	 * Validate ancestors for this page
	 * @param RewriteRule $rule The matched rewrite rule
	 * @param string The URL stub requested
	 * @param array $params Some stuff
	 */
	public static function rewrite_match_subpage( $rule, $stub, $params )
	{
		// TODO Is there any way to get these apart from rematching ?
		// They're not set until there's a handler, and that isn't set yet.
		$slugs = explode('/', $stub);

		$args = array(
			'content_type' => 'page'
		);

		// Check we can get a page for each part of the slug hierarchy
		$args['slug'] = $slugs;

		$posts = Posts::get($args);

		if ( count($posts) != count($slugs) ) {
			return false;
		}

		// Check the stub matches the expected stub
		return $stub == self::subpage_stub( array_pop( $slugs ) );
	}

	/**
	 * Rewrite a post's permalink if it's a subpage
	 * @param Array $rules Current rewrite rules
	 */
	public function filter_post_permalink( $permalink, $post )
	{
		if ( $post->content_type == Post::type( self::$content_type ) ) {
			$subpage_vocab = Vocabulary::get( self::$vocabulary );
			$page_term = $subpage_vocab->get_term( $post->slug );
			if ( null != $page_term ) {
				$permalink = Site::get_url( 'habari' ) . '/' . self::subpage_stub( $page_term );
			}
		}

		return $permalink;
	}

	private static function subpage_stub( $term )
	{
		if ( is_string($term) ) {
			$term = Vocabulary::get( self::$vocabulary )->get_term($term);
		}
		if ( null == $term ) {
			return false;
		}
		$ancestors = $term->ancestors();

		$stub_parts = array();
		foreach ( $ancestors as $ancestor ) {
			$stub_parts[] = $ancestor->term;
		}
		$stub_parts[] = $term->term;

		return implode('/', $stub_parts);
	}
}

?>
