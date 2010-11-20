<?php

/**
 * FeedList Plugin - Makes feeds available for output in a <ul> from a theme.
 * 
 * To display the feed list in the template output, add this code where the 
 * list should be displayed:
 * <code>
 * <?php echo $feedlist; ? >
 * </code>
 */ 

class FeedList extends Plugin
{ 

	
	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( 'Feed List', '9a75f180-3da2-11dd-ae16-0800200c9a66', $this->info->version );
	}

	/**
	 * Plugin init action, executed when plugins are initialized.
	 */ 
	public function action_init()
	{
		// Register the name of a new database table.
		DB::register_table('feedlist');
	}

	/**
	 * Plugin plugin_activation action, executed when any plugin is activated
	 * @param string $file The filename of the plugin that was activated.
	 */ 
	public function action_plugin_activation( $file ='' )
	{
		// Was this plugin activated?
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) { 
		// Register a default event log type for this plugin
		EventLog::register_type( "default", "FeedList" );
		// Register the name of a new database table
		DB::register_table('feedlist');// 'plugin_activation' hook is called without first calling 'init'
		// Create the database table, or upgrade it
		
		switch ( DB::get_driver_name() ) {
			case 'mysql':
				$schema = 'CREATE TABLE {feedlist} (
				id INT UNSIGNED AUTO_INCREMENT,
				feed_id INT NOT NULL DEFAULT 0,
				guid VARCHAR(255) NOT NULL,
				title VARCHAR(255) NOT NULL,
				link VARCHAR(255) NOT NULL,
				updated DATETIME NOT NULL,
				description TEXT,
				PRIMARY KEY (id),
				UNIQUE KEY guid (guid)
				);';
				break;
			case 'sqlite':
				$schema = 'CREATE TABLE {feedlist} (
				id INTEGER PRIMARY KEY AUTOINCREMENT, 
				feed_id INTEGER NOT NULL DEFAULT 0,
				guid VARCHAR(255) UNIQUE NOT NULL,
				title VARCHAR(255) NOT NULL,
				link VARCHAR(255) NOT NULL,
				updated DATETIME NOT NULL,
				description TEXT
				);';
				break;

		}
		DB::dbdelta( $schema );

		// Log a table creation event
		EventLog::log('Installed feedlist cache table.');
		// Add a periodical execution event to be triggered hourly
		CronTab::add_hourly_cron( 'feedlist', 'load_feeds', 'Load feeds for feedlist plugin.' );
		// Log the cron creation event
		EventLog::log('Added hourly cron for feed updates.');
		}
	}

	/**
	 * Plugin plugin_deactivation action, executes when any plugin is deactivated.
	 * @param string $plugin_id The filename of the plguin that was deactivated.
	 */

	public function action_plugin_deactivation( $file )
	{
		// Was this plugin deactivated?
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			// Drop the database table
			DB::query( 'DROP TABLE IF EXISTS {feedlist};');
			// Log a dropped table event
			EventLog::log('Removed feedlist cache table.');
			// Remove the periodical execution event
			CronTab::delete_cronjob( 'feedlist' );
			// Log the cron deletion event.
			EventLog::log('Deleted cron for feed updates.');
		}
	}

	/**
	 * Executes when the admin plugins page wants to know if plugins have configuration links to display.
	 * 
	 * @param array $actions An array of existing actions for the specified plugin id. 
	 * @param string $plugin_id A unique id identifying a plugin.
	 * @return array An array of supported actions for the named plugin
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		// Is this plugin the one specified?
		if($plugin_id == $this->plugin_id()) {
			// Add a 'configure' action in the admin's list of plugins
			$actions[]= 'Configure';
		}
		return $actions;
	}
	
	/**
	 * Executes when the admin plugins page wants to display the UI for a particular plugin action.
	 * Displays the plugin's UI.
	 * 
	 * @param string $plugin_id The unique id of a plugin
	 * @param string $action The action to display
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		// Display the UI for this plugin?
		if($plugin_id == $this->plugin_id()) {
			// Depending on the action specified, do different things
			switch($action) {
			// For the action 'configure':
			case 'Configure':
				// Create a new Form called 'feedlist'
				$ui = new FormUI( 'feedlist' );
				// Add a text control for the feed URL
				$feedurl = $ui->append('textmulti', 'feedurl', 'feedlist__feedurl', 'Feed URL');
				// Mark the field as required
				$feedurl->add_validator( 'validate_required' );
				// Mark the field as requiring a valid URL
//				$feedurl->add_validator( 'validate_url' );
				// When the form is successfully completed, call $this->updated_config()
				$ui->on_success( array( $this, 'updated_config') );
				$ui->set_option( 'success_message', _t( 'Configuration updated' ) );
				// Display the form
				$ui->append( 'submit', 'save', _t( 'Save' ) );
				$ui->out();
				break;
			}
		}
	}
	
	/**
	 * Perform actions when the admin plugin form is successfully submitted. 
	 * 
	 * @param FormUI $ui The form that successfully completed
	 * @return boolean True if the normal processing should occur to save plugin options from the form to the database
	 */
	public function updated_config( $ui )
	{
		$ui->save();

		// Delete the cached feed data
		DB::query( 'TRUNCATE {feedlist}' );
		
		// Reset the cronjob so that it runs immediately with the change
		CronTab::delete_cronjob( 'feedlist' );
		CronTab::add_hourly_cron( 'feedlist', 'load_feeds', 'Load feeds for feedlist plugin.' );

		return false;
	} 
	
	/**
	 * Plugin add_template_vars action, executes just before a template is to be rendered.
	 * Note that $theme and $handler_vars are passed by reference, and so you can add things to them.
	 * @param Theme $theme The theme object that is displaying the template.
	 * @param array $handler_vars Variables passed in the URL to the action handler.
	 */ 
	public function action_add_template_vars( $theme, $handler_vars )
	{
		// Get the most recent ten items from each feed
		$feedurls = Options::get( 'feedlist__feedurl' );
		if ( $feedurls ) {
			$feeds = array();
			$feeditems = array();
			foreach( $feedurls as $index=>$feedurl ) {
				$items = DB::get_results( 'SELECT * FROM {feedlist} WHERE feed_id = ? ORDER BY updated DESC LIMIT 10', array($index) );

				// If there are items to display, produce output
				if(count($items)) {
					$feed = "<ul>\n";
				
					foreach ( $items as $item ) {
						$feed.= sprintf( 
							"\t" . '<li><a href="%1$s">%2$s</a></li>' . "\n", 
							$item->link, 
							$item->title
						);
					}
				
					$feed.= "</ul>\n";
				}
				else {
					$feed = '<p>Sorry, no items to display.</p>';
				}
				$feeds[] = $feed;	
				$feeditems = array_merge($feeditems, $items);
			}
			// Assign the output to the template variable $feedlist

			//<? echo $feedlist[0];? >// This will output the first feed list in the template 
			$theme->assign( 'feedlist', $feeds );
			$theme->assign( 'feeditems', $feeditems ); 
		} else {
			$theme->assign( 'feedlist', "Feedlist needs to be configured." );	
		}
	}

	/**
	 * Plugin load_feeds filter, executes for the cron job defined in action_plugin_activation()
	 * @param boolean $result The incoming result passed by other sinks for this plugin hook
	 * @return boolean True if the cron executed successfully, false if not.
	 */ 	
	public function filter_load_feeds( $result )
	{
		$feedurls = Options::get( 'feedlist__feedurl' );

		foreach( $feedurls as $feed_id => $feed_url ) {
			
			if ( $feed_url == '' ) {
				EventLog::log( sprintf( _t('Feed ID %1$d has an invalid URL.'), $feed_id ), 'warning', 'feedlist', 'feedlist' );
				continue;
			}
			
			// load the XML data
			$xml = RemoteRequest::get_contents( $feed_url );
			
			if ( !$xml ) {
				EventLog::log( sprintf( _t('Unable to fetch feed %1$s data.'), $feed_url ), 'err', 'feedlist', 'feedlist' );
			}
			
			$dom = new DOMDocument();
			// @ to hide parse errors
			@$dom->loadXML( $xml );
			
			if ( $dom->getElementsByTagName('rss')->length > 0 ) {
				$items = $this->parse_rss( $dom );
				$this->replace( $feed_id, $items );
			}
			else if ( $dom->getElementsByTagName('feed')->length > 0 ) {
				$items = $this->parse_atom( $dom );
				$this->replace( $feed_id, $items );
			}
			else {
				// it's an unsupported format
				EventLog::log( sprintf( _t('Feed %1$s is an unsupported format.'), $feed_url), 'err', 'feedlist', 'feedlist' );
				continue;
			}
			
			// log that the feed was updated
			EventLog::log( sprintf( _t( 'Updated feed %1$s' ), $feed_url ), 'info', 'feedlist', 'feedlist' );
			
		}
		
		// log that we finished
		EventLog::log( sprintf( _t( 'Finished updating %1$d feed(s).' ), count( $feedurls ) ), 'info', 'feedlist', 'feedlist' );
		
		// clean up old feed items
		$old_date = DB::get_value( 'select updated from {feedlist} order by updated desc limit 10, 1' );
		DB::query( 'delete from {feedlist} where updated < ?', array( $old_date ) );
		
		EventLog::log( sprintf( _t( 'Old feed items purged.') ), 'info', 'feedlist', 'feedlist' );
		
		return $result;		// only change a cron result to false when it fails
		
	}
	
	/**
	 * Parse out RSS 2.0 feed items.
	 * 
	 * See the example feed: http://www.rss-tools.com/rss-example.htm
	 * 
	 * @param DOMDocument $dom
	 * @return array Array of items.
	 */
	private function parse_rss ( DOMDocument $dom ) {
		
		// each item is an 'item' tag in RSS2
		$items = $dom->getElementsByTagName('item');
		
		$feed_items = array();
		foreach ( $items as $item ) {
			
			$feed = array();
			
			// snag all the child tags we need
			$feed['title'] = $item->getElementsByTagName('title')->item(0)->nodeValue;
			$feed['description'] = $item->getElementsByTagName('description')->item(0)->nodeValue;
			$feed['link'] = $item->getElementsByTagName('link')->item(0)->nodeValue;
			$feed['guid'] = $item->getElementsByTagName('guid')->item(0)->nodeValue;
			$feed['published'] = $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
			
			// try to blindly make sure the date is a HDT object - it should be a pretty standard PHP-parseable format
			$feed['published'] = HabariDateTime::date_create( $feed['published'] );
			
			$feed_items[] = $feed;
			
		}
		
		return $feed_items;
		
	}
	
	/**
	 * Parse out ATOM feed items.
	 * 
	 * See the example feed: http://www.atomenabled.org/developers/syndication/#sampleFeed
	 * 
	 * @param DOMDocument $dom
	 * @return array Array of items.
	 */
	private function parse_atom ( DOMDocument $dom ) {
		
		// each item is an 'entry' tag in ATOM
		$items = $dom->getElementsByTagName('entry');
		
		$feed_items = array();
		foreach ( $items as $item ) {
			
			$feed = array();
			
			// snag all the child tags we need
			$feed['title'] = $item->getElementsByTagName('title')->item(0)->nodeValue;
			$feed['description'] = $item->getElementsByTagName('summary')->item(0)->nodeValue;
			$feed['link'] = $item->getElementsByTagName('link')->item(0)->getAttribute('href');
			$feed['guid'] = $item->getElementsByTagName('id')->item(0)->nodeValue;
			$feed['published'] = $item->getElementsByTagName('updated')->item(0)->nodeValue;
			
			// try to blindly make sure the date is a HDT object - it should be a pretty standard PHP-parseable format
			$feed['published'] = HabariDateTime::date_create( $feed['published'] );
			
			$feed_items[] = $feed;
			
		}
		
		return $feed_items;
		
	}
	
	/**
	 * Insert all the feed items into the database using REPLACE INTO, so we'll actually replace existing matched GUIDs.
	 * 
	 * @param int $feed_id The feed ID stored in the DB.
	 * @param array $items Array of items parsed from the feed to add.
	 */
	private function replace ( $feed_id, $items ) {
		
		$sql = 'replace into {feedlist} ( feed_id, guid, title, link, updated, description ) values ( ?, ?, ?, ?, ?, ? )';
		
		foreach ( $items as $item ) {
		
			$params = array(
				$feed_id,
				$item['guid'],
				$item['title'],
				$item['link'],
				HabariDateTime::date_create(),
				$item['description'],
			);
			
			$result = DB::query( $sql, $params );
			
			if ( !$result ) {
				EventLog::log( 'There was an error saving a feed item.', 'err', 'feedlist', 'feedlist' );
			}
			
		}
		
	}

}	

?>
