<?php
/**
 * StatusNet Twitter-Compat API Plugin
 *
 * Show your latest StatusNet notices in your theme and/or
 * post your latest blog post to your StatusNet service.
 *
 * Usage: <?php $theme->statusnet(); ?> to show your latest notices.
 * Copy the statusnet.php template to your active theme to customize
 * output display.
 *
 **/

class StatusNet extends Plugin
{
	public function action_update_check()
	{
		Update::add( 'StatusNet', '8676A858-E4B1-11DD-9968-131C56D89593', $this->info->version );
	}

	/**
	 * Set defaults.
	 **/
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			if ( Options::get( 'statusnet__hide_replies' ) !== 0 ) {
				Options::set( 'statusnet__hide_replies', 1 );
			}
			if ( Options::get( 'statusnet__linkify_urls' ) !== 0 ) {
				Options::set( 'statusnet__linkify_urls', 1 );
			}
			if ( !Options::get( 'statusnet__svc' )  ) {
				Options::set( 'statusnet__svc', 'identi.ca' );
			}
			if ( Options::get( 'statusnet__show' ) !== 0 ) {
				Options::set( 'statusnet__show', 1 );
			}
			if ( !Options::get( 'statusnet__limit' ) ) {
				Options::set( 'statusnet__limit', 1 );
			}
		}
	}

	/**
	 * Respond to the user selecting an action on the plugin page
	 * @param string $plugin_id The string id of the acted-upon plugin
	 * @param string $action The action string supplied via the filter_plugin_config hook
	 **/
	public function configure()
	{
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$ui->append('fieldset', 'svcinfo', _t('Service', 'statusnet'));

		$statusnet_svc = $ui->append( 'text', 'svc', 'statusnet__svc', _t('&micro;blog service:') );
		$ui->svc->move_into($ui->svcinfo);

		$statusnet_username = $ui->append( 'text', 'username', 'statusnet__username', _t('Service username:') );
		$ui->username->move_into($ui->svcinfo);

		$statusnet_password = $ui->append( 'password', 'password', 'statusnet__password', _t('Service password:') );
		$ui->password->move_into($ui->svcinfo);

		$ui->append('fieldset', 'publishinfo', _t('Publish', 'statusnet'));			

		$statusnet_post = $ui->append( 'checkbox', 'post_status', 'statusnet__post_status', 
			_t('Announce new blog posts on µblog') );
		$statusnet_post->options = array( '0' => _t('Disabled'), '1' => _t('Enabled') );
		$ui->post_status->move_into($ui->publishinfo);

		$statusnet_post = $ui->append( 'text', 'prefix', 'statusnet__prefix',
			 _t('Announcement prefix (e.g., "New post: "):') );
		$ui->prefix->move_into($ui->publishinfo);

		$ui->append('fieldset', 'subscribeinfo', _t('Subscribe', 'statusnet'));			

		$statusnet_show = $ui->append( 'checkbox', 'show', 'statusnet__show', 
			_t('Retrieve µblog notices for blog display') );
		$ui->show->move_into($ui->subscribeinfo);

		$statusnet_limit = $ui->append( 'select', 'limit', 'statusnet__limit', 
			_t('Number of notices to display:') );
		$statusnet_limit->options = array_combine(range(1, 20), range(1, 20));
		$ui->limit->move_into($ui->subscribeinfo);

		$statusnet_show = $ui->append( 'checkbox', 'hide_replies', 
			'statusnet__hide_replies', _t('Hide @replies') );
		$ui->hide_replies->move_into($ui->subscribeinfo);

		$statusnet_show = $ui->append( 'checkbox', 'linkify_urls', 
			'statusnet__linkify_urls', _t('Linkify URLs') );
		$ui->linkify_urls->move_into($ui->subscribeinfo);

		$statusnet_cache_time = $ui->append( 'text', 'cache', 'statusnet__cache', 
			_t('Cache expiry in seconds:') );
		$ui->cache->move_into($ui->subscribeinfo);

		$ui->on_success( array( $this, 'updated_config' ) );
		$ui->append( 'submit', 'save', _t('Save') );

		return $ui->get();
	}

	/**
	 * Returns true if plugin config form values defined in action_plugin_ui should be stored in options by Habari
	 * @return bool True if options should be stored
	 **/
	public function updated_config( FormUI $ui )
	{
		Session::notice( _t( 'StatusNet options saved.', 'statusnet' ) );
		$ui->save();
	}

	/**
	 * Add the StatusNet user options to the list of valid field names.
	 * This causes adminhandler to recognize the statusnet fields and
	 * to set the userinfo record appropriately
	**/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		$fields['statusnet_name'] = 'statusnet_name';
		$fields['statusnet_pass'] = 'statusnet_pass';
		return $fields;
	}

	/**
	 * Post a status to service
	 * @param string $svcurl Catenation of user server, API endpoints
	 * @param string $notice The new status to post
	 **/
	public function post_status( $svcurl, $notice, $name, $pw )
	{
		$request = new RemoteRequest( $svcurl, 'POST' );
		$request->add_header( array( 'Authorization' => 'Basic ' . base64_encode( "{$name}:{$pw}" ) ) );
		$request->set_body( 'source=habari&status=' . urlencode( $notice ) );
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
			if ( Options::get( 'statusnet__post_status' ) == '1' ) {
				$user = User::get_by_id( $post->user_id );
				if ( ! empty( $user->info->statusnet_name ) && ! empty( $user->info->statusnet_pass ) ) {
					$name = $user->info->statusnet_name;
					$pw = $user->info->statusnet_pass;
				} else {
					$name = Options::get( 'statusnet__username' );
					$pw = Options::get( 'statusnet__password' );
				}
				$svcurl = 'https://' . Options::get('statusnet__svc') . '/api/statuses/update.xml';
				$this->post_status( $svcurl, Options::get( 'statusnet__prefix' ) . $post->title . ' ' . $post->permalink, $name, $pw );
			}
		}
	}

	public function action_post_insert_after( $post )
	{
		return $this->action_post_update_status( $post, -1, $post->status );
	}

	/**
	 * Add last service status, time, and image to the available template vars
	 * for the use of the theme_statusnet and action_block functions.
	 **/
	public function notices()
	{
		$notices = array();
		if ( Options::get( 'statusnet__show' ) && Options::get( 'statusnet__svc' ) && Options::get( 'statusnet__username' ) != '' ) {
			$statusnet_url = 'http://' . Options::get( 'statusnet__svc' ) . '/api/statuses/user_timeline/' . urlencode( Options::get( 'statusnet__username' ) ) . '.xml';
			
			/* 
			 * Only need to get a single notice if @replies are hidden.
			 * (Otherwise, rely on the maximum returned and hope one is a non-reply.)
			 */
			if ( !Options::get( 'statusnet__hide_replies' ) &&  Options::get( 'statusnet__limit' ) ) {
				$statusnet_url .= '?count=' . Options::get( 'statusnet__limit' );
			}
			// get cache group.
			if ( Cache::has_group('statusnet') ) {
				$notices = Cache::get_group('statusnet');
			}
			else {
				try {
					$response = RemoteRequest::get_contents( $statusnet_url );
					$xml = @new SimpleXMLElement( $response );
					// Check we've got a load of statuses returned
					if ( $xml->getName() === 'statuses' ) {
						foreach ( $xml->status as $status ) {
							if ( ( !Options::get( 'statusnet__hide_replies' ) ) || ( strpos( $status->text, '@' ) === false) ) {
								$notice = (object) array (
									'text' => (string) $status->text, 
									'time' => (string) $status->created_at, 
									'image_url' => (string) $status->user->profile_image_url,
									'id' => (int) $status->id,
									'permalink' => 'http://' . Options::get('statusnet__svc') . '/notice/' . (string) $status->id,
								);
								
								$notices[] = $notice;
								if ( Options::get( 'statusnet__hide_replies' ) && count($notices) >= Options::get( 'statusnet__limit' ) )
									break;
							}
							else {
							// it's a @. Keep going.
							}
						}
						if ( !$notices ) {
							$notice->text = 'Only replies available from service.';
							$notice->permalink = '';
							$notice->time = '';
							$notice->image_url = '';
						}
					}
					// You can get error as a root element if service is in maintance mode.
					else if ( $xml->getName() === 'error' ) {
						$notice->text = (string) $xml;
						$notice->permalink = '';
						$notice->time = '';
						$notice->image_url = '';
					}
					// Should not be reached.
					else {
						$notice->text = 'Received unexpected XML from service.';
						$notice->permalink = '';
						$notice->time = '';
						$notice->image_url = '';
					}
				}
				catch ( Exception $e ) {
					$notice->text = 'Unable to contact service.';
					$notice->permalink = '';
					$notice->time = '';
					$notice->image_url = '';
				}
				if (!$notices) {
					$notices[] = $notice;
				}
				// Cache (even errors) to avoid hitting rate limit.
				// Use cache group to cache multiple statuses (objects)
				foreach ($notices as $i => $notice) {
					Cache::set( array('statusnet', $i), $notice, Options::get( 'statusnet__cache' ) );
				}
			}
			if ( Options::get( 'statusnet__linkify_urls' ) != FALSE ) {
				/* http: links */
				foreach ($notices as $notice) {
					$notice->text = preg_replace( '%https?://\S+?(?=(?:[.:?"!$&\'()*+,=]|)(?:\s|$))%i', "<a href=\"$0\">$0</a>", $notice->text );
				}
			}
		}
		else {
			$notice = (object) array (
			'text' => _t('Check "Service username" and "Retrieve µblog notices" settings in <a href="%s">StatusNet plugin config</a>', array( URL::get( 'admin' , 
			'page=plugins&configure=' . $this->plugin_id . '&configaction=Configure' ) . '#plugin_' . $this->plugin_id ) , 'statusnet' ), 
			'time' => '', 
			'image_url' => ''
			);
			$notices[] = $notice;
		}
		return $notices;
	}

	/**
	 * The older, $theme->statusnet() handle.
	 * @param Theme $theme The theme that will display the template	 
	 */
	public function theme_statusnet( $theme )
	{
		$theme->notices = $this->notices();
		return $theme->fetch( 'statusnet' );
	}

	/**
	 * The newer Blocks and Areas support.
	 */	
	public function filter_block_list($block_list)
	{
		$block_list['statusnet'] = _t('StatusNet');
		return $block_list;
	}
	
	public function action_block_content_statusnet($block, $theme)
	{
		$block->notices = $this->notices();
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init()
	{
		$this->add_template('statusnet', dirname(__FILE__) . '/statusnet.php');
		$this->add_template('block.statusnet', dirname(__FILE__) . '/block.statusnet.php');
	}	
	
}

?>
