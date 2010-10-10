<?php

	class Export extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export', '41f7ab69-dddd-4308-b31e-92f3d1270123', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::create_token( 'export', 'Export the Habari database.', 'export' );
				
			}
			
		}
		
		public function filter_plugin_config ( $action, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$actions[] = _t('Configure');
				
				// only users with the proper permission should be allowed to export
				if ( User::identify()->can('export now') ) {
					$actions[] = _t('Export');
				}
				
			}
			
		}
		
	}

?>