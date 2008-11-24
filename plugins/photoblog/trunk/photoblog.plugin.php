<?php
// Very ugly, but only way to load FormUI before FormControlMarqueeTool
// Note: it's 2:35am, brain is half-dead by now
new FormUI('dummy');

class Photoblog extends Plugin
{
	
	private static $folder_chmod = 0755; // For paranoids!

	public function info()
	{
		return array (
			'name' => 'Photoblog',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'version' => '0.1',
			'description' => 'Adds features needed to create a photoblog.',
			'license' => 'Apache License 2.0',
		);
	}
	
	public function action_plugin_activation( $plugin_file )
	{
		if( Plugins::id_from_file(__FILE__) == Plugins::id_from_file($plugin_file) ) {
			Post::add_new_type( 'photo' );
			$this->make_default_dirs(false);
		}
	}
	
	function action_plugin_deactivation( $plugin_file )
	{
		if( Plugins::id_from_file( __FILE__ ) == Plugins::id_from_file( $plugin_file  ) ) {
			Post::deactivate_post_type( 'photo' );
		}
	}

	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'photo' => array(
				'singular' => _t('Photo'),
				'plural' => _t('Photos'),
			)
		); 
 		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}

	public function filter_post_photo( $out, $post )
	{
		$photo = new stdClass();
		$photo->src = $post->info->photo_src;
		return $photo;
	}
	
	public function filter_post_thumbnail( $out, $post )
	{
		$thumbnail = new stdClass();
		return $thumbnail;
	}
	
	public function make_default_dirs($force = true) // Bad name, needs to be improved
	{
		$defaults = array(
			'pb__dirs_photos' => Site::get_dir('user') . '/files/photoblog/photos',
			'pb__dirs_thumbnails' => Site::get_dir('user') . '/files/photoblog/thumbnails'
			);
		
		if ($force) {
			$dirs = $defaults;
		}
		else {
			/* This is ugly too */
			$dirs = Options::get(array('pb__dirs_photos', 'pb__dirs_thumbnails'));
			if (isset($dirs[0]) && !empty($dirs[0])) {
				$dirs['pb__dirs_photos'] = $dirs[0];
			}
			if (isset($dirs[1]) && !empty($dirs[1])) {
				$dirs['pb__dirs_thumbnails'] = $dirs[1];
			}
			$dirs = array_merge( $defaults, $dirs );
		}

		return $this->make_dirs($dirs);
	}
	
	public function make_dirs( $dirs )
	{
		$errors = (array) self::validate_dirs( $dirs );
		
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				Session::error($error);
			}
			Session::error(_t('Photoblog will not work until folders are created and writable.'));
			Options::set('pb__installed', false);
			return false;
		}
		else {
			foreach ($dirs as $name => $dir) {
				Options::set($name, $dir);
			}
			Options::set('pb__installed', true);
			return true;
		}
	}
	
	public static function validate_dirs( $dirs )
	{
		$dirs = (array) $dirs;
		$errors = array();
		
		foreach ($dirs as $path) {
			if (is_writable($path)) {
				continue;
			}
			elseif (file_exists($path) && !@chmod($path, self::$folder_chmod)) {
				$errors[]= sprintf(_t('Photoblog was unable to change permissions: %s.'), $path);
			}
			else {
				if (!@mkdir($path, self::$folder_chmod, TRUE)) {
					$errors[]= sprintf(_t('Photoblog was unable to create required folder: %s.'), $path);
				}
			}
		}
		
		return $errors;
	}
	
	public function add_controls( $form )
	{
		$form->publish_controls->insert('tagselector', 'fieldset', 'pb_description', _t('Description'));
		$form->publish_controls->pb_description->append($form->content);
		
		$form->content->caption = _t('Description');
		$form->content->tabindex = 4;
		
		$form->append('wrapper', 'pb_wrapper');
		$form->move_after( $form->pb_wrapper, ($form->silos instanceOf FormControlSilos) ? $form->silos : $form->title );
		$form->pb_wrapper->append('text', 'pb_photo_src', 'null:null', _t('Photo Source URL'), 'admincontrol_pb_photo_src');
		$form->pb_photo_src->tabindex = 2;
		
		$form->append('marqueetool', 'pb_thumbnail', 'null:null', _t('Thumbnail'), 'formcontrol_marqueetool');
		$form->move_after( $form->pb_thumbnail, $form->pb_wrapper );
		
		$form->publish_controls->settings->append('checkbox', 'pb_refresh', 'null:null', _t('Refresh photo and thumbnail'), 'tabcontrol_checkbox');
		$form->publish_controls->settings->append('checkbox', 'pb_savephoto', 'null:null', _t('Save a local copy of the photo'), 'tabcontrol_checkbox');

		return $form;
	}
	
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('photo')) {
			Stack::add('admin_stylesheet', array($this->get_url() . '/css/jquery.jcrop.css', 'screen'), 'jcrop');
			Stack::add('admin_stylesheet', array( $this->get_url() . '/css/thickbox.css', 'screen'), 'thickbox-css' );
			Stack::add('admin_stylesheet', array($this->get_url() .'/css/photoblog.css', 'screen'), 'photoblog');
			Stack::add('admin_header_javascript', array($this->get_url() . '/js/jquery.jcrop.js'), 'jcrop');
			Stack::add('admin_header_javascript', array($this->get_url() . '/js/thickbox.js'), 'thickbox-js' );
			Stack::add('admin_header_javascript', array(Site::get_url('scripts') . '/photoblog.js'), 'photoblog');
			
			$this->add_controls($form);
			
			if (isset($post->info->photo_src)) {
				$form->pb_photo_src->value = $post->info->photo_src;
			}
			
			if (empty($post->info->thumbnail_json)) {
				$form->pb_thumbnail->value = json_encode( array('x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 0, 'w' => 0, 'h' => 0) );
			}
			else {
				$form->pb_thumbnail->value = $post->info->thumbnail_json;
			}

		}
	}
	
	public function action_publish_post( $post, $form )
	{
		if ($post->content_type == Post::type('photo')) {
			// We need this to retrieve our form values
			$this->add_controls($form);
			$photo_src = $form->pb_photo_src->value;
			$thumbnail_json = urldecode($form->pb_thumbnail->value);
			
			$post->info->pb_savephoto = $form->pb_savephoto->value;
			
			$request = new RemoteRequest( $form->pb_photo_src->value, 'GET' );
			
			/* We do thumbnails first because we require them */
			if ($post->info->pb_savephoto || $form->pb_refresh->value || ($post->info->thumbnail_json != urldecode($form->pb_thumbnail->value))) {
				// Saves processing time if thumbnails aren't to be updated (also means photo won't be)
				if ($request->execute()) {
					$path_thumbnail = Options::get('pb__dirs_thumbnails') . '/' . basename($photo_src); // PATH_SEPARATOR?
					$path_thumbnail = $this->get_save_path($path_thumbnail, $post);
					$this->gd_make_thumbnail($request->get_response_body(), $path_thumbnail['path'], json_decode($thumbnail_json));
					$post->info->thumbnail_path = $path_thumbnail['path'];
					$post->info->thumbnail_filename = $path_thumbnail['filename'];
				}
				else {
					// This won't stop the saving process, wouldn't it would be a bitch?
					Session::error(_t('Photoblog was unable to retrieve the photo, check source URL.'));
				}
			}
			
			/* Let's make sure the request completed successfully */
			if ($request->executed() && ($post->info->pb_savephoto || Options::get('pb__savephotos'))) {
				$path_photo = Options::get('pb__dirs_photos') . '/' . basename($photo_src); // PATH_SEPARATOR?
				$path_photo = $this->get_save_path($path_photo, $post);
				if (!file_exists($path_photo['path']) || $form->pb_refresh->value) {
					file_put_contents($path_photo['path'], $request->get_response_body() );
					$post->info->photo_path = $path_photo['path'];
					$post->info->photo_filename = $path_photo['filename'];
				}
			}
			
			// Overwrite values at the end so we can previously compare values properly
			$post->info->photo_src = $photo_src;
			$post->info->thumbnail_json = $thumbnail_json;
		}
	}
	
	public function get_save_path( $path, $post = null )
	{
		$pathinfo = pathinfo($path);
		$path_sprintf = str_replace($pathinfo['filename'], '%s', $path);
		$filename_clean = $pathinfo['filename'];
		$filename = $filename_clean . '_' . $post->pubdate->format('Ymdhis');
		$save_path = sprintf($path_sprintf, $filename);
		
		/*do {
			$filename = $filename_clean . '_' . date('Ymdhis');
			$save_path = sprintf($path_sprintf, $filename); 
		} while (file_exists($save_path));*/

		return array( 'path' => $save_path, 'filename' => $filename . '.' . $pathinfo['extension'] );
	}
	
	public function filter_rewrite_rules( $rules ) 
	{
		$rules[]= new RewriteRule( array(
			'name' => 'photoblog-js',
			'parse_regex' => '%scripts/photoblog.js$%i',
			'build_str' =>  'scripts/photoblog.js',
			'handler' => 'UserThemeHandler',
			'action' => 'display_photoblog_js',
			'priority' => 6,
			'is_active' => 1,
		) );
		
		return $rules;
	}
	
	public function action_handler_display_photoblog_js( $handler_vars )
	{
		$options = Options::get_group('pb');
		header("content-type: application/x-javascript");
		include('photoblog.js.php');
		exit;
	}
	
	public function filter_control_theme_dir( $admin_dir, $control )
	{
		if ($control->name == 'pb_wrapper') {
			$admin_dir = dirname( $this->get_file() ) . '/formcontrols/';
		}
		return $admin_dir;
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
			$actions[] = _t( 'Restore Default Directories' );
		}

		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ($action) {
				case _t( 'Configure' ):
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$ui->append( 'fieldset', 'pb_thumbnail', _t('Thumbnail Size') );
					$ui->pb_thumbnail->append( 'text', 'pb_thumbnail_w', 'pb__thumbnail_w', _t('Width:') );
					$ui->pb_thumbnail->append( 'text', 'pb_thumbnail_h', 'pb__thumbnail_h', _t('Height:') );
					$ui->append( 'fieldset', 'pb_dirs', _t('Directories') );
					$ui->pb_dirs->append( 'text', 'pb_dirs_photos', 'pb__dirs_photos', _t('Photos:') );
					$ui->pb_dirs->append( 'text', 'pb_dirs_thumbnails', 'pb__dirs_thumbnails', _t('Thumbnail:') );
					$ui->pb_dirs_photos->add_validator( array(__CLASS__, 'validate_dirs') );
					$ui->pb_dirs_thumbnails->add_validator( array(__CLASS__, 'validate_dirs') );
					$ui->append( 'fieldset', 'pb_misc', _t('Miscellaneous') );
					$ui->pb_misc->append( 'checkbox', 'pb_savephotos', 'pb__savephotos', _t('Save a local copy of the photos') );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->set_option('success_message', _t('Options saved'));					
					$ui->out();
					break;
				case _t( 'Restore Default Directories' ):
					if ($this->make_default_dirs(true)) {
						Session::notice(_t('Default directories restored.'));
						// Would be nice to redirect to Configure rather than have a Close button
					}
					else {
						Session::error(_t('Unable to restore default directories.'));
					}
					break;
			}
		}
	}
	
	function gd_make_thumbnail($src,$path,$thumbnail)
	{
		$src = imagecreatefromstring($src);
		$dst = imagecreatetruecolor($thumbnail->w,$thumbnail->h);
		imagecopyresampled($dst,$src,0,0,$thumbnail->x,$thumbnail->y,$thumbnail->w,$thumbnail->h,$thumbnail->w2,$thumbnail->h2);
		imagejpeg($dst,$path);
	}
	
}

class FormControlMarqueeTool extends FormControl
{
	public function get($forvalidation = true)
	{
		return '
		<div id="pb_container">
			<div id="pb_cropbox_container">
			</div>
			<div id="pb_subcontainer">
				<div id="pb_preview_container">
				</div>
			</div>
		</div>
		<p><input type="hidden" id="pb_coords" name="' . $this->field . '" value="' . urlencode($this->value) . '"></p>
		';
	}
}
?>
