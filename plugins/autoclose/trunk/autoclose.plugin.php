<?php

class autoclose extends Plugin
{

	public function info() {
		return array(
			'name' => 'Autoclose',
			'version' => '0.0.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Automatically close comments on posts older than X days',
			'copyright' => '2008'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::add_daily_cron( 'autoclose_check_posts', array( __CLASS__, 'check_posts' ), 'Check for posts to close comments on' );
			self::check_posts( true );
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			CronTab::delete_cron( 'autoclose_check_posts' );
		}
	}

	public static function check_posts( $nolimit = false ) {
		$would_close= array();
		$age_in_days= Options::get( 'autoclose__age_in_days' );
		if ( is_null( $age_in_days ) ) $age_in_days= 90;
		$age_in_days = abs( intval( $age_in_days ) );
		
		$search= array(
			'before' => HabariDateTime::date_create()->modify('-' . $age_in_days . ' days'),
			'nolimit' => true,
			'status' => 'published',
		);
		if (!$nolimit) {
			$search['after'] = HabariDateTime::date_create()->modify('-' . ($age_in_days + 30). ' days');
		}
		
		$posts= Posts::get( $search );
		foreach ($posts as $post) {
			if (!$post->info->comments_disabled && !$post->info->comments_autoclosed) {
				$would_close[]= sprintf('<a href="%s">%s</a>', $post->permalink, htmlspecialchars($post->title));
				$post->info->comments_disabled= true;
				$post->info->comments_autoclosed= true;
				$post->info->commit();
			}
		}
		Session::notice("Comments autoclosed for " . implode(', ', $would_close));

		return true;
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure', 'autoclose');
		}
		return $actions;
	}
	
	public function action_admin_theme_get_autoclose( $handler, $theme ) {
		self::check_posts( !is_null( $handler->handler_vars['nolimit'] ) );
		Session::messages_out();exit;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure', 'autoclose') :
					$ui = new FormUI( 'autoclose' );

					$age_in_days = $ui->append( 'text', 'age_in_days', 'autoclose__age_in_days', _t('Post age (days) for autoclose', 'autoclose') );
					$age_in_days->add_validator( 'validate_required' );

					$ui->append( 'submit', 'save', _t( 'Save', 'autoclose' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved', 'autoclose' ) );
					$ui->out();
					break;
			}
		}
	}

}

?>
