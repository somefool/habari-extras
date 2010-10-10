<?php

	class Export extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export', '41f7ab69-dddd-4308-b31e-92f3d1270123', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				
				
			}
			
		}
		
	}

?>