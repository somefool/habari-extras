<?php

class RandomPostLink extends Plugin
{
	function filter_rewrite_rules($rules)
	{
		$rules[] = RewriteRule::create_url_rule('"randompost"', 'PluginHandler', 'randompost');
		return $rules;
	}
	
	function action_plugin_act_randompost($handler)
	{
		$criteria = array(
			'status' => Post::status('published'), 
			'type' => Post::type('entry'),
			'orderby' => 'RAND()',
			'limit' => 1,
		);
		$post = Post::get($criteria);
		
		Utils::redirect($post->permalink);
	}
}
?>