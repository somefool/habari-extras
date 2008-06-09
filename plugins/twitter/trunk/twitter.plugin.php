<?php
/**
 * Twitter Plugin
 *
 * Lets you show your current Twitter status in your theme, as well
 * as an option automatically post your new posts to Twitter.
 *
 * Usage: <?php $theme->twitter (); ?> to show your latest tweet in a theme.
 * A sample tweets.php template is included with the plugin.  This can be copied to your
 * active theme and modified.
 *
 **/

class Twitter extends Plugin
{
	/**
	 * Required plugin information
	 * @return array The array of information
	 **/
	public function info()
	{
		return array(
			'name' => 'Twitter',
			'version' => '0.9',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Twitter plugin for Habari',
			'copyright' => '2008'
		);
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Twitter', 'DD2774BA-96ED-11DC-ABEF-3BAA56D89593', $this->info->version );
	}

	/**
	 * Add actions to the plugin page for this plugin
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The string id of a plugin, generated by the system
	 * @return array The array of actions to attach to the specified $plugin_id
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= 'Configure';
		}

		return $actions;
	}


	/**
	 * Sets the new 'hide_replies' option to '0' to mimic current, non-reply-hiding
	 * functionality.
	 **/

	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			if ( Options::get( 'twitter__hide_replies' ) == null ) {
				Options::set( 'twitter__hide_replies', 0 );
			}
		}
	}


	/**
	 * Respond to the user selecting an action on the plugin page
	 * @param string $plugin_id The string id of the acted-upon plugin
	 * @param string $action The action string supplied via the filter_plugin_config hook
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case 'Configure' :
					$ui= new FormUI( strtolower( get_class( $this ) ) );
					$twitter_username= $ui->append( 'text', 'username', 'twitter__username', 'Twitter Username:' );
					$twitter_password= $ui->append( 'password', 'password', 'twitter__password', 'Twitter Password:' );
					$twitter_post= $ui->append( 'select', 'post_status', 'twitter__post_status', 'Autopost to Twitter:' );
					$twitter_post->options= array( '0' => 'Disabled', '1' => 'Enabled' );
					$twitter_show= $ui->append( 'select', 'show', 'twitter__show', 'Make Tweets available to Habari' );
					$twitter_show->options= array( '0' => 'No', '1' => 'Yes' );
					$twitter_show= $ui->append( 'select', 'hide_replies', 'twitter__hide_replies', 'Hide @replies' );
					$twitter_show->options= array( '1' => 'Yes' , '0' => 'No' );
					$twitter_cache_time= $ui->append( 'text', 'cache', 'twitter__cache', 'Cache expiry in seconds:' );
					// $ui->on_success( array( $this, 'updated_config' ) );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					break;
			}
		}
	}

	/**
	 * Returns true if plugin config form values defined in action_plugin_ui should be stored in options by Habari
	 * @return bool True if options should be stored
	 **/
	public function updated_config( $ui )
	{
		return true;
	}

	/**
	 * Add the Twitter options to the list of valid field names.
	 * This causes adminhandler to recognize the Twitter fields and
	 * to set the userinfo record appropriately
	**/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		$fields['twitter_name']= 'twitter_name';
		$fields['twitter_pass']= 'twitter_pass';
		return $fields;
	}

	/**
	 * Add Twitter options to the user profile page.
	 * Should only be displayed when a user accesses their own profile.
	**/
	public function action_theme_admin_user( $user )
	{
		// only allow the user to set this option for themselves
		if ( User::identify() != $user ) {
			return;
		}
		$twitter_name= (isset($user->info->twitter_name)) ? $user->info->twitter_name : '';
		$twitter_pass= (isset($user->info->twitter_pass)) ? $user->info->twitter_pass : '';
		_e('Your Twitter user name: ');
		echo '<input type="text" size="20" value="' . $twitter_name . '"><br>';
		_e('Your Twitter password: ');
		echo '<input type="password" size="20" value="' . $twitter_pass . '"><br>';
	}

	/**
	 * Post a status to Twitter
	 * @param string $tweet The new status to post
	 **/
	public function post_status( $tweet, $name, $pw )
	{
		$request= new RemoteRequest( 'http://twitter.com/statuses/update.xml', 'POST' );
		$request->add_header( array( 'Authorization' => 'Basic ' . base64_encode( "{$name}:{$pw}" ) ) );
		$request->set_body( 'source=habari&status=' . urlencode( $tweet ) );
		$request->execute();

	}

	/**
	 * React to the update of a post status to 'published'
	 * @param Post $post The post object with the status change
	 * @param int $oldvalue The old status value
	 * @param int $newvalue The new status value
	 **/
	public function action_post_update_status( $post, $oldvalue, $newvalue )
	{
		if ( is_null( $oldvalue ) ) return;
		if ( $newvalue == Post::status( 'published' ) && $post->content_type == Post::type('entry') && $newvalue != $oldvalue ) {
			if ( Options::get( 'twitter:post_status' ) == '1' ) {
				$user= User::get_by_id( $post->user_id );
				if ( ! empty( $user->info->twitter_name ) && ! empty( $user->info->twitter_pass ) ) {
					$name= $user->info->twitter_name;
					$pw= $user->info->twitter_pass;
				} else {
					$name= Options::get( 'twitter:username' );
					$pw= Options::get( 'twitter:password' );
				}
				$this->post_status( 'New Blog post: ' . $post->title . ' ' . $post->permalink, $name, $pw );
			}
		}
	}

	public function action_post_insert_after( $post )
	{
		return $this->action_post_update_status( $post, -1, $post->status );
	}

	/**
	 * Add last Twitter status, time, and image to the available template vars
	 * @param Theme $theme The theme that will display the template
	 **/
	public function theme_twitter( $theme )
	{
		if ( Options::get( 'twitter:show' ) && Options::get( 'twitter:username' ) != '' ) {
			$twitter_url= 'http://twitter.com/statuses/user_timeline/' . urlencode( Options::get( 'twitter:username' ) ) . '.xml';

			if ( Cache::has( 'twitter_tweet_text' ) && Cache::has( 'twitter_tweet_time' ) && Cache::has( 'tweet_image_url' ) ) {
				$theme->tweet_text= Cache::get( 'twitter_tweet_text' );
				$theme->tweet_time= Cache::get( 'twitter_tweet_time' );
				$theme->tweet_image_url= Cache::get( 'tweet_image_url' );
			}
			else {
				try {
					$response= RemoteRequest::get_contents( $twitter_url );
					$xml= new SimpleXMLElement( $response );
					if ( $xml->getName() === 'statuses' ) {
						foreach ( $xml->status as $status ) {
							if ( ( Options::get( 'twitter:hide_replies' ) == '0' ) || ( strpos( $status->text,"@" ) === false) ) {
								$theme->tweet_text= (string) $status->text;
								$theme->tweet_time= (string) $status->created_at;
								$theme->tweet_image_url= (string) $status->user->profile_image_url;
								break;
							}
							else {
							// it's a @. Keep going.
							}
						}
						if ( !isset( $theme->tweet_text ) ) {							
							$theme->tweet_text= 'No non-replies replies available from Twitter.';
							$theme->tweet_time= '';
							$theme->tweet_image_url= '';
						}
					}
					else if ( $xml->getName() === 'error' ) {
						$theme->tweet_text= (string) $xml;
						$theme->tweet_time= '';
						$theme->tweet_image_url= '';
					}
					else {
						$theme->tweet_text= 'Received unexpected XML from Twitter.';
						$theme->tweet_time= '';
						$theme->tweet_image_url= '';
					}
					Cache::set( 'twitter_tweet_text', $theme->tweet_text, Options::get( 'twitter:cache' ) );
					Cache::set( 'twitter_tweet_time', $theme->tweet_time, Options::get( 'twitter:cache' ) );
					Cache::set( 'tweet_image_url', $theme->tweet_image_url, Options::get( 'twitter:cache' ) );
				}
				catch ( Exception $e ) {
					$theme->tweet_text= 'Unable to contact Twitter.';
					$theme->tweet_time= '';
					$theme->tweet_image_url= '';
				}
			}
		}
		else {
			$theme->tweet_text= 'Please set your username in the Twitter plugin config.';
			$theme->tweet_time= '';
			$theme->tweet_image_url= '';
		}
		return $theme->fetch( 'tweets' );
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->add_template('tweets', dirname(__FILE__) . '/tweets.php');
	}
}

?>
