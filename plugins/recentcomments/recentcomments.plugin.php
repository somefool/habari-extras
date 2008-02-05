<?php
Class RecentComments extends Plugin
{
	private $recent_comments;
		
	public function info()
	{
		return array(
		'name'=>'Recent Comments',
		'version'=>'1.0',
		'url'=>'http://habariproject.org/',
		'author'=>'Ali B.',
		'authorurl'=>'http://awhitebox.com',
		'license'=>'Apache License 2.0',
		'description'=>'Displays the most recent comments in your Habari blog'	
		);
	}

public function action_init()
	{
		//initalize: nothing needed here, so far
	}
	
	public function filter_plugin_config( $actions, $plugin_id)
	{
		
		if ( $this->plugin_id()==$plugin_id ){
			$actions[]='Configure';		
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id && $action=='Configure'){
			$form=new FormUI(strtolower(get_class($this)));
			$format=$form->add('text','format','List item format (use [[user]], [[post]] and/or [[date]]):','[[user]] on [[post]]');
			$format->add_validator('validate_required');
			$dateformat=$form->add('text','dateformat','Date fomrat <i>(if [[date]] is used)</i>:','Mj n:ia');
			$count=$form->add('text','count','Number of comments to display:','5');
			$count->add_validator('validate_required');
			$withul=$form->add('checkbox','withul','Wrap the list with 	&lt;ul&gt;&lt;/ul&gt;',true);
			$form->on_success( array( $this, 'saved_config' ) );
			$form->out();
			}
	}
	
	public function saved_config($form)
	{   
		return true;
	}
	
	public function action_add_template_vars( $theme ) {
		$theme->assign ('recent_comments',$this->get_recent_comments());
	}
	
	public function get_recent_comments(){

		$limit=Options::get(strtolower(get_class($this)) . ':count');
		$format=Options::get(strtolower(get_class($this)) . ':format');
		$dateformat=Options::get(strtolower(get_class($this)) . ':dateformat');
		$withul=Options::get(strtolower(get_class($this)) . ':withul');
		if ($withul==false) $ul=array('','');
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