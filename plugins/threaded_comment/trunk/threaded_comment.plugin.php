<?php

class ThreadedComment extends Plugin
{
	const MAIL_FILENAME = 'threaded_comment_mail.php';
	const SUBJECT_DEL = '====== Subject ======';
	const TEXT_DEL = '====== Text ======';
	const HTML_DEL = '====== HTML ======';
	const END_DEL = '====== End ======';

	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			EventLog::register_type( 'ThreadedComment' );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			EventLog::unregister_type( 'ThreadedComment' );
		}
	}

	/* Add threaded_comment.js to header */
	public function action_init()
	{
		Stack::add( 'template_header_javascript', $this->get_url( true ) . 'threaded_comment.js', 'threaded_comment' );
	}

	public function configure()
	{
		$form = new FormUI( 'threaded_comment' );
		$depth = $form->append( 'text', 'threaded_depth', 'threaded_comment__depth', _t( 'Max depth of thread:' ) );
		$depth->add_validator( 'validate_regex', '/^[0-9]*$/', _t( 'Please enter a valid positive integer.' ) );
		$form->append( 'submit', 'save', _t( 'Save' ) );
		$form->set_option( 'success_message', _t( 'Configuration saved' ) );
		return $form;
	}

	/* Add comment parent field to comment */
	public function action_comment_accepted( $comment, $handlervars, $extra )
	{
		if( isset( $extra['cf_commentparent'] ) && $extra['cf_commentparent'] != '-1' ) {
			$comment->info->comment_parent = $extra['cf_commentparent'];
		}

		if( isset( $extra['cf_emailnotify'] ) ) {
			$comment->info->email_notify = 1;
		}
	}

	/* Adds the subscribe button to the comment form
	 *
	 */
	public function action_comment_form( $form )
	{

	}

	/* Email notify to subscribed user */
	public function action_comment_insert_after( $comment )
	{
		if( ( $comment->type != Comment::COMMENT ) || ( $comment->status == Comment::STATUS_SPAM ) ) {
			return;
		}

		$post = Post::get( array( 'id' => $comment->post_id ) );
		$author = User::get_by_id( $post->user_id );

		$c = $comment;
		$sent = array();
		$sent[] = $author->email;
		$sent[] = $comment->email;

		while( isset( $c->info->comment_parent ) ) {
			$cc = Comment::get( $c->info->comment_parent );

			if( isset( $cc->info->email_notify ) && $cc->info->email_notify == 1 ) {
				if( !in_array( $cc->email, $sent ) ) {
					$sent[] = $cc->email;
					$this->mail_notify( $cc->email, $cc, $comment );
				}
			}

			$c = $cc;
		}
	}

	public function action_admin_moderate_comments( $action, $comments )
	{
		if( 'approved' == $action ) {
			foreach( $comments as $c ) {
				$this->action_comment_insert_after( $c );
			}
		}
	}

	public function filter_post_threadedComments( $out, $post )
	{
		$ret = null;

		if( $post->comments->moderated->count ) {
			$ret = array();
			$comments = $post->comments->moderated->getArrayCopy();

			$num = count( $comments );
			while( $num-- > 0) {
				$c = array_pop( $comments );

				if( isset( $c->info->comment_parent ) ) {
					$p = $this->get_comment_by_id( $comments, $c->info->comment_parent );

					if( $p != null ) {
						if( !isset( $p->children ) ) {
							$children = array();
						} 
						else {
							$children = $p->children;
						}

						array_unshift( $children, $c );

						$p->children = $children;

						continue;
					}
				}

				array_unshift( $ret, $c );
			}
		}

		return $ret;
	}

	public function action_add_template_vars( $theme, $handler_vars )
	{
		if( !$theme->template_engine->assigned( 'commentThreadMaxDepth' ) ) {
			$depth = Options::get( 'threaded_comment__depth', 5 ); // default value is 5.

			if( ( isset( $theme->user ) && isset( $theme->post ) ) && ( $theme->user->id == $theme->post->user_id ) ) {
				$depth++;
			}

			$theme->assign( 'commentThreadMaxDepth', $depth );
		}
	}

	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"tc_unsubscribe"', 'ThreadedComment', 'unsubscribe' );
		return $db_rules;
	}

	public function action_handler_unsubscribe( $handler_vars )
	{
		$key = $handler_vars['id'];
		if( $key != null ) {
			$key_text = base64_decode( $key );

			$pieces = explode( ',', $key_text );
			if( count( $pieces ) == 3) {
				if( Utils::crypt( $pieces[0] . $pieces[1] ) ) {
					$comments = Comments::get( array( 'id' => $pieces[1], 'email' => $pieces[0] ) );
					if( count( $comments ) == 1 ) {
						unset( $comments[0]->info->email_notify );
						die( 'Unsubscribe successfully' );
					}
				}
			}
		}

		die( 'Invalid Request' );
	}

	private function get_comment_by_id( $comments, $id )
	{
		$ret = null;

		$begin = 0;
		$end = count( $comments) - 1;
		while( $begin <= $end ) {
			$curr = floor( ( $begin + $end ) / 2 );

			if( $comments[$curr]->id == $id ) {
				$ret = $comments[$curr];
				break;
			}
			else if( $comments[$curr]->id > $id ) {
				$end = $curr - 1;
			}
			else {
				$begin = $curr + 1;
			}
		};

		return $ret;
	}

	private function mail_notify( $email, $comment, $reply )
	{
		EventLog::log( 'Email notify ' . $email, 'info', 'ThreadedComment', 'ThreadedComment' );
		$post = Post::get( array( 'id' => $comment->post_id ) );
		$author = User::get_by_id( $post->user_id );
		$mail_data = $this->get_mail_data( $comment, $reply );
		$boundary = md5( time() );

		$headers = array(
			'MIME-Version: 1.0',
			"Content-type: multipart/alternative; boundary=\"$boundary\"",
			'From: ' . $this->mh_utf8( Options::get( 'title' ) ) . ' <no-reply@' . Site::get_url( 'hostname' ) . '>',
		);

		$message = "--$boundary" . PHP_EOL;
		$message .= 'Content-Type: text/plain; charset=UTF-8' . PHP_EOL;
		$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
		$message .= $mail_data['text'];

		$message .= PHP_EOL . "--$boundary" . PHP_EOL;
		$message .= 'Content-Type: text/html; charset=UTF-8' . PHP_EOL;
		$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
		$message .= $mail_data['html'];

		$message .= PHP_EOL . "--$boundary--" . PHP_EOL;

		mail( $email, $this->mh_utf8( $mail_data['subject'] ), $message, implode( PHP_EOL, $headers ) );
	}

	private function get_mail_data( $comment, $reply)
	{
		$ret = array();
		$eol_tag = '--EOL--';

		$fp = $this->find_file( self::MAIL_FILENAME);

		$post = Post::get( array( 'id' => $comment->post_id ) );

		/* prepare for vars used in template file */
		$site_name = Options::get( 'title' );
		$site_link = Site::get_url( 'habari' );
		$post_title = $post->title;
		$post_link = $post->permalink;
		$comment_id = $comment->id;
		$comment_author = $comment->name;
		$comment_content = str_replace( "\r\n", "\n", $comment->content );
		$comment_content = str_replace( "\n", $eol_tag, $comment_content );
		$reply_id = $reply->id;
		$reply_author = $reply->name;
		$reply_content = str_replace( "\r\n", "\n", $reply->content );
		$reply_content = str_replace( "\n", $eol_tag, $reply_content );

		$unsubscribe_link = $site_link . '/tc_unsubscribe?id=' . base64_encode( $comment->email. ',' . $comment_id . ',' . Utils::crypt( $comment->email . $comment_id ) );

		ob_start();
		include( $fp );
		$cont = ob_get_clean();

		$pieces = explode( self::END_DEL, $cont);

		$ret['subject'] = substr( $pieces[0],
		 strpos( $pieces[0], self::SUBJECT_DEL )
		 + strlen( self::SUBJECT_DEL . PHP_EOL ) );

		$ret['text'] = substr( $pieces[1],
		 strpos( $pieces[1], self::TEXT_DEL )
		 + strlen( self::TEXT_DEL . PHP_EOL ) );
		$ret['text'] = str_replace( $eol_tag, PHP_EOL, $ret['text'] );

		$ret['html'] = substr( $pieces[2],
		 strpos( $pieces[2], self::HTML_DEL )
		 + strlen( self::HTML_DEL . PHP_EOL ) );
		$ret['html'] = str_replace( $eol_tag, '<br />' . PHP_EOL, $ret['html'] );

		return $ret;
		}

	private function mh_utf8( $str )
	{
		return '=?UTF-8?B?' . base64_encode( $str ) . '?=';
	}

	private function find_file( $filename )
	{
		$theme_dir = Site::get_dir( 'theme', true );
		$fp = $theme_dir . $filename;

		if( !file_exists( $fp ) ) {
			$plugin_dir = dirname( $this->get_file() );

			$fp = $plugin_dir . '/' . $filename;
		}

		return $fp;
	}
}
?>
