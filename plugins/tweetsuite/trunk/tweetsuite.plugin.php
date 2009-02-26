<?php
/**
 * TweetSuite Plugin
 */

class TweetSuite extends Plugin
{
	private $class_name = '';

	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'TweetSuite',
			'version' => '0.1',
			'url' => 'http://code.google.com/p/bcse/wiki/TweetSuite',
			'author' => 'Joel Lee',
			'authorurl' => 'http://blog.bcse.info/',
			'license' => 'Apache License 2.0',
			'description' => 'Display Tweetbacks. STILL VERY BUGGY!',
			'copyright' => '2009'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add('TweetSuite', '5d8eb2b0-e609-11dd-ba2f-0800200c9a66', $this->info->version);
	}

	public function theme_after_act_display_post(Theme $theme)
	{
		$post = $theme->post;
		$cache_name = $this->class_name . '__' . $post->slug;

		if (!Cache::has($cache_name)) {
			$this->update_tweetbacks($theme->post);
			Cache::set($cache_name, TRUE, 3600); // Update Tweetbacks once an hour
		}
	}

	private function update_tweetbacks(Post $post)
	{
		// Get the lastest tweetback in database
		$tweetbacks = $post->comments->tweetbacks;
		if ($tweetbacks->count > 0) {
			$tweet_url = explode('/', $tweetbacks[0]->url);
			$since_id = end($tweet_url);
		}
		else {
			$since_id = 0;
		}

		// Get short urls
		$aliases = array_filter((array)ShortURL::get($post));
		//$aliases[] = $post->permalink; // Do not include original permalink, because Twitter Search has character limit, too.

		// Construct request URL
		$url = 'http://search.twitter.com/search.json?';
		$url .= http_build_query(array(
			'ors' => implode(' ', $aliases), // Post permalink and short-urls
			'rpp' => 50,
			'since_id' => $since_id // This makes Twiiter only return unread tweets.
			), '', '&');

		// Get JSON content
		$call = new RemoteRequest($url);
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			throw Error::raise(_t('Unable to contact Twitter.', $this->class_name));
		}
		$response = $call->get_response_body();
		// Decode JSON
		$obj = json_decode($response);
		if (isset($obj->results) && is_array($obj->results)) {
			$obj = $obj->results;
		}
		else {
			throw Error::raise(_t('Response is not correct, Twitter server may be down or API is changed.', $this->class_name));
		}

		// Store new tweetbacks into database
		foreach ($obj as $tweet) {
			Comment::create(array(
				'post_id' => $post->id,
				'name' => $tweet->from_user,
				'url' => sprintf('http://twitter.com/%1$s/status/%2$d', $tweet->from_user, $tweet->id),
				'content' => $tweet->text,
				'status' => Comment::STATUS_APPROVED,
				'date' => HabariDateTime::date_create($tweet->created_at),
				'type' => Comment::TWEETBACK
			));
		}
	}

	/**
	 * $theme->show_tweetbacks();
	 * @param Theme $theme The theme that will display the template
	 **/
	public function theme_show_tweetbacks($theme, $params = array())
	{
		return $theme->fetch('tweetbacks');
	}

	/**
	 * On plugin activation, set the default options
	 */
	public function action_plugin_activation($file)
	{
		if (realpath($file) == __FILE__) {
			$this->class_name = strtolower(get_class($this));

			foreach ($this->default_options as $name => $value) {
				$current_value = Options::get($this->class_name . '__' . $name);
				if (is_null($current_value)) {
					Options::set($this->class_name . '__' . $name, $value);
				}
			}
		}
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->class_name = strtolower(get_class($this));
		$this->load_text_domain($this->class_name);
		$this->add_template('tweetbacks', dirname(__FILE__) . '/tweetbacks.php');
	}

	public function filter_comment_profile_url($user, $comment)
	{
		return 'http://twitter.com/' . $comment->name;
	}

	public function filter_comment_content($content, $comment)
	{
		if ($comment->type == Comment::TWEETBACK) {
			// Linkify URIs
			$content = preg_replace(
				'#(?<uri>' .
				'(?<scheme>[a-z][a-z0-9-.+]+)://' .
				'(?:(?<username>[a-z0-9-_.!~*\'()%]+)(?::(?<password>[a-z0-9-_.!~*\'()%;&=+$,]+))?@)?' .
				'(?<host>(?:(?:(?:[a-z0-9]+|[a-z0-9][a-z0-9\-]+[a-z0-9]+)\.)+(?:[a-z]|[a-z][a-z0-9\-]+[a-z0-9])+|[0-9]{1,3}(?:\.[0-9]{1,3}){3})\.?)' .
				'(?::(?<port>\d{2,5}))?' .
				'(?<path>(?:/[a-z0-9\'\-!$%&()*,.:;@_~+=]+|[a-z0-9\'\-!$%&()*,.:;?@_~+=][a-z0-9\'\-!$%&()*,./:;?@_~+=]+)+)?' .
				'(?<query>\?[a-z0-9\'\-!$%&()*,./:;?@[]_{}~+=]+)?' .
				'(?<fragment>\#[a-z0-9\'\-!$%&()*,./:;?@_~+=]+)?' .
				')#i', '<a href="${1}">${5}${7}</a>', $content);
			// Linkify Users
			$content = preg_replace('|\B@([a-z0-9_]+)\b|i', '@<a href="http://twitter.com/${1}">${1}</a>', $content);
		}
		return $content;
	}
}



/**
 * ShortURL Class
 */

class ShortURL
{
	/**
	 * Generate short urls and store in post info
	 */
	public static function get(Post $post)
	{
		$shorten_url_services = array(
			'bitly' => NULL,
			'metamark' => NULL,
			'orztw' => NULL,
		//	'snipurl' => NULL, // I have no idea why this alway failed.
			'tinyurl' => NULL,
		//	'trimurl' => NULL, // tr.im always returns different shorten url, therefore it's useless to generate it beforehand.
			'tweetburner' => NULL,
			'zima' => NULL
			);
		$ret = $post->info->short_url;

		foreach ($shorten_url_services as $service => $url) {
			if (!isset($ret[$service]) || !is_string($ret[$service])) {
				$ret[$service] = self::strip_protocol(self::$service($post->permalink));
			}
		}
		$post->info->short_url = $ret;
		$post->info->commit();
		return $ret;
	}

	public static function strip_protocol($url)
	{
		$url = trim($url, '/');
		$url = str_replace('http://www.', '', $url);
		$url = str_replace('http://', '', $url);
		return $url;
	}

	public static function bitly($url)
	{
		$call = new RemoteRequest('http://api.bit.ly/shorten');
		$call->set_params(array(
			'version' => '2.0.1',
			'login' => 'bcse',
			'apiKey' => 'R_e1fc8042b300a2163b5b7d4a8cb0c85c',
			'longUrl' => $url
			));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		$response = $call->get_response_body();
		$obj = json_decode($response);
		if (is_object($obj) && $obj->statusCode === 'OK') {
			return current($obj->results)->shortUrl;
		}
		return FALSE;
	}

	public static function metamark($url)
	{
		$call = new RemoteRequest('http://metamark.net/api/rest/simple?long_url=' . urlencode($url), 'POST');
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		return trim($call->get_response_body());
	}

	public static function orztw($url)
	{
		$call = new RemoteRequest('http://0rz.tw/createget.php?url=' . urlencode($url));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		$response = $call->get_response_body();
		if (preg_match('@value="(http://0rz.tw/[\d\w-_]+)"@i', $response, $matches)) {
			return $matches[1];
		}
		return FALSE;
	}

	public static function snipurl($url)
	{
		$call = new RemoteRequest('http://snipurl.com/site/getsnip', 'POST');
		$call->set_params(array(
			'sniplink' => $url,
			'snipuser' => 'bcse',
			'snipapi' => '9fa84accb6cee0451a91feaf37e961ad'
			));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			Utils::debug($call, $result);
			return FALSE;
		}
		$response = $call->get_response_body();
		Utils::debug($response);
		$obj = new SimpleXMLElement($response);
		if ($obj instanceof SimpleXMLElement && isset($obj->id)) {
			return $obj->id;
		}
		return FALSE;
	}

	public static function tinyurl($url)
	{
		$call = new RemoteRequest('http://tinyurl.com/create.php?url=' . urlencode($url));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		$response = $call->get_response_body();
		if (preg_match('@<b>(http://tinyurl.com/[\d\w-_]+)</b>@i', $response, $matches)) {
			return $matches[1];
		}
		return FALSE;
	}

	public static function trimurl($url)
	{
		$call = new RemoteRequest('http://tr.im/api/trim_url.json?url=' . urlencode($url));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		$response = $call->get_response_body();
		$obj = json_decode($response);
		if (is_object($obj) && $obj->status->code === '200') {
			return $obj->url;
		}
		return FALSE;
	}

	public static function tweetburner($url)
	{
		$call = new RemoteRequest('http://tweetburner.com/links?link[url]=' . urlencode($url), 'POST');
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		return trim($call->get_response_body());
	}

	public static function zima($url)
	{
		$call = new RemoteRequest('http://zi.ma/');
		$call->set_params(array(
			'module' => 'ShortURL',
			'file' => 'Add',
			'mode' => 'API',
			'url' => $url
			));
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			return FALSE;
		}
		return trim($call->get_response_body());
	}
}
?>
