<?php
/**
* Twitter Silo
*/
class TwitterSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'Twitter';
	const CONSUMER_KEY = 'xBsV65xckJicZlcjHorQ';
	const CONSUMER_SECRET = 'm6HEFdJBYAd45mbo33GIgnSmmcbyx3Wo1YCymgUlc';

	protected $Twitter;

	/**
	* Provide plugin info to the system
	*/
	public function info() {
		return array(
			'name' => 'Twitter Tweet Silo',
			'version' => '1.1',
			'url' => 'http://seancoates.com/habari',
			'author' => 'Sean Coates',
			'authorurl' => 'http://seancoates.com/',
			'license' => 'Apache License 2.0',
			'description' => 'Simple Twitter Silo',
			'copyright' => '2008',
		);
	}

	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*	  icon- An icon to represent the silo
	*/
	public function silo_info()
	{
		return array( 'name' => self::SILO_NAME );
	}
	
	/**
	 * Return directory contents for the silo path
	 * @param string $path The path to retrieve the contents of
	 * @return array An array of MediaAssets describing the contents of the directory
	 **/
	public function silo_dir( $path )
	{
		switch ( strtok( $path, '/' ) ) {
			case '':
				return array(
					new MediaAsset( self::SILO_NAME . '/mine/', true ),
					new MediaAsset( self::SILO_NAME . '/friends/', true ),
					new MediaAsset( self::SILO_NAME . '/custom/', true ),
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
				}
				else {
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
	public function silo_get( $path, $qualities = null )
	{
		return MediaAsset( 'foo', false );
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


	public function action_admin_footer( $theme )
	{
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
					$.get("<?php echo Site::get_url('habari'); ?>/auth_ajax/tweetcustom?tweet=" + escape($('#tweetcustom').val()), function( data ){
						if (data.text) {
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
	
	
	public function action_auth_ajax_tweetcustom( $handler )
	{
		$tweet = isset( $_GET['tweet'] ) ? $_GET['tweet'] : '';
		$tweet = preg_replace( '@http://(www\.)?twitter.com/([^/]+)/([^/]+)/([0-9]+)@', '$4', $tweet );
		if ( ctype_digit( $tweet ) ) {
			$ret = self::twitter_status( $tweet );
		}
		else {
			$ret = false;
		}
		echo json_encode( $ret );
	}

	public function theme_header()
	{
		// add CSS
		return '<link rel="stylesheet" type="text/css" media="screen" href="'
					. $this->get_url( true ) . 'twittersilo.css" />';
	}
	
	protected function get_mine()
	{
		return $this->to_assets( self::twitter_mine(), 'mine' );
	}

	protected function get_friend_tweets ( $id )
	{
		return $this->to_assets( self::twitter_friend_tweets( $id ), 'friends' );
	}
	
	protected function get_friends()
	{
		$friends = array();
		$friendsObj = self::twitter_friends();
		if ( ! empty( $friendsObj ) ) {
			foreach ( $friendsObj as $friend ) {
				$friends[] = new MediaAsset( self::SILO_NAME . '/friends/' . $friend->screen_name, true );
			}
		}
		return $friends;
	}
	
	protected function to_assets( $objs, $type )
	{
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
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			if ( User::identify()->info->twittersilo__access_token  ) {
				$actions['deauthorize'] = _t( 'De-Authorize' );
			}
			else {
				$actions['authorize'] = _t( 'Authorize' );
			}
		}
		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$ui = new FormUI( strtolower( __CLASS__ ) );
			$user = User::identify();
			require_once dirname( __FILE__ ) . '/lib/twitteroauth/twitteroauth.php';

			switch ( $action ){
				case 'authorize':
					unset( $_SESSION['TwitterSiloReqToken'] ); // Just being safe.
					$oauth = new TwitterOAuth(TwitterSilo::CONSUMER_KEY, TwitterSilo::CONSUMER_SECRET );
					$oauth_token = $oauth->getRequestToken( URL::get( 'admin', array( 'page' => 'plugins', 'configure' => $this->plugin_id(), 'configaction' => 'confirm' ) ) );
					$request_link = $oauth->getAuthorizeURL( $oauth_token );
					$reqToken = array( "request_link" => $request_link, "request_token" => $oauth_token['oauth_token'], "request_token_secret" => $oauth_token['oauth_token_secret'] );
					$_SESSION['TwitterSiloReqToken'] = serialize( $reqToken );
					$ui->append( 'static', 'nocontent', '<h3>Authorize the Habari TwitterSilo Plugin</h3>
														 <p>Authorize your blog to have access to your Twitter account.</p>
														 <p>Click the button below, and you will be taken to Twitter.com. If you\'re already logged in, you will be presented with the option to authorize your blog. Press the "Allow" button to do so, and you will come right back here.</p>
														 <br><p style="text-align:center"><a href="'.$reqToken['request_link'].'"><img src="'. URL::get_from_filesystem( __FILE__ ) .'/lib/twitter_connect.png" alt="Sign in with Twitter" /></a></p>
								');
					$ui->out();
					break;

				case 'confirm':
					if( !isset( $_SESSION['TwitterSiloReqToken'] ) ){
						$auth_url = URL::get( 'admin', array( 'page' => 'plugins', 'configure' => $this->plugin_id(), 'configaction' => 'authorize' ) );
						$ui->append( 'static', 'nocontent', '<p>'._t( 'Either you have already authorized Habari to access your Twitter account, or you have not yet done so.  Please ' ).'<strong><a href="' . $auth_url . '">'._t( 'try again' ).'</a></strong>.</p>');
						$ui->out();
					}
					else {
						$reqToken = unserialize( $_SESSION['TwitterSiloReqToken'] );
						$oauth = new TwitterOAuth( TwitterSilo::CONSUMER_KEY, TwitterSilo::CONSUMER_SECRET, $reqToken['request_token'], $reqToken['request_token_secret'] );
				        $token = $oauth->getAccessToken($_GET['oauth_verifier']);
						//$config_url = URL::get( 'admin', array( 'page' => 'plugins', 'configure' => $this->plugin_id(), 'configaction' => 'Configure' ) );

						if( ! empty( $token ) && isset( $token['oauth_token'] ) ){
							$user->info->twittersilo__access_token = $token['oauth_token'];
							$user->info->twittersilo__access_token_secret = $token['oauth_token_secret'];
							$user->info->twittersilo__user_id = $token['user_id'];
							$user->info->commit();
							echo '<form><p>'._t( 'Habari TwitterSilo plugin successfully authorized.' ).'</p></form>';
							Session::notice( _t( 'Habari TwitterSilo plugin successfully authorized.', 'twittersilo' ) );
							//Utils::redirect( $config_url );
						}
						else{
							// TODO: We need to fudge something to report the error in the event something fails.  Sadly, the Twitter OAuth class we use doesn't seem to cater for errors very well and returns the Twitter XML response as an array key.
							// TODO: Also need to gracefully cater for when users click "Deny"
							echo '<form><p>'._t( 'There was a problem with your authorization.' ).'</p></form>';
						}
						unset( $_SESSION['TwitterSiloReqToken'] );
					}
					break;
				case 'deauthorize':
					$user->info->twittersilo__user_id = '';
					$user->info->twittersilo__access_token = '';
					$user->info->twittersilo__access_token_secret = '';
					$user->info->commit();
					$reauth_url = URL::get( 'admin', array( 'page' => 'plugins', 'configure' => $this->plugin_id(), 'configaction' => 'authorize' ) ) . '#plugin_options';
					$ui->append( 'static', 'nocontent', '<p>'._t( 'The Twitter Plugin authorization has been deleted. Please ensure you ' ) . '<a href="http://twitter.com/settings/connections" target="_blank">' . _t( 'revoke access ' ).'</a>'._t( 'from your Twitter account too.' ).'<p><p>'._t( 'Do you want to ' ).'<b><a href="'.$reauth_url.'">'._t( 're-authorize this plugin' ).'</a></b>?<p>' );
					Session::notice( _t( 'Habari TwitterSilo plugin authorization revoked. <br>Don\'t forget to revoke access on Twitter itself.', 'twitter' ) );
					//Utils::redirect( $reauth_url );
					$ui->out();
					break;
			}
		}
	}
	
	protected static function twitter_fetch ( $url ) 
	{
		$user = User::identify();
		require_once dirname( __FILE__ ) . '/lib/twitteroauth/twitteroauth.php';

		$connection = new TwitterOAuth( TwitterSilo::CONSUMER_KEY, TwitterSilo::CONSUMER_SECRET, $user->info->twittersilo__access_token, $user->info->twittersilo__access_token_secret );
		$connection->useragent = 'Habari Twitter Silo - 1.1';

		if ( $result = $connection->get( $url ) ) {
			return $result;
		}
		else {
			return false;
		}
	}
	
	protected static function twitter_status( $id )
	{
		return self::twitter_fetch( 'statuses/show/' . $id );
	}
	
	protected static function twitter_mine( )
	{
		return self::twitter_fetch( 'statuses/user_timeline' );
	}
	
	protected static function twitter_friend_tweets( $id )
	{
		return self::twitter_fetch( 'statuses/user_timeline/'. urlencode( $id ) );
	}

	protected static function twitter_friends( )
	{
		return self::twitter_fetch( 'statuses/friends' );
	}

}

?>
