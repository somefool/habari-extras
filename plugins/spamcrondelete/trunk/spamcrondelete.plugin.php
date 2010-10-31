<?php

class SpamCronDelete extends Plugin
{
	public function action_plugin_activation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			CronTab::add_daily_cron('spamcrondelete', 'cron_delete_spam', 'Deletes spam comments that are old.');
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			CronTab::delete_cronjob('spamcrondelete');
		}
	}
	
	private function delete_old_spam()
	{
		// The inline values are safe and used this way for a reason 
		$comments = Comments::get(array('where' => 'date < ' . strtotime('yesterday') . ' AND {comments}.status = ' . Comment::STATUS_SPAM));
		if($comments->count == 0) {
			$message = _t( 'No old spam to delete.' );
		}
		else {
			$total = $comments->count();
			$comments->delete();
			$message = _t( 'Deleted all %s spam comments.', array($total));
		}
		return $message;
	}

	public function filter_cron_delete_spam($result, $params)
	{
		$message = $this->delete_old_spam();
		EventLog::log($message);
		return true;
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions['delete_old_spam'] = _t( 'Delete Old Spam Now' );
		}

		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case 'delete_old_spam' :
					echo $this->delete_old_spam();
					break;
			}
		}
	}
}

?>