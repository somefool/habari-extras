<?php
/**
 *    ScratchPad
 *    Provides an easy way to store links and quotes for use in your posts
 *
 */

class ScratchPad extends Plugin implements MediaSilo
{
	const SILO_NAME = 'ScratchPad';

	/**
	* Provide plugin info to the system
	*/
	public function info()
	{
		return array(
			'name' => 'ScratchPad',
			'version' => '0.6-0.1.1',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache Software License 2.0',
			'description' => 'Provides an easy way to store links and quotes for use in your posts',
			'copyright' => '2008',
			);
	}

	public function is_auth()
	{
		return true;
	}

// Silo functions

	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*/
	public function silo_info()
	{
		if($this->is_auth()) {
			return array('name' => self::SILO_NAME);
		}
		else {
			return array();
		}
	}

	/**
	* Return contents of silo directories. This is what the publish page uses.
	*
	*/
	public function silo_dir($path)
	{
		$section = strtok($path, '/');
		$results = array();
		$user = User::identify();

		// If the root is being requested, return all scratchpads for this user
		if ( $section == '' ) {
			$scratchpads = DB::get_results( 'SELECT id, name, slug FROM {scratchpads} WHERE user_id = ?', array($user->id) );
			foreach ( $scratchpads as $scratchpad ) {
				$results[] = new MediaAsset(
					self::SILO_NAME . '/' . $scratchpad->slug,
					true,
					array('title' => ucfirst($scratchpad->name))
				);
			}
		}
		else {
			$scratchpad = DB::get_row( 'SELECT id, slug, template FROM {scratchpads} WHERE slug = ? AND user_id = ?', array( $section, $user->id ) );
			// Get a template, either this scratchpad's or the default.
			$template = '';
			if ( !isset($scratchpad->template) ) {
				$template = DB::get_value( 'SELECT template FROM {scratchpads} WHERE slug = ? AND user_id = ?', array( 'default', $user->id ) );
			}
			else {
				$template = $scratchpad->template;
			}
			$entries = DB::get_results( 'SELECT * FROM {scratchpad_entries} WHERE scratchpad_id = ?', array( $scratchpad->id ) );
			foreach ( $entries as $entry ) {
				$this->entry = $entry;
				// Insert relevant parts in the template
				$output = preg_replace_callback('%\{\$(.+?)\}%', array(&$this, 'replace_parts'), $template);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/' . $scratchpad->slug . '/' . $entry->slug,
					false,
					array(
						'filetype'=>'scratchpad',
						'title' => $entry->title,
						'url' => $entry->url,
						'content' => $entry->content,
						'output' => $output
					)
				);
			}
		}

		return $results;
	}

	public function silo_get( $path, $qualities = null ) {}

	public function silo_put( $path, $filedata ) {}

	public function silo_delete( $path ) {}

	public function silo_highlights() {}

	public function silo_permissions( $path ) {}

	// Plugin functions

	/**
	 * Plugin init action, executed when plugins are initialized.
	 */
	public function action_init()
	{
		// Register database tables to hold ScratchPads and ScratchPads entries information.
		DB::register_table('scratchpads');
		DB::register_table('scratchpad_entries');
		// Make sure this user has a default scratchpad
		if ( !DB::get_value( 'SELECT id FROM {scratchpads} WHERE slug = ?', array( 'default' ) ) ) {
			// TODO What if the user isn't logged in ?
			// Perhaps we should be checking this somewhere else ?
			$user = User::identify();
			$template = "<blockquote cite=\"{\$url}\">{\$content}</blockquote>\n<a href=\"{\$url}\" title=\"{\$title}\">{\$title}</a>\n";
			DB::query('INSERT INTO {scratchpads} (user_id, name, slug, template) VALUES (?, ?, ?, ?)', array( $user->id, 'default', 'default', $template) );
		}
	}

	/**
	 * Plugin plugin_activation action, executed when any plugin is activated
	 * @param string $file The filename of the plugin that was activated.
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath($file) == __FILE__ ) {
			// Register a default event log type for this plugin
			// TODO For some reason the table doesn't get created if I register the log type
			// EventLog::register_type();  // Defaults to ( "default", __CLASS__ )

			// 'plugin_activation' hook is called without first calling 'init'
			// Register database tables to hold ScratchPads and ScratchPads entries information.
			DB::register_table('scratchpads');
			DB::register_table('scratchpad_entries');
			// Create the database table, or upgrade it
			DB::dbdelta( $this->get_db_schema() );
			Session::notice( 'Updated the ScratchPad database schema.' );
			// Log a table creation event
			EventLog::log('Installed ScratchPad database schema.');
		}
	}

	/**
	 * Plugin plugin_deactivation action, executes when any plugin is deactivated.
	 * @param string $plugin_id The filename of the plguin that was deactivated.
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( realpath($file) == __FILE__ ) {
			DB::register_table('scratchpads');
			DB::register_table('scratchpad_entries');
			// Drop the database tables
			DB::query( 'DROP TABLE IF EXISTS {scratchpads};');
			DB::query( 'DROP TABLE IF EXISTS {scratchpad_entries};');
			// Log a dropped table event
			EventLog::log('Removed ScratchPad data.');
		}
	}

	public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule(array(
			'name' => 'scratchpad_script',
			'parse_regex' => '%scripts/scratchpad.js$%i',
			'build_str' =>  'scripts/scratchpad.js',
			'handler' => 'UserThemeHandler',
			'action' => 'display_js',
			'is_active' => 1,
		));
		$rules[] = new RewriteRule(array(
			'name' => 'scratchpad',
			'parse_regex' => '%scratchpad$%i',
			'build_str' =>  'scratchpad',
			'handler' => 'UserThemeHandler',
			'action' => 'scratchpad',
			'is_active' => 1,
		));
		return $rules;
	}

	public function action_handler_display_js($handler_vars)
	{
		ob_clean();
		header( 'content-type: application/x-javascript' );
		// TODO It would be nicer if this could be straight JS.
		// The only PHP required is Habari's URL.
		// Can we create a JS object in the bookmarklet and refer to that ?
		include( 'scratchpad.js.php' );
		exit;
	}

	public function action_handler_scratchpad($handler_vars)
	{
		$user = User::identify();
		if ( !$user->loggedin ) {
			// TODO We should allow the user to log in somehow
			$response = "User not logged in.";
		}
		else {
			// Create a form to enter the ScratchPad entry information
			$theme = Themes::create();
			$this->add_template( 'scratchpad', dirname(__FILE__) . '/scratchpad.php' );

			$form = new FormUI('scratchpad');

			// Create the Title field
			$form->append('text', 'title', 'null:null', _t('Title'));
			$form->title->tabindex = 1;
			$form->title->value = $handler_vars['title'];

			// Create the Content field
			$form->append('textarea', 'content', 'null:null', _t('Content'));
			$form->content->tabindex = 2;
			if ( isset($handler_vars['selection']) ) {
				$form->content->value = $handler_vars['selection'];
			}

			// Add the existing scratchpads
			$scratchpads = array();
			$results = DB::get_results( 'SELECT id, name FROM {scratchpads} WHERE user_id = ?', array($user->id) );
			foreach ($results as $result) {
				$scratchpads[$result->id] = $result->name;
			}
			$form->append('select', 'scratchpads', 'null:null', 'ScratchPads', $scratchpads );
			$form->scratchpads->tabindex = 3;

			// Create a field for adding a new scratchpad
			$form->append('text', 'scratchpad', 'null:null', _t('New ScratchPad'));
			$form->scratchpad->tabindex = 4;

			// Add the URL as a hidden control
			$form->append('hidden', 'url', 'null:null');
			$form->url->value = $handler_vars['url'];

			// Create the Save button
			$form->append('submit', 'save', 'Save');

			// Add a callback to save the form data
			$form->on_success( array($this, 'insert_scratchpad') );

			// Put the form into the theme
			$theme->form = $form;

			$theme->display( 'scratchpad' );
		}
	}

	public function insert_scratchpad($form)
	{
		$response = '';
		$user = User::identify();
		if ( !$user->loggedin ) {
			// TODO should we allow the user to log in here ? They're sending data directly to where they shouldn't be. Maybe their session timed out ?
			$response = "User not logged in.";
		}
		else {
			// Insert the data in the appropriate scratchpad
			// New scratchpad else chosen scratchpad, whose default is 'default'
			if ( $form->scratchpad->value != '' ) {
				$scratchpad = $form->scratchpad->value;
				$slug = Utils::slugify($scratchpad);
				// This is a free text name. We need to get a scratchpad id.
				// Check if the new ScratchPad name already exists
				$scratchpad_id = DB::get_value( 'SELECT id FROM {scratchpads} WHERE slug = ?', array( $slug ) );
				// if not, create one and get the id
				if ( !$scratchpad_id ) {
					DB::query( 'INSERT INTO {scratchpads} (user_id, name, slug) VALUES (?, ?, ?)', array( $user->id, $scratchpad, $slug ) );
					$scratchpad_id = DB::get_value( 'SELECT id FROM {scratchpads} WHERE slug = ?', array( $slug ) );
				}
			}
			else {
				$scratchpad_id = $form->scratchpads->value;
			}
			// There might not have been any selection
			$content = $form->content->value != '' ? $form->content->value : '';
			$title = $form->title->value;
			$url = $form->url->value;
			// TODO What to do if the user has an entry in the scratchpad with the same title ?
			DB::query('INSERT INTO {scratchpad_entries} (scratchpad_id, title, slug, url, content) VALUES (?, ?, ?, ?, ?)', array( $scratchpad_id, $title, Utils::slugify($title), $url, $content) );
			$response = 'ScratchPad data inserted';
		}

		return $response;
	}

	/**
	* Create the bookmarklet to send entries to your ScratchPads
	*
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	private function get_bookmarklet()
	{
		$scripts_url = Site::get_url('scripts');
		$link_name = Options::get('title');
		$bookmarklet = "
		<p>Bookmark this link to leave the page when quoting:
		<a href=\"javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('src','{$scripts_url}/scratchpad.js');document.body.appendChild(e)})())\">{$link_name} ScratchPad</a>
		</p>";
		return $bookmarklet;
	}

 /**
	* Add actions to the plugin page for this plugin
	* The authorization should probably be done per-user.
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ( $plugin_id == $this->plugin_id() )
		{
			$actions[] = _t('Manage ScratchPads');
		}
		return $actions;
	}

	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ( $plugin_id == $this->plugin_id() )
		{
			switch ($action)
			{
				case _t('Manage ScratchPads'):
					echo $this->get_bookmarklet();
					$form = new FormUI(strtolower(get_class($this)));
					// TODO Some ScratchPad Management logic
					$form->append( 'submit', 'save', _t( 'Save' ) );
					$form->out();
				break;
			}
		}
	}

	/**
	* Injects Javascript responsible for displaying and inserting media assets in the publish page
	*
	*/
	public function action_admin_footer( $theme )
	{
		if ( $theme->admin_page == 'publish' ) {
			// TODO Preview needs work, doesn't show content
			echo <<< SCRATCHPAD
			<script type="text/javascript">
				habari.media.output.scratchpad = {display: function(fileindex, fileobj) {
					habari.editor.insertSelection(fileobj.output);
				}}
				habari.media.preview.scratchpad = function(fileindex, fileobj) {
					var stats = '';
					return '<div class="mediatitle">' + fileobj.title + '</div><div>' + fileobj.content + '</div>';
				}
			</script>
SCRATCHPAD;
		}
	}

	/**
	* Returns the replacement results for a preg_replace_callback
	*
	* @param array $parts The matches form the expression
	* @return The scratchpad entry property with that name
	* @see PublishQuote::add_quote_to_post()
	*/
	public function replace_parts($parts)
	{
		return $this->entry->$parts[1];
	}

	/**
	* Registers this plugin for updates against the beacon
	*/
	public function action_update_check()
	{
		Update::add('ScratchPad', '7064f73c-011d-42f2-955d-5ebb822b7d00', $this->info->version);
	}
}
?>
