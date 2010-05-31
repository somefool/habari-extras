<?php

define( 'IMPORT_BATCH', 100 );

/**
 * Chyrp Importer - Imports data from Chyrp
 *
 */
class ChyrpImport extends Plugin implements Importer
{
	private $supported_importers = array();

	const TYPE_MYSQL = 0;
	const TYPE_SQLITE = 1;

	/**
	 * Initialize plugin.
	 * Set the supported importers.
	 */
	public function action_init()
	{
		$this->supported_importers = array( _t( 'Chyrp Database' ) );
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
	 * Plugin filter that supplies the UI for the importer
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
		if ( !in_array( $import_name, $this->supported_importers ) ) {
			// Must return $stageoutput as it may contain the stage HTML of another importer
			return $stageoutput;
		}

		$inputs = array();

		// Validate input from various stages...
		switch ( $stage ) {
			case 1:
				if ( count( $_POST ) ) {
					$inputs = $_POST->filter_keys( 'db_type', 'db_name', 'db_host', 'db_user', 'db_pass');
					foreach ( $inputs as $key => $value ) {
						$$key = $value;
					}

					$connect_string = $this->get_connect_string( $db_type, $db_host, $db_name );
					if ( $this->chryp_connect( $connect_string, $db_user, $db_pass ) ) {
						$stage = 2;
					}
					else {
						$inputs['warning']= _t( 'Could not connect to the database using the values supplied. Please correct them and try again.' );
					}
				}
				break;
		}

		// Based on the stage of the import we're on, do different things...
		switch ( $stage ) {
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
		$default_values = array(
			'db_type' => self::TYPE_MYSQL,
			'db_name' => '',
			'db_host' => 'localhost',
			'db_user' => '',
			'db_pass' => '',
			'warning' => ''
		 );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );
		if ( $warning != '' ) {
			$warning = "<p class=\"warning\">{$warning}</p>";
		}
		$output = <<< IMPORT_STAGE1
			<p>Habari will attempt to import from a Chyrp database.</p>
			{$warning}
			<p>Please provide the connection details the Chyrp database:</p>
			<div class="item clear">
				<span class="pct25"><label for="db_type">Database Type</label></span>
				<span>
					<input type="radio" name="db_type" value="0" tab index="1" checked />MySQL
					<input type="radio" name="db_type" value="1" tab index="2" />SQLite
				</span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_name">Database Name</label></span><span class="pct40"><input type="text" name="db_name" value="{$db_name}" tab index="4"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_host">Database Host</label></span><span class="pct40"><input type="text" name="db_host" value="{$db_host}" tab index="5"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_user">Database User</label></span><span class="pct40"><input type="text" name="db_user" value="{$db_user}" tab index="6"></span>
			</div>
			<div class="item clear">
				<span class="pct25"><label for="db_pass">Database Password</label></span><span class="pct40"><input type="password" name="db_pass" value="{$db_pass}" tab index="7"></span>
			</div>

			<div class="clear"></div>
				<input type="hidden" name="stage" value="1">
			</div>
			<div
			<div class="container transparent"
				<input type="submit" class="button" name="import" value="Import" />
			</div>
IMPORT_STAGE1;
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
		$inputs = $inputs->filter_keys(  'db_type', 'db_name', 'db_host', 'db_user', 'db_pass');
		foreach ( $inputs as $key => $value ) {
			$$key = $value;
		}

		$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'chyrp_import_users' ) );
		EventLog::log( sprintf( _t('Starting import from "%s"'), $db_name ) );
		Options::set( 'import_errors', array() );

		$vars = Utils::addslashes( array( 'host' => $db_host, 'name' => $db_name, 'user' => $db_user, 'pass' => $inputs['db_pass'] ) );

		$output = <<< IMPORT_STAGE2
		<p>Import In Progress</p>
		<div id="import_progress">Starting Import...</div>
		<script type="text/javascript">
		// A lot of ajax stuff goes here.
		$( document ).ready( function() {
			$( '#import_progress' ).load(
				"{$ajax_url}",
				{
					db_type: "{$db_type}",
					db_host: "{$vars['host']}",
					db_name: "{$vars['name']}",
					db_user: "{$vars['user']}",
					db_pass: "{$vars['pass']}",
					userindex: 0
				}
			 );
		} );
		</script>
IMPORT_STAGE2;
		return $output;
	}

	/**
	 * Attempt to connect to the Chyrp database
	 *
	 * @param string $connect_string The connection string of the Habari database
	 * @param string $db_user The user of the database
	 * @param string $db_pass The user's password for the database
	 * @return mixed false on failure, DatabseConnection on success
	 */
	private function chryp_connect( $connect_string, $db_user, $db_pass )
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
		}
		return $connect_string;
	}

	/**
	 * The plugin sink for the auth_ajax_chyrp_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param mixed $handler
	 * @return
	 */
	public function action_auth_ajax_chyrp_import_users( $handler )
	{
		$inputs = $_POST->filter_keys( 'db_type', 'db_name', 'db_host', 'db_user', 'db_pass', 'userindex' );
		foreach ( $inputs as $key => $value ) {
			$$key = $value;
		}

		$connect_string = $this->get_connect_string( $db_type, $db_host, $db_name );
		$db = $this->chryp_connect( $connect_string, $db_user, $db_pass );
		if ( $db ) {
			DB::begin_transaction();
			$new_users = $db->get_results(
				"
					SELECT
						login as username,
						password,
						email,
						users.id as old_id
					FROM users
					INNER JOIN posts ON posts.user_id = users.id
					GROUP BY users.id
				",
				array(),
				'User'
			);
			$usercount = 0;
			_e('<p>Importing users...</p>');

			foreach ( $new_users as $user ) {
				$habari_user = User::get_by_name($user->username);
				// If username exists
				if ( $habari_user instanceof User ) {
					$habari_user->info->old_id = $user->old_id;
					$habari_user->update();
				}
				else {
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
			if ( DB::in_transaction() ) {
				DB::commit();
			}

			$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'chyrp_import_posts' ) );

			$vars = Utils::addslashes( array( 'type' => $db_type, 'host' => $db_host, 'name' => $db_name, 'user' => $db_user, 'pass' => $db_pass ) );

			echo <<< IMPORT_POSTS
			<script type="text/javascript">
			// A lot of ajax stuff goes here.
			$( document ).ready( function(){
				$( '#import_progress' ).load(
					"{$ajax_url}",
					{
						db_type: "{$db_type}",
						db_host: "{$vars['host']}",
						db_name: "{$vars['name']}",
						db_user: "{$vars['user']}",
						db_pass: "{$vars['pass']}",
						postindex: 0
					}
				 );
			} );
			</script>
IMPORT_POSTS;
		}
		else {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			Session::error( $e->getMessage() );
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
		}
	}

	/**
	 * The plugin sink for the auth_ajax_chyrp_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_chyrp_import_posts( $handler )
	{
		$inputs = $_POST->filter_keys( 'db_type', 'db_name', 'db_host', 'db_user', 'db_pass', 'postindex' );
		foreach ( $inputs as $key => $value ) {
			$$key = $value;
		}

		$connect_string = $this->get_connect_string( $db_type, $db_host, $db_name );
		$db = $this->chryp_connect( $connect_string, $db_user, $db_pass );
		if ( $db ) {
			DB::begin_transaction();

			$postcount = $db->get_value( "SELECT count(id) FROM posts;" );
			$min = $postindex * IMPORT_BATCH + ( $postindex == 0 ? 0 : 1 );
			$max = min( ( $postindex + 1 ) * IMPORT_BATCH, $postcount );

			// old_id was set when we imported the users
			$user_map = array();
			$user_info = DB::get_results( "SELECT user_id, value FROM {userinfo} WHERE name='old_id';" );
			foreach ( $user_info as $info ) {
				$user_map[$info->value] = $info->user_id;
			}
			// Posts
			// id INTEGER PRIMARY KEY AUTOINCREMENT,
			// feather VARCHAR(32) DEFAULT '',
			// clean VARCHAR(128) DEFAULT '',
			// url VARCHAR(128) DEFAULT '',
			// pinned BOOLEAN DEFAULT FALSE,
			// status VARCHAR(32) DEFAULT 'public',
			// user_id INTEGER DEFAULT 0,
			// created_at DATETIME DEFAULT NULL,
			// updated_at DATETIME DEFAULT NULL
			//
			// Post attributes
			// post_id INTEGER NOT NULL ,
			// name VARCHAR(100) DEFAULT '',
			// value LONGTEXT,
			echo "<p>Importing posts {$min}-{$max} of {$postcount}.</p>";
			$posts = $db->get_results( "
				SELECT
					attr_body.value as content,
					id,
					attr_title.value as title,
					user_id,
					created_at as pubdate,
					updated_at as updated,
					updated_at as modified,
					status
				FROM posts
				INNER JOIN post_attributes attr_title ON posts.id = attr_title.post_id AND attr_title.name = 'title'
				INNER JOIN post_attributes attr_body ON posts.id = attr_body.post_id AND attr_body.name = 'body'
				ORDER BY id DESC
				LIMIT {$min}, " . IMPORT_BATCH
				, array(), 'Post' );

			$post_map = DB::get_column( "SELECT value FROM {postinfo} WHERE name='old_id';");
			foreach ( $posts as $post ) {
				if ( in_array($post->id, $post_map) ) {
					continue;
				}

				$post_array = $post->to_array();
				$post_array['content_type']= Post::type( 'entry' );
				$p = new Post( $post_array );
				$p->slug = $post->slug;
				if ( isset($user_map[$p->user_id]) ) {
					$p->user_id = $user_map[$p->user_id];
				}
				else {
					$errors = Options::get('import_errors');
					$errors[] = _t('Post author id %s was not found in the external database, assigning post "%s" (external post id #%d) to current user.', array($p->user_id, $p->title,$post_array['id']) );
					Options::set('import_errors', $errors);
					$p->user_id = User::identify()->id;
				}

				$p->guid = $p->guid; // Looks fishy, but actually causes the guid to be set.

				$p->info->old_id = $post_array['id'];  // Store the old post id in the post_info table for later

				try {
					$p->insert();
					$p->updated = $post_array['updated'];
					$p->update();
				}
				catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($p, $e), 1));
					Session::error( $e->getMessage() );
					$errors = Options::get('import_errors');
					$errors[] = $p->title . ' : ' . $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}

			if ( DB::in_transaction() ) {
				DB::commit();
			}

			if ( $max < $postcount ) {
				$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'chyrp_import_posts' ) );
				$postindex++;

				$vars = Utils::addslashes( array( 'host' => $db_host, 'name' => $db_name, 'user' => $db_user, 'pass' => $db_pass ) );

				echo <<< IMPORT_POSTS
					<script type="text/javascript">
					$( '#import_progress' ).load(
						"{$ajax_url}",
						{
							db_type: "{$db_type}",
							db_host: "{$vars['host']}",
							db_name: "{$vars['name']}",
							db_user: "{$vars['user']}",
							db_pass: "{$vars['pass']}",
							postindex: {$postindex}
						}
					);

				</script>
IMPORT_POSTS;
			}
			else {
				EventLog::log('Import complete from "'. $db_name .'"');
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
		else {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			Session::error( $e->getMessage() );
			echo '<p>'._t( 'The database connection details have failed to connect.' ).'</p>';
		}
	}

}

?>
