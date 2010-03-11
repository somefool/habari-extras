<?php

class autoclose extends Plugin
{

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::add_daily_cron( 'autoclose_check_posts', array( __CLASS__, 'check_posts' ), 'Check for posts to close comments on' );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::delete_cron( 'autoclose_check_posts' );
		}
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure', 'autoclose' );
			$actions[] = _t( 'Re-open autoclosed', 'autoclose' );
		}
		return $actions;
	}
	
	// debug/test: go to admin/autoclose or admin/autoclose?nolimit to force a run
	public function action_admin_theme_get_autoclose( $handler, $theme ) {
		self::check_posts( !is_null( $handler->handler_vars['nolimit'] ) );
		Session::messages_out();
		exit;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure', 'autoclose' ) :
					$ui = new FormUI( 'autoclose' );

					$age_in_days = $ui->append( 'text', 'age_in_days', 'autoclose__age_in_days', _t('Post age (days) for autoclose', 'autoclose') );
					$age_in_days->add_validator( 'validate_required' );

					$ui->append( 'submit', 'save', _t( 'Save', 'autoclose' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved', 'autoclose' ) );
					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
				case _t( 'Re-Open', 'autoclose' ) :
					$this->reopen_autoclosed();
					Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
					break;
			}
		}
	}
	
	public function updated_config( $ui ) {
		// is this needed?
		$ui->save();
		// close comments on all old posts
		self::check_posts( true );
		
		return false;
	}

	public static function check_posts( $nolimit = false ) {
		$autoclosed = array();
		$age_in_days = Options::get( 'autoclose__age_in_days' );
		if ( is_null( $age_in_days ) ) return;
		$age_in_days = abs( intval( $age_in_days ) );
		
		$search= array(
			'content_type' => 'entry',
			'before' => HabariDateTime::date_create()->modify('-' . $age_in_days . ' days'),
			'nolimit' => true,
			'status' => 'published',
		);
		if (!$nolimit) {
			$search['after'] = HabariDateTime::date_create()->modify('-' . ($age_in_days + 30). ' days');
		}
		
		$posts = Posts::get( $search );
		foreach ( $posts as $post ) {
			if ( !$post->info->comments_disabled && !$post->info->comments_autoclosed ) {
				$post->info->comments_disabled = true;
				$post->info->comments_autoclosed = true;
				$post->info->commit();
				$autoclosed[] = sprintf( '<a href="%s">%s</a>', $post->permalink, htmlspecialchars( $post->title ) );
			}
		}
		if ( count( $autoclosed ) ) {
			if ( count( $autoclosed ) > 5 ) {
				Session::notice( sprintf( _t( 'Comments autoclosed for: %s and %d other posts', 'autoclose' ), implode( ', ', array_slice( $autoclosed, 0, 5 ) ), count( $autoclosed ) - 5 ) );
			}
			else {
				Session::notice( sprintf( _t( 'Comments autoclosed for: %s', 'autoclose' ), implode( ', ', $autoclosed ) ) );
			}
		}
		else {
			Session::notice( sprintf( _t( 'Found no posts older than %d days with comments enabled.', 'autoclose' ), $age_in_days ) );
		}

		return true;
	}
	
	public function reopen_autoclosed() {
		$reopened = array();
		$posts = Posts::get( array(
			'content_type' => 'entry',
			'info' => array(
				'comments_autoclosed' => true,
			),
			'nolimit' => true,
		) );
		foreach ( $posts as $post ) {
			$post->info->comments_disabled = false;
			$post->info->comments_autoclosed = false;
			$post->info->commit();
			$reopened[] = sprintf( '<a href="%s">%s</a>', $post->permalink, htmlspecialchars( $post->title ) );
		}
		Session::notice( sprintf( _t( 'Comments reopened on %d posts', 'autoclose' ), count( $reopened ) ) );
		
		return true;
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Autoclose', 'f63a1f56-0bc8-4b32-88cf-bc73fa51db7d', $this->info->version );
	}
}

?>
