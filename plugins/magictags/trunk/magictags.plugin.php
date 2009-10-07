<?php
class MagicTags extends Plugin
{
	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'MagicTags', '1455ba80-3bcd-11dd-ae16-0800200c9a66', $this->info->version );
	}
	
	/**
	 * filters display of tags for posts to hide any that begin with "@" from display
	 **/
	 
	public function filter_post_tags_out( $tags )
	{
		$tags= array_filter($tags, create_function('$a', 'return $a{0} != "@";'));
		$tags= Format::tag_and_list($tags);
		return $tags;
	}

	/**
	 * Displays a list of all tags used on the site except those begining with "@" as a comma seperated linked list.
	 **/
	 
	public function magic_site_tags()
	{
		$tagcount= 0;
		foreach(DB::get_results('SELECT * FROM ' . DB::table('tags'). ' ORDER BY tag_text ASC') as $tag) {
			if (substr($tag->tag_text, 0, 1)== "@") {continue;}
			if ($tagcount!= 0) {echo ", ";}
			echo "<a href=\"" . URL::get('display_posts_by_tag', 'tag=' . $tag->tag_slug) . "\">{$tag->tag_text}</a>";
			$tagcount++;
		}	
	}
}

?>
