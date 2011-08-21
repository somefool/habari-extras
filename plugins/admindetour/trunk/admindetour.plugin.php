<?php
class admindetour extends Plugin {
	
	public $mainmenus = array();
	
	public function filter_rewrite_rules( $db_rules )
	{	
		$db_rules[] = new RewriteRule ( array(
			'name' => 'admin_dashboard',
			'parse_regex' => '%^admin/dashboard/?$%i',
			'build_str' => 'admin/dashboard',
			'handler' => 'AdminHandler',
			'action' => 'admin',
			'priority' => 7,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Add "admin/dashboard" so Admin Detour can hijack "admin".'
			) );
		return $db_rules;
	}
	
	public function action_before_act_admin( $that ) 
	{
		if (!isset($that->handler_vars['page'])) {
			$args = User::identify()->info->admindetour_real['args'];
			$that->handler_vars = $that->handler_vars->merge( $args );
		}
	}
	
	public function filter_adminhandler_post_loadplugins_main_menu( $mainmenus )
	{
		$mainmenus['dashboard'] = array( 'url' => URL::get( 'admin', 'page=dashboard' ), 'title' => _t( 'View your user dashboard' ), 'text' => _t( 'Dashboard' ), 'hotkey' => 'D', 'selected' => false );
		
		$this->mainmenus = $mainmenus;
		
		return $mainmenus;
	}
	
	public function filter_plugin_config( $actions )
	{
		$actions[] = _t('Configure');
		return $actions;
	}
	
	public function configure( $plugin_id, $action )
	{
		$mainmenus = array();
		foreach ($this->mainmenus as $mainmenu) {
			$mainmenus[$mainmenu['url']] = $mainmenu['text'];
		}
		
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$ui->append( 'select', 'mainmenus', 'user:admindetour_fake', _t('Select the wanted admin frontpage:') );
		$ui->mainmenus->options = $mainmenus;

		$ui->append( 'submit', 'save', _t('Save') );
		$ui->on_success( array( $this, 'save_mainmenu' ) );
		$ui->out();
		break;
	}
	
	public function save_mainmenu($form)
	{
		$base_url = Site::get_url('habari', true);
		$start_url = $form->mainmenus->value;
		
		/* Strip out the base URL from the requested URL */
		/* but only if the base URL isn't / */
		if ( '/' != $base_url) {
			$start_url = str_replace($base_url, '', $start_url);
		}

		/* Trim off any leading or trailing slashes */
		$start_url = trim($start_url, '/');

		/* Remove the querystring from the URL */
		if ( strpos($start_url, '?') !== FALSE ) {
			list($start_url, $query_string)= explode('?', $start_url);
		}
		
		/* Allow plugins to rewrite the stub before it's passed through the rules */
		$start_url = Plugins::filter('rewrite_request', $start_url);

		$stub = $start_url;

		/* Grab the URL filtering rules from DB */
		$matched_rule = URL::parse($stub);

		if ($matched_rule === FALSE) {
			print 'error, cant find rule';
			// error!!!!
		}
		
		/* Return $_GET values to their proper place */
		$args = array();
		if( !empty($query_string) ) {
			parse_str($query_string, $args);
		}

		$rule = $matched_rule->name;
		$args = array_merge($matched_rule->named_arg_values, $args);
		User::identify()->info->admindetour_real = array( 'rule' => $rule, 'args' => $args );
		$_POST[$form->mainmenus->field] = URL::get( $rule, $args );

		$form->save();
	}

}
?>
