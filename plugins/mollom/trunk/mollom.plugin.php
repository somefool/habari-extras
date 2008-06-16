<?php

/**
 * Mollom Plugin for Habari
 *
 * @todo style by "mollom_quality" ... like defensio plugin.
 * @todo add cron to update server list every month.
 * @todo add fallback for blacklist
 * @todo add fallback for reputaion when mollom implements api access
 * @todo 
 *
 */

require "mollom.php";

class MollomPlugin extends Plugin
{

	public function info()
	{
		return array(
			'name' => 'Mollom',
			'author' => 'Habari Community',
			'description' => 'Provides the Mollom content filtering webservice to Habari.',
			'url' => 'http://habariproject.org',
			'version' => '0.2',
			'license' => 'Apache License 2.0'
			);
	}

	public function set_priorities()
	{
		return array(
			'action_comment_insert_before' => 1,
			'action_mollom_feedback' => 10
		);
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			Session::notice( _t( 'Please set your Mollom API Keys in the configuration.', 'mollom' ) );
			Options::set( 'mollom__public_key', '' );
			Options::set( 'mollom__private_key', '' );
			Options::set( 'mollom__servers', array() );
			CronTab::add_monthly_cron( 'mollom', 'mollom_update_server_list_cron', 'Cron job to update mollom server list every month' );
		}
	}
	
	public function filter_dash_modules( $modules )
	{
		$this->add_template( 'mollom_stats', dirname( __FILE__ ) . '/templates/dash_module_mollom.php' );
		$modules[] = 'Mollom Stats';
		return $modules;
	}
	
	public function filter_dash_module_mollom_stats( $module, $id, $theme )
	{
		$theme->mollom_stats = $this->theme_mollom_stats();
		$module['content'] = $theme->fetch( 'mollom_stats' );
		return $module;
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure', 'mollom' );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure', 'mollom' ) :
					$ui= new FormUI( 'mollom' );

					$public_key= $ui->append( 'text', 'public_key', 'option:mollom__public_key', _t( 'Mollom Public Key: ', 'mollom' ) );
					$public_key->add_validator( 'validate_required' );
					$public_key->add_validator( array( $this, 'validate_public_key' ) );

					$private_key= $ui->append( 'text', 'private_key', 'option:mollom__private_key', _t( 'Mollom Private Key: ', 'mollom' ) );
					$private_key->add_validator( 'validate_required' );
					$private_key->add_validator( array( $this, 'validate_private_key' ) );

					$register= $ui->append( 'static', 'register', '<a href="http://mollom.com/user/register">' . _t( 'Get A New Mollom API Key.', 'mollom' ) . '</a>' );

					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved' ) );

					$ui->out();
					break;
			}
		}
	}

	public function validate_public_key( $key )
	{
		Mollom::setPublicKey( $key );
		return array();
	}
	
	public function validate_private_key( $key )
	{
		Mollom::setPrivateKey( $key );
		try {
			$servers = Mollom::getServerList();
			Options::set( 'mollom__servers', $servers );
			Mollom::setServerList( $servers );
		}
		catch( Exception $e ) {
			EventLog::log( $e->getMessage(), 'notice', 'comment', 'Mollom' );
			return array( _t( 'The mollom server list could not be fetched.', 'mollom' ) );
		}

		try {
			if ( !Mollom::verifyKey() ) {
				return array( sprintf( _t( 'Sorry, the Mollom API key <b>%s</b> is invalid. Please check to make sure the key is entered correctly and is <b>registered for this site (%s)</b>.', 'mollom' ), $key, Site::get_url( 'habari' ) ) );
			}
		}
		catch ( Exception $e ) {
			return array( $e->getMessage() );
		}
		return array();
	}

	public function action_init()
	{
		$this->load_text_domain( 'mollom' );
		
		$this->add_template( 'mollom_fallback_captcha', dirname(__FILE__) . '/templates/mollom_fallback_captcha.php' );
		
		Mollom::setUserAgent( 'habari/' . Version::get_habariversion() );
		
		if ( Options::get( 'mollom__private_key' ) ) {
			Mollom::setPrivateKey( Options::get( 'mollom__private_key' ) );
			Mollom::setPublicKey( Options::get( 'mollom__public_key' ) );
			
			if ( ! $servers = Options::get( 'mollom__servers' ) ) {
				try {
					$servers = Mollom::getServerList();
					Options::set( 'mollom__servers', $servers );
					Mollom::setServerList( $servers );
				}
				catch( Exception $e ) {
					EventLog::log( $e->getMessage(), 'crit', 'comment', 'Mollom' );
				}
			}
			else {
				Mollom::setServerList( $servers );
			}
		}
	}
	
	public function filter_mollom_update_server_list_cron( $result )
	{
		try {
			$servers = Mollom::getServerList();
			Options::set( 'mollom__servers', $servers );
			return true;
		}
		catch( Exception $e ) {
			EventLog::log( $e->getMessage(), 'crit', 'comment', 'Mollom' );
			return fasle;
		}
	}

	public function theme_mollom_stats()
	{
		if ( Cache::has( 'mollom_stats' ) ) {
			$stats= Cache::get( 'mollom_stats' );
		}
		else {
			try {
				$stats= array();
				$stats['total_days']= Mollom::getStatistics( 'total_days' );
				$stats['total_accepted']= Mollom::getStatistics( 'total_accepted' );
				$stats['total_rejected']= Mollom::getStatistics( 'total_rejected' );
				$stats['yesterday_accepted']= Mollom::getStatistics( 'yesterday_accepted' );
				$stats['yesterday_rejected']= Mollom::getStatistics( 'yesterday_rejected' );
				$stats['today_accepted']= Mollom::getStatistics( 'today_accepted' );
				$stats['today_rejected']= Mollom::getStatistics( 'today_rejected' );
				
				if ( ( (int) $stats['total_rejected'] + (int) $stats['total_accepted'] ) > 0 ) {
					$avg= ( (int) $stats['total_rejected'] / ( (int) $stats['total_rejected'] + (int) $stats['total_accepted'] ) );
					$avg= sprintf( '%.2f', $avg * 100 );
				}
				else {
					$avg= 0;
				}
				$stats['avg']= $avg;
				
				Cache::set( 'mollom_stats', $stats );
			}
			catch ( Exception $e ) {
				EventLog::log( $e->getMessage(), 'notice', 'theme', 'Mollom' );
				return array();
			}
		}
		return $stats;
	}

	public function filter_default_rewrite_rules( $rules )
	{
		$rule= array();
		$rule['name']= 'mollom_fallback';
		$rule['parse_regex']= '%^mollom/fallback/?$%i';
		$rule['build_str']= 'mollom/fallback';
		$rule['handler']= 'ActionHandler';
		$rule['action']= 'mollom_fallback';
		$rule['description']= 'Dispatches the fallback mechanism.';

		$rules[]= $rule;

		return $rules;
	}

	public function action_handler_mollom_fallback( $handler_vars )
	{
		$comment= Session::get_set( 'mollom' );
		if ( isset( $comment['comment'] ) ) {
			Plugins::act( 'mollom_fallback', $handler_vars, $comment['comment'] );
		}
		else {
			die( _t( 'Sorry, we could not procces your comment.', 'mollom' ) );
		}
	}

	public function action_mollom_fallback( $handler_vars, $comment )
	{
		if ( !empty( $handler_vars['mollom_captcha'] ) && !empty( $comment ) ) {
			if ( Mollom::checkCaptcha( $comment->info->mollom_session_id, $handler_vars['mollom_captcha'] ) ) {
				$comment->status= 'ham';
				$comment->insert();
				/**
				 * @todo set cookie here.
				 */
				$anchor= '#comment-' . $comment->id;
				Utils::redirect( $comment->post->permalink . $anchor );
				exit;
			}
			else {
				Session::error( _t( 'Sorry, that answer was incorrect. Please try again.', 'mollom' ) );
				$this->send_captcha( $comment );
				exit;
			}
		}
		elseif ( empty( $comment ) ) {
			die( _t( 'Sorry, the gremlins ate your comment...', 'mollom' ) );
		}
		else {
			$this->send_captcha( $comment );
			exit;
		}
	}
	
	/**
	 * @todo use formui
	 */
	private function send_captcha( $comment= null )
	{
		Session::add_to_set( 'mollom', $comment, 'comment' );
		$theme= Themes::create();
		$theme->comment= $comment;
		$theme->captcha= Mollom::getImageCaptcha( $comment->info->mollom_session_id );
		$theme->audio_captcha= Mollom::getAudioCaptcha( $comment->info->mollom_session_id );
		$theme->display( 'mollom_fallback_captcha' );
	}

	public function action_comment_insert_before( $comment )
	{
		if ( $comment->info->mollom_session_id ) {
			return;
		}

		$user= User::identify();

		$author_name= $comment->name;
		$author_url= $comment->url ? $comment->url : null;
		$author_email= $comment->email ? $comment->email : null;
		$author_id= $user instanceof User ? $user->id : null;
		$author_open_id= ( $user instanceof User && $user->info->openid_url ) ? $user->info->openid_url : null;
		$post_body= $comment->content;

		try {
			$result= Mollom::checkContent( null, null, $post_body, $author_name, $author_url, $author_email, $author_open_id, $author_id );
			$comment->info->mollom_session_id= $result['session_id'];
			$comment->info->mollom_quality= $result['quality'];
			switch ( $result['spam'] ) {
				case 'spam':
					$comment->status= 'spam';
					if ( $comment->info->spamcheck ) {
						$comment->info->spamcheck[]=  _t( 'Flagged as Spam by Mollom', 'mollom' );
					}
					else {
						$comment->info->spamcheck= array( _t( 'Flagged as Spam by Mollom', 'mollom' ) );
					}
					break;

				case 'ham':
					// mollom is 100% it is ham, so approve it
					$comment->status= 'ham';
					return;
					break;

				case 'unsure':
				case 'unknown':
					Plugins::act( 'mollom_fallback', Controller::get_handler()->handler_vars, $comment );
					return;
					break;
			}
		}
		catch ( Exception $e ) {
			EventLog::log( $e->getMessage(), 'notice', 'comment', 'Mollom' );
		}
	}

	public function action_admin_moderate_comments( $action, $comments, $handler )
	{
		$false_positives= array();
		$false_negatives= array();

		foreach ( $comments as $comment ) {
			switch ( $action ) {
				case 'spam':
					if ( ( $comment->status == Comment::STATUS_APPROVED || $comment->status == Comment::STATUS_UNAPPROVED )
						&& isset( $comment->info->mollom_session_id ) ) {
						try {
							mollom::sendFeedback( $comment->info->mollom_session_id, 'spam' );
						}
						catch ( Exception $e ) {
							EventLog::log( $e->getMessage(), 'notice', 'comment', 'Mollom' );
						}
					}
					break;
				case 'delete':
					if ( $comment->status != Comment::STATUS_SPAM && isset( $comment->info->mollom_session_id ) ) {
						try {
							mollom::sendFeedback( $comment->info->mollom_session_id, 'unwanted' );
						}
						catch ( Exception $e ) {
							EventLog::log( $e->getMessage(), 'notice', 'comment', 'Mollom' );
						}
					}
					break;
			}
		}
	}
}

?>
