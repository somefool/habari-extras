<?php
/*
 * Recent Comments Plugin
 * Usage: <?php $theme->show_recentcomments(); ?> 
 * A sample recentcomments.php template is included with the plugin.  This can be copied to your 
 * active theme and modified to fit your preference.
 */

Class RecentComments extends Plugin
{	
	/**
	 * Implement the update notification feature
	 */
  	public function action_update_check()
  	{
    	Update::add( 'RecentComments', '6d49a362-db63-11dc-95ff-0800200c9a66',  $this->info->version );
  	}
	
  	/**
  	 * Set default values for unset option
  	 *
  	 * @param unknown_type $file
  	 */
	public function action_plugin_activation( $file )
	{	
		$default_options = array (
			'title' => 'Recent Comments',
			'format' => '[[user]] on [[post]]',
			'dateformat' => 'Mj n:ia',
			'count' => '5'
			);
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			foreach ( $default_options as $name => $value ) {
				$current_value = Options::get( 'recentcomments__' . $name );
				if ( !isset( $current_value) ) Options::set( 'recentcomments__' . $name, $value );
			}
		}
	}
	
	/**
	 * Creates a UI form to handle the plguin configurations
	 * @return FormUI The configuration form
	 */
	public function configure()
	{
		$form = new FormUI( 'recentcomments' );
		$form->append( 'text', 'title', 'option:recentcomments__title', 'Title: ' );
		$form->append( 'text','format', 'option:recentcomments__format','List item format (use [[user]], [[post]] and/or [[date]]): ' );
		$form->format->add_validator( 'validate_required' );
		$form->append( 'text','dateformat', 'option:recentcomments__dateformat','Date format <i>(if [[date]] is used)</i>: ' );
		$form->append( 'text','count', 'option:recentcomments__count','Number of comments to display:' );
		$form->count->add_validator( 'validate_required' );
		$form->append( 'submit', 'save', 'Save' );
		return	$form;
	}
	
	/**
	 * Compiles and formats the recent comments list
	 *
	 * @return string An HTML unorderd list of the recent comments
	 */
	public function theme_show_recentcomments( $theme ){
		//Get the plugin options
		$limit = Options::get(strtolower(get_class($this)) . '__count' );
		$format = Options::get( strtolower(get_class( $this ) ) . '__format' );
		$dateformat =Options::get(strtolower(get_class($this)) . '__dateformat' );
		$theme->recentcomments_title = Options::get(strtolower(get_class($this)) . '__title' );
		
		$status =Comment::STATUS_APPROVED;
		$commentarray =array('limit'=>$limit, 'status'=>$status, 'type'=>Comment::COMMENT, 'orderby'=>'date DESC');
		$comments =Comments::get($commentarray);
		
		$list = array();
		foreach ($comments as $comment){
			$name ='<a href="'.$comment->url.'" rel="external">'.$comment->name.'</a>';
			$post ='<a href="'.$comment->post->permalink.'">'.$comment->post->title.'</a>';
			$post = '<a href="'.$comment->post->permalink.'#comment-'.$comment->id.'">'.$comment->post->title.'</a>';
			$datearray =date_parse($comment->date);
			$date =date($dateformat,mktime($datearray['hour'],$datearray['minute'],0,$datearray['month'],$datearray['day'],$datearray['year']));
			$list[]="<li>".str_replace('[[user]]',$name, str_replace('[[post]]',$post,str_replace('[[date]]',$date,$format)))."</li>\n";
		}
		$theme->recentcomments_links = $list;
		return $theme->fetch( 'recentcomments' );

	}

	public function action_init()
	{
		$this->add_template('recentcomments', dirname(__FILE__) . '/recentcomments.php');
	}
	


}
?>
