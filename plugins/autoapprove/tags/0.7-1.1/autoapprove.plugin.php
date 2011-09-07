<?php

/*
 * PreApproved Class
 *
 * This class allows us to auto-approve comments
 *
 */

class AutoApprove extends Plugin
{
	/*
	 * Register the PreApproved event type with the event log
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			EventLog::register_type( 'autoapprove', 'autoapprove' );
		}
	}

	/*
	 * Unregister the PreApproved event type on deactivation if it isn't being used
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			EventLog::unregister_type( 'autoapprove', 'autoapprove' );
		}
	}

	/*
	 * function act_comment_insert_before
	 * This function is executed when the action "comment_insert_before"
	 * is invoked from a Comment object.
	 * The parent class, Plugin, handles registering the action
	 * and hook name using the name of the function to determine
	 * where it will be applied.
	 * You can still register functions as hooks without using
	 * this method, but boy, is it handy.
	 * @param Comment The comment that will be processed before storing it in the database.
	 * @return Comment The comment result to store.
	 */
	function action_comment_insert_before ( $comment )
	{
		// This plugin ignores non-comments and comments already marked as spam
		if( $comment->type == Comment::COMMENT && $comment->status != Comment::STATUS_SPAM ) {
			$comment->status = Comment::STATUS_APPROVED;
			EventLog::log( 'Comment by ' . $comment->name . ' automatically approved.', 'info', 'autoapprove', 'autoapprove' );
		}
		return $comment;
	}

	function set_priorities()
	{
	  return array( 'action_comment_insert_before' => 10 );
	}

}
?>
