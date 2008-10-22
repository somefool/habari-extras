<?php
// Very ugly, but only way to load FormUI before FormControlMarqueeTool
// Note: it's 2:35am, brain is half-dead by now
new FormUI('dummy');

class Photoblog extends Plugin
{

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
		}
	}
	
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('photo')) {
			Stack::add('admin_stylesheet', array(Site::get_url('user') . '/plugins/photoblog/css/jquery.jcrop.css', 'screen'), 'jcrop');
			Stack::add('admin_stylesheet', array(Site::get_url('user') . '/plugins/photoblog/css/photoblog.css', 'screen'), 'photoblog');
			Stack::add('admin_header_javascript', array(Site::get_url('user') . '/plugins/photoblog/js/jquery.jcrop.js'), 'jcrop');
			Stack::add('admin_header_javascript', array(Site::get_url('user') . '/plugins/photoblog/js/jquery.exif.js'), 'exif');
			Stack::add('admin_header_javascript', array(Site::get_url('scripts') . '/photoblog.js'), 'photoblog');
			
			$desc_tab = $form->publish_controls->insert('tagselector', 'fieldset', 'description', _t('Description'));
			$desc_tab->append($form->content);
			$form->content->caption = _t('Description');
			$form->content->tabindex = 4;
			
			$pb_wrapper= $form->append('wrapper', 'pb_wrapper', 'pb_wrapper');
			$form->move_after( $pb_wrapper, $form->title );
			$photo_url = $pb_wrapper->append('text', 'photourl', 'null:null', _t('Photo URL'), 'admincontrol_photourl');
			$photo_url->tabindex = 2;
			if (isset($post->info->photo_url)) {
				$photo_url->value = $post->info->photo_url;
			}
			
			$photo = $form->append('marqueetool', 'thumbnail', 'null:null', _t('Thumbnail'), 'formcontrol_marqueetool');
			$form->move_after( $photo, $pb_wrapper );
			if (empty($post->info->thumbnail_json)) {
				$photo->value = json_encode( array('x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 0, 'w' => 0, 'h' => 0) );
			}
			else {
				$photo->value = $post->info->thumbnail_json;
			}

		}
	}
	
	public function action_publish_post( $post, $form )
	{
		if ($post->content_type == Post::type('photo')) {
			$post->info->photo_url = $form->photourl->value;
			$post->info->thumbnail_json = urldecode($form->thumbnail->value);
		}
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
	
}

class FormControlMarqueeTool extends FormControl
{
	public function get($forvalidation = true)
	{
		return '
		<div id="pb_container" style="position:relative;margin-left:auto;margin-right:auto;">
			<div id="cropbox_container">
			</div>
			<div style="position:absolute;bottom:5px;right:5px;z-index:10000;text-align:center;">
				<p><input type="button" id="pb_setThumb" name="pb_setThumb" value="' . _t('Set and close') . '"></p>
				<div id="preview_container" style="width:150px;height:150px;overflow:hidden;border:1px solid #FFF;">
				</div>
			</div>
		</div>
		<p><input type="hidden" id="pb_coords" name="' . $this->field . '" value="' . urlencode($this->value) . '"></p>
		';
	}
}
?>