<?php

class CustomQuery extends Plugin
{
	const VERSION = '0.2';
	
	public function info()
	{
		return array( 
			'name' => 'Custom Query',
			'author' => 'Habari Community',
			'description' => 'Allows you to set the number of posts to display for each page.',
			'url' => 'http://habariproject.org',
			'version' => self::VERSION,
			'license' => 'Apache License 2.0'
			);
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
      {
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure', 'customquery');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure', 'customquery') :
					$ui = new FormUI( 'customquery' );
					
					foreach ( RewriteRules::get_active()->getArrayCopy() as $rule ) {
						if ( strpos( $rule->name, 'display_' ) === 0 and ( $rule->name <> 'display_404' )) {
							$ui->append( 'text', $rule->name,'customquery__'. $rule->name,
								 'Number of Posts for ' . $rule->name );
						}
					}

					$ui->append( 'submit', 'save', _t('Save') );					
					$ui->out();
					break;
			}
		}
	}

	public function action_update_check()
	{
	 	Update::add( 'Custom Query', '1fe407a0-729e-11dd-ad8b-0800200c9a66', $this->info->version );
	}
	
	public function filter_template_where_filters( $where_filters )
	{
		if ( Options::get( 'customquery__' . URL::get_matched_rule()->name ) ) {
			$where_filters['limit']= Options::get( 'customquery__' . URL::get_matched_rule()->name );
		}
		return $where_filters;
	}
}

?>
