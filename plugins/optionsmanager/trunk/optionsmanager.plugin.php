<?php
/**
 * Options Manager Plugin
 *
 * Lets you manage all Habari options. This includes viewing, editing and
 * deleting.
 *
 * Warning: YOU CAN SERIOUSLY DAMAGE YOUR HABARI SETUP!
 * Warning: USE YOUR POWERS WISELY!
 *
 * So be sure you know what you're doing, especially when dealing with
 * core options. This plugin only guesses if a plugin is from core or
 * not, so also keep that in mind. In other words, this plugin may be
 * lying to you, and accidentally allow you to do very bad things.
 *
 * Thanks to the CronManager plugin for portions of this code base.
 *
 * @todo allow serialized (type=1) options to be viewed in a nice way
 * @todo allow serialized (type=1) options to be edited in a nice way
 * @todo better deal with log/session notices
 * @todo clean up hacked code, and find/fix non-habari coding standards
 * @todo determine proper _t() usage, and start passing domain one day
 * @todo add "import all default options, and/or delete the rest" feature
 * @todo convince a Habari veteran to review this questionable code
 *
 * @version $Id$
 **/

class OptionsManager extends Plugin
{
	public $class_name          = '';
	public $opts_core           = array();
	public $opts_local          = array();
	public $opts_count_total    = 0;
	public $opts_count_inactive = 0;

	/**
	 *  Action: sets up a get/post alias
	 *
	 * @return array the aliases
	 */
	public function alias()
	{
		return array(
			'action_admin_theme_get_options_edit' => 'action_admin_theme_post_options_edit',
		);
	}

	/**
	 *  Action: Plugin Configure
	 *
	 * Sets up the plugin actions
	 *
	 * - Creates 'Configure' option to configure the plugin
	 * - Creates 'View Options' tab, to view options
	 *
	 * @return array the actions
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'View Options' );
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}

	
	/**
	 *  Action: plugin user interface
	 *
	 * Sets up main global user interface for this plugin
	 *
	 * - Creates 'Configure' option to configure the plugin
	 * - Creates 'View Options' tab, to view options
	 *
	 * @return void
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id === $this->plugin_id() ) {

			switch ( $action ) {
				case _t( 'View Options' ):
					Utils::redirect( URL::get( 'admin', array( 'page'=>'options_view' ) ), TRUE );
					break;
				case _t( 'Configure' ):
					$ui = new FormUI($this->class_name . '_configure');

					$ui->append( 'checkbox', 'allow_delete_core',  $this->class_name . '__allow_delete_core',  _t( 'Allow core options to be deleted?' ) );
					$ui->append( 'checkbox', 'allow_delete_other', $this->class_name . '__allow_delete_other', _t( 'Allow non-core options to be deleted?' ) );

					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option( 'success_message', _t( 'Options saved' ) );

					$ui->out();
					break;
			}
		}
	}

	/**
	 *  Action: plugin activation 
	 *
	 * Sets up this plugin when activated, including:
	 *
	 * - Creates the ACL token for admin users
	 * - Creates plugin options with initial values of 0 (off)
	 *
	 * @return void
	 */
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			ACL::create_token( 'manage_options', _t( 'Manage Options' ), 'Options', FALSE );
			$group = UserGroup::get_by_name( 'admin' );
			$group->grant( 'manage_options' );
		}

		Options::set( strtolower(get_class($this)) . '__allow_delete_core',  0);
		Options::set( strtolower(get_class($this)) . '__allow_delete_other', 0);
	}

	/**
	 *  Action: plugin deactivation 
	 *
	 * Cleans up after this plugin when deactivated, including:
	 *
	 * - Removes ACL token
	 * - Removes plugin options
	 *
	 * @return void
	 */
	public function action_plugin_deactivation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			ACL::destroy_token( 'manage_options' );
		}

		Options::delete( $this->class_name . '__allow_delete_core' );
		Options::delete( $this->class_name . '__allow_delete_other' );
	}

	/**
	 * Initializes the action
	 *
	 * Executed upon script execution, and initiates several conditions and variables including:
	 *
	 * - Templates (options_view and options_edit)
	 * - $this->class_name : lowercase
	 * - $this->opts_core  : a hard coded list of habari core options. Likely NOT complete
	 * - $this->opts_local : the options created by this plugin
	 *
	 * @return void
	 */
	public function action_init()
	{
		$this->add_template( 'options_view', dirname( $this->get_file() ) . '/options-view.php' );
		$this->add_template( 'options_edit', dirname( $this->get_file() ) . '/options-edit.php' );
		
		$this->class_name = strtolower(get_class($this));

		// @todo better way to determine this? most likely
		$this->opts_core = array(
			'235381938', 'about', 'active_plugins', 'atom_entries', 'base_url', 'cron_running', 'comments_require_id',
			'dash_available_modules', 'dateformat', 'db_version', 'db_upgrading', 'failed_plugins', 'GUID',
			'import_errors', 'installed', 'import_errors', 'locale', 'log_backtraces', 'next_cron', 'pagination', 'plugins_present',
			'system_locale', 'tagline', 'title', 'timeformat', 'timezone', 'theme_name', 'theme_dir', 'undelete__style',
		);

		// Note: This strips the group prefix, so returns allow_delete_core and allow_delete_other
		$this->opts_local = Options::get_group( $this->class_name );
	}

	/**
	 * Admin token filter
	 *
	 * Filters for the required admin token of manage_options
	 *
	 * @param array $require_any
	 * @param string $page
	 * @return array or null 
	 */
	public function filter_admin_access_tokens( array $require_any, $page )
	{
		switch ($page) {
			case 'options_view':
			case 'options_edit':
				$require_any = array('manage_options', TRUE);
				break;
		}
		return $require_any;
	}

	/**
	 * Admin control for view post
	 *
	 * @param AdminHandler $handler
	 * @param Theme $theme
	 * @return void
	 */
	public function action_admin_theme_post_options_view( AdminHandler $handler, Theme $theme )
	{
		// saving is handled by FormUI
		$this->action_admin_theme_get_options_view($handler, $theme);
		$theme->display('options_view');
	}

	/**
	 * Admin control for view get
	 *
	 * Processes actions (like edit, delete) from the options view
	 *
	 * @param AdminHandler $handler
	 * @param Theme $theme
	 * @return void
	 */
	public function action_admin_theme_get_options_view( AdminHandler $handler, Theme $theme )
	{
		$vars  = $handler->handler_vars->getArrayCopy();
		$theme = $this->create_add_option_form_tab( $theme );

		if( isset($vars['action']) ) {

			if (empty($vars['option_name'])) {
				Session::error(_t( 'There is no option name set, not sure how you got here.') );
				$vars['action'] = 'nothing';
			} else {
				$info = $this->get_option_from_name( $vars['option_name'] );
			
				if ($info === FALSE) {
					Session::error(_t( 'The %s option does not exist, so it cannot be acted upon',  array($vars['option_name'])));
					$vars['action'] = 'nothing';
				}
			}

			switch($vars['action']) {

				case 'delete':

					if ( $this->opts_local['allow_delete_core'] !== '1' && $info['genre'] === 'core' ) {
						Session::notice(_t( '%s is a core option, configuration disallows its deletion',  array($vars['option_name'])));
						break;
					}

					if ( $this->opts_local['allow_delete_other'] !== '1' && $info['genre'] !== 'core' ) {
						Session::notice(_t( '%s is configured to not be deleted',  array($vars['option_name'])), $this->class_name);
						break;
					}

					Options::delete ( $vars['option_name'] );

					$success = Options::get ( $vars['option_name']);
					if ( is_null( $success ) ) {
						Session::notice( _t( 'The %s option was deleted', array($vars['option_name'])), $this->class_name );
						EventLog::log(   _t( 'The %s option was deleted', array($vars['option_name'])), 'notice', 'plugin' );
					} else {
						Session::error(  _t( 'I was unable to delete this option: %s', array($vars['option_name']) ) );
					}
					break;

				case 'delete_group':
					Session::notice( _t( 'Group removal not implemented yet' ) );
					break;

				case 'edit_group':
					Session::notice( _t( 'Group editing not implemented yet' ) );
					break;
				case 'nothing':
					break;
			}
		}

		$theme->options             = $this->get_options_info();
		$theme->opts_local          = $this->opts_local;
		$theme->opts_count_inactive = $this->opts_count_inactive;
		$theme->opts_count_total    = $this->opts_count_total;
	}

	/**
	 * Admin control for edit get
	 *
	 * @param AdminHandler $handler
	 * @param Theme $theme
	 * @return void
	 */
	public function action_admin_theme_get_options_edit( AdminHandler $handler, Theme $theme )
	{
		$option = $this->get_option_from_name( $handler->handler_vars['option_name'] );
		$theme->option = $option;

		$form = new FormUI( 'options_edit' );

		$current_option_name = $form->append( 'hidden', 'current_option_name', 'null:null' );
		$current_option_name->value = $option['name'];

		$name = $form->append(
			'text', 'option_name', 'null:null', _t( 'Name' ), 'optionscontrol_text'
		);
		$name->class = 'item clear';
		$name->value = $option['name'];
		$name->helptext = _t( 'A unique name for option.' );

		$value = $form->append(
			'text', 'option_value', 'null:null', _t( 'Value' ), 'optionscontrol_text'
		);
		$value->class = 'item clear';
		$value->value = $option['value'];
		$value->helptext = _t( 'The value of the new option' );

		$type = $form->append(
			'text', 'option_type', 'null:null', _t( 'Type' ), 'optionscontrol_text'
		);
		$type->class = 'item clear';
		$type->value = $option['type'];
		$type->helptext = _t( 'The type of this option, odds are you do not want to touch this. In fact, changing will not do anything yet.' );

		$form->append( 'submit', 'save', _t( 'Save' ) );
		$form->on_success( array( $this, 'formui_submit_edit' ) );
		$theme->form = $form->get();
	}
	
	/**
	 * Admin control for edit post
	 *
	 * @param AdminHandler $handler
	 * @param Theme $theme
	 * @return void
	 */
	public function action_admin_theme_post_options_edit( AdminHandler $handler, Theme $theme )
	{
		// saving is handled by FormUI
		$option = $this->get_option_from_name( $handler->handler_vars['option_name'] );
		$theme->option = $option;
		$theme->display( 'options_edit' );
	}

	/**
	 * Processes the 'option add' form
	 *
	 * @param FormUI $form
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function formui_submit_add ( FormUI $form )
	{
		if ( strlen( $form->option_name->value  ) < 1 ) {
			Session::error( _t( 'The "option_name" requires a value' ) );
			return FALSE;
		}
		if ( strlen( $form->option_value->value ) < 1 ) {
			Session::error( _t( 'The "option_value" requires a value' ) );
			return FALSE;
		}

		$message_success = 'The "%s" option was added';
		if ( Options::get( $form->option_name->value ) !== NULL ) {
			Session::notice( _t( 'The "%s" option exists already, so I will attempt to overwrite', array( $form->option_name->value ) ) );
			$message_success = 'The "%s" option was updated';
		}

		Options::set ( $form->option_name->value, $form->option_value->value );

		$success = Options::get ( $form->option_name->value );
		if (is_null($success)) {
			Session::error( _t( 'The "%s" option failed to add', array( $form->option_name->value ) ) );
		} else {
			Session::notice( _t( $message_success, array( $form->option_name->value ) ) );
			EventLog::log(   _t( $message_success, array( $form->option_name->value ) ), 'notice', 'plugin' );
		}

		Utils::redirect( URL::get( 'admin', array( 'page'=>'options_view' ) ), TRUE );
		return TRUE;
	}

	/**
	 * Processes the 'option edit' form
	 *
	 * @param FormUI $form
	 * @return bool TRUE on success, FALSE on failure
	 */
	public function formui_submit_edit( FormUI $form )
	{
		if ( strlen( $form->option_name->value  ) < 1 ) {
			Session::error( _t( 'The "option_name" requires a value' ) );
			return FALSE;
		}
		if ( strlen( $form->option_value->value ) < 1 ) {
			Session::error( _t( 'The "option_value" requires a value' ) );
			return FALSE;
		}
		if ( strlen( $form->current_option_name->value ) < 1 ) {
			Session::error( _t( 'The current/old "option_name" is missing' ) );
			return FALSE;
		}
		
		$_opt_curr = $this->get_option_from_name( $form->current_option_name->value );

		Options::set ( $form->option_name->value, $form->option_value->value );

		// @todo okay? what if option type = serialized? research later
		$success = Options::get ( $form->option_name->value );
		if ( is_null( $success ) || $success !== $form->option_value->value ) {
			Session::error( _t( 'The "%s" option failed to edit', array( $form->option_name->value ) ) );
		} else {
			// The name was changed, so delete the old, depending on if we're allowed
			if ($form->current_option_name->value !== $form->option_name->value) {
				
				if ($this->is_option_genre_delete_allowed( $_opt_curr['genre'] ) ) {
					Options::delete($form->current_option_name->value);
					$message = 'The "%s" option name was changed and renamed to "%s"';
				} else {
					$message = 'The "%s" option name could not be renamed, but a new option named "%s" was added';
				}
				Session::notice( _t( $message, array( $form->current_option_name->value, $form->option_name->value ) ) );
				EventLog::log( _t( $message, array( $form->current_option_name->value, $form->option_name->value ) ), 'notice', 'plugin' );
				
			} else {
				Session::notice( _t( 'The "%s" option was edited to %s', array( $form->option_name->value, $form->option_value->value ) ) );
				EventLog::log( _t( 'The %s option was edited', array( $form->option_name->value ) ), 'notice', 'plugin' );
			}
		}
		Utils::redirect( URL::get( 'admin', array( 'page'=>'options_view' )), TRUE );
		return TRUE;
	}

	/**
	 * Creates (attaches) the 'add new option' form/tab
	 * @todo Determine if this is a hack :)
	 *
	 * @param  $theme The theme being attached to
	 * @return Theme The modified Theme object
	 */
	public function create_add_option_form_tab( Theme $theme ) 
	{
		$form = new FormUI('options-view-new');
		$form->set_option( 'form_action', URL::get('admin', 'page=options_view' ) );
		$form->class[] = 'form comment';

		$tabs = $form->append( 'tabs', 'publish_controls' );

		$new = $tabs->append( 'fieldset', 'settings', _t( 'Add a new option' ) );

		$action = $new->append( 'hidden', 'action', 'null:null' );
		$action->value = 'add';

		$name = $new->append( 'text', 'option_name', 'null:null', _t( 'Name'), 'tabcontrol_text' );
		$name->value = '';
		$name->helptext = _t( 'Name of the new option' );

		$value = $new->append( 'text', 'option_value', 'null:null', _t( 'Value' ), 'tabcontrol_text' );
		$value->value = '';
		$value->helptext = _t( 'Value of the new option' );

		$new->append( 'submit', 'save', _t('Save' ) );
		$form->on_success( array( $this, 'formui_submit_add' ) );
		$theme->form = $form->get();
		
		return $theme;
	}

	/**
	 * Gets option information from a name
	 *
	 * @param  $name  The options name (e.g., 'theme_name')
	 * @return mixed  Returns the options information (array) on success, or FALSE on error
	 */
	public function get_option_from_name( $name )
	{
		$options = $this->get_options_info();
		
		if ( !empty( $options[$name] ) ) {
			return $options[$name];
		}
		return FALSE;
	}

	/**
	 * Gets all Habari option information, and determines other information about these options
	 *
	 * Returned array equals: array('name','value','type','genre','plugin_name','active','delete_allowed');
	 * It also sets:
	 * $this->opts_count_total with the number of found options
	 * $this->opts_count_inactive with the number of inactive options
	 *
	 * @return array An array of options [with associated info] on success, or FALSE on failure
	 */
	public function get_options_info()
	{
		$raw_options = DB::get_results( 'SELECT name, value, type FROM {options}', array(), 'QueryRecord' );
		$actives   = Options::get('active_plugins');
		$actives   = array_change_key_case($actives, CASE_LOWER);
		//$deactives = $this->get_deactives();

		if (!$raw_options) {
			return FALSE;
		}

		$this->opts_count_inactive = 0;
		$options = array();
		foreach ($raw_options as $raw_option) {
			
			$_name = $raw_option->name;
			
			$options[$_name] = array(
				'name'  => $_name,
				'type'  => $raw_option->type,
				'value' => $raw_option->value,
			);
			
			if ($raw_option->type) {
				$options[$_name]['value_unserialized'] = unserialize( $raw_option->value );
			}

			$active = 'no';

			// Guessing theme options begin with themes name followed by '_'
			if ($theme = $this->is_theme_option($_name)) {
				$genre       = 'theme';
				$plugin_name = $theme;
				
				if (Options::get('theme_name') === $theme || (0 === strpos( Options::get('theme_dir') . '_', $theme))) {
					$active = 'yes';
				} else {
					$active = 'no';
				}

			// Guessing that only plugins contain '__'
			} elseif (FALSE === strpos($_name, '__')) {

				$active = 'unknown';
				if (in_array($_name, $this->opts_core)) {
					$genre       = 'core';
					$plugin_name = 'core';
				} else {
					$genre       = 'unknown';
					$plugin_name = 'unknown';
				}
			// So, these contain '__', so I guess are from plugins
			} else {
				
				// @todo Obviously only a guess, consider better checks
				$plugin_name = substr($_name, 0, strpos($_name, '__'));
				$genre       = 'plugin';

				// @todo Check if known deactive instead of just guessing
				if (isset($actives[$plugin_name])) {
					$active = 'yes';
				}
			}
			
			if ($active === 'no') {
				$this->opts_count_inactive++;
			}

			$options[$_name]['active']         = $active;
			$options[$_name]['genre']          = $genre;
			$options[$_name]['plugin_name']    = $plugin_name;
			$options[$_name]['delete_allowed'] = $this->is_option_genre_delete_allowed($genre);
		}

		$this->opts_count_total = count($raw_options);

		if ($this->opts_count_total > 0) {
			return $options;
		}
		return FALSE;
	}

	/**
	 * Gets the deactived plugins and themes
	 *
	 * @todo This makes guesses and is not yet used.
	 *
	 * @return array An array of deactivated plugins, with an empty array() on failure
	 */
	public function get_deactives()
	{
		$active_plugins   = Plugins::get_active();
		$deactive_plugins = array();

		foreach ( Plugins::list_all() as $fname => $fpath ) {
			$id    = Plugins::id_from_file( $fpath );
			$info  = Plugins::load_info ( $fpath );

			if (is_null($info) || is_null($id)) {
				continue;
			}

			//@todo $info differs from get_option_from_name() output, but would be inefficient to use for 300+ plugins
			if (empty($active_plugins[$id])) {
				$deactive_plugins[$id] = $info;
			}
		}
		return $deactive_plugins;
	}

	/**
	 * Determines if the option can be deleted, by using the appropriate configure
	 * directives.
	 *
	 * @param  $genre will likely be either core or something else
	 * @return bool
	 */
	public function is_option_genre_delete_allowed( $genre )
	{
		if ( $genre === 'core' ) {
			return (bool) $this->opts_local['allow_delete_core'];
		} else {
			return (bool) $this->opts_local['allow_delete_other'];
		}
		return FALSE;
	}

	/**
	 * Determines if an option is for a theme
	 *
	 * @param  $option Name of the option being checked
	 * @return mixed Returns the theme name on success, or FALSE if not found
	 */
	public function is_theme_option( $option )
	{
		if ( $all_themes = Themes::get_all() ) {

			foreach ($all_themes as $theme_name => $theme_file) {
				if (0 === strpos($option, strtolower($theme_name) . '_')) {
					return $theme_name;
				}
			}
		}
		return FALSE;
	}
}

?>
