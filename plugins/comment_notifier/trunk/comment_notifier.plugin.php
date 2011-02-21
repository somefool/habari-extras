<?php
class comment_notifier extends Plugin
{
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
		$title = _t( '[%1$s] New%3$s comment on: %2$s', array( Options::get( 'title' ), $post->title, $status ) );
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
