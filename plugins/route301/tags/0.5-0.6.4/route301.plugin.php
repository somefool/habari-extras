<?php

	class Route301 extends Plugin
	{
		/* Add your custom rules here.
		 *
		 * You have to use the value `Route301` as the handler for your redirecting rules.
		 * You have to use the key of your $redirect_rules as the action for your redirecting rules.
		 */
		public $custom_rules = array(
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
				'description' => 'Wordpress entry id based permalink',
				'priority' => 10
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
				'version' => '0.6.4',
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

			foreach ( $this->custom_rules as $rule_name => $paramarray ) {
				$option_name = 'route301__' . $rule_name;

				// a turned off option is an empty string. 1 == enabled, null == no actual options value (for backwards compatibility)
				if ( Options::get( $option_name ) !== '' ) {
					$paramarray = array_merge( $defaults, $paramarray );
					$db_rules[] = new RewriteRule( $paramarray );
				}
			}
			return $db_rules;
		}

		/* Act function called by Controller, required for an handler.
		 * Redirects the request to a user-defined rule with a header 301 (moved permanently).
		 */
		public function act( $action )
		{
			if ( $action === 'atom_feed' ) {
				$url = URL::get( 'atom_feed', array_merge( array( 'index' => 1 ), $this->handler_vars ), false );
			} else
			if ( $action === 'display_entry' ) {
				if ( isset( $this->handler_vars['slug'] ) ) {
					$url = URL::get( 'display_entry', $this->handler_vars, false );
				} else {
					$url = Post::get( $this->handler_vars )->permalink;
				}
			} else {
				$url = URL::get( $action, $this->handler_vars, false );
			}

			header( 'Location: ' . $url, true, 301 );
		}

		public function action_update_check()
		{
			Update::add( 'Route301', 'b4bf3419-518f-27c4-c18d-ab9786936f21', $this->info->version );
		}

		public function filter_plugin_config( $actions, $plugin_id )
		{
			if ( $plugin_id == $this->plugin_id() ) {
				$actions[] = _t( 'Configure' );
			}

			return $actions;
		}

		public function action_plugin_ui( $plugin_id, $action )
		{
			if ( $plugin_id == $this->plugin_id() ) {
				if ( $action == _t( 'Configure' ) ) {
					$class_name = strtolower( get_class( $this ) );

					$form = new FormUI( $class_name );

					$form->append( 'fieldset', 'post_options', 'Post Options' );
					$form->append( 'fieldset', 'feed_options', 'Feed Options' );

					foreach ( $this->custom_rules as $rule ) {
						$rule['build_str'] = preg_replace( '/\([^)]+\)/', '', $rule['build_str'] );
						$rule['build_str'] = str_replace( array( '{$', '}' ), array( '<', '>' ), $rule['build_str'] );
						$rule['build_str'] = trim( $rule['build_str'], '/' );
						$rule['build_str'] = htmlspecialchars( $rule['build_str'] );
						if ( $rule['action'] === 'display_entry' ) {
							$form->post_options->append( 'checkbox', $rule['name'], 'route301__' . $rule['name'], sprintf( _t( 'Route <code>%s</code> URLs' ), $rule['build_str'] ) );
						}
						else {
							$form->feed_options->append( 'checkbox', $rule['name'], 'route301__' . $rule['name'], sprintf( _t( 'Route <code>%s</code> URLs' ), $rule['build_str'] ) );
						}
					}
					$form->append( 'submit', 'save', _t( 'Save' ) );

					$form->on_success( array( $this, 'updated_config' ) );
					$form->out();
				}
			}
		}

		public function updated_config( $form )
		{
			$form->save();
		}

		public function action_plugin_activation( $file )
		{
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				// default options: name => default value
				foreach ( $this->custom_rules as $rule ) {
					$options['route301__' . $rule['name']] = 1;
				}

				foreach ( $options as $option => $value ) {
					if ( Options::get( $option ) == null ) {
						Options::set( $option, $value );
					}
				}
			}
		}
	}

?>
