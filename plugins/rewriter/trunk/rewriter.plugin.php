<?php

class Rewriter extends Plugin
{
	var $rules;
	
	public function info()
	{
		return array(
			'name' => 'Rewriter',
			'author' => 'Habari Community',
			'description' => 'Allows you to change rewrite rules for custom permalinks.',
			'url' => 'http://habariproject.org',
			'version' => '0.1',
			'license' => 'Apache License 2.0'
			);
	}

	public function action_init()
	{
		$this->add_template( 'rules', dirname(__FILE__) . '/rules.php' );
	}
	
	public function filter_default_rewrite_rules( $rules ) {
		$this->rules= $rules;
		
		return $rules;
	}
	
	function action_add_template_vars( $theme ) {
		$theme->rules= self::get_rules();
		
	}
	
	function action_admin_theme_get_rules( $handler, $theme )
	{
		
		$theme->display( 'rules' );
		exit;
	}

	function action_admin_theme_post_rules( $handler, $theme )
	{
		$this->action_admin_theme_get_spam( $handler, $theme );
	}

	public function filter_adminhandler_post_loadplugins_main_menu( $menu )
	{
		$menu['rules']= array( 'url' => URL::get( 'admin', 'page=rules' ), 'title' => _t('Modify rewrite rules'), 'text' => _t('Rewrite Rules'), 'hotkey' => 'R', 'selected' => false );
		return $menu;
	}
	
	function get_rules() {
		
		$rules= $this->rules;
		
		$rules= RewriteRules::sort_rules( RewriteRules::get_active());
		
		$this->rules= $rules;
		
		return $rules;
	}
}

?>
