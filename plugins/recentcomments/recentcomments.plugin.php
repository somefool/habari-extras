<?php

Class RecentComments extends Plugin
{
	private $recent_comments;
	
	/**
	 * Provides Plugin information.
	 **/	
	public function info()
	{
		return array(
		'name'=>'Recent Comments',
		'version'=>'1.1',
		'url'=>'http://habariproject.org/',
		'author'=>'Habari Community',
		'authorurl'=>'http://habariproject.org/',
		'license'=>'Apache License 2.0',
		'description'=>'Displays the most recent comments in your Habari blog'
		);
	}
	
	/**
	 * Implement the update notification feature
	 */
  	public function action_update_check()
  	{
    	Update::add( 'RecentComments', '6d49a362-db63-11dc-95ff-0800200c9a66',  $this->info->version );
  	}
	
  	/**
  	 * Initializes the plugin's output variable when this plugin initializes 
  	 */
	public function action_init()
	{	
		$recent_comments='';
	}
	
	/**
	 * Adds a Configure action to the plugin
	 * 
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The id of a plugin
	 * @return array The array of actions
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= 'Configure';		
		}
		return $actions;
	}
	
	/**
	 * Creates a UI form to handle the plguin configurations
	 *
	 * @param string $plugin_id The id of a plugin
	 * @param array $actions An array of actions that apply to this plugin
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id && $action=='Configure' ){
			$form= new FormUI( strtolower(get_class( $this ) ) );
			$format= $form->add( 'text','format','List item format (use [[user]], [[post]] and/or [[date]]):','[[user]] on [[post]]' );
			$format->add_validator( 'validate_required' );
			$dateformat= $form->add( 'text','dateformat','Date fomrat <i>(if [[date]] is used)</i>:','Mj n:ia' );
			$count= $form->add( 'text','count','Number of comments to display:','5' );
			$count->add_validator( 'validate_required' );
			$withul= $form->add('checkbox','withul','Wrap the list with &lt;ul&gt;&lt;/ul&gt;',true );
			$form->on_success( array( $this, 'saved_config' ) );
			$form->out();
			}
	}
	
	/**
	 * Invoked when the before the plugin configurations are saved
	 *
	 * @param FormUI $form The configuration form being saved
	 * @return true
	 */
	public function saved_config( $form )
	{   
		return true;
	}
	
	/**
	 * Makes the variable holding the recent comments list available to the theme
	 *
	 * @param Theme $theme The theme to add the variable to
	 */
	public function action_add_template_vars( $theme ) {
		$theme->assign ('recent_comments',$this->get_recent_comments());
	}
	
	/**
	 * Compiles and formats the recent comments list
	 *
	 * @return string An HTML unorderd list of the recent comments
	 */
	public function get_recent_comments(){
		//Get the plugin options
		$limit= Options::get(strtolower(get_class($this)) . ':count' );
		$format= Options::get( strtolower(get_class( $this ) ) . ':format' );
		$dateformat=Options::get(strtolower(get_class($this)) . ':dateformat' );
		$withul=Options::get(strtolower(get_class($this)) . ':withul' );
		
		//Assign default values if options not set
		if (!$withul) $ul=array('','');
		else $ul=array("<ul>\n","</ul>\n");
		if (empty($limit)) $limit='5';
		if (empty($format)) $format='[[user]] on [[post]]';
		if (empty($dateformat)) $dateformat='Mj n:ia';
		
		$status=Comment::STATUS_APPROVED;
		$commentarray=array('limit'=>$limit, 'status'=>$status, 'type'=>Comment::COMMENT, 'orderby'=>'date DESC');
		$comments=Comments::get($commentarray);
		$list=$ul[0];
		
		foreach ($comments as $comment){
			$name='<a href="'.$comment->url.'">'.$comment->name.'</a>';
			$post='<a href="'.$comment->post->permalink.'" rel="external">'.$comment->post->title.'</a>';
			$datearray=date_parse($comment->date);
			$date=date($dateformat,mktime($datearray['hour'],$datearray['minute'],0,$datearray['month'],$datearray['day'],$datearray['year']));
			$list.="<li>".str_replace('[[user]]',$name, str_replace('[[post]]',$post,str_replace('[[date]]',$date,$format)))."</li>\n";
		}
		
		$list.=$ul[1];
		
		return $list;
	}
	
}
?>