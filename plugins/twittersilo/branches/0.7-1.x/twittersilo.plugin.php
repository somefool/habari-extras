<?php
/**
* Twitter Silo
*/
class TwitterSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'Twitter';

	protected $Twitter;
	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*	  icon- An icon to represent the silo
	*/
	public function silo_info() {
		return array( 'name' => self::SILO_NAME );
	}
	
	/**
	 * Return directory contents for the silo path
	 * @param string $path The path to retrieve the contents of
	 * @return array An array of MediaAssets describing the contents of the directory
	 **/
	public function silo_dir( $path ) {
		switch ( strtok( $path, '/' ) ) {
			case '':
				return array(
					new MediaAsset(self::SILO_NAME . '/mine/', true),
					new MediaAsset(self::SILO_NAME . '/friends/', true),
					new MediaAsset(self::SILO_NAME . '/custom/', true),
				);
				break; // (for good measure)
			
			case 'custom':
				return array(
					new MediaAsset(
						self::SILO_NAME . '/mine/custom',
						false,
						array(
							'url' => 'http://twitter.com/home',
							'filetype' => 'twittertweetcustom',
						)
					),
				);
				break; // (for good measure)
			
			case 'mine':
				return $this->get_mine();
				break; // (for good measure)

			case 'friends':
				$friend = strtok( '/' );
				if ( $friend === false ) {
					return $this->get_friends();
				} else {
					return $this->get_friend_tweets( $friend );
				}
				break; // (for good measure)
		}
	}

	/**
	 * Get the file from the specified path
	 * @param string $path The path of the file to retrieve
	 * @param array $qualities Qualities that specify the version of the file to retrieve.
	 * @return MediaAsset The requested asset
	 **/
	public function silo_get( $path, $qualities = null ) {
		return MediaAsset('foo', false);
	}

	/**
	 * Store the specified media at the specified path
	 * @param string $path The path of the file to retrieve
	 * @param MediaAsset The asset to store
	 **/
	public function silo_put( $path, $filedata ) {}

	/**
	 * Delete the file at the specified path
	 * @param string $path The path of the file to retrieve
	 **/
	public function silo_delete( $path ) {}

	/**
	 * Retrieve a set of highlights from this silo
	 * This would include things like recently uploaded assets, or top downloads
	 * @return array An array of MediaAssets to highlihgt from this silo
	 **/
	public function silo_highlights() {}

	/**
	 * Retrieve the permissions for the current user to access the specified path
	 * @param string $path The path to retrieve permissions for
	 * @return array An array of permissions constants (MediaSilo::PERM_READ, MediaSilo::PERM_WRITE)
	 **/
	public function silo_permissions( $path ) {}


	public function action_admin_footer( $theme ) {

		if ( Controller::get_var( 'page' ) == 'publish' ) {
			?><script type="text/javascript">
				function inject_tweet(text, url, user, img) {
					habari.editor.insertSelection('<!-- TWEET --><div class="twitter-tweet"><div class="tweet-text"><a href="' + url + '"><img src="' + img + '" class="tweet-image" /></a>' + text + '</div><div class="tweet-author"><a href="' + url + '">' + user + '</a></div></div><!-- /TWEET -->');
				}
				//$('.media_controls').css('display', 'none');
				habari.media.output.twittertweet = {'Insert': function(fileindex, fileobj) {
					inject_tweet(fileobj.tweet_text, fileobj.url, fileobj.tweet_user, fileobj.tweet_user_img);
				}}
				habari.media.preview.twittertweet = function(fileindex, fileobj) {
					return '<div class="mediatitle"><a href="' + fileobj.url + '" target="_new" class="medialink">media</a>' + fileobj.tweet_user_screen_name + '</div>' + fileobj.tweet_text_short;
				}
				habari.media.output.twittertweetcustom = {'Insert': function(fileindex, fileobj) {
					$.get("/auth_ajax/tweetcustom?tweet=" + escape($('#tweetcustom').val()), function( data ){
						if (data) {
							inject_tweet(data.text, 'http://twitter.com/' + escape(data.user.screen_name) + '/statuses/' + escape(data.id), data.user.screen_name, data.user.profile_image_url);
						}
					}, {}, 'json' );
				}}
				habari.media.preview.twittertweetcustom = function(fileindex, fileobj) {
					return '<div class="mediatitle">CUSTOM</div>Tweet ID/URL: <input id="tweetcustom" type="text" />';
				}
			</script><?php
		}
	}
	
	
	public function action_auth_ajax_tweetcustom( $handler ) {
		$tweet = isset( $_GET['tweet'] ) ? $_GET['tweet'] : '';
		$tweet = preg_replace( '@http://(www\.)?twitter.com/([^/]+)/([^/]+)/([0-9]+)@', '$4', $tweet );
		if ( ctype_digit( $tweet ) ) {
			$ret = self::twitter_status( $tweet );
		} else {
			$ret = false;
		}
		echo json_encode( $ret );
	}

	public function theme_header() {
		// add CSS
		return '<link rel="stylesheet" type="text/css" media="screen" href="'
					. $this->get_url( true ) . 'twittersilo.css" />';
	}
	
	protected function get_mine() {
		return $this->to_assets( self::twitter_mine(), 'mine' );
	}

	protected function get_friend_tweets ( $id ) {
		return $this->to_assets( self::twitter_friend_tweets( $id ), 'friends' );
	}
	
	protected function get_friends() {
		$friends = array();
		$friendsObj = self::twitter_friends();
		foreach ($friendsObj as $friend) {
			$friends[] = new MediaAsset(self::SILO_NAME . '/friends/' . $friend->screen_name, true);
		}
		return $friends;
	}
	
	protected function to_assets( $objs, $type ) {
		foreach ($objs as $obj) {
			$tweets[] = new MediaAsset(
				self::SILO_NAME . '/' . $type . '/' . $obj->user->name . '/' . $obj->id,
				false,
				array(
					'tweet_id' => $obj->id,
					'url' => 'http://twitter.com/' . $obj->user->screen_name . '/statuses/' . $obj->id,
					'tweet_user' => (string) $obj->user->name,
					'tweet_user_img' => (string) $obj->user->profile_image_url,
					'tweet_user_screen_name' => (string) $obj->user->screen_name,
					'tweet_text_short' => substr( $obj->text, 0, 20 ) . ( strlen( $obj->text ) > 20 ? '...' : '' ),
					'tweet_text' => (string) $obj->text,
					'filetype' => 'twittertweet',
				)
			);
		}
		return $tweets;
	}

	/**
	* Add actions to the plugin page for this plugin
	* The authorization should probably be done per-user.
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		$actions[] = 'Configure';
		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()){
			switch ($action){
				case 'Configure':
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$ui->append( 'text', 'twitter_user', 'option:twittersilo__user', _t( 'Twitter Username:' ) );
					$ui->append( 'password', 'twitter_pass', 'option:twittersilo__pass', _t( 'Twitter Password:' ) );
					$ui->append('submit', 'save', _t( 'Save' ) );
					$ui->set_option('success_message', _t('Options saved'));
					$ui->out();
					break;
			}
		}
	}
	
	protected static function twitter_fetch ( $url ) {
		if ( $user = Options::get( 'twittersilo__user' ) ) {
			// cheap hack:
			$tweetURL = preg_replace(
				'@^(https?)://@',
				'$1://' . urlencode( $user ) . ':' . Options::get('twittersilo__pass') .'@',
				$url
			);
		} else {
			$tweetURL = $url;
		}
		if ( $result = @file_get_contents( $tweetURL ) ) {
			return json_decode( $result );
		} else {
			return false;
		}
	}
	
	protected static function twitter_status( $id ) {
		return self::twitter_fetch( 'http://twitter.com/statuses/show/' . ((int)$id) . '.json' );
	}
	
	protected static function twitter_mine( ) {
		return self::twitter_fetch( 'http://twitter.com/statuses/user_timeline.json' );
	}
	
	protected static function twitter_friend_tweets( $id ) {
		return self::twitter_fetch( 'http://twitter.com/statuses/user_timeline/'. urlencode( $id ) .'.json' );
	}

	protected static function twitter_friends( ) {
		return self::twitter_fetch( 'http://twitter.com/statuses/friends.json' );
	}

}

?>
