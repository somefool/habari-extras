<?php


// Currently using another library because RemoteRequest is buggy as hell with https
require( dirname(__FILE__) . '/simplenoteapi.php' );

class Simplenote extends Plugin implements MediaSilo
{
	
	const SILO_NAME = 'Simplenote';

	static $cache = array();
	
	public function action_init() {
		if( isset( User::identify()->info->simplenote_email ) ) {
			$this->api = new SimpleAPI( User::identify()->info->simplenote_email, User::identify()->info->simplenote_password );
		}
	}
	
	/**
	 * Check for updates
	 */
	public function action_update_check()
	{
		Update::add( $this->info->name, '9f2d9881-ebad-489d-9aa7-9ae7b6c09a38', $this->info->version );
	}
	
	/**
	 * Create plugin configuration
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	/**
	 * Create configuration panel
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
				
					$form = new FormUI( strtolower( get_class( $this ) ) );

					$form->append( 'text', 'search', 'simplenote__search', _t('Search query:') );

					$form->append( 'submit', 'save', _t('Save') );
					$form->out();
					
					break;
			}
		}
	}
	
	/**
	 * Add the configuration to the user page 
	 **/
	public function action_form_user( $form, $user )
	{
		$fieldset = $form->append( 'wrapper', 'simplynoted', 'Simplenote' );
		$fieldset->class = 'container settings';
		$fieldset->append( 'static', 'simplynoted', '<h2>' . htmlentities( 'Simplenote', ENT_COMPAT, 'UTF-8' ) . '</h2>' );
		
		$email = $fieldset->append( 'text', 'simplenote_email', 'null:null', _t('Email:'), 'optionscontrol_text' );
		$email->class[] = 'item clear';
		$email->add_validator( 'validate_email' );
		$email->value = $user->info->simplenote_email;
		
		$password = $fieldset->append( 'password', 'simplenote_password', 'null:null', _t('Password:'), 'optionscontrol_text' );
		$password->type = 'password';
		$password->class[] = 'item clear';
		$password->add_validator( array( $this, 'validate_credentials' ), $fieldset->simplenote_email->value );
		$password->value = $user->info->simplenote_password;
		
		$form->move_before( $fieldset, $form->page_controls );
		
	}
	
	/**
	 * Save authentication fields
	 **/
	public function filter_adminhandler_post_user_fields( $fields )
	{
		$fields[] = 'simplenote_email';
		$fields[] = 'simplenote_password';
		
		return $fields;
	}
	
	/**
	 * A validator to check the supplied credentials
	 **/
	public function validate_credentials( $password, $control, $form, $email)
	{
		$api = new SimpleAPI;
		
		if( $api->authenticate( $email, $password ) ) {
			return array();
		}
		else {
			return array( _t( 'Authentication failed. Check your email and password.') );
		}
	}
	
	/**
	 * Add note field to publish form
	 **/
	public function action_form_publish( $form, $post )
	{
		// Create the Note display field
		$form->append('hidden', 'note_key', 'null:null');
		$form->note_key->id= 'note_key';
		$form->note_key->value = $post->note->key;
		
		// Create the Notes display field
		$form->append('textarea', 'notes', 'null:null', _t('Notes'), 'admincontrol_textarea');
		$form->notes->class[] = 'resizable';
		$form->notes->raw = true;
		$form->notes->value = $post->note->content;
		
		$form->move_before( $form->notes, $form->content );
	}
	
	/**
	 * Save our data to the database
	 */
	public function action_publish_post( $post, $form )
	{
		$this->action_form_publish($form, $post);
		
		$post->info->note_key = $post->note->key = $form->note_key->value;
		
		$post->note->content = $form->notes->value;
		
		Utils::debug( $post->note->update(), $post->note );
		exit;
		
	}
	
	/**
	 * Return related note 
	 **/
	public function filter_post_note( $note, $post )
	{
		if( isset( $post->info->note_key ) ) {
			$note = $this->api->get( $post->info->note_key );
			return $note;
		}
		else {
			return new Note();
		}
	}
	
	/**
	 * Gets a list of notes
	 **/
	private function get_notes()
	{
		$notes = $this->api->search( Options::get('simplenote__search') );
				
		return $notes;
	}
	
	public function action_admin_footer() {
		echo '<script type="text/javascript">';
		require('simplynoted.js');
		echo '</script>';		
	}
	
	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*	  icon- An icon to represent the silo
	*/
	public function silo_info()
	{
		if( isset( $this->api ) ) {
			return array('name' => self::SILO_NAME, 'icon' => URL::get_from_filesystem(__FILE__) . '/icon.png');
		}
		else {
			return array();
		}
	}

	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_dir($path)
	{
		$results = array();
		$user = User::identify()->info->lastfm_username;

		$section = strtok($path, '/');
		switch($section) {
			case '':
				foreach($this->get_notes() as $note) {
					$props = array();
					$props['title'] = $note->title;
					$props['filetype'] = 'note';
					$props['content'] = $note->content;
					$props['summary'] = $note->summary;
					$props['key'] = $note->key;

					$results[] = new MediaAsset(
						self::SILO_NAME . '/' . $note->key,
						false,
						$props
					);						
				}
				
				break;
		}
		return $results;
	}

	/**
	 * Provide controls for the media control bar
	 *
	 * @param array $controls Incoming controls from other plugins
	 * @param MediaSilo $silo An instance of a MediaSilo
	 * @param string $path The path to get controls for
	 * @param string $panelname The name of the requested panel, if none then emptystring
	 * @return array The altered $controls array with new (or removed) controls
	 *
	 * @todo This should really use FormUI, but FormUI needs a way to submit forms via ajax
	 */
	public function filter_media_controls( $controls, $silo, $path, $panelname )
	{
		$controls = array();
		return $controls;
	}

	public function silo_upload_form() {
	}

	/**
	* Get the file from the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return MediaAsset The requested asset
	*/
	public function silo_get($path, $qualities = null)
	{
	}

	/**
	* Get the direct URL of the file of the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return string The requested url
	*/
	public function silo_url($path, $qualities = null)
	{
	}

	/**
	* Create a new asset instance for the specified path
	*
	* @param string $path The path of the new file to create
	* @return MediaAsset The requested asset
	*/
	public function silo_new($path)
	{
	}

	/**
	* Store the specified media at the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param MediaAsset $ The asset to store
	*/
	public function silo_put($path, $filedata)
	{
	}

	/**
	* Delete the file at the specified path
	*
	* @param string $path The path of the file to retrieve
	*/
	public function silo_delete($path)
	{
	}

	/**
	* Retrieve a set of highlights from this silo
	* This would include things like recently uploaded assets, or top downloads
	*
	* @return array An array of MediaAssets to highlihgt from this silo
	*/
	public function silo_highlights()
	{
	}

	/**
	* Retrieve the permissions for the current user to access the specified path
	*
	* @param string $path The path to retrieve permissions for
	* @return array An array of permissions constants (MediaSilo::PERM_READ, MediaSilo::PERM_WRITE)
	*/
	public function silo_permissions($path)
	{
	}

	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_contents()
	{
	}

	
}


/**
* Interfaces with the Simplenote API
*/
class SimpleAPI
{
	
	/**
	 * Construct stuff
	 **/
	public function __construct( $email = NULL, $password = NULL )
	{
		$this->api = new simplenoteapi;
		
		if( $email != NULL and $password != NULL ) {
			$this->authenticate( $email, $password );
		}
		
	}
	
	/**
	 * Finds notes which match a given query
	 **/
	public function search( $query )
	{
		$result = $this->api->search( $query, 2000000 );
		
		$notes = array();
		
		foreach( $result['results'] as $key => $content ) {
			$note = new Note( $key );
			$note->content = $content;
			
			$notes[] = $note;
		}
		
		return $notes;
	}
	
	/**
	 * Gets a note 
	 **/
	public function get( $key )
	{
		$result = $this->api->get_note( $key );
		
		$note = new Note( $result['key'] );
		$note->content = $result['content'];
		
		return $note;
	}
	
	/**
	 * Updates a note
	 **/
	public function update( $key, $content )
	{
		Utils::debug( $this->api->save_note( $content, $key ) );
		
		if( $this->api->save_note( $content, $key ) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Authenticates with a given username and password
	 *
	 * @return bool successful?
	 **/
	public function authenticate( $email, $password )
	{
		return $this->api->login( $email, $password );
	}
	
}

/**
* Represents a single note
*/
class Note
{

	public $content = '';

	public function __construct( $key = '' ) {
		$this->key = $key;
	}

	public function __get( $name ) {
		switch( $name ) {
			case 'title':
				$title = explode( "\n", $this->content);
				return $title[0];
			case 'summary':
				$summary = Utils::truncate( $this->content, '100' );
				return $summary;
		}
	}
	
	/**
	 * Update a post
	 **/
	public function update()
	{
		$api = new SimpleAPI( User::identify()->info->simplenote_email, User::identify()->info->simplenote_password );
		
		return $api->update( $this->key, $this->content );
		
	}
	
}


?>