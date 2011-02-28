<?php
/**
 * Twitter Litte Plugin
 *
 * Usage: <?php $theme->twitterlitte(); ?>
 *
 **/

class TwitterLitte extends Plugin
{
	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->load_text_domain('twitterlitte');
		$this->add_template('block.twitterlitte', dirname(__FILE__) . '/block.twitterlitte.php');
	}
	
	public function filter_block_list($block_list)
	{
		$block_list['twitterlitte'] = _t('Twitter Litte');
		return $block_list;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add('Twitter Litte', '0c695810-c050-11dd-ad8b-0800200c9a66', $this->info->version);
	}

	public function action_block_form_twitterlitte($form, $block)
	{
		$form->append('text', 'username', $block, _t('Twitter Username', 'twitterlitte'));
		$form->username->add_validator(array($this, 'validate_username'));
		$form->username->add_validator('validate_required');
		// How many tweets to show?
		$form->append('text', 'limit', $block, _t('&#8470; of Tweets', 'twitterlitte'));
		$form->limit->add_validator(array($this, 'validate_uint'));
		// Match specified string
		$form->append('text', 'search', $block, _t('Filter by', 'twitterlitte'));
		// Cache
		$form->append('text', 'cache', $block, _t('Cache Expiry (in seconds)', 'twitterlitte'));
		$form->cache->add_validator(array($this, 'validate_uint'));
	}

	public function validate_username($username)
	{
		if (preg_match('/[A-Za-z0-9-_]+/', $username) === false) {
			return array(_t('Your Twitter username is not valid.', 'twitterlitte'));
		}
		return array();
	}

	public function validate_uint($value)
	{
		if (!ctype_digit($value) || strstr($value, '.') || $value < 0) {
			return array(_t('This field must be positive integer.', 'twitterlitte'));
		}
		return array();
	}

	private static function build_api_url($block)
	{
		if ($block->search != '') {
			$url = 'http://search.twitter.com/search.json?';
			$url .= http_build_query(array(
				'from' => $block->username,
				'phrase' => $block->search,
				'rpp' => $block->limit
				), '', '&');
		}
		else {
			$url = 'http://twitter.com/statuses/user_timeline/' . $block->username . '.json';
		}

		return $url;
	} 

	private static function get_external_content($url)
	{
		// Get JSON content via Twitter API
		$call = new RemoteRequest($url);
		$call->set_timeout(5);
		$result = $call->execute();
		if (Error::is_error($result)) {
			throw Error::raise(_t('Unable to contact Twitter.', 'twitterlitte'));
		}
		return $call->get_response_body();
	}

	private static function parse_json($block, $json)
	{
		// Decode JSON
		$obj = json_decode($json);
		if (isset($obj->query)) {
			$obj = $obj->results;
			// Strip user designate tags
			foreach ($obj as &$o) {
				$o->text = str_replace($block->search, '', $o->text);
			}
		}
		if (!is_array($obj)) {
			// Response is not JSON
			throw Error::raise(_t('Response is not correct, Twitter server may be down or API is changed.', 'twitterlitte'));
		}
		$serial = serialize($obj);
		// Convert stdClass to TwitterLitteTweet and TwitterLitteUser
		$serial = str_replace('s:4:"user";O:8:"stdClass":', 's:4:"user";O:16:"TwitterLitteUser":', $serial);
		$serial = str_replace('O:8:"stdClass":', 'O:17:"TwitterLitteTweet":', $serial);
		$tweets = unserialize($serial);
		return $tweets;
	}

	public function action_block_content_twitterlitte($block, $theme)
	{
		$cache_name = 'twitterlitte_' . md5(serialize(array(
			$block->username,
			$block->limit,
			$block->search
			)));

		if ($block->username != '') {
			if (Cache::has($cache_name)) {
				$block->tweets = Cache::get($cache_name);
			}
			else {
				try {
					$url = self::build_api_url($block);
					$json = self::get_external_content($url);
					$tweets = self::parse_json($block, $json);
					// Pass $tweets to $theme
					$block->tweets = array_slice($tweets, 0, $block->limit);
					// Do cache
					Cache::set($cache_name, $block->tweets, $block->cache);
				}
				catch (Exception $e) {
					$block->tweets = $e->getMessage();
				}
			}
		}
		else {
			$block->tweets = _t('Please set your username in the Twitter Litte block config.', 'twitterlitte');
		}
	}
}

class TwitterLitteTweet
{
	function __get($name)
	{
		switch ($name) {
			case 'url':
				return $this->user->profile_url . '/status/' . $this->id_str;
				break;
			case 'message_out':
				$message = $this->text;
				// Linkify URIs
				$message = preg_replace(
					'#(?<uri>' .
					'(?<scheme>[a-z][a-z0-9-.+]+)://' .
					'(?:(?<username>[a-z0-9-_.!~*\'()%]+)(?::(?<password>[a-z0-9-_.!~*\'()%;&=+$,]+))?@)?' .
					'(?<host>(?:(?:(?:[a-z0-9]+|[a-z0-9][a-z0-9\-]+[a-z0-9]+)\.)+(?:[a-z]|[a-z][a-z0-9\-]+[a-z0-9])+|[0-9]{1,3}(?:\.[0-9]{1,3}){3})\.?)' .
					'(?::(?<port>\d{2,5}))?' .
					'(?<path>(?:/[a-z0-9\'\-!$%&()*,.:;@_~+=]+|[a-z0-9\'\-!$%&()*,.:;?@_~+=][a-z0-9\'\-!$%&()*,./:;?@_~+=]+)+)?' .
					'(?<query>\?[a-z0-9\'\-!$%&()*,./:;?@[]_{}~+=]+)?' .
					'(?<fragment>\#[a-z0-9\'\-!$%&()*,./:;?@_~+=]+)?' .
					')#i', '<a href="${1}">${5}${7}</a>', $message);
				// Linkify Users
				$message = preg_replace('|\B@([a-z0-9_]+)\b|i', '@<a href="http://twitter.com/${1}">${1}</a>', $message);
				return $message;
				break;
			case 'user': // Append 'user' when search
				if (isset($this->user)) {
					return $this->user;
				}
				else {
					return new TwitterLitteUser(array(
						'profile_image_url' => $this->profile_image_url,
						'screen_name' => $this->from_user,
						'id' => $this->from_user_id
					));
				}
				break;
			default:
				return NULL;
				break;
		}
	}
}

class TwitterLitteUser
{
	function __construct($params = NULL)
	{
		if (is_array($params)) {
			foreach ($params as $k => $v) {
				$this->$k = $v;
			}
		}
	}

	public static function profile_url($username)
	{
		return 'http://twitter.com/' . $username;
	}

	function __get($name)
	{
		SWITCH ($name) {
			case 'profile_url':
				return self::profile_url($this->screen_name);
				break;
			default:
				return NULL;
				break;
		}
	}
}
?>
