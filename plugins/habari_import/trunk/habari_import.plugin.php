<?php

define( 'IMPORT_BATCH', 100 );

/**
 * Habari Importer - Imports data from another Habari database
 *
 */
class HabariImport extends Plugin implements Importer
{
	private $supported_importers = array();

	const TYPE_MYSQL = 0;
	const TYPE_SQLITE = 1;
	const TYPE_PGSQL = 2;

	private $default_values = array(
		'db_type' => self::TYPE_MYSQL,
		'db_name' => '',
		'db_host' => 'localhost',
		'db_user' => '',
		'db_pass' => '',
		'db_prefix' => 'habari_',
		'warning' => '',
		'tag_import' => 1,
		'import_index' => 0.
	 );

	/**
	 * Initialize plugin.
	 * Set the supported importers.
	 **/
	public function action_init()
	{
		$this->supported_importers = array( _t( 'Habari Database' ) );
	}

	/**
	 * Return a list of names of things that this importer imports
	 *
	 * @return array List of importables.
	 */
	public function filter_import_names( $import_names )
	{
		return array_merge( $import_names, $this->supported_importers );
	}

	/**
	 * Plugin filter that supplies the UI for the Habari importer
	 *
	 * @param string $stageoutput The output stage UI
	 * @param string $import_name The name of the selected importer
	 * @param string $stage The stage of the import in progress
	 * @param string $step The step of the stage in progress
	 * @return output for this stage of the import
	 */
	public function filter_import_stage( $stageoutput, $import_name, $stage, $step )
	{
		// Only act on this filter if the import_name is one we handle...
		if( !in_array( $import_name, $this->supported_importers ) ) {
			// Must return $stageoutput as it may contain the stage HTML of another importer
			return $stageoutput;
		}

		$inputs = array();

		// Validate input from various stages...
		switch( $stage ) {
		case 1:
			if( isset( $_POST['habimport'] ) ) {
				$inputs = $_POST->filter_keys( 'db_type', 'db_name','db_host','db_user','db_pass','db_prefix', 'tag_import' );
				$inputs = $inputs->getArrayCopy();

				$connect_string = $this->get_connect_string( $inputs['db_type'], $inputs['db_host'], $inputs['db_name'] );
				if( $this->hab_connect( $connect_string, $inputs['db_user'], $inputs['db_pass'], $inputs['db_prefix'] ) ) {
					$stage = 2;
				}
				else {
					$inputs['warning']= _t( 'Could not connect to the Habari database using the values supplied. Please correct them and try again.' );
				}
			}
			break;
		}

		// Based on the stage of the import we're on, do different things...
		switch( $stage ) {
		case 1:
		default:
			$output = $this->stage1( $inputs );
			break;
		case 2:
			$output = $this->stage2( $inputs );
		}

		return $output;
	}

	/**
	 * Create the UI for stage one of the import process
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the first stage of the import process
	 */
	private function stage1( $inputs )
	{
		$warning = '';

		$inputs = array_merge( $this->default_values, $inputs );
		if( $inputs['warning'] != '' ) {
			$warning = "<p class=\"warning\">{$inputs['warning']}</p>";
		}
		$output = <<< HAB_IMPORT_STAGE1
			<p>Habari will attempt to import from another Habari Database.</p>
			{$warning}
			<p>Please provide the connection details for an existing Habari database:</p>
			<div class="item clear">
				<span class="pct25"><label for="db_type">Database Type</label></span>
				<span>
					<input type="radio" name="db_type" value="0" tab index="1" checked />MySQL
					<input type="radio" name="db_type" value="1" tab index="2" />SQLite
					<input type="radio" name="db_type" value="2" tab index="3" />PostgreSQL
				</span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_name">Database Name</label></span><span class="pct40"><input type="text" name="db_name" value="{$inputs['db_name']}" tab index="4"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_host">Database Host</label></span><span class="pct40"><input type="text" name="db_host" value="{$inputs['db_host']}" tab index="5"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_user">Database User</label></span><span class="pct40"><input type="text" name="db_user" value="{$inputs['db_user']}" tab index="6"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_pass">Database Password</label></span><span class="pct40"><input type="password" name="db_pass" value="{$inputs['db_pass']}" tab index="7"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_prefix">Table Prefix</label></span><span class="pct40"><input type="text" name="db_prefix" value="{$inputs['db_prefix']}" tab index="8"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="tag_import">Import Tags</label></span><span class="pct40"><input type="checkbox" name="tag_import" value="1" checked></span>
			</div>
				
			<div class="clear"></div>
				<input type="hidden" name="stage" value="1">
			</div>
			<div
			<div class="container transparent">
				<input type="submit" class="button" name="habimport" value="Import" />
			</div>
HAB_IMPORT_STAGE1;
		return $output;
	}

	/**
	 * Create the UI for stage two of the import process
	 * This stage kicks off the ajax import.
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the second stage of the import process
	 */
	private function stage2( $inputs )
	{
		$inputs = array_merge($this->default_values, $inputs );

		$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'hab_import_users' ) );
		EventLog::log( sprintf( _t('Starting import from "%s"'), $inputs['db_name'] ) );
		Options::set( 'import_errors', array() );

		$output = "<p>Import In Progress</p>";
		$output .= '<div id="import_progress">Starting Import...</div>';
		$output .= $this->get_ajax( $ajax_url, $inputs );
		return $output;
	}

	/**
	 * Attempt to connect to the Habari database
	 *
	 * @param string $connect_string The connection string of the Habari database
	 * @param string $db_user The user of the database
	 * @param string $db_pass The user's password for the database
	 * @param string $db_prefix The table prefix in the database
	 * @return mixed false on failure, DatabseConnection on success
	 */
	private function hab_connect( $connect_string, $db_user, $db_pass )
	{
		// Connect to the database or return false
		try {
			$db = DatabaseConnection::ConnectionFactory( $connect_string );;
			$db->connect( $connect_string, $db_user, $db_pass );
			return $db;
		}
		catch( Exception $e ) {
			return false;
		}
	}
	
	private function get_connect_string( $db_type, $db_host, $db_name )
	{
		switch ( $db_type ) {
			case self::TYPE_MYSQL:
				$connect_string = "mysql:host={$db_host};dbname={$db_name}";
				break;
			case self::TYPE_SQLITE:
				$connect_string = "sqlite:{$db_name}";
				break;
			case self::TYPE_PGSQL:
				$connect_string = "pgsql:host={$db_host} dbname={$db_name}";
				break;
		}
		return $connect_string;
	}

	private function get_ajax( $url, $vars = array() )
	{
		// generate the vars we'll use
		$ajax_vars = array();
		foreach ( $vars as $k => $v ) {
			$ajax_vars[] = $k . ': "' . Utils::addslashes( $v ) . '"';
		}
		$ajax_vars = implode( ',', $ajax_vars );

		$output = <<< OUTPUT_AJAX
			<script type="text/javascript">
				$(document).ready( function() {
					$('#import_progress').load(
						"{$url}",
						{
							{$ajax_vars}
						}
					);
				} );
			</script>
OUTPUT_AJAX;
		return $output;
	}

	/**
	 * The plugin sink for the auth_ajax_hab_import_users hook.
	 * Responds via authenticated ajax to requests for user importing.
	 *
	 * @param mixed $handler
	 * @return
	 */
	public function action_auth_ajax_hab_import_users( $handler )
	{
		$inputs = $_POST->filter_keys( 'db_type', 'db_name','db_host','db_user','db_pass','db_prefix','userindex', 'tag_import' );
		$inputs = $inputs->getArrayCopy( $inputs );
		$inputs = array_merge( $this->default_values, $inputs );

		$connect_string = $this->get_connect_string( $inputs['db_type'], $inputs['db_host'], $inputs['db_name'] );
		$db = $this->hab_connect( $connect_string, $inputs['db_user'], $inputs['db_pass'] );

		if( !$db ) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $inputs['db_name']), 'crit');
			Session::error( $e->getMessage() );
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
		}

		DB::begin_transaction();
		$new_users = $db->get_results(
			"SELECT username, password, email, {$inputs['db_prefix']}users.id as old_id
				FROM {$inputs['db_prefix']}users
				INNER JOIN {$inputs['db_prefix']}posts ON {$inputs['db_prefix']}posts.user_id = {$inputs['db_prefix']}users.id
				GROUP BY {$inputs['db_prefix']}users.id",
			array(),
			'User'
		);
		$usercount = 0;
		_e('<p>Importing users...</p>');

		foreach($new_users as $user) {
			$habari_user = User::get_by_name($user->username);
			// If username exists
			if($habari_user instanceof User) {
				$habari_user->info->old_id = $user->old_id;
				$habari_user->update();
			}
			else {
				// Add a new user
				try {
					$user->info->old_id = $user->old_id;
					// This should probably remain commented until we implement ACL more,
					// or any imported user will be able to log in and edit stuff
					//$user->password = '{MD5}' . $user->password;
					$user->exclude_fields( array( 'old_id' ) );
					$user->insert();
					$usercount++;
				}
				catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($user, $e), 1));
					Session::error( $e->getMessage() );
					$errors = Options::get('import_errors');
					$errors[] = $user->username . ' : ' . $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}
		}
		if( DB::in_transaction()) DB::commit();

		$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'hab_import_posts' ) );
		$inputs['import_index'] = 0;
		echo $this->get_ajax( $ajax_url, $inputs );
	}

	/**
	 * The plugin sink for the auth_ajax_hab_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_hab_import_posts( $handler )
	{
		$inputs = $_POST->filter_keys( 'db_type', 'db_name','db_host','db_user','db_pass','db_prefix','postindex', 'tag_import' );
		$inputs = $inputs->getArrayCopy($inputs);
		$inputs = array_merge($this->default_values, $inputs );

		$connect_string = $this->get_connect_string( $inputs['db_type'], $inputs['db_host'], $inputs['db_name'] );
		$db = $this->hab_connect( $connect_string, $inputs['db_user'], $inputs['db_pass'] );

		if( !$db ) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $inputs['db_name']), 'crit');
			Session::error( $e->getMessage() );
			echo '<p>'._t( 'The database connection details have failed to connect.' ).'</p>';
		}

		DB::begin_transaction();

		$old_db_version = (int)$db->get_value( "SELECT value FROM {$inputs['db_prefix']}options WHERE name = ?", array( 'db_version' ) );
		$postcount = $db->get_value( "SELECT count( id ) FROM {$inputs['db_prefix']}posts;" );
		$min = $inputs['import_index'] * IMPORT_BATCH + ( $inputs['import_index'] == 0 ? 0 : 1 );
		$max = min( ( $inputs['import_index'] + 1 ) * IMPORT_BATCH, $postcount );

		$user_map = array();
		$user_info = DB::get_results( "SELECT user_id, value FROM {userinfo} WHERE name= 'old_id';" );
		foreach( $user_info as $info ) {
			$user_map[$info->value]= $info->user_id;
		}
		echo "<p>Importing posts {$min}-{$max} of {$postcount}.</p>";
		$posts = $db->get_results( "
			SELECT
				content,
				id,
				title,
				slug,
				user_id,
				guid,
				pubdate,
				updated,
				modified,
				status,
				content_type
			FROM {$inputs['db_prefix']}posts
			ORDER BY id DESC
			LIMIT {$min}, " . IMPORT_BATCH
			, array(), 'Post' );

		$post_map = DB::get_column( "SELECT value FROM {$inputs['db_prefix']}postinfo WHERE name='old_id';");
		foreach( $posts as $post ) {
			if(in_array($post->id, $post_map)) {
				continue;
			}

			if ($inputs['tag_import'] == 1 ) {
				// Import tags
				if( $old_db_version < 3749 ) {
					$tags = $db->get_column(
						"SELECT tag_text
						FROM {$inputs['db_prefix']}tags
						INNER JOIN {$inputs['db_prefix']}tag2post
						ON {$inputs['db_prefix']}tags.id = {$inputs['db_prefix']}tag2post.tag_id
						WHERE post_id = {$post->id}"
					 );
				}
				else {
					$tags = $db->get_column(
						"SELECT term_display
						FROM {$inputs['db_prefix']}terms
						INNER JOIN {$inputs['db_prefix']}object_terms
						ON {$inputs['db_prefix']}terms.id = {$inputs['db_prefix']}object_terms.term_id
						WHERE object_id = ? AND object_type_id = ?", array( $post->id, Vocabulary::object_type_id( 'post' ) )
					 );
				}
			}
			else {
				$tags = array();
			}

			$tags = implode( ',', $tags );

			$post_array = $post->to_array();
			$p = new Post( $post_array );
			$p->slug = $post->slug;
			if(isset($user_map[$p->user_id])) {
				$p->user_id = $user_map[$p->user_id];
			}
			else {
				$errors = Options::get('import_errors');
				$errors[] = _t('Post author id %s was not found in the external database, assigning post "%s" (external post id #%d) to current user.', array($p->user_id, $p->title,$post_array['id']) );
				Options::set('import_errors', $errors);
				$p->user_id = User::identify()->id;
			}

			$p->guid = $p->guid; // Looks fishy, but actually causes the guid to be set.
			$p->tags = $tags;

			$infos = $db->get_results("SELECT name, value, type FROM {$inputs['db_prefix']}postinfo WHERE post_id = ?", array( $post_array['id'] ) );

			$p->info->old_id = $post_array['id'];  // Store the old post id in the post_info table for later

			try {
				$p->insert();
				$p->updated = $post_array['updated'];
				$p->update();
				foreach ( $infos as $info ) {
					$fields = $info->get_url_args();
					$fields['post_id'] = $p->id;
					DB::insert( DB::table( 'postinfo'), $fields );
				}
			}
			catch( Exception $e ) {
				EventLog::log($e->getMessage(), 'err', null, null, print_r(array($p, $e), 1));
				Session::error( $e->getMessage() );
				$errors = Options::get('import_errors');
				$errors[] = $p->title . ' : ' . $e->getMessage();
				Options::set('import_errors', $errors);
			}
		}

		if( DB::in_transaction() ) DB::commit();

		if( $max < $postcount ) {
			$inputs['import_index']++;
			$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'hab_import_posts' ) );
		}
		else {
			$inputs['import_index'] = 0;
			$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'hab_import_comments' ) );
		}
		echo $this->get_ajax( $ajax_url, $inputs );
	}

	/**
	 * The plugin sink for the auth_ajax_hab_import_comments hook.
	 * Responds via authenticated ajax to requests for comment importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_hab_import_comments( $handler )
	{
		$inputs = $_POST->filter_keys( 'db_type', 'db_name','db_host','db_user','db_pass','db_prefix','commentindex', 'tag_import' );
		$inputs = $inputs->getArrayCopy( $inputs );
		$inputs = array_merge( $this->default_values, $inputs );

		$connect_string = $this->get_connect_string( $inputs['db_type'], $inputs['db_host'], $inputs['db_name'] );
		$db = $this->hab_connect( $connect_string, $inputs['db_user'], $inputs['db_pass'] );

		if( !$db ) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $inputs['db_name']), 'crit');
			Session::error( $e->getMessage() );
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
		}

		DB::begin_transaction();

		$commentcount = $db->get_value( "SELECT count( id ) FROM {$inputs['db_prefix']}comments;" );
		$min = $inputs['import_index'] * IMPORT_BATCH + ( $inputs['import_index'] == 0 ? 0 : 1 );
		$max = min( ( $inputs['import_index'] + 1 ) * IMPORT_BATCH, $commentcount );

		echo "<p>Importing comments {$min}-{$max} of {$commentcount}.</p>";

		$post_info = DB::get_results( "SELECT post_id, value FROM {$inputs['db_prefix']}postinfo WHERE name= 'old_id';" );
		foreach( $post_info as $info ) {
			$post_map[$info->value] = $info->post_id;
		}

		$comments = $db->get_results( "
			SELECT
			c.id,
			c.content,
			c.name,
			c.email,
			c.url,
			c.ip,
			c.status,
			c.date,
			c.type,
			c.post_id as old_post_id
			FROM {$inputs['db_prefix']}comments c
			INNER JOIN
			{$inputs['db_prefix']}posts on {$inputs['db_prefix']}posts.id = c.post_id
			LIMIT {$min}, " . IMPORT_BATCH
			, array(), 'Comment' );

		foreach( $comments as $comment ) {
			$carray = $comment->to_array();

			if( isset( $post_map[$carray['old_post_id']] ) ) {
				$carray['post_id']= $post_map[$carray['old_post_id']];
				unset( $carray['old_post_id'] );

				$c = new Comment( $carray );

				$infos = $db->get_results("SELECT name, value, type FROM {$inputs['db_prefix']}commentinfo WHERE comment_id = ?", array( $carray['id'] ) );

				foreach( $infos as $info ) {
					$fields = $info->get_url_args();
					if($fields['type'] == 1) {
						$fields['value'] = unserialize( $fields['value'] );
					}
					$c->info->$fields['name'] = $fields['value'];
				}
				try{
					$c->insert();
				}
				catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($c, $e), 1));
					Session::error( $e->getMessage() );
					$errors = Options::get('import_errors');
					$errors[] = $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}
		}
		if( DB::in_transaction() ) DB::commit();

		if( $max < $commentcount ) {
			$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'hab_import_comments' ) );
			$inputs['import_index']++;
			echo $this->get_ajax( $ajax_url, $inputs );
		}
		else {
			EventLog::log('Import complete from "'. $inputs['db_name'] .'"');
			echo '<p>' . _t( 'Import is complete.' ) . '</p>';

			$errors = Options::get('import_errors');
			if(count($errors) > 0 ) {
				echo '<p>' . _t( 'There were errors during import:' ) . '</p>';

				echo '<ul>';
				foreach($errors as $error) {
					echo '<li>' . $error . '</li>';
				}
				echo '</ul>';
			}
		}
	}
}

?>