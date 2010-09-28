<?php
/**
 * @file backup and restore SQLite storage snapshots for Habari.
 *
 * As long as this is a development plugin, I've found enough reasons to make
 * use of gzip and other facilities for handling the 'backups'. This is not a
 * backup plugin replacement.
 *
 * @author ilo
**/

/**
 * SQLite snapshot plugin class
 */
class SQLiteSnapshot extends Plugin
{

	/**
	 * Plugin public methods section.
	 */

	/**
	 * Plugin activation action.
	 * Create custom requirements: token,
	 * @param string $file plugin being activated.
	**/
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {

			// Register our own log type.
			EventLog::register_type( 'default', 'snapshot' );

			// Create an ACL token.
			ACL::create_token( 'manage_sqlite_snapshots', _t('Create and restore site snapshots'), 'Administration' );

			// Set a default value for the backup directory.
			$backup_path = Site::get_path('user', true) . 'files' . DIRECTORY_SEPARATOR;
			if ( !is_writable( $backup_path ) || !is_dir( $backup_path ) ) {
				$link = $this->helper_build_admin_link( array( 'configaction' => 'Configure'), _t('configure the plugin'));
				Session::notice( _t( 'Default backup directory is not writable, please %s first.', array( $link ) ) );
			}
			Options::set('sqlite_snapshot__path', $backup_path );

			if ( DB::get_driver_name() != 'sqlite' ) {
				Session::notice( _t( 'Your current database driver is not SQLite, so all options will be disabled.' ) );
			}
		}
	}

	/**
	 * Plugin deactivation action.
	 * Remove any site item, but don't remove site snapshots.
	 * @param string $file plugin being activated.
	**/
	public function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {

			// Remove Dashboard module, and ACL token.
			Modules::remove_by_name( 'Site Snapshots' );
			ACL::destroy_token( 'manage_sqlite_snapshots' );

			// Notify remaining snapshot files are found in the system.
			if ( $snapshots = $this->sqlite_snapshot_get() ) {
				Session::notice( _t( 'There are snapshots left in the backup folder.' ) );
			}

			// Remove variables created by this plugin.
			Options::delete('sqlite_snapshot__path');
		}
	}

	/**
	 * Plugin user interface action.
	 * Reacts on user seletected option to show the form or take the snapshot.
	 * @param string $plugin_id Id of plugin that fired the action.
	 * @param string $action action fired.
	 * @return FormUI for the required action.
	**/
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ) :
					// Show the main configuration form.
					$ui = $this->form_ui_build_configuration();
					$ui->out();
					break;
				case _t( 'Snapshot' ) :
					// Take the snapshot of current db file, and redirect to restore.
					$this->sqlite_snapshot_take();
					Utils::redirect( URL::get('admin', array('page' => 'plugins', 'configure' => $this->plugin_id, 'configaction' =>'Restore') ));
					break;
				case _t( 'Restore' ) :
					$ui = $this->form_ui_build_restore();
					$ui->out();
					break;
			}
		}
	}

	/**
	 * Plugin configuration filter.
	 * Declare the plugin configuration entries.
	 * @param array $actions array of actions available.
	 * @param string $plugin_id Id of plugin to put configuration actions.
	**/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		// Taking and restore snapshots are only available to permissionsed users.
		if ( ( $plugin_id == $this->plugin_id() ) && ( User::identify()->can( 'manage_sqlite_snapshots' ) ) && ( DB::get_driver_name() == 'sqlite' ) ) {
			$actions = array (
				_t( 'Configure'),
				_t( 'Snapshot' ),
				_t( 'Restore' ),
			);
		}
		return $actions;
	}

	/**
	 * Declare dashboard widgets of this plugin.
	 * @param array $modules list of modules that have dashboard widgets.
	 * @return array including the module controled widgets.
	**/
	public function filter_dash_modules( $modules )
	{
		if ( User::identify()->can( 'manage_sqlite_snapshots' ) ) {
			// If user has permissions, let him show the snapshots dashboard widget.
			array_push( $modules, 'Site Snapshots' );
		}
		return $modules;
	}

	/**
	 * Generate snapshot dashboard widget.
	 * @param array $module associative array with information for the widget.
	 * @param integer $module_id widget id.
	 * @param theme $theme Theme object.
	 * @return array associative array with the module information: title, content..
	**/
	public function filter_dash_module_site_snapshots( $module, $module_id, $theme )
	{
		if ( ! User::identify()->can( 'manage_sqlite_snapshots' ) ) {
			// this user doesn't have access to snapshots.
			return $module;
		}
		return $this->form_ui_dashboard_snapshots($module, $module_id, $theme);
	}




	/**
	 * Forms section
	 */

	/**
	 * Builds the configuration form.
	 * @return FormUI
	 */
	private function form_ui_build_configuration()
	{
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$ui->append( 'text', 'path', 'option:sqlite_snapshot__path', _t('Path to backup directory: ') );
		$ui->append( 'submit', 'save', _t('Save' ) );
		$ui->path->add_validator( array( $this, 'form_ui_configuration_path_validator' ) );
		return $ui;
	}

	/**
	 * Verify that $path is a folder and is writable.
	 * @param mixed $path value of the field being validated.
	 * @param FormControl $control control being validated.
	 * @param FormUI $ui submitted configuration form.
	 * @return array validation errors to be shown.
	**/
	public function form_ui_configuration_path_validator( $path, $control, $ui )
	{
		$errors = array();
		if ( !is_writable( $path ) || !is_dir( $path ) ) {
			$errors[] = _t( 'Selected directory is not writable' );
		}
		if ( ! in_array( substr( $path, -1 ), array( "/", "\\") ) ) {
			$errors[] = _t( 'Backup directory must finish with a slash' );
		}
		return $errors;
	}

	/**
	 * Builds the snapshot restore form.
	 * @return FormUI
	 */
	private function form_ui_build_restore()
	{
		// Build a list of snapshots.
		$ui = new FormUI( strtolower( get_class( $this ) ) );

		// Only keep building the UI if there are snapshots.
		if ( $snapshots = $this->sqlite_snapshot_get() ) {

			// Format the date and time according to user preferences.
			$user = User::identify();
			$format = $user->info->locale_date_format . " " . $user->info->locale_time_format;

			// Build the select options.
			$options = array();
			foreach ( $snapshots as $id => $snapshot) {
				$options[$id] = date($format, $id);
			}
			$ui->append( 'select', 'snapshots', 'null:null', _t('Select snapshot to restore'));
			$ui->snapshots->options = $options;

			// Set a default value (passed in the url by dashboard widget).
			$ui->snapshots->value = isset($_GET['snapshot']) ? intval ( $_GET['snapshot'] ) : 0 ;

			// Restoring the session table would log out current session, set a way
			// to keep it working.
			$ui->append( 'checkbox', 'session_keep', 'null:null', _t( 'Keep current session active' ) );
			$ui->append( 'submit', 'restore', _t('Restore' ) );
			$ui->on_success( array( $this, 'form_ui_configuration_restore_success' ) );
		}
		else {
			//No snapshosts found, append a link to encourage a snapshot creation.
			$link = $this->helper_build_admin_link( array( 'configaction' => 'Snapshot'), _t( 'First snapshot' ));
			$ui->append( 'label', 'info', _t('Create the %s now!', array( $link ) ) );
		}
		return $ui;
	}

	/**
	 * Callback function for the restore configuration form. Try to restore from
	 * the selected snapshot in the form, and keep current session active if
	 * enabled.
	 * @param FormUI $ui
	 * @return FormUI
	 */
	public function form_ui_configuration_restore_success( $ui ) {
		$selected = $ui->snapshots->value;
		// Get all the snapshots
		$snapshots = $this->sqlite_snapshot_get();

		// and now keep session active by inserting into the database.
		if ( $ui->session_keep->value == true ) {
			$data = Session::read(session_id());
		}

		// Call the restore function
		$this->sqlite_snapshot_restore( $snapshots[$selected] );

		// and now keep session active by inserting into the database.
		if ( $ui->session_keep->value == true ) {
			Session::write(session_id(), $data);
		}

		return $ui->out;
	}

	/**
	 * Build the content of the Site Snapshots widget.
	 * @param array $module associative array with information for the widget.
	 * @param integer $module_id widget id.
	 * @param theme $theme Theme object.
	 * @return array associative array with the module information: title, content.
	 */

	private function form_ui_dashboard_snapshots($module, $module_id, $theme) {

		// Build dashboard content
		$content = '<ul class="items">';
		if ( $files = $this->sqlite_snapshot_get() ) {

			// Format the date and time according to user preferences.
			$info = new UserInfo(User::identify()->id);
			$format = $info->locale_date_format . " " . $info->locale_time_format;

			// Loop up to eight files.
			$count = 0;
			foreach ( $files as $id => $snapshot ) {
				$content .= '<li class="item clear"><span class="date pct25 minor">' . date( $format, $id ) . '</span>';
				$content .= '<span class="message pct60 minor">' . $snapshot . '</span>';
				$content .= '<span class="comments pct15">';
				$content .= $this->helper_build_admin_link( array( 'snapshot' => $id ), _t( 'Restore' ), _t( 'Restore the system using this snapshot.' ) );
				$content .= '</span></li>';
				if ( $count++  > 6 ) break;
			}
		}
		else {
			// Build a link to inform that there are no snapshots available for this site.
			$content .= '<li class="item clear"><span class="message pct85 minor">';
			$settings = array (
			 'page'         => 'plugins',
			 'configure'    => $this->plugin_id(),
			 'configaction' => 'Snapshot',
			 'snapshot'     => time(),
			);
			$content .= sprintf( _t( "No backups found. Make a snapshot %s " ),  $this->helper_build_admin_link( $settings, _t( 'Now' ) ) );
			$content .= '</span></li>';
		}
		$content .= '</ul>';

		// Set widget information.
		$module['title']   = _t( 'Latest Snapshots' );
		$module['content'] = $content;

		return $module;
	}




	/**
	 * API functions
	 */

	/**
	 * Find snapshot(s) file(s) in the configured directory. Only return those
	 * with the same database name that current site.
	 * @param string $id of the snapshot to find, it is a timestamp value to string.
	 * @Return an array in the form of [timestamp] => filename with the available
	 * snapshots, empty array if none found.
	 */
	private function sqlite_snapshot_get( $id = null) {
		$snapshots = array();

		// find only snapshots of this site. Multiple database files from other
		// sites can be in this directory.
		$db_name = $this->helper_get_sqlite_db_name();
		$files = glob( Options::get( 'sqlite_snapshot__path' ) . $db_name . '-*.db' );
		if ( count( $files ) ) {
			// Files are in dbname-timestamp.db form.
			rsort( $files );
			// create the return array
			foreach ( $files as $snapshot) {
				$tstamp = substr($snapshot, -13, 10);
				$snapshots[$tstamp] = $snapshot;
			}
			if ( isset( $id ) && isset( $snapshots[$id] ) ){
				return array( $id => $snapshots[$id]);
			}
		}
		return $snapshots;
	}

	/**
	 * Creates a snapshot of current sqlite database.
	 * @return boolean true or false if snapshot has been created successfuly.
	**/
	private function sqlite_snapshot_take()
	{
		// Get current database name.
		if ( ! $db_name = $this->helper_get_sqlite_db_name() ) {
			return false;
		}
		// Build paths
		$db_file = Site::get_path( 'user', TRUE ) . $db_name;
		$outfile = Options::get('sqlite_snapshot__path') . basename( $db_name . '-' . time() . '.db' );

		// Verify that final file does not exist, just in case.
		if ( file_exists( $outfile ) || is_dir($outfile) ) {
			Session::error( _t( 'Can not create backup file: a file or a directory with that name exist, try again.' ) );
			return false;
		}

		// let's optimize the DB file before backing it up. It is a devel module
		// so don't expect locking database or other things.
		DB::query( 'VACUUM' );

		if ( $of = fopen( $outfile, 'wb9' ) ) {
			if ( $if = fopen( $db_file, 'rb' ) ) {
				while ( ! feof( $if ) ) {
					fwrite( $of, fread( $if, 1024*512 ) );
				}
				fclose( $if );
				fclose( $of );
				Session::notice( _t( 'Snapshot created' ));
				return true;
			} else {
				Session::error( _t( 'Can not open current database for backup.' ) );
			}
			fclose( $of );
		} else {
			Session::error( _t( 'Can not create backup file.' ) );
		}

		return false;
	}

	/**
	 * Restores a snapshot of an sqlite database.
	 * @param string $infile file path to be restored.
	 * @return boolean true or false if snapshot has been created successfuly.
	**/
	private function sqlite_snapshot_restore( $infile )
	{
		// Get current database name.
		if ( ! $db_name = $this->helper_get_sqlite_db_name() ) {
			return false;
		}

		// Build paths
		$db_file = Site::get_path( 'user', TRUE ) . $db_name;

		// Verify that final file does not exist, just in case.
		if ( !file_exists( $infile ) ) {
			Session::error( _t( 'Can not restore from this snapshot file: the file does not exist.' ) );
			return false;
		}

		if ( $of = fopen( $db_file, 'wb9' ) ) {
			if ( $if = fopen( $infile, 'rb' ) ) {
				while ( ! feof( $if ) ) {
					fwrite( $of, fread( $if, 1024*512 ) );
				}
				fclose( $if );
				fclose( $of );
				Session::notice( _t( 'Snapshot restored' ));
				return true;
			} else {
				Session::error( _t( 'Can not open snapshot file.' ) );
			}
			fclose( $of );
		} else {
			Session::error( _t( 'Can not write to current database.' ) );
		}

		return false;
	}


	/**
	 *  Helper functions.
	 */

	/**
	 * Returns current sqlite database file path.
	 * @return string path to current database name.
	 */
	private function helper_get_sqlite_db_name()
	{
		// Get database name, if we get here there is a connection string.
		list( $type, $db_name )= explode( ':', Config::get( 'db_connection' )->connection_string );
		if( basename( $db_name ) == $db_name && !file_exists( './' . $db_name ) ) {
			return $db_name;
		}
		Session::error( _t( 'Unable to find current database. Interesting.. please report this in the tracker' ) );
		return false;
	}

	/**
	 * Simple function to build a link.
	 * @param array $settings settings for the link.
	 * @param string $text Link readable text.
	 * @param string $title the Alternative title.
	 * @return string built html code of the link.
	**/
	private function helper_build_admin_link( $settings = array(), $text, $title = '' ) {
		 $settings += array(
			 'page'         => 'plugins',
			 'configure'    => $this->plugin_id(),
			 'configaction' => 'Restore',
			 'snapshot'     => 0,
		 );
		 return '<a  href="' . URL::get('admin', $settings, true) . '" title="' . $title . '">' . $text . '</a>';
	}

}

?>
