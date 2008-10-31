<?php

define( 'DRUPAL_IMPORT_BATCH', 100 );

/**
 * Drupal Importer - Imports data from Drupal into Habari
 */
class DrupalImport extends Plugin implements Importer
{
	private $supported_importers = array();
	
	/**
	 * Initialize plugin.
	 * Set the supported importers.
	 **/
	public function action_init()
	{
		$this->supported_importers = array( _t( 'Drupal 5.x Database' ) );
	}
	
	/**
	 * Return plugin metadata for this plugin
	 *
	 * @return array Plugin metadata
	 */
	public function info()
	{
		return array( 'name' => 'Drupal Importer', 'version' => '0.1', 'url' => 'http://habariproject.org/', 'author' => 'Joshua Benner', 'authorurl' => 'http://jbenner.net', 'license' => 'Apache License 2.0', 'description' => 'Import Drupal 5.x content.', 'copyright' => '2008' );
	}
	
	/**
	 * Return a list of names of things that this importer imports
	 *
	 * @return array List of importables.
	 */
	public function filter_import_names($import_names)
	{
		return array_merge( $import_names, $this->supported_importers );
	}
	
	/**
	 * Plugin filter that supplies the UI for the Drupal importer
	 *
	 * @param string $stageoutput The output stage UI
	 * @param string $import_name The name of the selected importer
	 * @param string $stage The stage of the import in progress
	 * @param string $step The step of the stage in progress
	 * @return output for this stage of the import
	 */
	public function filter_import_stage($stageoutput, $import_name, $stage, $step)
	{
		// Only act on this filter if the import_name is one we handle...
		if ( ! in_array( $import_name, $this->supported_importers ) ) {
			// Must return $stageoutput as it may contain the stage HTML of another importer
			return $stageoutput;
		}
		
		$inputs = array();
		
		// Validate input from various stages...
		switch ( $stage ) {
			case 1:
				if ( isset( $_POST ) ) {
					$valid_fields = array( 'db_name', 'db_host', 'db_user', 'db_pass', 'db_prefix', 'import_comments' );
					$inputs = array_intersect_key( $_POST, array_flip( $valid_fields ) );
					if ( $drupaldb = $this->drupal_connect( $inputs['db_host'], $inputs['db_name'], $inputs['db_user'], $inputs['db_pass'], $inputs['db_prefix'] ) ) {
						$has_node_type = count( $drupaldb->get_results( "SHOW TABLES LIKE '{$inputs['db_prefix']}node_type'" ) );
						$has_menu = count( $drupaldb->get_results( "SHOW TABLES LIKE '{$inputs['db_prefix']}menu'" ) );
						if ( $has_node_type && $has_menu ) {
							$stage = 2;
						}
						else {
							$inputs['warning'] = _t( 'Specified database does not appear to be a Drupal 5.x database. Please enter connection values for a Drupal 5.x database.' );
						}
					}
					else {
						$inputs['warning'] = _t( 'Could not connect to the Drupal database using the values supplied. Please correct them and try again.' );
					}
				}
				break;
			case 2:
				if ( isset( $_POST ) ) {
					$valid_fields = array( 'db_name', 'db_host', 'db_user', 'db_pass', 'db_prefix', 'import_comments', 'entry_type', 'page_type', 'tag_vocab' );
					$inputs = array_intersect_key( $_POST, array_flip( $valid_fields ) );
					// We could re-do the Drupal types/vocab lookup... is that really necessary?
					$stage = 3;
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
				break;
			case 3:
				$output = $this->stage3( $inputs );
		}
		
		return $output;
	}
	
	/**
	 * Create the UI for stage one of the Drupal import process
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the first stage of the import process
	 */
	private function stage1($inputs)
	{
		$default_values = array( 'db_name' => '', 'db_host' => 'localhost', 'db_user' => '', 'db_pass' => '', 'db_prefix' => '', 'import_comments' => 1, 'warning' => '' );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );
		if ( $warning != '' ) {
			$warning = "<p class=\"warning\">{$warning}</p>";
		}
		$import_comments_checked = $import_comments ? ' checked="checked"' : '';
		$output = <<< DRUPAL_IMPORT_STAGE1
			<p>Habari will attempt to import from a Drupal 5.x Database.</p>
			{$warning}
			<p>Please provide the connection details for an existing Drupal 5.x database:</p>
			<table>
				<tr><td>Database Name</td><td><input type="text" name="db_name" value="{$db_name}"></td></tr>
				<tr><td>Database Host</td><td><input type="text" name="db_host" value="{$db_host}"></td></tr>
				<tr><td>Database User</td><td><input type="text" name="db_user" value="{$db_user}"></td></tr>
				<tr><td>Database Password</td><td><input type="password" name="db_pass" value="{$db_pass}"></td></tr>
				<tr><td>Table Prefix</td><td><input type="text" name="db_prefix" value="{$db_prefix}"></td></tr>
			</table>
			<input type="hidden" name="stage" value="1">
			<p class="extras" style="border: solid 1px #ccc; padding: 5px;">
				Extras - additional data from Drupal modules
				<table>
				<tr>
				<td>Import comments</td>
				<td><input type="checkbox" name="import_comments" value="1"{$import_comments_checked}></td>
				</tr>
				</table>
			</p>
			<p class="submit"><input type="submit" name="import" value="Import" /></p>

DRUPAL_IMPORT_STAGE1;
		return $output;
	}
	
	/**
	 * Create UI to prompt user to map Drupal content types to Habari standard
	 * types.
	 *
	 * @param array $inputs Inputs received via $_POST to the importer.
	 * @return string The UI for the second stage of the import process.
	 */
	private function stage2($inputs)
	{
		$default_values = array( 'entry_type' => 'blog', 'page_type' => 'page', 'tag_vocab' => 0, 'warning' => '' );
		$inputs = array_merge( $default_values, $inputs );
		extract( $inputs );
		if ( $warning != '' ) {
			$warning = "<p class=\"warning\">{$warning}</p>";
		}
		$drupaldb = $this->drupal_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		// Retrieve lists of Drupal content types and vocabularies.
		$drupal_types = $drupaldb->get_results( "SELECT type,name FROM {$db_prefix}node_type" );
		$drupal_vocabs = $drupaldb->get_results( "SELECT vid,name FROM {$db_prefix}vocabulary" );
		$entry_options = $page_options = '<option value="">None</option>';
		$vocab_options = '<option value="0">Do not import tags</option>';
		foreach ( $drupal_types as $type ) {
			$entry_options .= "\n" . '<option value="' . $type->type . '"' . ($entry_type == $type->type ? ' selected="selected" ' : '') . '>' . $type->name . '</option>';
			$page_options .= "\n" . '<option value="' . $type->type . '"' . ($page_type == $type->type ? ' selected="selected" ' : '') . '>' . $type->name . '</option>';
		}
		foreach ( $drupal_vocabs as $vocab ) {
			$vocab_options .= "\n" . '<option value="' . $vocab->vid . '"' . ($tag_vocab == $vocab->vid ? ' selected="selected" ' : '') . '>' . $vocab->name . '</option>';
		}
		$output = <<< DRUPAL_IMPORT_STAGE2
			<p>Habari will attempt to import from a Drupal 5.x Database.</p>
			{$warning}
			<p>Select the content types to import as entries and pages, and the vocabulary to import as tags:</p>
			<table>
				<tr><td>Entry Content Type</td><td><select name="entry_type">{$entry_options}</select></td></tr>
				<tr><td>Page Content Type</td><td><select name="page_type">{$page_options}</select></td></tr>
				<tr><td>Tag Vocabulary</td><td><select name="tag_vocab" >{$vocab_options}</td></tr>
			</table>
			<input type="hidden" name="stage" value="2">
			<input type="hidden" name="db_host" value="{$db_host}">
			<input type="hidden" name="db_name" value="{$db_name}">
			<input type="hidden" name="db_user" value="{$db_user}">
			<input type="hidden" name="db_pass" value="{$db_pass}">
			<input type="hidden" name="db_prefix" value="{$db_prefix}">
			<input type="hidden" name="import_comments" value="{$import_comments}">
			<p class="submit"><input type="submit" name="import" value="Import" /></p>
DRUPAL_IMPORT_STAGE2;
		return $output;
	}
	
	/**
	 * Create the UI for stage two of the Drupal import process
	 * This stage kicks off the ajax import.
	 *
	 * @param array $inputs Inputs received via $_POST to the importer
	 * @return string The UI for the third stage of the import process
	 */
	private function stage3($inputs)
	{
		extract( $inputs );
		
		$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'drupal_import_users' ) );
		EventLog::log( sprintf( _t( 'Starting import from "%s"' ), $db_name ) );
		Options::set( 'import_errors', array() );
		
		$output = <<< DRUPAL_IMPORT_STAGE3
			<p>Import In Progress</p>
			<div id="import_progress">Starting Import...</div>
			<script type="text/javascript">
			// A lot of ajax stuff goes here.
			$( document ).ready( function(){
				$( '#import_progress' ).load(
					"{$ajax_url}",
					{
						db_host: "{$db_host}",
						db_name: "{$db_name}",
						db_user: "{$db_user}",
						db_pass: "{$db_pass}",
						db_prefix: "{$db_prefix}",
						import_comments: "{$import_comments}",
						entry_type: "{$entry_type}",
						page_type: "{$page_type}",
						tag_vocab: "{$tag_vocab}",
						postindex: 0
					}
				 );
			} );
			</script>
DRUPAL_IMPORT_STAGE3;
		return $output;
	}
	
	/**
	 * Attempt to connect to the Drupal database
	 *
	 * @param string $db_host The hostname of the Drupal database
	 * @param string $db_name The name of the Drupal database
	 * @param string $db_user The user of the Drupal database
	 * @param string $db_pass The user's password for the Drupal database
	 * @param string $db_prefix The table prefix for the Drupal instance in the database
	 * @return mixed false on failure, DatabseConnection on success
	 */
	private function drupal_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix)
	{
		// Connect to the database or return false
		try {
			$drupaldb = new DatabaseConnection( );
			$drupaldb->connect( "mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass, $db_prefix );
			return $drupaldb;
		}
		catch ( Exception $e ) {
			return false;
		}
	}
	
	/**
	 * The plugin sink for the auth_ajax_drupal_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_drupal_import_posts($handler)
	{
		$valid_fields = array( 'db_name', 'db_host', 'db_user', 'db_pass', 'db_prefix', 'import_comments', 'postindex', 'entry_type', 'page_type', 'tag_vocab' );
		$inputs = array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract( $inputs );
		
		$drupaldb = $this->drupal_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		if ( $drupaldb ) {
			$postcount = $drupaldb->get_value( "SELECT count( nid ) FROM {$db_prefix}node WHERE type IN ( '{$entry_type}', '{$page_type}' );" );
			$min = $postindex * DRUPAL_IMPORT_BATCH + ($postindex == 0 ? 0 : 1);
			$max = min( ($postindex + 1) * DRUPAL_IMPORT_BATCH, $postcount );
			
			$user_map = array();
			$userinfo = DB::table( 'userinfo' );
			$user_info = DB::get_results( "SELECT user_id, value FROM {$userinfo} WHERE name= 'drupal_uid';" );
			foreach ( $user_info as $info ) {
				$user_map[$info->value] = $info->user_id;
			}
			
			echo "<p>Importing posts {$min}-{$max} of {$postcount}.</p>";
			$posts = $drupaldb->get_results( "
				SELECT
					n.nid,
					nr.body as content,
					n.title,
					n.uid as user_id,
					FROM_UNIXTIME( n.created ) as pubdate,
					FROM_UNIXTIME( n.changed ) as updated,
					n.status as post_status,
					n.type as post_type
				FROM {$db_prefix}node AS n
				INNER JOIN {$db_prefix}node_revisions AS nr ON (nr.vid = n.vid)
				ORDER BY n.nid DESC
				LIMIT {$min}, " . DRUPAL_IMPORT_BATCH, array(), 'Post' );
			$post_map = DB::get_column( "SELECT value FROM {postinfo} WHERE name='drupal_nid';" );
			foreach ( $posts as $post ) {
				
				if ( in_array( $post->nid, $post_map ) ) {
					continue;
				}
				
				if ( $tag_vocab ) {
					$tags = $drupaldb->get_column( "SELECT DISTINCT td.name
						FROM {$db_prefix}term_node AS tn
						INNER JOIN {$db_prefix}term_data AS td ON (td.tid = tn.tid AND td.vid = {$tag_vocab})
						WHERE tn.nid = {$post->nid}" );
				}
				else {
					$tags = array();
				}
				
				$post_array = $post->to_array();
				switch ( $post_array['post_status'] ) {
					case '1':
						$post_array['status'] = Post::status( 'published' );
						break;
					default:
						$post_array['status'] = Post::status( 'draft' );
						break;
				}
				unset( $post_array['post_status'] );
				
				switch ( $post_array['post_type'] ) {
					case $entry_type:
						$post_array['content_type'] = Post::type( 'entry' );
						break;
					case $page_type:
						$post_array['content_type'] = Post::type( 'page' );
						break;
				}
				unset( $post_array['post_type'] );
				
				$post_array['content'] = preg_replace( '/<!--\s*break\s*-->/', '<!--more-->', $post_array['content'] );
				
				$p = new Post( $post_array );
				$p->user_id = $user_map[$p->user_id];
				$p->tags = $tags;
				$p->info->drupal_nid = $post_array['nid']; // Store the Drupal post id in the post_info table for later
				$p->exclude_fields( array( 'nid' ) );
				
				try {
					$p->insert();
				}
				catch ( Exception $e ) {
					EventLog::log( $e->getMessage(), 'err', null, null, print_r( array( $p, $e ), 1 ) );
					$errors = Options::get( 'import_errors' );
					$errors[] = $p->title . ' : ' . $e->getMessage();
					Options::set( 'import_errors', $errors );
				}
			}
			if ( $max < $postcount ) {
				$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'drupal_import_posts' ) );
				$postindex ++;
				
				echo <<< DRUPAL_IMPORT_AJAX1
					<script type="text/javascript">
					$( '#import_progress' ).load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							import_comments: "{$import_comments}",
							entry_type: "{$entry_type}",
							page_type: "{$page_type}",
							tag_vocab: "{$tag_vocab}",
							postindex: {$postindex}
						}
					 );

				</script>
DRUPAL_IMPORT_AJAX1;
			}
			else {
				$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'drupal_import_comments' ) );
				
				echo <<< DRUPAL_IMPORT_AJAX2
					<script type="text/javascript">
					$( '#import_progress' ).load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							import_comments: "{$import_comments}",
							entry_type: "{$entry_type}",
							page_type: "{$page_type}",
							tag_vocab: "{$tag_vocab}",
							commentindex: 0
						}
					 );

				</script>
DRUPAL_IMPORT_AJAX2;
			
			}
		}
		else {
			EventLog::log( sprintf( _t( 'Failed to import from "%s"' ), $db_name ), 'crit' );
			echo '<p>' . _t( 'The database connection details have failed to connect.' ) . '</p>';
		}
	}
	
	/**
	 * The plugin sink for the auth_ajax_drupal_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @param mixed $handler
	 * @return
	 */
	public function action_auth_ajax_drupal_import_users($handler)
	{
		$valid_fields = array( 'db_name', 'db_host', 'db_user', 'db_pass', 'db_prefix', 'import_comments', 'userindex', 'entry_type', 'page_type', 'tag_vocab' );
		$inputs = array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract( $inputs );
		$drupaldb = $this->drupal_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		if ( $drupaldb ) {
			$drupal_users = $drupaldb->get_results( "
				SELECT
					uid,
					name as username,
					pass as password,
					mail as email
				FROM {$db_prefix}users
				WHERE uid > 0
			", array(), 'User' );
			$usercount = 0;
			_e( '<p>Importing users...</p>' );
			foreach ( $drupal_users as $user ) {
				$habari_user = User::get_by_name( $user->username );
				// If username exists
				if ( $habari_user instanceof User ) {
					$habari_user->info->drupal_uid = $user->uid;
					$habari_user->update();
				}
				else {
					try {
						$user->info->drupal_uid = $user->uid;
						// This should probably remain commented until we implement ACL more,
						// or any imported user will be able to log in and edit stuff
						//$user->password = '{MD5}' . $user->password;
						$user->exclude_fields( array( 'uid', 'drupal_uid' ) );
						$user->insert();
						$usercount ++;
					}
					catch ( Exception $e ) {
						EventLog::log( $e->getMessage(), 'err', null, null, print_r( array( $user, $e ), 1 ) );
						$errors = Options::get( 'import_errors' );
						$errors[] = $user->username . ' : ' . $e->getMessage();
						Options::set( 'import_errors', $errors );
					}
				}
			}
			$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'drupal_import_posts' ) );
			echo <<< DRUPAL_IMPORT_USERS1
			<script type="text/javascript">
			// A lot of ajax stuff goes here.
			$( document ).ready( function(){
				$( '#import_progress' ).load(
					"{$ajax_url}",
					{
						db_host: "{$db_host}",
						db_name: "{$db_name}",
						db_user: "{$db_user}",
						db_pass: "{$db_pass}",
						db_prefix: "{$db_prefix}",
						import_comments: "{$import_comments}",
						entry_type: "{$entry_type}",
						page_type: "{$page_type}",
						tag_vocab: "{$tag_vocab}",
						postindex: 0
					}
				 );
			} );
			</script>
DRUPAL_IMPORT_USERS1;
		}
		else {
			EventLog::log( sprintf( _t( 'Failed to import from "%s"' ), $db_name ), 'crit' );
			echo '<p>' . _t( 'Failed to connect using the given database connection details.' ) . '</p>';
		}
	}
	
	/**
	 * The plugin sink for the auth_ajax_drupal_import_comments hook.
	 * Responds via authenticated ajax to requests for comment importing.
	 *
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_drupal_import_comments($handler)
	{
		$valid_fields = array( 'db_name', 'db_host', 'db_user', 'db_pass', 'db_prefix', 'import_comments', 'commentindex', 'entry_type', 'page_type', 'tag_vocab' );
		$inputs = array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract( $inputs );
		$drupaldb = $this->drupal_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		if ( $drupaldb ) {
			$commentcount = $drupaldb->get_value( "SELECT count( c.cid ) FROM {$db_prefix}comments AS c INNER JOIN {$db_prefix}node AS n ON (n.nid = c.nid) WHERE n.type IN ('{$entry_type}', '{$page_type}')" );
			$min = $commentindex * DRUPAL_IMPORT_BATCH + 1;
			$max = min( ($commentindex + 1) * DRUPAL_IMPORT_BATCH, $commentcount );
			
			echo "<p>Importing comments {$min}-{$max} of {$commentcount}.</p>";
			
			$postinfo = DB::table( 'postinfo' );
			$post_info = DB::get_results( "SELECT post_id, value FROM {$postinfo} WHERE name= 'drupal_nid';" );
			foreach ( $post_info as $info ) {
				$post_map[$info->value] = $info->post_id;
			}
			
			if ( $import_comments ) {
				$comments = $drupaldb->get_results( "
					SELECT
						c.nid as drupal_post_nid,
						c.comment as content,
						c.name,
						c.mail as email,
						c.homepage as url,
						INET_ATON( c.hostname ) as ip,
						c.status,
						FROM_UNIXTIME( c.timestamp ) as date
					FROM {$db_prefix}comments AS c
					INNER JOIN {$db_prefix}node AS n on ( n.nid = c.nid )
					LIMIT {$min}, " . DRUPAL_IMPORT_BATCH, array(), 'Comment' );
			}
			else {
				$comments = array();
			}
			
			foreach ( $comments as $comment ) {
				$comment->type = Comment::COMMENT;
				$comment->status = $comment->status == '0' ? 1 : 0;
				$carray = $comment->to_array();
				if ( $carray['ip'] == '' ) {
					$carray['ip'] = 0;
				}
				if ( ! isset( $post_map[$carray['drupal_post_nid']] ) ) {
					Utils::debug( $carray );
				}
				else {
					$carray['post_id'] = $post_map[$carray['drupal_post_nid']];
					unset( $carray['drupal_post_nid'] );
					
					$c = new Comment( $carray );
					//Utils::debug( $c );
					try {
						$c->insert();
					}
					catch ( Exception $e ) {
						EventLog::log( $e->getMessage(), 'err', null, null, print_r( array( $c, $e ), 1 ) );
						$errors = Options::get( 'import_errors' );
						$errors[] = $e->getMessage();
						Options::set( 'import_errors', $errors );
					}
				}
			}
			
			if ( $max < $commentcount ) {
				$ajax_url = URL::get( 'auth_ajax', array( 'context' => 'drupal_import_comments' ) );
				$commentindex ++;
				
				echo <<< DRUPAL_IMPORT_AJAX1
					<script type="text/javascript">
					$( '#import_progress' ).load(
						"{$ajax_url}",
						{
							db_host: "{$db_host}",
							db_name: "{$db_name}",
							db_user: "{$db_user}",
							db_pass: "{$db_pass}",
							db_prefix: "{$db_prefix}",
							import_comments: "{$import_comments}",
							entry_type: "{$entry_type}",
							page_type: "{$page_type}",
							tag_vocab: "{$tag_vocab}",
							commentindex: {$commentindex}
						}
					 );

				</script>
DRUPAL_IMPORT_AJAX1;
			}
			else {
				EventLog::log( 'Import complete from "' . $db_name . '"' );
				echo '<p>' . _t( 'Import is complete.' ) . '</p>';
				
				$errors = Options::get( 'import_errors' );
				if ( count( $errors ) > 0 ) {
					echo '<p>' . _t( 'There were errors during import:' ) . '</p>';
					
					echo '<ul>';
					foreach ( $errors as $error ) {
						echo '<li>' . $error . '</li>';
					}
					echo '</ul>';
				}
			
			}
		}
		else {
			EventLog::log( sprintf( _t( 'Failed to import from "%s"' ), $db_name ), 'crit' );
			echo '<p>' . _t( 'Failed to connect using the given database connection details.' ) . '</p>';
		}
	}

}