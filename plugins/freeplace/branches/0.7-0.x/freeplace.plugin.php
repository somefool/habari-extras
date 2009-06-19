<?php

class Freeplace extends Plugin
{
	
	public function action_update_check()
	{
		Update::add( $this->info->name, 'c7d47111-d452-4522-a343-f1d0df1d9095', $this->info->version );
	}
	
	/**
	 * Create plugin configuration
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t('Replace');
		}
		return $actions;
	}

	/**
	 * Create configuration panel
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Replace') :
				
					$form = new FormUI( strtolower( get_class( $this ) ) );

					$form->append( 'text', 'search', 'null:null', _t('Search:') );
					$form->search->add_validator('validate_required');
					
					$form->append( 'text', 'replace', 'null:null', _t('Replace:') );
					$form->replace->add_validator('validate_required');

					$form->append( 'submit', 'save', _t('Replace') );
					
					$form->on_success( 'do_replace' );
					
					$form->out();
					
					break;
			}
		}
	}
	
	/**
	 * UPDATE [your_table_name] SET [your_table_field] = REPLACE([your_table_field], ‘[string_to_find]‘ , ‘[string_to_be_replaced]‘);
	 */
	
	/**
	 * Handler FormUI success action and do the replacement
	 **/
	public function filter_do_replace( $show_form, $form )
	{		
		
		if( DB::query( 'UPDATE {posts} SET content = REPLACE(content, ? , ?)', array( $form->search->value, $form->replace->value ) ) ) {
			Session::notice( sprintf( _t( 'Successfully replaced \'%s\' with \'%s\' in all posts' ), $form->search->value, $form->replace->value ) );
			Utils::redirect( URL::get( 'admin', array( 'page' => 'plugins', 'configure' => Plugins::id_from_file( __FILE__ ), 'configaction' => _t('Replace') ) ), false );
		}
		else {
			Session::error( _t( 'There was an error with replacement.' ) );
		}
		
		return false;
	}
	
}

?>