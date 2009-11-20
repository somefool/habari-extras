<?php
/**
* Backtype Connect
*/
class BacktypePlugin extends Plugin
{
	/**
	* Provide plugin info to the system
	*/
	public function info() {
		return array(
			'name' => 'Backtype Connect',
			'version' => '1.0',
			'url' => 'http://seancoates.com/habari',
			'author' => 'Sean Coates',
			'authorurl' => 'http://seancoates.com/',
			'license' => 'Apache License 2.0',
			'description' => 'Backtype Connect integration; see: http://www.backtype.com/connect',
			'copyright' => '2009',
		);
	}
	
	
	public function filter_post_comments(Comments $comments, Post $post) {
		$url = Site::get_url('habari', true) . $post->slug;
		foreach (self::fetch_backtype($url) as $new) {
			$comments[] = $new;
		}
		return $comments;
	}
	
	protected static function fetch_backtype($url) {
		$backtype = array();
		$cacheName = "backtype-$url";
		if ( Cache::has( $cacheName ) ) {
			foreach (Cache::get( $cacheName ) as $cachedBacktype) {
				$cachedBacktype->date = HabariDateTime::date_create($cachedBacktype->date);
				$backtype[] = $cachedBacktype;
			}
			return $backtype;
		}
		
		$connectData = json_decode(file_get_contents("http://api.backtype.com/comments/connect.json?url={$url}&key=key&itemsperpage=10000"));
		if (isset($connectData->comments)) {
			foreach ($connectData->comments as $dat) {
				$comment = new StdClass;
				switch ($dat->entry_type) {
					case 'tweet':
						$comment->id = 'backtype-twitter-' . $dat->tweet_id;
						$comment->url = 'http://twitter.com/' . $dat->tweet_from_user . '/status/' . $dat->tweet_id;
						$comment->name = '@' . $dat->tweet_from_user . ' (via Backtype: Twitter)';
						$comment->content_out = InputFilter::filter($dat->tweet_text);
						$comment->date = $dat->tweet_created_at;
						break;
					case 'comment':
						$comment->id = 'backtype-comment-' . $dat->comment->id;
						$comment->url = $dat->comment->url;
						$comment->name = $dat->author->name . ' (via Backtype: ' . InputFilter::filter($dat->blog->title) . ')';
						$comment->content_out = InputFilter::filter($dat->comment->content);
						$comment->date = $dat->comment->date;
						break;
				}
				if (!$comment) {
					continue;
				}
				$comment->status = Comment::STATUS_APPROVED;
				$comment->type = Comment::TRACKBACK;
				$comment->email = null;
				$backtype[] = $comment;
			}
		}
		Cache::set( $cacheName, $backtype );
		return $backtype;
	}
}

?>