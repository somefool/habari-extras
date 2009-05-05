<?php

class SubPagesPlugin extends Plugin
{
	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	function info()
	{
		return array (
			'name' => 'SubPages',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'version' => '0.1',
			'description' => 'Lets you define parent pages, so you can create an arbitrary page hierarchy. WARNING: This plugin is experimental and based on an unstable Habari API. Do not use in production. (Though if you wanted to test it and let report how it breaks, that would be grand :)',
			'license' => 'Apache License 2.0',
		);
	}

	/**
	 * Add the subpage vocabulary
	 *
	 **/
	public function action_plugin_activation($file)
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			$params = array(
				'name' => 'subpages',
				'description' => 'A vocabulary for describing hierarchical relationships between pages',
				'feature_mask' => Vocabulary::feature_mask(true, false, false, false)
			);
			$subpages = new Vocabulary($params);
			$subpages->insert();

			$subpages->add_term('root');
		}
	}

	/**
	 * Add the necessary template
	 *
	 **/
	function action_init()
	{
		$this->add_template( 'subpages', dirname(__FILE__) . '/subpages.php' );
	}

	/**
	 * Add the page parent control to the publish page
	 *
	 * @param FormUI $form The publish page form
	 * @param Post $post The post being edited
	 **/
	public function action_form_publish ( $form, $post )
	{
		if ( $form->content_type->value == Post::type( 'page' ) ) {

			$parent_term = null;
			$descendants = null;
			$subpage_vocab = Vocabulary::get('subpages');

			// If there's an existing page, see if it has a related term, parent, and descendants
			if ( null != $post->slug ) {
				$term = $subpage_vocab->get_term($post->slug);
				if ( null != $term ) {
					$parent_term = $term->parent();
					$descendants = $term->descendants();
				}
			}

			// If there are pages, work out which ones can be a parent to this page
			$pages = (array)Posts::get(array('content_type'=>'page', 'status'=>'published', 'nolimit'=>true));

			if ( 0 != count($pages) ) {

				// Descendants of the current page can't be its parent
				if ( null != $descendants ) {
					/* TODO Why doesn't this work ?
					$pages = array_udiff($pages, $descendants,
						create_function( '$a, $b', 'return $a->slug != $b->term;' ) );
					*/
					for ( $i=0;$i<count($pages);$i++ ) {
						foreach ( $descendants as $descendant ) {
							if ( $pages[$i]->slug == $descendant->term ) {
								unset($pages[$i]);
								break;
							}
						}
					}
				}

				// Create an array of slug => title, appropriate for passing to a select control
				$candidates = array('none' => 'No parent');
				foreach ( $pages as $page ) {
					if ( $page->slug != $post->slug ) {
						$candidates[$page->slug] = $page->title;
					}
				}

				// Add a parent selector to the page settings
				$parent_select = $form->settings->append( 'text', 'parent', 'null:null', _t( 'Parent: '), 'tabcontrol_select' );
				$parent_select->value = $parent_term == null ? 'none' : $parent_term->term;
				$parent_select->options = $candidates;
			}
		}
	}

	/**
	 * Handle update of parent
	 * @param Post $post The post being updated
	 * @param FormUI $form. The form from the publish page
	 **/
	public function action_publish_post( $post, $form )
	{
		if ( $post->content_type == Post::type( 'page' ) ) {
			$subpage_vocab = Vocabulary::get('subpages');
			$parent_term = null;
			$term = $subpage_vocab->get_term($post->slug);
			if ( null != $term ) {
				$parent_term = $term->parent;
			}

			$form_parent = $form->settings->parent->value;
			// If the parent has been changed, delete this page from its children
			if ( null != $parent_term && $form_parent != $parent_term->term ) {
				$subpage_vocab->delete_term($term->term);

				// TODO If the parent no longer has descendants, delete it.
				// TODO What to do if the term has descendants, and we change the parent ?
			}

			// If a new term has been set, add it to the subpages vocabulary
			if ( 'none' != $form_parent ) {
				// Make sure the parent term exists.
				$parent_term = $subpage_vocab->get_term($form_parent);
				if ( null == $parent_term ) {
					// There's no term for the parent, add it as a top-level term
					$parent_term = $subpage_vocab->add_term( $form_parent, $subpage_vocab->get_term('root') );
				}

				$subpage_vocab->add_term( $post->slug, $parent_term );
			}

		}
	}

	/**
	 * Enable update notices to be sent using the Habari beacon
	 **/
	public function action_update_check()
	{
		Update::add( 'SubPages', 'c6a39795-d5cb-4042-b05b-9666825b1bb4',  $this->info->version );
	}

	public function theme_subpages($theme, $post)
	{
		if ( $post->content_type == Post::type( 'page' ) ) {
			$subpages = null;
			$subpage_vocab = Vocabulary::get('subpages');
			$term = $subpage_vocab->get_term($post->slug);
			if ( null != $term ) {

				// TODO this should be get_objects etc

				$slugs = array();
				$children = $term->children();
				foreach ( $children as $child ) {
					$slugs[] = $child->term;
				}
				if ( count($slugs) > 0 ) {
					$theme->subpages = Posts::get(array('slug' => $slugs));
					return $theme->display('subpages');
				}
			}
		}
	}

	public function help()
	{
		return <<< END_HELP
<p>To output subpages in a page, insert this code where you want them linked from:</p>
<blockquote><code>&lt;?php \$theme-&gt;subpages(); ?&gt;</code></blockquote>
<p>The default theme inserts a link to each subpage.  If you want to alter
this, you should copy the <tt>subpages.php</tt> template included with this
plugin to your current theme directory and make changes to it there.</p>
END_HELP;
	}

}


?>
