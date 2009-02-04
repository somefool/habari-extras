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
					
					$ui->append( 'checkbox', 'pluralization', 'tagrewriter__plurals', _t('Attempt automatic pluralization coordination to existing tags') );
					
					$ui->append( 'static', 'explanation', 'Create aliases in the form of <code>{original}={new}</code>' );
					$ui->append( 'textmulti', 'aliases', 'tagrewriter__aliases', _t('Aliases:') );
					
					$ui->append( 'submit', 'save', _t('Save') );
					
					$ui->out();
					break;
			}
		}
	}
	
	private static function get_aliases() {
		$option= Options::get('tagrewriter__aliases');
		
		$aliases= array();
		foreach($option as $alias) {
			$exploded= explode('=', $alias);
			$aliases[$exploded[0]]= $exploded[1];
		}
		
		return $aliases;
	}
	
	public function action_post_update_before($post) {
		
		$aliases= self::get_aliases();
		
		if(Options::get('tagrewriter__plurals') != NULL && Options::get('tagrewriter__plurals') == 1) {
			$pluralize= true;
		} else {
			$pluralize= false;
		}
		
		$tags= array();
		foreach($post->tags as $tag) {
			if(isset($aliases[$tag])) {
				$tags[]= $aliases[$tag];
				continue;
			}
			
			if($pluralize) {
				if(Tags::get_by_slug($tag . 's') != false) {
					$tags[]= $tag . 's';
					continue;
				}
				elseif(Tags::get_by_slug(rtrim($tag, 's')) != false) {
					$tags[]= rtrim($tag, 's');
					continue;
				}
			}
			
			$tags[]= $tag;
		}
		
		$post->tags= $tags;
	}

}	

?>