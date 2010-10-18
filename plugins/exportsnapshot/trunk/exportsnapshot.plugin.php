<?php

	class ExportSnapshot extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export Snapshot', 'c73d0e0f-5e8c-4eb6-b360-920440b512ce', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				ACL::create_token( 'snapshot', 'Manage Database Snapshots', 'Export', true );
				
				// save the default options
				Options::set( 'exportsnapshot__frequency', 'manually' );
				
				// add the module
				Modules::add( _t('Snapshots') );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::destroy_token( 'snapshot' );
				
				// wipe out the default options we added
				Options::delete( 'exportsnapshot__frequency' );
				
				// remove the module
				Modules::remove_by_name( _t('Snapshots') );
				
				// @todo what about the snapshots option and deleting those cached items?
				// probably an uninstall method too?
				
			}
			
		}
		
		public function filter_dash_modules ( $modules ) {
			
			if ( User::identify()->can( 'snapshot', 'read' ) ) {
				
				$modules[] = _t('Snapshots');
				
				$this->add_template( 'dash_snapshots', dirname( __FILE__ ) . '/dash_snapshots.php' );
				
			}
			
			return $modules;
			
		}
		
		public function filter_dash_module_snapshots ( $module, $module_id, $theme ) {
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			$s = array();
			foreach ( $snapshots as $ts => $size ) {
				$t = new stdClass();
				$t->date = HabariDateTime::date_create( $ts );
				$t->size = $size;
				
				$s[] = $t;
			}
			
			$theme->snapshots = $s;
			
			$module['title'] = _t('Snapshots');
			$module['content'] = $theme->fetch('dash_snapshots');
			
			return $module;
			
		}
		
		public function filter_plugin_config ( $actions, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$actions[] = _t('Configure');
				
				if ( User::identify()->can( 'snapshot', 'create' ) ) {
					$actions[] = _t('Take Snapshot');
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
						$ui->append( 'text', 'exportsnapshot_max_snapshots', 'option:exportsnapshot__max_snapshots', _t('Max Snapshots to Save:'));
						
						$ui->append( 'select', 'exportsnapshot_freq', 'option:exportsnapshot__frequency', _t('Auto Snapshot frequency:'), $frequencies );
						
						$ui->append( 'submit', 'save', _t( 'Save' ) );
						$ui->on_success( array( $this, 'updated_config' ) );
						
						$ui->out();
						
						break;
						
					case _t('Take Snapshot'):
						
						self::run();
						Session::notice( _t('Snapshot saved!') );
						
						//CronTab::add_single_cron('snapshot_single', array( 'ExportSnapshot', 'run' ), HabariDateTime::date_create(), 'Run a single snapshot.' );
						//Session::notice( _t( 'Snapshot scheduled for next cron run.' ) );
						
						// don't display the configuration page, just redirect back to the plugin page
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
					
				}
				
			}
			
		}
		
		public function updated_config ( $ui ) {
			
			$ui->save();
			
			// if they selected an option other than manually, set up the cron
			$frequency = Options::get('exportsnapshot__frequency');
			
			// delete the crontab entry, if there is one
			CronTab::delete_cronjob('snapshot');
			
			switch ( $frequency ) {
				
				case 'manually':
					// do nothing
					break;
					
				case 'hourly':
					CronTab::add_hourly_cron('snapshot', array( 'ExportSnapshot', 'run' ));
					break;
					
				case 'daily':
					CronTab::add_daily_cron('snapshot', array( 'ExportSnapshot', 'run' ));
					break;
					
				case 'weekly':
					CronTab::add_weekly_cron('snapshot', array( 'ExportSnapshot', 'run' ));
					break;
					
				case 'monthly':
					CronTab::add_monthly_cron('snapshot', array( 'ExportSnapshot', 'run' ));
					break;
				
			}
			
			return false;
			
		}
		
		private static function test_cache ( ) {
			
			// test the cache
			$cache = Cache::set('export_test', 'test');
			
			if ( $cache == null ) {
				// we can't export!
				EventLog::log( _t( 'Unable to write to the cache, export failed!' ), 'critical', 'export', 'export' );
				
				// if a user is running manually, also give them a session error notice
				if ( User::identify() ) {
					Session::error( _t( 'Unable to write to the cache, export failed!' ) );
				}
				
				return false;
			}
			else {
				
				Cache::expire( 'export_test' );
				return true;
				
			}
			
		}
		
		public static function run ( ) {
			
			Plugins::act('exportsnapshot_run_before');
			
			// if we can't save the file, throw an error and bail
			if ( !self::test_cache() ) {
				return false;
			}
			
			$export = new Export();
			$xml = $export->run();
			
			EventLog::log( _t( 'Export Snapshot completed!' ), 'info', 'export', 'snapshot' );
			
			Plugins::act('exportsnapshot_run_after');
			
			$xml = Plugins::filter('exportsnapshot_contents', $xml);
			
			// save the snapshot
			$save_result = self::save( $xml );
			
			// cleanup old snapshots
			$clean_result = self::cleanup();
			
			if ( $save_result && $clean_result ) {
				return true;
			}
			else {
				return false;
			}
			
		}
		
		private static function save ( $xml ) {
			
			$timestamp = HabariDateTime::date_create('now')->format('YmdHis');
			
			$result = Cache::set( 'exportsnapshot__' . $timestamp, $xml, 0, true );	// 0s expiration, but keep it forever
			
			if ( $result ) {
			
				$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
				$snapshots[ $timestamp ] = mb_strlen( $xml, '8bit' );
			
				Options::set( 'exportsnapshot__snapshots', $snapshots );
				
				return true;
				
			}
			else {
				return false;
			}
			
		}
		
		private static function cleanup ( ) {
			
			return true;
			
			// the limit on the number of snapshots to retain
			$max_snapshots = Options::get( 'exportsnapshot__max_snapshots' );
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			// they should be in timestamp order, but make sure
			ksort( $snapshots );
			
			if ( $max_snapshots != null ) {
				
				// get the oldest snapshots - we need to dump these
				$old = array_slice( $snapshots, 0, $max_snapshots, true );
				
				// get the newest snapshots - these are the ones we'll save
				$snapshots = array_slice( $snapshots, $max_snapshots, null, true );
				
				// if we've got things to clean up
				if ( !empty( $old ) ) {
					
					foreach ( $old as $ts => $size ) {
						// expire the entries we don't want to keep any longer
						Cache::expire( 'exportsnapshot__' . $ts );
					}
					
				}
				
			}
			
			// save it again
			Options::set( 'exportsnapshot__snapshots', $snapshots );
			
			return true;
			
		}
		
	}

?>