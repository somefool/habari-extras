<?php
class Route301 extends Plugin
{
	/* Add your custom rules here.
	 *
	 * You have to use the value `Route301` as the handler for your redirecting rules.
	 * You have to use the key of your $redirect_rules as the action for your redirecting rules.
	 */
	var $custom_rules = array(
		'display_entry_by_date_and_slug' => array( // Wordpress date and slug based permalink: 2007/09/17/<slug>
			'name' => 'display_entry_by_date_and_slug',
			'parse_regex' => '%^(?P<year>[1,2]\d{3})/(?P<month>\d{2})/(?P<day>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+)/?)?$%i',
			'build_str' => '{$year}/{$month}/{$day}/{$slug}/(page/{$page}/)',
			'action' => 'display_entry',
			'description' => 'Wordpress date and name based permalink'
			),
		'display_entry_by_month_and_slug' => array( // Wordpress month and slug based permalink: 2007/09/<slug>
			'name' => 'display_entry_by_month_and_slug',
			'parse_regex' => '%^(?P<year>[1,2]\d{3})/(?P<month>\d{2})/(?P<slug>[^/]+)(?:/page/(?P<page>\d+)/?)?$%i',
			'build_str' => '{$year}/{$month}/{$slug}/(page/{$page}/)',
			'action' => 'display_entry',
			'description' => 'Wordpress month and name based permalink'
			),
		'display_entry_by_id' => array( // Wordpress entry id based permalink: archives/<id>
			'name' => 'display_entry_by_id',
			'parse_regex' => '%^archives/(?P<id>\d+)(?:/page/(?P<page>\d+))?/?$%i',
			'build_str' => 'archives/{$id}(/page/{$page})',
			'action' => 'display_entry',
			'description' => 'Wordpress entry id based permalink'
			),
		'display_entry_by_tag_and_slug' => array( // Wordpress tag and slug based permalink: <tag>/<slug>
			'name' => 'display_entry_by_tag_and_slug',
			'parse_regex' => '%^(?P<tag>[^/]+)/(?P<slug>[^/]+)(?:/page/(?P<page>\d+))?/?$%i',
			'build_str' => '{$tag}/{$slug}(/page/{$page})',
			'action' => 'display_entry',
			'description' => 'Wordpress tag and name based permalink',
			'priority' => 10
			),
		'wordpress_feed' => array( // Wordpress RSS/Atom feed
			'name' => 'wordpress_feed',
			'parse_regex' => '%^feed/?$%i',
			'build_str' => 'feed',
			'action' => 'atom_feed',
			'description' => 'Wordpress RSS/Atom feed'
			),
		'wordpress_tag_feed' => array( // Wordpress tag feed
			'name' => 'wordpress_tag_feed',
			'parse_regex' => '%^tag/(?P<tag>[^/]+)/feed/?$%i',
			'build_str' => 'tag/{$tag}/feed',
			'action' => 'atom_feed_tag',
			'description' => 'Wordpress tag feed'
			),
		'wordpress_comments_feed' => array( // Wordpress comments feed
			'name' => 'wordpress_comments_feed',
			'parse_regex' => '%^comments/feed/?$%i',
			'build_str' => 'comments/feed',
			'action' => 'atom_feed_comments',
			'description' => 'Wordpress comments feed'
			)
		);

	/* Custom callback functions.
	 * Functions called to add handler_vars values needed by custom rules.
	 */
	var $callback_functions = array(
		'get_date', // Extracts the year, month and day from a post
		'feed_index',
		);


	/* Information function called by the plugin manager. */
	public function info() {
		return array(
			'name' => 'Route 301',
			'version' => '0.5.2',
			'url' => 'http://habariproject.org/',
			'author' =>	'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Assign user-defined rules to existing or custom rules.',
			'copyright' => '2007'
			);
	}

	/* Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules.
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$defaults = array(
			'handler' => 'Route301',
			'priority' => 1,
			'is_active' => 1,
			'rule_class' => RewriteRule::RULE_CUSTOM,
			'description' => 'Custom Route 301 rule.'
			);

		foreach ( $this->custom_rules as $paramarray ) {
			$paramarray = array_merge( $defaults, $paramarray );
			$db_rules[] = new RewriteRule( $paramarray );
		}
		return $db_rules;
	}

	/* Act function called by Controller, required for an handler.
	 * Redirects the request to a user-defined rule with a header 301 (moved permanently).
	 */
	public function act( $action )
	{
		$handler_vars = Controller::get_handler()->handler_vars;
		$callback_vars = array();
		foreach( $this->callback_functions as $callback ) {
			$callback_vars = array_merge( $callback_vars, $this->$callback($handler_vars) );
		}
		$handler_vars = array_merge( $handler_vars, $callback_vars );
		$url = URL::get( $action, $handler_vars, false );

		if ( empty( $url ) && method_exists( $this, $action ) ) {
			$url = $this->$action( $handler_vars );
		}
		if ( empty( $url ) ) {
			$url = URL::get( 'display_entry', $handler_vars, false );
		}

		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		header("Connection: close");
	}

	/* Get_date callback function.
	 * Extracts the year, month and day from a pubdate and returns it to be added to handler_vars.
	 */
	public function get_date( $handler_vars )
	{
		$posts = Posts::get( $handler_vars );
		if ( isset( $posts[0] ) ) {
			$pubdate = strtotime( $posts[0]->pubdate );
			$paramarray = array(
				'year' => date( 'Y', $pubdate ),
				'month' => date( 'm', $pubdate ),
				'day' => date( 'd', $pubdate ),
				);

			$handler_vars = array_merge( $paramarray, $handler_vars );
			return $handler_vars;
		}
		else {
			return false;
			}
		}

	/* feed_index callback function
	 * Used to pass the $index variable to atom_feed
	 */
	public function feed_index ( $handler_vars )
	{
		return array_merge( array( 'index' => 1 ), $handler_vars );
	}

}
?>
