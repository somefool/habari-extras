<?php

/**
 * Bumping - Bumps a post to first position (order by modified) if it's
 * modified or a comment is made (and approved)
 */

class Bumping extends Plugin
{
	function filter_template_where_filters( $where_filters )
	{
		$where_filters["orderby"] = "modified DESC, pubdate DESC";
		return $where_filters;
	}

	function action_comment_insert_after( $comment )
	{
		// check if comment is approved (user is logged in)
		if ( $comment->status == Comment::STATUS_APPROVED ) {
			$this->update_post_modified( $comment->post_id );
			EventLog::log( 'bumped post ' . $comment->post_id, 'info', 'default', 'bumping' );
		}
	}

	function action_admin_moderate_comments( $action, $comments, $handler )
	{
		if ( $action == 'approve' ) {
			foreach ( $comments as $c ) {
				$this->update_post_modified( $c->post_id );
				EventLog::log( 'bumped post ' . $c->post_id . ', by admin approval', 'info', 'default', 'bumping' );
			}
		}
	}

	private function update_post_modified( $post_id )
	{
		$post = Post::get( array( 'id' => $post_id ) );
		$post->modified = HabariDateTime::date_create();
		$post->update();
	}
}
?>
