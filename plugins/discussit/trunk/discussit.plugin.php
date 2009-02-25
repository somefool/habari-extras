<?php

class Discussit extends Plugin
{ 
	
	static $regex_name= '/@([A-Za-z0-9]+)/';
	static $regex_url= '/@\<a href="(.+)#comment-([0-9]+)"\>([A-Za-z0-9]+)\<\/a\>/';
	
	/**
	 * Required plugin info() implementation provides info to Habari about this plugin.
	 */ 
	public function info()
	{
		return array (
			'name' => 'DiscussIT',
			'url' => 'http://habariproject.org',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => 0.1,
			'description' => 'Enables discussion and threading features for comments.',
			'license' => 'ASL 2.0',
		);
	}

	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, '95d9b76a-ca6c-f5d4-9551-e8c0b020e097', $this->info->version );
	}
	
	/**
	 * Find referenced comments
	 */
	private static function get_parents($comment, $content, $comments) {		
		$parents= array();
		
		$params= array(
			'post_id' => $comment->post->id,
			'limit' => 1,
			'orderby' => 'date DESC',
			'where' => array('date < ' . $comment->date->sql)
		);
		
		// Check for name
		preg_match_all(self::$regex_name, $content, $matches);
		
		$matches= $matches[1];
		
		foreach($matches as $name) {
			$matched_comments= Comments::get(array_merge($params, array('criteria_fields' => array('name'), 'criteria' => $name)));
			if(count($matched_comments) > 0) {
				$parents[]= $matched_comments[0];
			}
		}
		
		// Check for url
		$matches= array();
		
		preg_match_all(self::$regex_url, $content, $matches);
		
		$ids= $matches[2];
		
		foreach($ids as $id) {
			$matched_comments= Comments::get(array_merge($params, array('id' => $id)));
			if(count($matched_comments) > 0) {
				$parents[]= $matched_comments[0];
			}
		}
		
		return $parents;
	}
	
	/**
	 * Remove all relationships
	 */
	private function remove_relationships($comment, $parents) {
		foreach($parents as $parent) {
			if($parent->info->children) {
				$children= unserialize($parent->info->children);
				unset($children[$comment->id]);
								
				$parent->info->children= serialize($children);
				$parent->update();
			}
		}
	}
	
	/**
	 * Create relationships as needed
	 */
	private function create_relationships($comment, $parents, $update = true) {
		
		$parents_save= array();
		foreach($parents as $parent) {
			if($parent->info->children) {
				$children= unserialize($parent->info->children);
				$children[$comment->id]= $comment->id;
			} else {
				$children= array($comment->id => $comment->id);
			}
			
			$parent->info->children= serialize($children);
			$parent->update();
			
			$parents_save[$parent->id]= $parent->id;
		}
		
		$comment->info->parents= serialize($parents_save);
		
		if($update) {
			$comment->update();
		}
		
	}
	
	/**
	 * Test old comments for relationships
	 * This will take a loooooong time
	 */
	public function action_plugin_activation( $plugin_file )
	{
		
		$comments= Comments::get(array('nolimit' => true));
		
		foreach($comments as $comment) {
			$parents= self::get_parents($comment, $comment->content, $comment->post->comments);
			
			$this->create_relationships($comment, $parents);
		}
	}
	
	/**
	 * Remove all comment relationships
	 */
	public function action_plugin_deactivation( $plugin_file )
	{
		DB::delete(DB::table('commentinfo'), array('name' => 'parents'));
		DB::delete(DB::table('commentinfo'), array('name' => 'children'));
	}
	
	/**
	* Catch new comments
	**/
	public function action_comment_insert_after($comment)
	{
		$parents= self::get_parents($comment, $comment->content, $comment->post->comments);
		
		$this->create_relationships($comment, $parents);
	}
	
	/**
	 * Clear and update relationships
	 */
	public function action_comment_update_content($comment, $old, $new) {
		if(is_object($new)) {
			$new= $new->value;
		}
		
		// Severe old ties
		$this->remove_relationships($comment, $comment->parents);
		
		// Create new ties
		$parents= self::get_parents($comment, $new, $comment->post->comments);
		$this->create_relationships($comment, $parents, false);
	}
	
	/**
	 * Magically enable use of parents
	 */
	public function filter_comment_parents($value, $comment) {
		$parent_ids= unserialize($comment->info->parents);
		
		if(!is_array($parent_ids)) {
			return array();
		}
		
		$parents= Comments::get(array('id' => $parent_ids, 'nolimit' => true, 'orderby' => 'date ASC'));
				
		return $parents;
	}
	
	/**
	 * Magically enable use of children
	 */
	public function filter_comment_children($value, $comment) {
		$child_ids= unserialize($comment->info->children);
		
		if(!is_array($child_ids)) {
			return array();
		}
		
		$children= Comments::get(array('id' => $child_ids, 'nolimit' => true, 'orderby' => 'date ASC'));
				
		return $children;
	}
	
	/**
	 * Linkify parents and children
	 */
	public function filter_comment_content_out($content, $comment) {
		
		preg_match_all(self::$regex_name, $content, $matches);
		
		$matches= $matches[1];
		
		foreach($matches as $name) {
			$params= array(
				'post_id' => $comment->post->id,
				'limit' => 1,
				'orderby' => 'date DESC',
				'where' => array('date < ' . $comment->date->sql),
				'criteria_fields' => array('name'),
				'criteria' => $name
			);
			$matched_comments= Comments::get($params);
			if(count($matched_comments) > 0) {
				$parent= $matched_comments[0];
				$content= str_replace('@' . $name, '@<a href="' . $comment->post->permalink . '#comment-' . $parent->id . '" title="This comment is in response to ' . $parent->name . '">' . $name . '</a>', $content);
			}
		}
				
		return $content;
	}
	
	/**
	 * Show stuff in the comment form
	 */
	public function action_form_comment_edit($form, $comment) {
		$form->comment_controls->append('fieldset', 'discussion_tab', _t('Discussion'));
		$discussion = $form->discussion_tab->append('wrapper', 'discussion');
		$discussion->class = 'container';
		
		if(count($comment->parents) > 0) {
			$parents= array();
			foreach($comment->parents as $parent) {
				$parents[]= '<a href="' . URL::get('admin', 'page=comment&id=' . $parent->id) . '">' . $parent->name . '</a>';
			}
			$str= 'This comment is descended from comments by ' . Format::and_list($parents) . '.';
		} else {
			$str= 'This comment has no parents.';
		}
		$discussion->append('static', 'parents', '<div class="container"><p class="pct25">'._t('Parents').'</p><p>' . $str . '</p></div><hr />');
		
		if(count($comment->children) > 0) {
			$children= array();
			foreach($comment->children as $child) {
				$children[]= '<a href="' . URL::get('admin', 'page=comment&id=' . $child->id) . '">' . $child->name . '</a>';
			}
			$str= 'There are children of this comment by ' . Format::and_list($children) . '.';
		} else {
			$str= 'This comment has no children.';
		}
		$discussion->append('static', 'children', '<div class="container"><p class="pct25">'._t('Children').'</p><p>' . $str . '</p></div><hr />');
	}

}	

?>