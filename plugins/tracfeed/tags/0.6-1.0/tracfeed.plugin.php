<?php

define('TRAC_DB_PATH', '/var/www/sites/habariproject/trac/habari/db/trac.db');

class TracFeed extends Plugin
{
	public function info()
	{
		return array (
			'name' => 'TracFeed',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '1.0',
			'description' => 'Produces a feed from the Trac database',
			'license' => 'Apache License 2.0',
		);
	}

	public function filter_rewrite_rules( $rules ) {
		$rules[] = new RewriteRule(array(
			'name' => 'item',
			'parse_regex' => '%feed/dev/?$%i',
			'build_str' => 'feed/dev',
			'handler' => 'UserThemeHandler',
			'action' => 'dev_feed',
			'priority' => 7,
			'is_active' => 1,
		));
		
		return $rules;
	}

	public function action_handler_dev_feed($handler_vars)
	{
		$xml= RSS::create_rss_wrapper();
		
		$db = DatabaseConnection::ConnectionFactory('sqlite:' . TRAC_DB_PATH);
		$db->connect('sqlite:' . TRAC_DB_PATH, '', '');
		
		$times = $db->get_column('SELECT time from ticket_change group by time order by time desc limit 15;');
		$mintime = array_reduce($times, 'min', reset($times));
		
		$comments = $db->get_results("
			SELECT *, ticket_change.time as changetime from ticket_change 
			INNER JOIN ticket on ticket.id = ticket_change.ticket
			where 
			changetime >= ? and
			not (field ='comment' and newvalue = '') 
			order by ticket_change.time DESC;
		", array($mintime));
		
		$posts = array();
		foreach($comments as $comment) {
			$post_id = md5($comment->ticket . ':' . $comment->changetime . ':' . $comment->author);
			if(!array_key_exists($post_id, $posts)) {
				$post = new Post();
				$post->title = "Ticket #{$comment->ticket}: {$comment->summary}";
				$post->content = "Changes by {$comment->author} on ticket <a href=\"http://trac.habariproject.org/habari/ticket/{$comment->ticket}\">#{$comment->ticket}</a>.\n<br>\n<br>";
				$post->guid = 'tag:' . Site::get_url( 'hostname' ) . ',trac_comment,' . $post_id;
				$post->content_type = 'dev_feed';
				$post->slug = "http://trac.habariproject.org/habari/ticket/{$comment->ticket}";
				$post->pubdate = date('Y-m-d H:i:s', $comment->changetime);
				$posts[$post_id] = $post;
			}
			else {
				$post = $posts[$post_id];
			}
			switch($comment->field) {
				case 'comment':
					$content = $comment->newvalue;
					$content = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', '<a href="$0">[link]</a>', $content);
					$content = preg_replace('%\br([0-9]+)\b%', '<a href="http://trac.habariproject.org/habari/changeset/$1">r$1</a>', $content);
					$content = preg_replace('%\b#([0-9]+)\b%', '<a href="http://trac.habariproject.org/habari/ticket/$1">$1</a>', $content);
					$post->content .= "Comment #{$comment->oldvalue} by {$comment->author}:\n<blockquote><pre>{$content}</pre></blockquote>\n<br>";
					break;
				default:
					if(trim($comment->oldvalue) == '') {
						$post->content .= "Set <b>{$comment->field}</b> to: {$comment->newvalue}\n<br>";
					}
					else {
						$post->content .= "Changed <b>{$comment->field}</b> from: {$comment->oldvalue}\n<br> To: {$comment->newvalue}\n<br>";
					}
					break;
			}
			
		}
		

		$xml= RSS::add_posts($xml, $posts );
		ob_clean();

		header( 'Content-Type: application/xml' );
		echo $xml->asXML();
		exit;
	}

	function filter_post_permalink($permalink, $post)
	{
		if($post->content_type == 'dev_feed') {
			return $post->slug;
		}
		return $permalink;
	}


}


?>
