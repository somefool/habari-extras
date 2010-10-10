<?php

	class Export extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export', '41f7ab69-dddd-4308-b31e-92f3d1270123', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				ACL::create_token( 'export now', 'Export the Habari database.', 'export' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::destroy_token( 'export now' );
				
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
		
		public function action_plugin_ui ( $plugin_id, $action ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				switch ( $action ) {
					
					case _t('Configure'):
						
						$ui = new FormUI( 'export' );
						$ui->append( 'text', 'export_path', 'option:export__export_path', _t('Export path:'));
						$ui->export_path->add_validator( 'validate_required' );
						
						$ui->append( 'submit', 'save', _t( 'Save' ) );
						$ui->on_success( array( $this, 'updated_config' ) );
						
						$ui->out();
						
						break;
						
					case _t('Export'):
						
						$this->export();
						
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
					
				}
				
			}
			
		}
		
	}

?>