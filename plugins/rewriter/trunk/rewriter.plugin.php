<?php

class Rewriter extends Plugin
{
	var $rules;

	public function action_init()
	{
		$this->add_template( 'rules', dirname(__FILE__) . '/rules.php' );
	}

	public function filter_default_rewrite_rules( $rules ) {
		$this->rules = $rules;

		return $rules;
	}

	function add_rule( $name, $params ) {
		$rule = RewriteRules::by_name( $name );
		if(count($rule) == 1) {
			$rule = $rule[0];

			foreach($params as $key => $param) {
				$rule->$key = $param;
			}

			$rule->update();
		} else {
			$rule = new RewriteRule($params);
			$rule->insert();
		}

	}

	function action_add_template_vars( $theme ) {
		$theme->simple = Options::get('rewriter__simpleEntry');

		$theme->rules = self::get_rules();
	}

	function action_admin_theme_get_rules( $handler, $theme )
	{
		$handler_vars = $handler->handler_vars;

		if(isset($handler_vars['names'])) {

			foreach($handler_vars['names'] as $key => $name) {
				$changes = array(
					'name' => $name,
					'parse_regex' => $handler_vars['regexes'][$key],
					'action' => $handler_vars['actions'][$key],
					'priority' => $handler_vars['priorities'][$key],
					'description' => $handler_vars['descriptions'][$key]
				);

				self::add_rule( $key, $changes);

			}

			Session::notice(_t('Rewrite rules updated.'));

			Utils::redirect();
		}

		$theme->display( 'rules' );
		exit;
	}

	function action_admin_theme_post_rules( $handler, $theme )
	{
		$this->action_admin_theme_get_rules( $handler, $theme );
	}

	public function filter_adminhandler_post_loadplugins_main_menu( $menu )
	{
		$menu['rules']= array( 'url' => URL::get( 'admin', 'page=rules' ), 'title' => _t('Modify rewrite rules'), 'text' => _t('Rewrite Rules'), 'hotkey' => 'R', 'selected' => false );
		return $menu;
	}

	function get_rules() {

		$rules = $this->rules;

		$rules = RewriteRules::sort_rules( RewriteRules::get_active());

		$this->rules = $rules;

		return $rules;
	}


/**
 * add ACL tokens when this plugin is activated
 **/
public function action_plugin_activation( $file )
{
	if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
		ACL::create_token( 'Rewriter', 'Create custom rewrite rules', 'rewriter' );
	}
}

/**
	* remove ACL tokens when this plugin is deactivated
**/
function action_plugin_deactivation( $plugin_file )
{
		if( Plugins::id_from_file( __FILE__ ) == Plugins::id_from_file( $plugin_file  ) ) {
				ACL::destroy_token( 'Rewriter' );
		}
}

	public function filter_admin_access_tokens( $require_any, $page, $type )
	{
		if ( 'rules' == $page ) {
			$require_any = array( 'super_user' => true, 'rewriter' => true );
		} return $require_any;
	}
}

?>
