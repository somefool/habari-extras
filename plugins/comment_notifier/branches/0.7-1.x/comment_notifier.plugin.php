<?php

class comment_notifier extends Plugin
{
	const VERSION = '1.2';
	
	public function info()
	{
		return array(
			'name' => 'Comment Notifier', 
			'url' => 'http://habariproject.org/', 
			'author' => 'Habari Community', 
			'authorurl' => 'http://habariproject.org/', 
			'version' => self::VERSION, 
			'description' => 'Send an email to the author of a post whenever a non-spam comment is moderated for one of their posts.', 
			'license' => 'Apache License 2.0'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Comment Notifier', '91175a80-38f6-11dd-ae16-0800200c9a66', $this->info->version );
	}
	
	private function mh_utf8($str) {
		return '=?UTF-8?B?' . base64_encode($str) . '?=';
	}
	
	public function action_comment_insert_after( $comment )
	{
		// we should only execute on comments, not pingbacks
		// and don't bother if the comment is know to be spam
		if ( ( $comment->type != Comment::COMMENT ) 
			|| ( $comment->status == Comment::STATUS_SPAM  ) ) {
			return;
		}

		$post = Post::get( array('id' => $comment->post_id ) );
		$author = User::get_by_id( $post->user_id );
		$status = $comment->status == Comment::STATUS_UNAPPROVED ? ' UNAPPROVED' : ' approved';
		$title = sprintf(_t('[%1$s] New%3$s comment on: %2$s'), Options::get('title'), $post->title, $status);
		$message = <<< MESSAGE
The following comment was added to the post "%1\$s".
%2\$s

Author: %3\$s <%4\$s>
URL: %5\$s

%6\$s

-----
Moderate comments: %7\$s
MESSAGE;
		$message = _t($message);
		$message = sprintf(
			$message,
			$post->title,
			$post->permalink,
			$comment->name,
			$comment->email,
			$comment->url,
			$comment->content,
			URL::get('admin', 'page=comments')
		);

		$headers = array(
			'MIME-Version: 1.0',
			'Content-type: text/plain; charset=utf-8',
			'Content-Transfer-Encoding: 8bit',
			'From: ' . $this->mh_utf8($comment->name) . ' <' . $comment->email . '>',
		);
		mail ($author->email, $this->mh_utf8($title), $message, implode("\r\n", $headers));
	}
}
?>
