<?php
/**
 * Movable Type Importer
 * Import Movable Type Database
 *
 * @package mtimport
 * @version $Id$
 * @author ayunyan <ayu@commun.jp>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link http://habariproject.org/
 *
 * Tested with Movable Type Ver 4.12 (MySQL)
 */
class MTImport extends Plugin implements Importer
{
	private $supported_importers;
	private $import_batch = 100;

	/**
	 * plugin information
	 *
	 * @access public
	 * @retrun void
	 */
	public function info()
	{
		return array(
			'name' => 'Movable Type Importer',
			'version' => '0.01-alpha',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Import Movable Type Database',
			'guid' => 'd77b95b2-769e-11dd-90de-001b210f913f'
			);
	}

	/**
	 * action: init
	 *
	 * @access public
	 * @return void
	 */
	public function action_init()
	{
		$this->load_text_domain('mtimport');

		$this->supported_importers = array();
		$this->supported_importers['mysql'] = _t('Movable Type Database (MySQL)', 'mtimport');
		$this->supported_importers['backup'] = _t('Movable Type Backup File', 'mtimport');
		$this->supported_importers['export'] = _t('Movable Type Export File', 'mtimport');
	}

	/**
	 * filter: import_names
	 *
	 * @access public
	 * @param array $import_names
	 * @return array
	 */
	public function filter_import_names($import_names)
	{
		return array_merge($import_names, $this->supported_importers);
	}

	/**
	 * action: update_check
	 *
	 * @access public
	 * @return void
	 */
	public function action_update_check()
	{
		Update::add('Movable Type Importer', $this->info->guid, $this->info->version);
	}

	/**
	 * Plugin filter that supplies the UI for the MT importer
	 *
	 * @access public
	 * @param string $stageoutput The output stage UI
	 * @param string $import_name The name of the selected importer
	 * @param string $stage The stage of the import in progress
	 * @param string $step The step of the stage in progress
	 * @return mixed output for this stage of the import
	 */
	public function filter_import_stage($stageoutput, $import_name, $stage, $step)
	{
		if (($importer = array_search($import_name, $this->supported_importers)) === false) {
			return $stageoutput;
		}


		if (empty($stage)) $stage = 1;

		$stage_method = $importer . '_stage_' . $stage;
		if (!method_exists($this, $stage_method)) {
			return sprintf(_t('Unknown Stage: %s', 'mtimport'), $stage);
		}

		$output = $this->$stage_method(array());

		return $output;
	}

	/**
	 * first stage of Movable Type (MySQL) import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function mysql_stage_1($inputs)
	{
		$default_values= array(
			'db_name' => '',
			'db_host' => 'localhost',
			'db_user' => '',
			'db_pass' => '',
			'db_prefix' => 'mt_',
			'warning' => ''
		 );
		$inputs= array_merge( $default_values, $inputs );
		extract( $inputs );

		ob_start();
?>
<p><?php echo _t('Habari will attempt to import from a Movable Type Database.', 'mtimport'); ?></p>
<?php if (!empty($warning)): ?>
<p class="warning"><?php echo htmlspecialchars($warning); ?></p>
<?php endif; ?>
<p><?php echo _t('Please provide the connection details for an existing Movable Type database:', 'mtimport'); ?></p>
<table>
	<tr><td><?php echo _t('Database Name', 'mtimport'); ?></td><td><input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>"></td></tr>
	<tr><td><?php echo _t('Database Host', 'mtimport'); ?></td><td><input type="text" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>"></td></tr>
	<tr><td><?php echo _t('Database User', 'mtimport'); ?></td><td><input type="text" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>"></td></tr>
	<tr><td><?php echo _t('Database Password', 'mtimport'); ?></td><td><input type="password" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>"></td></tr>
	<tr><td><?php echo _t('Table Prefix', 'mtimport'); ?></td><td><input type="text" name="db_prefix" value="<?php echo htmlspecialchars($db_prefix); ?>"></td></tr>
</table>
<input type="hidden" name="stage" value="2">
<p class="submit"><input type="submit" name="import" value="<?php echo _t('Next', 'mtimport'); ?>" /></p>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * second stage of Movable Type (MySQL) import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function mysql_stage_2($inputs)
	{
		$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix');
		$inputs= array_intersect_key($_POST, array_flip($valid_fields));
		extract($inputs);

		if(($mtdb = $this->mt_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix)) === false) {
			$inputs['warning']= _t('Could not connect to the Movable Type database using the values supplied. Please correct them and try again.', 'mtimport');
			return $this->stage_1($inputs);
		}

		$blogs = $mtdb->get_results("SELECT blog_id, blog_name FROM {$db_prefix}blog;");
		ob_start();
?>
<p><?php echo _t('Please specify Blog which imports:', 'mtimport'); ?></p>
<table>
	<tr><td><?php echo _t('Import Blog', 'mtimport'); ?></td><td>
	<select name="blog_id" size="1">
	<?php while (list(, $blog) = @each($blogs)): ?>
	  <option value="<?php echo $blog->blog_id; ?>"><?php echo htmlspecialchars($blog->blog_name); ?></option>
	<?php endwhile; ?>
	</select>
	</td></tr>
</table>
<input type="hidden" name="stage" value="3">
<?php while (list($key, $value) = @each($inputs)): ?>
<input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>" />
<?php endwhile; ?>
<p class="submit"><input type="submit" name="import" value="<?php echo _t('Import', 'mtimport'); ?>" /></p>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * third stage of Movable Type (MySQL) import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function mysql_stage_3($inputs)
	{
		$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix', 'blog_id');
		$inputs= array_intersect_key($_POST, array_flip($valid_fields));
		extract($inputs);

		if(($mtdb = $this->mt_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix)) === false) {
			$inputs['warning']= _t('Could not connect to the Movable Type database using the values supplied. Please correct them and try again.', 'mtimport');
			return $this->stage_1($inputs);
		}

		$ajax_url= URL::get('auth_ajax', array('context' => 'mt_mysql_import_users'));
		EventLog::log(sprintf(_t('Starting import from "%s"'), $db_name));
		Options::set('import_errors', array());

		ob_start();
?>
<p>Import In Progress</p>
<div id="import_progress">Starting Import...</div>
<script type="text/javascript">
// A lot of ajax stuff goes here.
$(document).ready(function(){
	$('#import_progress').load(
		"<?php echo $ajax_url; ?>",
		{
		db_host: "<?php echo htmlspecialchars($db_host); ?>",
		db_name: "<?php echo htmlspecialchars($db_name); ?>",
		db_user: "<?php echo htmlspecialchars($db_user); ?>",
		db_pass: "<?php echo htmlspecialchars($db_pass); ?>",
		db_prefix: "<?php echo htmlspecialchars($db_prefix); ?>",
		blog_id: "<?php echo htmlspecialchars($blog_id); ?>",
		postindex: 0
		}
	 );
});
</script>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * The plugin sink for the auth_ajax_mt_import_users hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @access public
	 * @param mixed $handler
	 * @return
	 */
	public function action_auth_ajax_mt_mysql_import_users($handler)
	{
		$valid_fields = array('db_name','db_host','db_user','db_pass','db_prefix','userindex', 'blog_id');
		$inputs = array_intersect_key( $_POST, array_flip( $valid_fields));
		extract( $inputs );

		$mtdb = $this->mt_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix);
		if(!$mtdb ) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
			return;
		}

		$mt_users = $mtdb->get_results("SELECT author_id AS mt_id, author_name AS username, author_email AS email FROM {$db_prefix}author;", array(), 'User');
		$usercount = 0;
		echo _t('<p>Importing users...</p>');

		@reset($mt_users);
		while (list(, $user) = @each($mt_users)) {
			$habari_user = User::get_by_name($user->username);
			// If username exists
			if(!($habari_user instanceof User)) {
				try {
					$user->info->mt_id = $user->mt_id;
					// This should probably remain commented until we implement ACL more,
					// or any imported user will be able to log in and edit stuff
					//$user->password = '{MD5}' . $user->password;
					$user->exclude_fields(array('mt_id'));
					$user->insert();
					$usercount++;
				} catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($user, $e), 1));
					$errors = Options::get('import_errors');
					$errors[] = $user->username . ' : ' . $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}
		}

		$ajax_url= URL::get('auth_ajax', array('context' => 'mt_mysql_import_posts'));
?>
<script type="text/javascript">
// A lot of ajax stuff goes here.
$( document ).ready( function(){
	$( '#import_progress' ).load(
		"<?php echo $ajax_url; ?>",
		{
		db_host: "<?php echo htmlspecialchars($db_host); ?>",
		db_name: "<?php echo htmlspecialchars($db_name); ?>",
		db_user: "<?php echo htmlspecialchars($db_user); ?>",
		db_pass: "<?php echo htmlspecialchars($db_pass); ?>",
		db_prefix: "<?php echo htmlspecialchars($db_prefix); ?>",
		blog_id: "<?php echo htmlspecialchars($blog_id); ?>",
		postindex: 0
		}
	 );
});
</script>
<?php
	}

	/**
	 * The plugin sink for the auth_ajax_mt_import_posts hook.
	 * Responds via authenticated ajax to requests for post importing.
	 *
	 * @access public
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_mt_mysql_import_posts($handler)
	{
		$valid_fields= array('db_name','db_host','db_user','db_pass','db_prefix','postindex', 'blog_id');
		$inputs= array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract($inputs);

		$mtdb= $this->mt_connect($db_host, $db_name, $db_user, $db_pass, $db_prefix);
		if(!$mtdb) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			echo '<p>'._t( 'The database connection details have failed to connect.' ).'</p>';
			return;
		}

		$postcount = $mtdb->get_value("SELECT count(entry_id) FROM {$db_prefix}entry WHERE entry_blog_id = '{$blog_id}';");
		$min= $postindex * $this->import_batch + ($postindex == 0 ? 0 : 1);
		$max= min( ( $postindex + 1 ) * $this->import_batch, $postcount );

		$user_map = array();
		$userinfo= DB::get_results('SELECT user_id, value FROM ' . DB::table('userinfo') . ' WHERE name = "mt_id";');
		@reset($userinfo);
		while (list(, $info) = @each($userinfo)) {
			$user_map[$info->value] = $info->user_id;
		}

		echo sprintf(_t('<p>Importing posts %d-%d of %d.</p>'), $min, $max, $postcount);
		flush();

		$posts = $mtdb->get_results("SELECT
			entry_id AS id,
			entry_author_id AS user_id,
			entry_authored_on AS pubdate,
			entry_modified_on AS updated,
			entry_title AS title,
			entry_atom_id AS guid,
			entry_basename AS slug,
			entry_text,
			entry_text_more,
			entry_class,
			entry_status,
			{$db_prefix}category.category_label
			FROM {$db_prefix}entry
			LEFT JOIN {$db_prefix}category ON entry_category_id = category_id
			WHERE entry_blog_id = '{$blog_id}'
			ORDER BY id DESC LIMIT {$min}," . $this->import_batch . ';',
			array(), 'Post');

		$post_map = DB::get_column("SELECT value FROM " . DB::table('postinfo') . " WHERE name='mt_id';");

		@reset($posts);
		while (list(, $post) = @each($posts)) {
			// already exists skipped
			if(in_array($post->id, $post_map)) continue;

			$tags = $mtdb->get_column("SELECT tag_name FROM {$db_prefix}objecttag
				LEFT JOIN {$db_prefix}tag ON objecttag_tag_id = tag_id
				WHERE objecttag_object_datasource = 'entry' AND objecttag_object_id = {$post->id};");

			$post_array= $post->to_array();
			$tags[] = $post_array['category_label'];
			unset($post_array['category_label']);
			$tags = implode(',', $tags);

			if ($post_array['entry_status'] == 2) {
				$post_array['status'] = Post::status('published' );
			} else {
				$post_array['status'] = Post::status('draft');
			}
			unset($post_array['entry_status']);

			switch($post_array['entry_class']) {
			case 'entry':
				$post_array['content_type'] = Post::type( 'entry' );
				break;
			default:
				// We're not inserting MT's media records.  That would be silly.
				continue;
			}
			unset($post_array['entry_class']);

			$post_array['content'] = $post_array['entry_text'] . $post_array['entry_text_more'];
			unset($post_array['entry_text']);
			unset($post_array['entry_text_more']);

			$p= new Post($post_array);
			$p->slug = $post->slug;
			$p->user_id = $user_map[$p->user_id];
			$p->guid = $p->guid; // Looks fishy, but actually causes the guid to be set.
			$p->tags = $tags;
			$p->info->mt_id= $post_array['id'];  // Store the MT post id in the post_info table for later

			try {
				$p->insert();
			} catch( Exception $e ) {
				EventLog::log($e->getMessage(), 'err', null, null, print_r(array($p, $e), 1));
				$errors = Options::get('import_errors');
				$errors[] = $p->title . ' : ' . $e->getMessage();
				Options::set('import_errors', $errors);
			}
		}

		if($max < $postcount) {
			$ajax_url= URL::get('auth_ajax', array('context' => 'mt_mysql_import_posts'));
			$postindex++;
		} else {
			$ajax_url= URL::get('auth_ajax', array('context' => 'mt_mysql_import_comments'));
		}
?>
<script type="text/javascript">
$('#import_progress').load(
	"<?php echo $ajax_url; ?>",
	{
	db_host: "<?php echo htmlspecialchars($db_host); ?>",
	db_name: "<?php echo htmlspecialchars($db_name); ?>",
	db_user: "<?php echo htmlspecialchars($db_user); ?>",
	db_pass: "<?php echo htmlspecialchars($db_pass); ?>",
	db_prefix: "<?php echo htmlspecialchars($db_prefix); ?>",
	blog_id: "<?php echo htmlspecialchars($blog_id); ?>",
	postindex: <?php echo $postindex; ?>,
	commentindex: 0
	}
);
</script>
<?php
	}

	/**
	 * The plugin sink for the auth_ajax_mt_import_comments hook.
	 * Responds via authenticated ajax to requests for comment importing.
	 *
	 * @access public
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_mt_mysql_import_comments($handler)
	{
		$valid_fields = array( 'db_name','db_host','db_user','db_pass','db_prefix', 'blog_id', 'commentindex');
		$inputs= array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract( $inputs );
		$mtdb= $this->mt_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		if(!$mtdb) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
			return;
		}

		$commentcount= $mtdb->get_value("SELECT count(comment_id) FROM {$db_prefix}comment WHERE comment_blog_id = '{$blog_id}';");
		$min = $commentindex * $this->import_batch + 1;
		$max = min(($commentindex + 1) * $this->import_batch, $commentcount);

		echo sprintf(_t('<p>Importing comments %d-%d of %d.</p>'), $min, $max, $commentcount);

		$post_info= DB::get_results("SELECT post_id, value FROM " . DB::table('postinfo') . " WHERE name= 'mt_id';");
		@reset($post_info);
		while (list(, $info) = @each($post_info)) {
			$post_map[$info->value]= $info->post_id;
		}

		$comments = $mtdb->get_results("SELECT
			comment_author AS name,
			comment_email AS email,
			comment_url AS url,
			INET_ATON(comment_ip) AS ip,
			comment_text AS content,
			comment_created_on AS date,
			comment_visible AS status,
			comment_entry_id AS mt_post_id,
			comment_id,
			comment_junk_status
			FROM {$db_prefix}comment
			WHERE comment_blog_id = '{$blog_id}'
			LIMIT {$min}," . $this->import_batch, array(), 'Comment');

		$comment_map = DB::get_column("SELECT value FROM " . DB::table('commentinfo') . " WHERE name='mt_comment_id';");

		@reset($comments);
		while (list(, $comment) = @each($comments)) {
			// already exists skipped
			if(in_array($comment->comment_id, $comment_map)) continue;

			$comment->type = Comment::COMMENT;

			$carray = $comment->to_array();
			if ($carray['ip'] == '') {
				$carray['ip'] = 0;
			}

			if ($carray['status'] == 1) {
				$carray['status'] = Comment::STATUS_APPROVED;
			} elseif ($carray['comment_junk_status'] != 0) {
				$carray['status'] = Comment::STATUS_SPAM;
			} else {
				$carray['status'] = Comment::STATUS_UNAPPROVED;
			}
			unset($carray['comment_junk_status']);


			if (!isset($post_map[$carray['mt_post_id']] ) ) {
				Utils::debug( $carray );
			} else {
				$carray['post_id']= $post_map[$carray['mt_post_id']];
				unset( $carray['mt_post_id'] );

				$comment_id = $carray['comment_id'];
				unset($carray['comment_id']);

				$c = new Comment( $carray );
				$c->info->mt_comment_id = $comment_id;
				try{
					$c->insert();
				} catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($c, $e), 1));
					$errors = Options::get('import_errors');
					$errors[] = $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}
		}

		if( $max < $commentcount ) {
			$ajax_url= URL::get('auth_ajax', array( 'context' => 'mt_mysql_import_comments'));
			$commentindex++;
		} else {
			$ajax_url= URL::get('auth_ajax', array('context' => 'mt_mysql_import_trackbacks'));
		}

?>
<script type="text/javascript">
$( '#import_progress' ).load(
	"<?php echo $ajax_url; ?>",
	{
	db_host: "<?php echo htmlspecialchars($db_host); ?>",
	db_name: "<?php echo htmlspecialchars($db_name); ?>",
	db_user: "<?php echo htmlspecialchars($db_user); ?>",
	db_pass: "<?php echo htmlspecialchars($db_pass); ?>",
	db_prefix: "<?php echo htmlspecialchars($db_prefix); ?>",
	blog_id: "<?php echo htmlspecialchars($blog_id); ?>",
	commentindex: <?php echo $commentindex; ?>,
	trackbackindex: 0
	}
);
</script>
<?php
	}

	/**
	 * The plugin sink for the auth_ajax_mt_import_trackbacks hook.
	 * Responds via authenticated ajax to requests for comment importing.
	 *
	 * @access public
	 * @param AjaxHandler $handler The handler that handled the request, contains $_POST info
	 */
	public function action_auth_ajax_mt_mysql_import_trackbacks($handler)
	{
		$valid_fields = array( 'db_name','db_host','db_user','db_pass','db_prefix', 'blog_id', 'trackbackindex');
		$inputs= array_intersect_key( $_POST, array_flip( $valid_fields ) );
		extract( $inputs );
		$mtdb= $this->mt_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix );
		if(!$mtdb) {
			EventLog::log(sprintf(_t('Failed to import from "%s"'), $db_name), 'crit');
			echo '<p>'._t( 'Failed to connect using the given database connection details.' ).'</p>';
			return;
		}

		$trackbackcount= $mtdb->get_value("SELECT count(trackback_id) FROM {$db_prefix}trackback WHERE trackback_blog_id = '{$blog_id}';");
		$min = $trackbackindex * $this->import_batch + 1;
		$max = min( ( $trackbackindex + 1 ) * $this->import_batch, $trackbackcount );

		echo sprintf(_t('<p>Importing trackbacks %d-%d of %d.</p>'), $min, $max, $trackbackcount);

		$post_info= DB::get_results("SELECT post_id, value FROM " . DB::table('postinfo') . " WHERE name= 'mt_id';");
		@reset($post_info);
		while (list(, $info) = @each($post_info)) {
			$post_map[$info->value]= $info->post_id;
		}

		$trackbacks = $mtdb->get_results("SELECT
			trackback_title AS name,
			trackback_url AS url,
			trackback_description AS content,
			trackback_created_on AS date,
			trackback_entry_id AS mt_post_id,
			trackback_id,
			trackback_is_disabled
			FROM {$db_prefix}trackback
			WHERE trackback_blog_id = '{$blog_id}'
			LIMIT {$min}," . $this->import_batch, array(), 'Comment');

		$comment_map = DB::get_column("SELECT value FROM " . DB::table('commentinfo') . " WHERE name='mt_trackback_id';");

		@reset($trackbacks);
		while (list(, $trackback) = @each($trackback)) {
			// already exists skipped
			if(in_array($trackback->trackback_id, $comment_map)) continue;

			$trackback->type= Comment::TRACKBACK;

			$carray = $trackback->to_array();
			$carray['ip']= 0;

			if ($carray['trackback_is_disabled'] == 0) {
				$carray['status']= Comment::STATUS_APPROVED;
			} else {
				$carray['status']= Comment::STATUS_UNAPPROVED;
			}
			unset($carray['trackback_is_disabled']);

			if (!isset($post_map[$carray['mt_post_id']] ) ) {
				Utils::debug( $carray );
			} else {
				$carray['post_id']= $post_map[$carray['wp_post_id']];
				unset( $carray['mt_post_id'] );

				$trackback_id = $carray['trackback_id'];
				unset($carray['trackback_id']);

				$c = new Comment( $carray );
				$c->info->mt_trackback_id = $trackback_id;
				try{
					$c->insert();
				} catch( Exception $e ) {
					EventLog::log($e->getMessage(), 'err', null, null, print_r(array($c, $e), 1));
					$errors = Options::get('import_errors');
					$errors[] = $e->getMessage();
					Options::set('import_errors', $errors);
				}
			}
		}

		if($max < $trackbackcount) {
			$ajax_url= URL::get('auth_ajax', array( 'context' => 'mt_mysql_import_trackbacks'));
			$trackbackindex++;
		} else {
			EventLog::log('Import complete from "'. $db_name .'"');
			echo '<p>' . _t( 'Import is complete.' ) . '</p>';

			$errors = Options::get('import_errors');
			if(count($errors) > 0 ) {
				echo '<p>' . _t('There were errors during import:') . '</p>';
				echo '<ul>';
				foreach($errors as $error) {
					echo '<li>' . $error . '</li>';
				}
				echo '</ul>';
			}
			return;
		}

?>
<script type="text/javascript">
$( '#import_progress' ).load(
	"<?php echo $ajax_url; ?>",
	{
	db_host: "<?php echo htmlspecialchars($db_host); ?>",
	db_name: "<?php echo htmlspecialchars($db_name); ?>",
	db_user: "<?php echo htmlspecialchars($db_user); ?>",
	db_pass: "<?php echo htmlspecialchars($db_pass); ?>",
	db_prefix: "<?php echo htmlspecialchars($db_prefix); ?>",
	blog_id: "<?php echo htmlspecialchars($blog_id); ?>",
	trackbackindex: <?php echo $trackbackindex; ?>
	}
);
</script>
<?php
	}

	/**
	 * first stage of Movable Type Backup File import process
	 *
	 * @access private
	 * @return string The UI for the first stage of the import process
	 */
	private function backup_stage_1($inputs)
	{
		$default_values= array(
			'db_name' => '',
			'db_host' => 'localhost',
			'db_user' => '',
			'db_pass' => '',
			'db_prefix' => 'mt_',
			'warning' => ''
		 );
		$inputs= array_merge( $default_values, $inputs );
		extract($inputs);

		ob_start();
?>
<p><?php echo _t('Habari will attempt to import from a Movable Type Backup File.', 'mtimport'); ?></p>
<?php if (!empty($warning)): ?>
<p class="warning"><?php echo htmlspecialchars($warning); ?></p>
<?php endif; ?>
<p><?php echo _t('Please provide the connection details for an existing Movable Type database:', 'mtimport'); ?></p>
<table>
	<tr><td><?php echo _t('Backup File', 'mtimport'); ?></td><td><input type="text" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>"></td></tr>
</table>
<input type="hidden" name="stage" value="2">
<p class="submit"><input type="submit" name="import" value="<?php echo _t('Next', 'mtimport'); ?>" /></p>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Attempt to connect to the Movable Type database
	 *
	 * @access private
	 * @param string $db_host The hostname of the MT database
	 * @param string $db_name The name of the MT database
	 * @param string $db_user The user of the MT database
	 * @param string $db_pass The user's password for the MT database
	 * @param string $db_prefix The table prefix for the MT instance in the database
	 * @return mixed false on failure, DatabseConnection on success
	 */
	private function mt_connect( $db_host, $db_name, $db_user, $db_pass, $db_prefix )
	{
		// Connect to the database or return false
		try {
			$mtdb= new DatabaseConnection();
			$mtdb->connect( "mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass, $db_prefix );
			return $mtdb;
		}
		catch( Exception $e ) {
			return false;
		}
	}
}
?>