<?php

	class DatabaseOptimizer extends Plugin
	{
		public function action_plugin_activation ( $file ='' )
		{
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				if ( Options::get( 'database_optimizer__frequency' ) == null ) {
					Options::set( 'database_optimizer__frequency', 'weekly' );
				}

				// add a cronjob to kick off next and optimize our db now
				CronTab::add_single_cron( 'optimize database tables initial', 'optimize_database', HabariDateTime::date_create( time() ), 'Optimizes database tables.' );
				$this->create_cron();
			}
		}
		
		public function create_cron ( )
		{
				// delete the existing cronjob
				CronTab::delete_cronjob( 'optimize database tables' );

				$frequency = Options::get( 'database_optimizer__frequency' );
				$function_name = 'add_' . $frequency . '_cron';

				call_user_func_array( array( 'CronTab', $function_name ), array(
					'optimize database tables',
					'optimize_database',
					'Optimizes database tables automagically ' . $frequency
				) );
				EventLog::log( 'CronTab added to optimize database tables ' . $frequency . '.' );
		}
		
		public function action_plugin_deactivation ( $file = '' )
		{
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				CronTab::delete_cronjob( 'optimize database tables initial' );
				CronTab::delete_cronjob( 'optimize database tables' );

				Options::delete( 'database_optimizer__frequency' );
			}
		}
		
		public function filter_optimize_database ( $result, $paramarray )
		{
			$space_saved = 0;
			$tables = 0;

			switch ( DB::get_driver_name() ) {
				case 'mysql':
					$q = 'SHOW TABLE STATUS WHERE data_free > 0';
					$tables = DB::get_results( $q );
					if ( count( $tables ) > 0 ) {
						foreach ( $tables as $table ) {
							$q2 = 'OPTIMIZE TABLE ' . $table->Name;
							if ( DB::query( $q2 ) ) {
								$space_saved += $table->Data_free;
								$tables++;
							}
						}
						EventLog::log( 'Database Tables Optimized. ' . Utils::human_size( $space_saved ) . ' reclaimed from ' . HabariLocale::_n( 'table', 'tables', $tables ) . '.' );
					}
					$result = true;
					break;
				case 'sqlite':
					if ( DB::exec( 'VACUUM' ) ) {
						$result = true;
						EventLog::log( 'SQLite database VACUUM\'ed successfully.' );
					}
					else {
						$result = false;
					}
					break;
				default:
					$result = false;
					break;
			}

			return $result;

		}

		public function filter_plugin_config ( $actions, $plugin_id )
		{
			if ( $plugin_id == $this->plugin_id() ) {
				$actions['optimize'] = _t( 'Optimize' );
			}
			return $actions;
		}

		public function configure()
		{
			$class_name = strtolower( get_class( $this ) );

			$form = new FormUI( $class_name );
			$form->append( 'select', 'frequency', 'database_optimizer__frequency', _t( 'Optimization Frequency' ), array( 'hourly' => 'hourly', 'daily' => 'daily', 'weekly' => 'weekly', 'monthly' => 'monthly' ) );
			$form->append( 'submit', 'save', _t( 'Save' ) );

			$form->on_success( array( $this, 'updated_config' ) );
			$form->out();
		}

		public function action_plugin_ui_optimize ( $plugin_id, $action )
		{
			$result = $this->filter_optimize_database( true, array() );
			if ( $result ) {
				echo _t( 'Database Optimized successfully!' );
			}
			else {
				echo _t( 'There was an error, or your database platform is not supported!' );
			}
		}

		public function updated_config ( $form )
		{
			$form->save();

			// create our cronjob
			$this->create_cron();
		}

	}
?>
