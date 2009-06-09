<?php

	class Dateyurl extends Plugin
	{ 

		public function action_init() {
			
			$format = Options::get( 'dateyurl__format' );
			
			// for backwards compatibility. the first time, set the option
			if ( $format == null ) {
				Options::set( 'dateyurl__format', 'date' );
				$format = 'date';
			}
			
			if ( $format == 'date' ) {
				// /{year}/{month}/{day}/{slug}
				$parse_regex= '%(?P<year>\d{4})/(?P<mon0>\d{2})/(?P<mday0>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
				$build_str= '{$year}/{$mon0}/{$mday0}/{$slug}(/page/{$page})';
			}
			
			if ( $format == 'month' ) {
				// /{year}/{month}/{slug}
				$parse_regex= '%(?P<year>\d{4})/(?P<mon0>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
				$build_str= '{$year}/{$mon0}/{$slug}(/page/{$page})';
			}
			
			// For backwards compatability. the first time, set the rules
			$rules = Options::get( 'dateyurl__rules' );
						
			// for backwards compatibility. the first time, set the option
			if ( $rules == null ) {
				$rules= array('display_entry');
				Options::set( 'dateyurl__rules', $rules );
			}
			
			foreach( $rules as $rule_name) {
				$rule = RewriteRules::by_name($rule_name);
				$rule = $rule[0];
				$rule->parse_regex= $parse_regex;
				$rule->build_str= $build_str;
			}
			
			
		}
		
	
	
		public function action_update_check()
		{
			Update::add( 'DateYURL', '376A1864-98A1-11DD-A1CC-365A55D89593', $this->info->version );
		}
	
		public function filter_plugin_config( $actions, $plugin_id )
		{
			if ( $plugin_id == $this->plugin_id() ) {
				$actions[] = _t( 'Configure' );
			}
	
			return $actions;
		}
		
		
		public function action_plugin_ui( $plugin_id, $action )
		{
			if ( $plugin_id == $this->plugin_id() ) {
				
				if ( $action == _t( 'Configure' ) ) {
					
					$class_name = strtolower( get_class( $this ) );

					$form = new FormUI( $class_name );
					$form->append( 'select', 'format', 'dateyurl__format', _t( 'URL Format' ), array( 'date' => '/{year}/{month}/{day}/{slug}', 'month' => '/{year}/{month}/{slug}' ) );
					$form->append( 'textmulti', 'rules', 'dateyurl__rules', _t( 'Rules to Change' ) );
					$form->append( 'submit', 'save', _t( 'Save' ) );

					$form->on_success( array( $this, 'updated_config' ) );
					$form->out();
					
				}
				
			}
		}

		public function updated_config( $form )
		{
			$form->save();
		}

		public function action_plugin_activation( $file )
		{
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				if ( Options::get( 'dateyurl__format' ) == null ) {
					Options::set( 'dateyurl__format', 'date' );
				}
				
			}
		}
		
	
	}

?>