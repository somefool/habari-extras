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
	
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('entry')) {
			Stack::add('admin_stylesheet', array(Site::get_url('user') . '/plugins/photoblog/css/jquery.jcrop.css', 'screen'), 'jcrop');
			Stack::add('admin_stylesheet', array(Site::get_url('user') . '/plugins/photoblog/css/photoblog.css', 'screen'), 'photoblog');
			Stack::add('admin_header_javascript', array(Site::get_url('user') . '/plugins/photoblog/js/jquery.jcrop.js'), 'jcrop');
			Stack::add('admin_header_javascript', array(Site::get_url('scripts') . '/photoblog.js'), 'photoblog');
			
			$desc_tab = $form->publish_controls->insert('tagselector', 'fieldset', 'description', _t('Description'));
			$desc_tab->append($form->content);
			$form->content->caption = _t('Description');
			
			$pb_wrapper= $form->append('wrapper', 'pb_wrapper', 'pb_wrapper');
			$form->move_after( $pb_wrapper, $form->title );
			$photo_url = $pb_wrapper->append('text', 'photourl', 'null:null', _t('Photo URL'), 'admincontrol_photourl');
			
			$photo = $form->append('marqueetool', 'photo', _t('Photo'));
			$form->move_after( $photo, $pb_wrapper );
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
			$admin_dir = Site::get_dir('user') . '/plugins/photoblog/formcontrols/';
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
				<input type="button" id="pb_saveThumb" name="pb_saveThumb" value="Save Position">
				<div id="preview_container" style="width:150px;height:150px;overflow:hidden;border:1px solid #FFF;">
				</div>
			</div>
		</div>
		<!-- Saved Values -->
		<input type="hidden" id="pb_x" name="pb_x" value="">
		<input type="hidden" id="pb_y" name="pb_y" value="">
		<input type="hidden" id="pb_x2" name="pb_x2" value="">
		<input type="hidden" id="pb_y2" name="pb_y2" value="">
		<input type="hidden" id="pb_w" name="pb_w" value="">
		<input type="hidden" id="pb_h" name="pb_h" value="">
		<!-- Current Position -->
		<input type="hidden" id="pb_nx" name="pb_nx">
		<input type="hidden" id="pb_ny" name="pb_ny">
		<input type="hidden" id="pb_nx2" name="pb_nx2">
		<input type="hidden" id="pb_ny2" name="pb_ny2">
		<input type="hidden" id="pb_nw" name="pb_nw">
		<input type="hidden" id="pb_nh" name="pb_nh">
		';
	}
}
?>