<?php

class TagRewriter extends Plugin
{ 
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'Tag Rewriter',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => 0.1,
			'description' => 'Automatically renames tags when writing a post',
			'license' => 'ASL 2.0',
		);
	}

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '7cc973f3-dd6e-4081-98a3-535c7bf6e799', $this->info->version );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$ui->append( 'static', 'explanation', 'Create aliases in the form of <code>{original}={new}</code>' );
					$ui->append( 'textmulti', 'aliases', 'tagrewriter__aliases', _t('Aliases:') );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					break;
			}
		}
	}
	
	public static function get_aliases() {
		$option= Options::get('tagrewriter__aliases');
		
		$aliases= array();
		foreach($option as $alias) {
			$exploded= explode('=', $alias);
			$aliases[$exploded[0]]= $exploded[1];
		}
		
		return $aliases;
	}
	
	public function action_form_publish($form, $post) {
		if($form->tags->value == '') return;
		// If we don't have tags, don't run
		
		$aliases= self::get_aliases();

		$tags= $form->tags->value;
		foreach($aliases as $original => $new) {
			$tags= str_replace($original, $new, $tags);
		}

		$form->tags->value= $tags;
		return $form;
	}

}	

?>