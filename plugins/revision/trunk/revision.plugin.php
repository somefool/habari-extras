<?php
/*

  Revision

  Revision: $Id$
  Head URL: $URL$

*/
require_once( 'revision_diff.php');

class Revision extends Plugin
{
	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Revision',
			'version' => '0.02',
			'url' => 'http://ayu.commun.jp/habari-postrevision',
			'author' => 'ayunyan',
			'authorurl' => 'http://ayu.commun.jp/',
			'license' => 'Apache License 2.0',
			'description' => '',
			);
	}

	/**
	 * action: plugin_activation
	 *
	 * @access public
	 * @param string $file
	 * @return void
	 */
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) != Plugins::id_from_file( __FILE__ ) ) return;

		Post::add_new_type( 'revision' );
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain( 'portrevision' );

		$this->add_template( 'revision', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'index.php' );
		$this->add_template( 'revision_diff', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'diff.php' );
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add( 'Revision', '01bcbf1c-2958-11dd-b5d6-001b210f913f', $this->info->version );
	}

	/**
	 * action: post_insert_after
	 *
	 * @access public
	 * @param object $post
	 * @return void
	 */
	public function action_post_insert_after( $post )
	{
		if ( $post->content_type == Post::type( 'revision' ) ) return;

		$revision = 1;
		$rev_post = clone $post;
		$rev_post->id = null;
		$rev_post->slug = 'revision-' . $revision . ':' . $rev_post->slug;
		$rev_post->content_type = Post::type( 'revision' );
		$rev_post->info->revision = $revision;
		$rev_post->info->revision_post_id = $post->id;

		DB::insert( DB::table( 'posts' ), array_diff_key( $rev_post->to_array(), $rev_post->list_excluded_fields() ) );
		$rev_post->info->commit( DB::last_insert_id() );
	}

	/**
	 * action: post_update_after
	 *
	 * @access public
	 * @param object $post
	 * @return void
	 */
	public function action_post_update_after( $post )
	{
		if ( $post->content_type == Post::type( 'revision' ) ) return;

		$rev_count = DB::get_row( 'SELECT COUNT(*) AS count FROM {posts} LEFT JOIN {postinfo} ON {posts}.id = {postinfo}.post_id WHERE {postinfo}.name = \'revision_post_id\' AND {postinfo}.value = :post_id', array( 'post_id' => $post->id ) );

		$revision = $rev_count->count + 1;
		$rev_post = clone $post;
		$rev_post->id = null;
		$rev_post->slug = 'revision-' . $revision . ':' . $rev_post->slug;
		$rev_post->content_type = Post::type( 'revision' );
		$rev_post->info->revision = $revision;
		$rev_post->info->revision_post_id = $post->id;

		DB::insert( DB::table( 'posts' ), array_diff_key( $rev_post->to_array(), $rev_post->list_excluded_fields() ) );
		$rev_post->info->commit( DB::last_insert_id() );
	}

	/**
	 * action: admin_theme_get_revision
	 *
	 * @access public
	 * @param object $handler
	 * @param object $theme
	 * @return void
	 */
	public function action_admin_theme_get_revision( $handler, $theme )
	{
		if ( isset( $handler->handler_vars['action'] ) && method_exists( $this, 'act_' . $handler->handler_vars['action'] ) ) {
			call_user_func( array( $this, 'act_' . $handler->handler_vars['action'] ), $handler, $theme );
			exit;
		}

		if ( empty( $handler->handler_vars['revision_id'] ) ) {
			Session::notice( sprintf( _t( 'missing parameter: %s' ), 'revision_id' ) );
			Utils::redirect( URL::get( 'admin', 'page=dashboard' ) );
			return;
		}

		$post = Posts::get( array( 'id' => $handler->handler_vars['revision_id'], 'fetch_fn' => 'get_row' ) );
		$theme->assign( 'post', $post );

		$theme->assign( 'revisions', Posts::get( array( 'info' => array( 'revision_post_id' => $post->info->revision_post_id ), 'orderby' => 'modified DESC', 'nolimit' => true ) ) );

		$theme->display( 'revision' );
		exit;
	}

	/**
	 * local admin action: rollback
	 *
	 * @access private
	 * @param object $handler
	 * @param object $theme
	 * @return void
	 */
	private function act_rollback( $handler, $theme )
	{
		if ( empty( $handler->handler_vars['revision_id'] ) ) {
			Session::notice( sprintf( _t( 'missing parameter: %s' ), 'revision_id' ) );
			Utils::redirect( URL::get( 'admin', 'page=dashboard' ) );
			return;
		}

		$revision = Posts::get( array( 'id' => $handler->handler_vars['revision_id'], 'fetch_fn' => 'get_row' ) );

		if ( !$revision ) {
			Session::notice( _t( 'specify revision not found.' ) );
			Utils::redirect( URL::get( 'admin', 'page=dashboard' ) );
			return;
		}

		$post = Posts::get( array( 'id' => $revision->info->revision_post_id, 'fetch_fn' => 'get_row' ) );

		$post->title = $revision->title;
		$post->content = $revision->content;
		$post->update();

		Utils::redirect( URL::get( 'admin', 'page=publish&id=' . $post->id ) );
	}

	/**
	 * action: admin_theme_get_revision_diff
	 *
	 * @access public
	 * @param object $handler
	 * @param object $theme
	 * @return void
	 */
	public function action_admin_theme_get_revision_diff( $handler, $theme )
	{
		if ( empty( $handler->handler_vars['old_revision_id'] ) || empty( $handler->handler_vars['new_revision_id'] ) ) {
			Session::notice( sprintf( _t( 'missing parameter: %s' ), 'old_revision_id or new_revision_id' ) );
			Utils::redirect( URL::get( 'admin', 'page=dashboard' ) );
			return;
		}

		$old_post = Posts::get( array( 'id' => $handler->handler_vars['old_revision_id'], 'fetch_fn' => 'get_row' ) );
		$new_post = Posts::get( array( 'id' => $handler->handler_vars['new_revision_id'], 'fetch_fn' => 'get_row' ) );
		$theme->assign( 'old_post', $old_post );
		$theme->assign( 'new_post', $new_post );

		$content_diff = RevisionDiff::format_diff( $old_post->content, $new_post->content );
        $theme->assign( 'content_diff', $content_diff );

		$theme->assign( 'revisions', Posts::get( array( 'info' => array( 'revision_post_id' => $old_post->info->revision_post_id ), 'orderby' => 'modified DESC', 'nolimit' => true ) ) );
		$theme->display('revision_diff');
		exit;
	}

	/**
	 * filter: adminhandler_post_loadplugins_main_menu
	 *
	 * @access public
	 * @param array $mainmenu
	 * @return array
	 */
	public function filter_adminhandler_post_loadplugins_main_menu( $mainmenu )
	{
		$post_type_id = Post::type( 'revision' );
		unset( $mainmenu['create']['submenu']['create_' . $post_type_id] );
		unset( $mainmenu['manage']['submenu']['manage_' . $post_type_id] );
		return $mainmenu;
	}

	/**
	 * filter: publish_controls
	 *
	 * @access public
	 * @param array
	 * @return array
	 */
	public function action_form_publish( $controls, $post )
	{
		if ( empty( $post->id ) ) return $controls;

		$rev_posts = Posts::get( array( 'info' => array( 'revision_post_id' => $post->id ), 'orderby' => 'modified DESC', 'nolimit' => true ) );

		$contents = '<div class="container">';
		if ( $rev_posts->count() ) {
			$contents .= '<ul>';
			foreach ( $rev_posts as $rev_post ) {
				$contents .= sprintf('<li><a href="%s">%s by %s</a></li>', URL::get( 'admin', 'page=revision&revision_id=' . $rev_post->id ), $rev_post->modified->format('F jS, Y H:i:s'), $rev_post->author->username );
			}
			$contents .= '</ul>';
		}
		else {
			$contents .= '<p>'._t('No revisions available.').'</p>';
		}
		$contents .= '</div>';

		$controls->publish_controls->append('fieldset', 'revisions', _t('Revisions'));
		$controls->publish_controls->revisions->append('static', 'revisions_list', $contents);
	}
}
?>