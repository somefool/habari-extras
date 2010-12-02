<?php
/**
 * A plugin to hide spoilers until a conscious action on the part of the user.
 *
 **/
class UpdatePosts extends Plugin
{
	/**
	 * Do nothing yet on activation
	 *
	 **/
	public function action_plugin_activation($file)
	{
		
	}
	
	// Add configuration here

	
	/**
	 * Do nothing yet on init
	 **/
	public function action_init()
	{
		
	}

	/**
	 * Publish post when old post is updated. This is invoked by a change in
	 * the 'updated' date, which is only changed when 'minor edit' is not set.
	 **/
	public function action_post_update_updated($post)
	{
		if($post->status == "published" && $post->pubdate != $post->modified)
		{
			$test = new Post();
			$test->title = $post->title." modified by ".$post->author->displayname;
			$test->user_id = $post->author->id;
			$test->content = "The following post has been modified significantly: <a href='".$post->permalink."'>".$post->title."</a>";
			$test->insert();
			$test->publish();
		}
	}
	
	// Add Beacon support here
}

?>
