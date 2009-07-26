<?php

class PlaintextPlugin extends Plugin
{

	/**
	 * Filter the rewrite rules to add our own new rule based on the existing display_entry rule
	 * @params array $rules An array of RewriteRule objects representing all active rules
	 * @return array An array of RewriteRule objects, including our new rule
	 */	 	 	 	
	function filter_rewrite_rules($rules)
	{
		static $ruleisset = false;
		
		if($ruleisset) {
			return $rules;
		}
		
		foreach($rules as $rule) {
			if($rule->name == 'display_entry') {
				$editrule = clone $rule;
				$delimiter = $editrule->parse_regex[0];
				$insertion_point = strrpos($editrule->parse_regex, $delimiter);
				if($editrule->parse_regex[$insertion_point-1] == '$') {
					$insertion_point--;
				}
				$editrule->parse_regex = substr($editrule->parse_regex, 0, $insertion_point) . '.text' . substr($editrule->parse_regex, $insertion_point);
				$editrule->priority--;
				$editrule->action = 'plaintext';
				$editrule->handler = 'PluginHandler';
				
				$rules[] = $editrule;
			}
		}
		
		$ruleisset = true;
		
		return $rules;
	}	
	
	/**
	 * Respond to the URL that was created
	 * Determine the post that was supposed to be displayed, and show it in raw
	 * @params array $handlervars An array of values passed in from the URL requested
	 */	 	 	 	
	function action_plugin_act_plaintext($handlervars)
	{
		$activetheme = Themes::create();

		$user_filters = array(
		 'fetch_fn' => 'get_row',
		 'limit' => 1,
		);

		$page_key = array_search( 'page', $activetheme->valid_filters );
		unset( $activetheme->valid_filters[$page_key] );

		$user_filters = Plugins::filter( 'template_user_filters', $user_filters );
		$user_filters = array_intersect_key( $user_filters, array_flip( $activetheme->valid_filters ) );

		$where_filters = Controller::get_handler()->handler_vars->filter_keys( $activetheme->valid_filters );

		$where_filters = $where_filters->merge( $user_filters );
		$where_filters = Plugins::filter( 'template_where_filters', $where_filters );

		$post = Posts::get( $where_filters );
		$current_url = URL::get();
		$created_at = $post->pubdate->get();
		
		header('Content-type: text/plain; charset=utf-8');
		echo <<<HERE
# {$post->title}

  By {$post->author->displayname}
  <{$current_url}>
  {$created_at}
	
{$post->content}
HERE;
		
		exit;
	}
	
}
?>