<?php

/* Share plugin to add Facebook and Twitter links to posts */

class Share extends Plugin {

	public function info() {
		return array(
			'name' => 'Share Plugin',
			'version' => '0.1',
			'url' => 'http://mgdm.net/',
			'author' => 'Michael Maclean',
			'authorurl' => 'http://mgdm.net/',
			'license' => 'Apache License 2.0',
			'description' => 'This plugin generates Facebook OpenGraph data, and adds it to the header of posts. It can also embed a Facebook Like button, and a Twitter Share button to the bottom of posts.'
		);
	}

	public function action_init() {
		$this->add_template('share_metadata', dirname(__FILE__) . '/share_metadata.php');
		$this->add_template('share_ui', dirname(__FILE__) . '/share_ui.php');
	}

	/**
	 * filter: plugin_config
	 *
	 * @access public
	 * @return array
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t( 'Configure' );
		}
		return $actions;
	}

	public function action_plugin_ui($plugin_id, $action) {
		if ($plugin_id == $this->plugin_id()) {
			switch ($action) {
			case _t('Configure'):
				$form = new FormUI(__CLASS__);
				$admins = $form->append('text', 'admins', 'share__fb_admins', _t('Facebook admin user IDs'));
				$form->append('static', 'admins_text', _t('<p>This is a comma-separated list of the IDs of the Facebook users allowed to admin this page.</p>'));
				$form->append('submit', 'save', 'Save');
				$form->on_success( array( $this, 'updated_config' ) );
				$form->out();
				break;
			}
		}
	}
	
	public function updated_config( FormUI $ui )
    {
        Session::notice( _t( 'Share plugin options saved.', 'share' ) );
        $ui->save();
	}
	
	public function theme_show_share_ui($theme, $post) {
		$theme->post = $post;
		return $theme->fetch('share_ui');	
	}

	public function theme_show_share_metadata($theme, $post) {
		$theme->post = $post;
		return $theme->fetch('share_metadata');	
	}

	public function theme_get_post_description($theme, $post) {
		$content = explode('. ', strip_tags($post->content));
		return $content[0] . '.';
	}
}
?>
