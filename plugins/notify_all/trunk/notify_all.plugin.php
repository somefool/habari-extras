<?php

class notify_all extends Plugin
{
	/**
	 * Add update beacon support
	 */
	public function action_update_check()
	{
		Update::add( 'Notify All', '7445fa2e-badc-4b6a-8098-4f3b85563ffb', $this->info->version );
	}

	/**
	 * Sets default values for notify_all options and userinfo
	 * Users will receive email notifications of new posts and comments by default
	 */
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			Options::set('notify_all__notify_posts', 1);
			Options::set('notify_all__notify_comments', 1);
			Options::set('notify_all__user_can_override', 1);
		}
	}

	/**
	 * Remove notify_all options on deactivation
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			Options::delete('notify_all__notify_posts');
			Options::delete('notify_all__notify_comments');
			Options::delete('notify_all__user_can_override');
		}
	}

	/**
	 * Add plugin config
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}

	/**
	 * Populate the plugin config form
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			if ( $action == _t( 'Configure' ) ) {
				$ui = new FormUI( strtolower( get_class( $this ) ) );

				$notify_posts = $ui->append( 'checkbox', 'notify_posts', 'notify_all__notify_posts', _t( 'Send email notifications for posts', 'notify_all' ) );
				$notify_comments = $ui->append( 'checkbox', 'notify_comments', 'notify_all__notify_comments', _t( 'Send email notifications for comments', 'notify_all' ) );
				$notify_user_can_override = $ui->append( 'checkbox', 'user_can_override', 'notify_all__user_can_override', _t( 'User can set whether notifications are sent', 'notify_all' ) );

				$ui->append( 'submit', 'save', 'Save' );
				$ui->out();
			}
		}
	}

	/**
	 * Add 'notify_posts' and 'notify_comments' options to the user profile page
	 */
	public function action_form_user( $form, $edit_user )
	{
		if ( Options::get( 'notify_all__user_can_override' ) ) {
			$notify_all = $form->insert('page_controls', 'wrapper', 'notify_all', _t( 'Notify All', 'notify_all' ) );
			$notify_all->class = 'container settings';
			$notify_all->append( 'static', 'post notifier', '<h2>' . htmlentities( _t( 'Notify All', 'notify_all' ), ENT_COMPAT, 'UTF-8' ) . '</h2>' );
			$notify_posts = $notify_all->append( 'checkbox', 'notify_all__notify_posts', 'null:null', _t( 'Notify Posts', 'notify_all' ), 'optionscontrol_checkbox' );
			$notify_posts->class[] = 'item clear';
			$notify_posts->helptext = _t( 'Whether the user should receive email notifications of new posts', 'notify_all' );
			$notify_posts->value = $edit_user->info->notify_all__notify_posts;
			$notify_comments = $notify_all->append( 'checkbox', 'notify_all__notify_comments', 'null:null', _t( 'Notify Comments', 'notify_all' ), 'optionscontrol_checkbox' );
			$notify_comments->class[] = 'item clear';
			$notify_comments->helptext = _t( 'Whether the user should receive email notifications of new comments', 'notify_all' );
			$notify_comments->value = $edit_user->info->notify_all__notify_comments;

			$form->move_after( $notify_all, $form->change_password );
		}
	}

	/**
	 * Add the 'notify_posts' and 'notify_comments' options to the list of valid field names.
	 * This causes adminhandler to recognize the 'notify_posts' and 'notify_comments' fields and to set the userinfo record accordingly
	 */
	public function filter_adminhandler_post_user_fields( $fields )
	{
		if ( Options::get( 'notify_all__user_can_override' ) ) {
			$fields['notify_all__notify_posts'] = 'notify_all__notify_posts';
			$fields['notify_all__notify_comments'] = 'notify_all__notify_comments';
		}
		return $fields;
	}

	/**
	 * Add help text to plugin configuration page
	 */
	public function help()
	{
		$help = _t( "<p>When there is a new post or comment this plugin sends an
			email notification to all registered users. On activation all users
			are set to receive these notifications. Users can turn off the
			notifications through the user profile page.</p>", 'notify_all'
			);
		return $help;
	}

	/**
	 * Send email notifications when a post is published
	 */
	public function action_publish_post( $post, $form )
	{
		if ( $post->status == Post::status( 'published' ) ) {
			$this->send_post_notifications( $post );
		}
	}

	/**
	 * Send email notifications when an approved comment is made
	 */
	public function action_comment_insert_after( $comment )
	{
		if ( $comment->status == Comment::STATUS_APPROVED ) {
			$this->send_comment_notifications( $comment );
		}
	}

	/**
	 * Send email notifications when an comment is approved
	 */
	public function action_admin_moderate_comments( $action, $comments, $handler )
	{
		$action = strtolower(trim($action));

		if ( $action == 'approve' || $action == 'approved' ) {
			foreach ( $comments as $c) {
				$this->send_comment_notifications( $c );
			}
		}
	}

	/**
	 * Set the 'notify_posts' and 'notify_comments' options to true if they are not already set
	 */
	private function set_notify( $user )
	{
		$commit = FALSE;
		if ( $user->info->notify_all__notify_posts == NULL ) {
			$user->info->notify_all__notify_posts = '1';
			$commit = TRUE;
		}
		if ( $user->info->notify_all__notify_comments == NULL ) {
			$user->info->notify_all__notify_comments = '1';
			$commit = TRUE;
		}
		if ( $commit ) {
			$user->info->commit();
		}
	}

	/**
	 * Send the email notifications for posts
	 */
	private function send_post_notifications( $post )
	{
		$author = User::get_by_id( $post->user_id );
		$title = sprintf(_t('[%1$s] New post: %2$s', 'notify_all'), Options::get('title'), $post->title );
		$message = <<< MESSAGE
There is a new post, "%1\$s", on %2\$s:
%3\$s

Author: %4\$s

Post:
%5\$s
MESSAGE;
		$message = _t( $message, 'notify_all' );
		$message = sprintf(
			$message,
			$post->title,
			Options::get('title'),
			$post->permalink,
			$author->username,
			$post->content
		);
		$from = $author->username . ' <' . $author->email . '>';
		$headers = array( 'From' => $from );

		$users = Users::get();
		for ( $i = 0; $i < count($users); $i++ ) {
			// if user is not allowed to override, and email notification for posts is on, send email
			// if user is allowed to override, and they have opted to receive emails, send email
			// also don't send email to the author of the post
			if (
				( ( !Options::get( 'notify_all__user_can_override' ) && Options::get( 'notify_all__notify_posts' ) ) ||
				( Options::get( 'notify_all__user_can_override' ) && $users[$i]->info->notify_all__notify_posts ) ) &&
				$users[$i]->id != $author->id
			) {
				$this->send_mail( $users[$i]->email, $title, $message, $headers, 'post' );
			}
		}
	}

	/**
	 * Send the email notifications for comments
	 */
	private function send_comment_notifications( $comment )
	{
		// we should only execute on comments, not pingbacks
		// and don't bother if the comment is know to be spam
		if ( ( $comment->type != Comment::COMMENT )
			|| ( $comment->status == Comment::STATUS_SPAM  ) ) {
			return;
		}

		$post = Post::get( array('id' => $comment->post_id ) );
		$author = User::get_by_id( $post->user_id );
		$title = sprintf(_t('[%1$s] New comment on: %2$s', 'notify_all'), Options::get('title'), $post->title);
		$message = <<< MESSAGE
There is a new comment on the post "%1\$s", on %2\$s:
%3\$s

Author: %4\$s <%5\$s>
URL: %6\$s

Comment:
%7\$s
MESSAGE;
		$message = _t( $message, 'notify_all' );
		$message = sprintf(
			$message,
			$post->title,
			Options::get('title'),
			$post->permalink . '#comment-' . $comment->id,
			$comment->name,
			$comment->email,
			$comment->url,
			$comment->content
		);

		$from = $comment->name . ' <' . $comment->email . '>';
		$headers = array( 'From' => $from );
		$users = Users::get();
		for ( $i = 0; $i < count($users); $i++ ) {
			// if user is not allowed to override, and email notification for comments is on, send email
			// if user is allowed to override, and they have opted to receive emails, send email
			// also don't send email to the email address of the person who wrote the comment
			if (
				( ( !Options::get( 'notify_all__user_can_override' ) && Options::get( 'notify_all__notify_comments' ) ) ||
				( Options::get( 'notify_all__user_can_override' ) && $users[$i]->info->notify_all__notify_comments ) ) &&
				$users[$i]->email != $comment->email
			) {
				$this->send_mail( $users[$i]->email, $title, $message, $headers, 'comment' );
			}
		}
	}

/**
 * Sends the email and writes to the log
 */
	private function send_mail( $email, $subject, $message, $headers, $type )
	{
		$headers = array_merge( array( "Content-Transfer-Encoding" => "8bit" ), $headers);
		$headers = array_merge( array( "Content-type" => "text/plain; charset=utf-8" ), $headers);
		$headers = array_merge( array( "MIME-Version" => "1.0" ), $headers);
		$message = strip_tags( $message );
		$message = Format::summarize( $message, 50, 3 );
		if ( ( Utils::mail( $email, $subject, $message, $headers ) ) === TRUE ) {
			EventLog::log( $type . ' email sent to ' . $email, 'info', 'default', 'notify_all' );
		}
		else {
			EventLog::log( $type . ' email could not be sent to  ' . $email, 'err', 'default', 'notify_all' );
		}
	}

}
?>
