<?php

	class Dateyurl extends Plugin
	{ 
		
		/**
		 * Required plugin info() implementation provides info to Habari about this plugin.
		 */ 
		public function info()
		{
			return array (
				'name' => 'DateYURL',
				'url' => 'http://habariproject.org',
				'author' => 'Habari Community',
				'authorurl' => 'http://habariproject.org',
				'version' => 0.2,
				'description' => 'Makes entry urls in the form /{year}/{month}/{day}/{slug} or /{year}/{month}/{slug}. <strong>Ensure the Route 301 plugin is not also attempting to rewrite your choice!</strong>',
				'license' => 'Apache License 2.0',
			);
		}
	
		public function action_init() {
			
			$rule = RewriteRules::by_name('display_entry');
			$rule = $rule[0];
			
			$format = Options::get( 'dateyurl__format' );
			
			// for backwards compatibility. the first time, set the option
			if ( $format == null ) {
				Options::set( 'dateyurl__format', 'date' );
				$format = 'date';
			}
			
			if ( $format == 'date' ) {
				// /{year}/{month}/{day}/{slug}
				$rule->parse_regex= '%(?P<year>\d{4})/(?P<mon0>\d{2})/(?P<mday0>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
				$rule->build_str= '{$year}/{$mon0}/{$mday0}/{$slug}(/page/{$page})';
			}
			
			if ( $format == 'month' ) {
				// /{year}/{month}/{slug}
				$rule->parse_regex= '%(?P<year>\d{4})/(?P<mon0>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i';
				$rule->build_str= '{$year}/{$mon0}/{$slug}(/page/{$page})';
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