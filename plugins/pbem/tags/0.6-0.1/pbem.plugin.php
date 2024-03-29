<?php

/*
 * TODO: Make increment configurable
 * TODO: Make config nicer (server type/name/port/ssl dropdown instead of server string)
 * TODO: allow user to choose content status
 */
class pbem extends Plugin
{

	public function info() {
		return array(
			'name' => 'PBEM',
			'version' => '0.1.0',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Post by sending e-mail to a special mailbox or IMAP folder.',
			'copyright' => '2009'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::add_cron( array(
				'name' => 'pbem_check_accounts',
				'callback' => array( __CLASS__, 'check_accounts' ),
				'increment' => 600,
				'description' => 'Check for new PBEM mail every 600 seconds.',
			) );
 			ACL::create_token( 'PBEM', 'Directly administer posts from the PBEM plugin', 'pbem' ); 
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::delete_cronjob( 'pbem_check_accounts' );

 			ACL::destroy_token( 'PBEM' ); 
		}
	}

	/* Go to http://your-blog/admin/pbem to immediately check the mailbox and post new posts AND SEE ERRORS. */
	function action_admin_theme_get_pbem( $handler, $theme )
	{
		self::check_accounts();
		exit;
	}

	public static function check_accounts() 
	{
		$users = Users::get();

		foreach ($users as $user) {
			$server_string = $user->info->pbem__server_string;
			$server_username = $user->info->pbem__server_username;
			$server_password = $user->info->pbem__server_password;

		if ($server_string) {
			$mh = imap_open($server_string, $server_username, $server_password, OP_SILENT | OP_DEBUG);
			$n = imap_num_msg($mh);
			for ($i = 1; $i <= $n; $i++) {
				$body = imap_body($mh, $i);
				$header = imap_header($mh, $i);
				$tags = '';
				if (stripos($body, 'tags:') === 0) {
					list($tags, $body) = explode("\n", $body, 2);
					$tags = trim(substr($tags, 5));
					$body = trim($body);
				}

				$postdata = array(
					'slug' =>$header->subject,
					'title' => $header->subject,
					'tags' => $tags,
					'content' => $body,
					'user_id' => $user->id,
					'pubdate' => HabariDateTime::date_create( date( 'Y-m-d H:i:s', $header->udate ) ),
					'status' => Post::status('published'),
					'content_type' => Post::type('entry'),
				);

// Utils::debug( $postdata ); 
				// This htmlspecialchars makes logs with &lt; &gt; etc. Is there a better way to sanitize?
				EventLog::log( htmlspecialchars( sprintf( 'Mail from %1$s (%2$s): "%3$s" (%4$d bytes)', $header->fromaddress, $header->date, $header->subject, $header->Size ) ) );

				$post = Post::create( $postdata );

				if ($post) {
					// done with the message, now delete it. Comment out if you're testing.
					imap_delete( $mh, $i );
				}
				else {
					EventLog::log( 'Failed to create a new post?' );
				}
			}
			imap_expunge( $mh );
			imap_close( $mh );
		}
	}

		return true;
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure', 'pbem');
			if ( User::identify()->can( 'PBEM' ) ) {
				// only users with the proper permission
				// should be able to retrieve the emails
				$actions[]= _t( 'Execute Now' );
			}


		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure', 'pbem') :
					$ui = new FormUI( 'pbem' );

					$server_string = $ui->append( 'text', 'server_string', 'user:pbem__server_string', _t('Mailbox (<a href="http://php.net/imap_open">imap_open</a> format): ', 'pbem') );
					$server_string->add_validator( 'validate_required' );

					$server_username = $ui->append( 'text', 'server_username', 'user:pbem__server_username', _t('Username: ', 'pbem') );
					$server_username->add_validator( 'validate_required' );

					$server_password = $ui->append( 'password', 'server_password', 'user:pbem__server_password', _t('Password: ', 'pbem') );
					$server_password->add_validator( 'validate_required' );

					$ui->append( 'submit', 'save', _t( 'Save', 'pbem' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved', 'pbem' ) );
					$ui->out();
					break;
				case _t('Execute Now','pbem') :
					$this->check_accounts();
					Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
					break;
			}
		}
	}

	/** 
	 * filter the permissions so that admin users can use this plugin 
 	 **/ 
	public function filter_admin_access_tokens( $require_any, $page, $type ) 
		{ 
		// we only need to filter if the Page request is for our page 
		if ( 'pbem' == $page ) { 
			// we can safely clobber any existing $require_any 
			// passed because our page didn't match anything in 
			// the adminhandler case statement 
			$require_any= array( 'super_user' => true, 'pbem' => true ); 
		} 
		return $require_any; 
	}

}

?>
