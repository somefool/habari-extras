<?php

	class Export extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export', '41f7ab69-dddd-4308-b31e-92f3d1270123', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				ACL::create_token( 'export now', 'Export the Habari database.', 'export' );
				
				// save the default options
				//Options::set( 'export__path', '' );
				Options::set( 'export__frequency', 'manually' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::destroy_token( 'export now' );
				
				// wipe out the default options we added
				//Options::delete( 'export__path' );
				Options::delete( 'export__frequency' );
				
			}
			
		}
		
		public function filter_plugin_config ( $actions, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$actions[] = _t('Configure');
				
				// only users with the proper permission should be allowed to export
				if ( User::identify()->can('export now') ) {
					$actions[] = _t('Export');
				}
				
			}
			
			return $actions;
			
		}
		
		public function action_plugin_ui ( $plugin_id, $action ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$frequencies = array(
					'manually' => _t('Manually'),
					'hourly' => _t('Hourly'),
					'daily' => _t('Daily'),
					'weekly' => _t('Weekly'),
					'monthly' => _t('Monthly'),
				);
				
				switch ( $action ) {
					
					case _t('Configure'):
						
						$ui = new FormUI( 'export' );
						//$ui->append( 'text', 'export_path', 'option:export__path', _t('Export path:'));
						//$ui->export_path->add_validator( 'validate_required' );
						
						$ui->append( 'select', 'export_freq', 'option:export__frequency', _t('Auto Export frequency:'), $frequencies );
						
						$ui->append( 'submit', 'save', _t( 'Save' ) );
						$ui->on_success( array( $this, 'updated_config' ) );
						
						$ui->out();
						
						break;
						
					case _t('Export'):
						
						$this->export();
						
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
					
				}
				
			}
			
		}
		
		public function updated_config ( $ui ) {
			
			$ui->save();
			
			// if they selected an option other than manually, set up the cron
			$frequency = Options::get('export__frequency');
			
			// delete the crontab entry, if there is one
			CronTab::delete_cronjob('export');
			
			switch ( $frequency ) {
				
				case 'manually':
					// do nothing
					break;
					
				case 'hourly':
					CronTab::add_hourly_cron('export', array( $this, 'export' ));
					break;
					
				case 'daily':
					CronTab::add_daily_cron('export', array( $this, 'export' ));
					break;
					
				case 'weekly':
					CronTab::add_weekly_cron('export', array( $this, 'export' ));
					break;
					
				case 'monthly':
					CronTab::add_monthly_cron('export', array( $this, 'export' ));
					break;
				
			}
			
			return false;
			
		}
		
		public function export ( ) {
			
			
			
		}
		
	}

?>