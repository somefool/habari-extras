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
				
				$modules[] = _t('Latest Snapshots');
				
				$this->add_template( 'dash_snapshots', dirname( __FILE__ ) . '/dash_snapshots.php' );
				
			}
			
			return $modules;
			
		}
		
		public function filter_dash_module_latest_snapshots ( $module, $module_id, $theme ) {
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			// reverse sort by key (ie: newest timestamp first)
			krsort( $snapshots );
			
			// a max of 8, or the number we have
			$count = ( count( $snapshots ) > 8 ) ? 8 : count( $snapshots );
			
			$snapshots = array_slice( $snapshots, 0, $count, true );
			
			$s = array();
			foreach ( $snapshots as $ts => $snapshot ) {
				$t = new stdClass();
				$t->date = HabariDateTime::date_create( $ts );
				$t->size = $snapshot['size'];
				$t->type = $snapshot['type'];
				
				$s[] = $t;
			}
			
			$theme->snapshots = $s;
			
			$module['title'] = _t('Latest Snapshots');
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
						
						self::run( 'manual' );
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
					CronTab::add_hourly_cron('snapshot', array( 'ExportSnapshot', 'run' ), _t('Hourly Export Snapshot'));
					break;
					
				case 'daily':
					CronTab::add_daily_cron('snapshot', array( 'ExportSnapshot', 'run' ), _t('Daily Export Snapshot'));
					break;
					
				case 'weekly':
					CronTab::add_weekly_cron('snapshot', array( 'ExportSnapshot', 'run' ), _t('Weekly Export Snapshot'));
					break;
					
				case 'monthly':
					CronTab::add_monthly_cron('snapshot', array( 'ExportSnapshot', 'run' ), _t('Monthly Export Snapshot'));
					break;
				
			}
			
			return false;
			
		}
		
		private static function test_cache ( ) {
			
			// test the cache
			$cache = Cache::set('export_test', 'test');
			
			if ( $cache == null ) {
				// we can't export!
				EventLog::log( _t( 'Unable to write to the cache, export failed!' ), 'critical', 'cache', 'ExportSnapshot' );
				
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
		
		public static function run ( $type = 'cron' ) {
			
			Plugins::act('exportsnapshot_run_before');
			
			// if we can't save the file, throw an error and bail
			if ( !self::test_cache() ) {
				return false;
			}
			
			$export = new Export();
			$xml = $export->run();
			
			EventLog::log( _t( 'Export Snapshot completed!' ), 'info', 'snapshot', 'ExportSnapshot', $type );
			
			Plugins::act('exportsnapshot_run_after');
			
			$xml = Plugins::filter('exportsnapshot_contents', $xml);
			
			// save the snapshot
			$save_result = self::save( $xml, $type );
			
			// cleanup old snapshots
			$clean_result = self::cleanup();
			
			if ( $save_result && $clean_result ) {
				return true;
			}
			else {
				return false;
			}
			
		}
		
		private static function save ( $xml, $type ) {
			
			$timestamp = HabariDateTime::date_create('now');
			
			$result = Cache::set( 'exportsnapshot__' . $timestamp->int, $xml, 0, true );	// 0s expiration, but keep it forever
			
			if ( $result ) {
			
				$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
				$snapshots[ $timestamp->int ] = array(
					'size' => MultiByte::strlen( $xml ),
					'type' => $type,
					'ts' => $timestamp->int,
				);
			
				Options::set( 'exportsnapshot__snapshots', $snapshots );
				
				return true;
				
			}
			else {
				return false;
			}
			
		}
		
		private static function cleanup ( ) {
						
			// the limit on the number of snapshots to retain
			$max_snapshots = Options::get( 'exportsnapshot__max_snapshots' );
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			// they should be in timestamp order, but make sure
			ksort( $snapshots );
			
			if ( $max_snapshots !== null && count( $snapshots ) > $max_snapshots ) {
				
				// get the oldest snapshots - we need to dump these
				$old = array_slice( $snapshots, 0, count( $snapshots ) - $max_snapshots, true );
				
				// if we've got things to clean up
				if ( !empty( $old ) ) {
					
					foreach ( $old as $ts => $snapshot ) {
						// expire the entries we don't want to keep any longer
						Cache::expire( 'exportsnapshot__' . $ts );
						
						// unset the snapshot directory entry
						unset( $snapshots[ $ts ] );
					}
					
					 EventLog::log( _t( 'Purged %d exports.', array( count( $old ) ) ), 'debug', 'cleanup', 'ExportSnapshot' );
					
				}
				
			}
			
			// save it again
			Options::set( 'exportsnapshot__snapshots', $snapshots );
			
			return true;
			
		}
		
		public function filter_rewrite_rules ( $rules ) {
			
			$rule = new RewriteRule( array(
				'name' => 'snapshot_download',
				'parse_regex' => '#^admin/snapshot/download/(?P<ts>[0-9.]+)/?$#i',
				'build_str' => 'admin/snapshot/download/{$ts}',
				'handler' => 'adminhandler',
				'action' => 'snapshot_download',
				'priority' => 1,
				'is_active' => 1,
				'rule_class' => RewriteRule::RULE_PLUGIN,
				'description' => 'Download an Export Snapshot',
			) );

			$rules['snapshot_download'] = $rule;
			
			$rule = new RewriteRule( array(
				'name' => 'snapshot_delete',
				'parse_regex' => '#^admin/snapshot/delete/(?P<ts>[0-9.]+)/?$#i',
				'build_str' => 'admin/snapshot/delete/{$ts}',
				'handler' => 'adminhandler',
				'action' => 'snapshot_delete',
				'priority' => 1,
				'is_active' => 1,
				'rule_class' => RewriteRule::RULE_PLUGIN,
				'description' => 'Delete an Export Snapshot',
			) );
			
			$rules['snapshot_delete'] = $rule;
			
			return $rules;
			
		}
		
		public function action_handler_snapshot_download ( $handler_vars ) {
			
			if ( !User::identify()->can( 'snapshot', 'read' ) ) {
				Utils::redirect( URL::get( 'admin', array( 'page' => 'unauthorized' ) ) );
			}
			
			$timestamp = Controller::get_var( 'ts' );
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			if ( !isset( $snapshots[ $timestamp ] ) ) {
				die('Unknown snapshot!');
			}
			
			// fetch the snapshot from the cache
			$snapshot = Cache::get( 'exportsnapshot__' . $timestamp );
			
			if ( $snapshot ) {
				$this->download( $snapshot );
			}
			
		}
		
		public function action_handler_snapshot_delete ( $handler_vars ) {
			
			if ( !User::identify()->can( 'snapshot', 'delete' ) ) {
				Utils::redirect( URL::get( 'admin', array( 'page' => 'unauthorized' ) ) );
			}
			
			$timestamp = Controller::get_var( 'ts' );
			
			$snapshots = Options::get( 'exportsnapshot__snapshots', array() );
			
			if ( !isset( $snapshots[ $timestamp ] ) ) {
				die('Unknown snapshot!');
			}
			
			// expire the snapshot in the cache
			Cache::expire( 'exportsnapshot__' . $timestamp );
			
			// remove it from the list
			unset( $snapshots[ $timestamp ] );
			
			// write a log event
			EventLog::log( _t( 'Export Snapshot deleted!' ), 'info', 'delete', 'ExportSnapshot' );
			
			// save the list
			Options::set( 'exportsnapshot__snapshots', $snapshots );
			
			// and redirect back to the dashboard
			Utils::redirect( URL::get( 'admin' ) );
			
		}
		
		private function download ( $xml ) {
			
			$timestamp = HabariDateTime::date_create('now')->format('YmdHis');
			
			$filename = 'habari_' . $timestamp . '.xml';
			
			// clear out anything that may have been output before us and disable the buffer
			ob_end_clean();
			
			header('Content-Type: text/xml');
			header('Content-disposition: attachment; filename=' . $filename);
			
			echo $xml;
			
			die();
			
		}
		
	}

?>