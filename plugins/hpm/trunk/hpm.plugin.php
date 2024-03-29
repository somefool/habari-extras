<?php

class HPM extends Plugin
{
	const VERSION = '0.2';
	const DB_VERSION = 001;

	public function action_init()
	{
	}

	public function action_update_check()
  	{
    	Update::add( 'hpm', '693E59D6-2B5F-11DD-A23A-9E6C56D89593',  $this->info->version );
  	}

	/**
	 * @todo fix this schema!!!!
	 */
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			DB::register_table('packages');

			switch( DB::get_driver_name() ) {
				case 'sqlite':
					$schema = 'CREATE TABLE ' . DB::table('packages') . ' (
						id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
						name VARCHAR(255) NOT NULL,
						guid VARCHAR(255) NOT NULL,
						version VARCHAR(255) NOT NULL,
						description TEXT ,
						author VARCHAR(255) ,
						author_url VARCHAR(255) ,
						habari_version VARCHAR(255) ,
						archive_md5 VARCHAR(255) ,
						archive_url VARCHAR(255) ,
						type VARCHAR(255) ,
						status VARCHAR(255) ,
						requires VARCHAR(255) ,
						provides VARCHAR(255) ,
						recomends VARCHAR(255) ,
						tags TEXT ,
						install_profile LONGTEXT
					);';
				break;

				default:
				case 'mysql':
					$schema = 'CREATE TABLE ' . DB::table('packages') . ' (
						id INT UNSIGNED NOT NULL AUTO_INCREMENT,
						name VARCHAR(255) NOT NULL,
						guid VARCHAR(255) NOT NULL,
						version VARCHAR(255) NOT NULL,
						description TEXT ,
						author VARCHAR(255) ,
						author_url VARCHAR(255) ,
						habari_version VARCHAR(255) ,
						archive_md5 VARCHAR(255) ,
						archive_url VARCHAR(255) ,
						type VARCHAR(255) ,
						status VARCHAR(255) ,
						requires VARCHAR(255) ,
						provides VARCHAR(255) ,
						recomends VARCHAR(255) ,
						tags TEXT ,
						install_profile LONGTEXT,
						PRIMARY KEY (id)
					)
					CHARACTER SET ' . MYSQL_CHAR_SET . ' ;';
				break;
			}
			if ( DB::dbdelta( $schema ) ) {
				Session::notice( 'Updated the HPM database tables.' );
			}
			Options::set( 'hpm__last_update', 1 );
			Options::set( 'hpm__repos', 'http://habariproject.org/en/packages' );

			# create default access tokens for: 'system', 'plugin', 'theme', 'class'
			ACL::create_token( 'install_new_system', _t('Install System Updates', 'hpm'), 'hpm', false );
			ACL::create_token( 'install_new_plugin', _t('Install New Plugins', 'hpm'), 'hpm', false );
			ACL::create_token( 'install_new_theme', _t('Install New Themes', 'hpm'), 'hpm', false );
			ACL::create_token( 'install_new_class', _t('Install New Classes', 'hpm'), 'hpm', false );
		}
	}
	public function action_plugin_deactivation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			DB::register_table('packages');
			DB::register_table('package_repos');

			DB::query( 'DROP TABLE IF EXISTS {packages} ' );
			DB::query( 'DROP TABLE IF EXISTS {package_repos} ' );

			# delete default access tokens for: 'system', 'plugin', 'theme', 'class'
			ACL::destroy_token( 'install_new_system' );
			ACL::destroy_token( 'install_new_plugin' );
			ACL::destroy_token( 'install_new_theme' );
			ACL::destroy_token( 'install_new_class' );
		}
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Add Sources', 'hpm');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Add Sources', 'hpm') :
					$ui = new FormUI( 'hpm' );

					$api_key = $ui->append( 'textarea', 'repos', 'option:hpm__repos', _t('HPM Repositories (comma seperated): ', 'hpm') );
					$api_key->add_validator( 'validate_required' );

					$ui->append( 'submit', 'save', _t( 'Save', 'hpm' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved', 'hpm' ) );
					$ui->out();
					break;
			}
		}
	}

	public function filter_admin_access_tokens( array $require_any, $page )
	{
		Plugins::act( 'hpm_init' );

		switch ($page) {
			case 'hpm':
				$types = HabariPackages::list_package_types();
				foreach ($types as $type) {
					$require_any['install_new_' . $type] = true;
				}
				break;
		}
		return $require_any;
	}

	public function filter_adminhandler_post_loadplugins_main_menu( $menus )
	{
		$menus['plugins']['submenu']['plugs'] =  array( 'url' => URL::get( 'admin', 'page=plugins'), 'title' => _t('Currently available plugins', 'hpm'), 'text' => _t('Available Plugins', 'hpm'), 'selected' => false, 'hotkey' => 1, 'access'=>array('install_new_plugins', true) );
		$menus['plugins']['submenu']['hpm'] =  array( 'url' => URL::get( 'admin', 'page=hpm&type=plugin'), 'title' => _t('Find new plugins', 'hpm'), 'text' => _t('Get New Plugins', 'hpm'), 'selected' => false, 'hotkey' => 2, 'access'=>array('install_new_plugins', true) );

		$menus['themes']['submenu']['themeses'] =  array( 'url' => URL::get( 'admin', 'page=themes'), 'title' => _t('Currently available themes', 'hpm'), 'text' => _t('Available Themes', 'hpm'), 'selected' => false, 'hotkey' => 1, 'access'=>array('install_new_themes', true) );
		$menus['themes']['submenu']['hpm'] =  array( 'url' => URL::get( 'admin', 'page=hpm&type=theme'), 'title' => _t('Find new themes', 'hpm'), 'text' => _t('Get New Themes', 'hpm'), 'selected' => false, 'hotkey' => 2, 'access'=>array('install_new_themes', true) );

		return $menus;
	}

	public function action_admin_theme_get_hpm( $handler, $theme )
	{
		$type = isset($handler->handler_vars['type']) ? $handler->handler_vars['type'] : null;
		$paged = isset($handler->handler_vars['paged']) ? $handler->handler_vars['paged'] : null;
		$limit = 20;

		if ( isset( $handler->handler_vars['action'] ) ) {
			$action = $handler->handler_vars['action'];
			if ( method_exists( $this, "act_$action" ) ) {
				$this->{"act_$action"}( $handler, $theme, $type );
			}
			else {
				Plugins::act( "hpm_act_$action", $handler, $theme, $type );
			}
		}

		if ( HabariPackages::require_updates() ) {
			Session::notice( "The packages list is out of date. " );
		}

		if ( !is_writable( HabariPackages::tempdir() ) || !is_writable( HABARI_PATH . '/3rdparty' ) ) {
			$theme->notice = 'permissions';
			$theme->display('hpm_notice');
			exit;
		}

		$offset = $paged ? $limit * $paged : 0;

		if ( $type ) {
			$theme->packages = DB::get_results(
				"SELECT * FROM {packages} WHERE type = ? LIMIT $limit OFFSET $offset",
				array($type),
				'HabariPackage'
			);
		}
		else {
			$theme->packages = DB::get_results(
				"SELECT * FROM {packages} LIMIT $limit OFFSET $offset",
				array(),
				'HabariPackage'
			);
		}
		$theme->type = $type;
		$theme->display('hpm');
		exit;
	}

	public function act_update( $handler, $theme )
	{
		try {
			HabariPackages::update();
			Session::notice( 'Package List is now up to date.' );
		}
		catch (Exception $e) {
			Session::error( 'Could not update Package List' );
			if ( DEBUG ) {
				Utils::debug($e);
			}
		}
	}

	public function act_upgrade( $handler, $theme )
	{
		try {
			$package = HabariPackages::upgrade( $handler->handler_vars['guid'] );
			Session::notice( "{$package->name} was upgraded to version {$package->version}." );
			if ( $package->readme_doc ) {
				$theme->notice = 'readme';
				$theme->package = $package;
				$theme->display('hpm_notice');
				exit;
			}
		}
		catch (Exception $e) {
			Session::error( 'Could not complete upgrade: '.  $e->getMessage() );
			if ( DEBUG ) {
				Utils::debug($e);
			}
		}
	}

	public function act_install( $handler, $theme )
	{
		try {
			$package = HabariPackages::install( $handler->handler_vars['guid'] );
			Session::notice( "{$package->name} {$package->version} was installed." );
			if ( $package->readme_doc ) {
				$theme->notice = 'readme';
				$theme->package = $package;
				$theme->display('hpm_notice');
				exit;
			}
		}
		catch (Exception $e) {
			Session::error( 'Could not complete install: '.  $e->getMessage() );
			if ( DEBUG ) {
				Utils::debug($e);
			}
		}
	}

	public function act_uninstall( $handler, $theme )
	{
		try {
			$package = HabariPackages::remove( $handler->handler_vars['guid'] );
			Session::notice( "{$package->name} {$package->version} was uninstalled." );
		}
		catch (Exception $e) {
			Session::error( 'Could not complete uninstall: '.  $e->getMessage() );
			if ( DEBUG ) {
				Utils::debug($e);
			}
		}
	}

	public function action_auth_ajax_hpm_packages( $handler )
	{
		Plugins::act('hpm_init');
		$theme = Themes::create( 'admin', 'RawPHPEngine', dirname(__FILE__) .'/' );
		$search = isset( $handler->handler_vars['search'] ) ? $handler->handler_vars['search'] : '';
		$search = explode( ' ', $search );
		$where = array();
		$vals = array();
		$limit = 20;
		$offset = (int) $handler->handler_vars['offset'] ? $handler->handler_vars['offset'] : 0;

		foreach ( $search as $term ) {
			$where[] = "(name LIKE CONCAT('%',?,'%') OR description LIKE CONCAT('%',?,'%') OR tags LIKE CONCAT('%',?,'%') OR type LIKE CONCAT('%',?,'%'))";
			$vals = array_pad( $vals, count($vals) + 4, $term );
		}

		$theme->packages = DB::get_results(
			'SELECT * FROM {packages} WHERE ' .
				implode( ' AND ', $where ) .
				"LIMIT $limit OFFSET $offset",
			$vals,
			'HabariPackage'
		);
		echo json_encode( array( 'items' =>  $theme->fetch('hpm_packages') ) );
	}

	public function action_hpm_init()
	{
		DB::register_table('packages');

		include 'habaripackage.php';
		include 'habaripackages.php';
		include 'packagearchive.php';
		include 'archivereader.php';
		include 'tarreader.php';
		include 'zipreader.php';
		include 'txtreader.php';

		PackageArchive::register_archive_reader( 'application/x-zip', 'ZipReader' );
		PackageArchive::register_archive_reader( 'application/zip', 'ZipReader' );
		PackageArchive::register_archive_reader( 'application/x-tar', 'TarReader' );
		PackageArchive::register_archive_reader( 'application/tar', 'TarReader' );
		PackageArchive::register_archive_reader( 'application/x-gzip', 'TarReader' );
		PackageArchive::register_archive_reader( 'text/plain', 'TxtReader' );
		PackageArchive::register_archive_reader( 'text/php', 'TxtReader' );
		PackageArchive::register_archive_reader( 'application/php', 'TxtReader' );

		$this->add_template( 'hpm', dirname(__FILE__) . '/templates/hpm.php' );
		$this->add_template( 'hpm_packages', dirname(__FILE__) . '/templates/hpm_packages.php' );
		$this->add_template( 'hpm_notice', dirname(__FILE__) . '/templates/hpm_notice.php' );
	}
}


?>
