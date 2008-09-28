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
		if ( $action === 'atom_feed' ) {
			$url = URL::get( 'atom_feed', array_merge( array( 'index' => 1 ), $this->handler_vars ) );
		} else
		if ( $action === 'display_entry' ) {
			if ( isset( $this->handler_vars['slug'] ) ) {
				$url = URL::get( 'display_entry', $this->handler_vars );
			} else {
				$url = Post::get( $this->handler_vars )->permalink;
			}
		} else {
			$url = URL::get( $action, $this->handler_vars );
		}

		header( 'Location: ' . $url, true, 301 );
	}
}
?>
