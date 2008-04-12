<?php
/*
 * Blogroll Plugin
 * Usage: <?php $theme->show_blogroll(); ?> 
 * A sample blogroll.php template is included with the plugin.  This can be copied to your 
 * active theme and modified to fit your preference.
 *
 * @todo OPML feed/export support via a BlogrollOPMLHandler and SimpleXMLElement
 * @todo Implement Blogs::get method to use params for sort/limit etc...
 * @todo sqlite schema
 * @todo Update wiki docs
 * @todo Make the delete and auto update buttons work on publish screen
 * @todo Provide check/uncheck all in manage screen
 * @todo Write a XFN/FOAF "plugin extension" to extend the blogroll ;)
 */

require_once "blogs.php";
require_once "bloginfo.php";
require_once "blog.php";

class Blogroll extends Plugin
{		
	public function info()
	{
		return array(
		'name' => 'Blogroll',
		'version' => '0.5-alpha',
		'url' => 'http://wiki.habariproject.org/en/plugins/blogroll',
		'author' => 'Habari Community',
		'authorurl' => 'http://habariproject.org/',
		'license' => 'Apache License 2.0',
		'description' => 'Displays a blogroll on your blog'
		);
	}
	
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			DB::register_table( 'blogroll' );
			DB::register_table( 'bloginfo' );
			DB::register_table( 'tag2blog' );
			
			if ( ! CronTab::get_cronjob( 'blogroll:update' ) ) {
				CronTab::add_hourly_cron( 'blogroll:update', 'blogroll_update_cron', 'Updates the blog updated timestamp from weblogs.com' );
			}
			
			Options::set( 'blogroll:use_updated', true );
			Options::set( 'blogroll:max_links', '10' );
			Options::set( 'blogroll:sort_by', 'updated' );
			Options::set( 'blogroll:list_title', 'Blogroll' );
			
			if ( $this->install_db_tables() ) {
				Session::notice( _t( 'Created the Blogroll database tables.', 'blogroll' ) );
			}
			else {
				Session::error( _t( 'Could not install Blogroll database tables.', 'blogroll' ) );
			}
		}
	}
	
	public function action_plugin_deactivation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			CronTab::delete_cronjob( 'blogroll:update' );
			// should we remove the tables here?
		}
	}
	
	public function install_db_tables()
	{
		switch ( DB::get_driver_name() ) {
			case 'mysql':
				$schema= "CREATE TABLE " . DB::table('blogroll') . " (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				url VARCHAR(255) NOT NULL,
				feed VARCHAR(255) NOT NULL,
				owner VARCHAR(255) NOT NULL,
				updated VARCHAR(12) NOT NULL,
				rel VARCHAR(255) NOT NULL,
				description TEXT,
				UNIQUE KEY id (id)
				);
				CREATE TABLE " . DB::table('bloginfo') . " (
				blog_id INT UNSIGNED NOT NULL,
				name VARCHAR(255) NOT NULL,
				type SMALLINT UNSIGNED NOT NULL DEFAULT 0,
				value TEXT,
				PRIMARY KEY (blog_id,name)
				);
				CREATE TABLE  " . DB::table('tag2blog') . " (
				tag_id INT UNSIGNED NOT NULL,
				blog_id INT UNSIGNED NOT NULL,
				PRIMARY KEY (tag_id,blog_id),
				KEY blog_id (blog_id)
				);";
				break;
			case 'sqlite':
				$schema= "";
				break;
		}
		return DB::dbdelta( $schema );
	}
	
  	public function action_update_check()
  	{
    	Update::add( 'blogroll', '0420cf10-db83-11dc-95ff-0800200c9a66',  $this->info->version );
  	}
	
	public function action_init()
	{
		DB::register_table( 'blogroll' );
		DB::register_table( 'bloginfo' );
		DB::register_table( 'tag2blog' );
	}
	
	public function filter_plugin_config( $actions, $plugin_id )
	{
		
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= _t( 'Configure', 'blogroll' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			switch ( $action ) {
				case _t( 'Configure', 'blogroll' ):
					$form= new FormUI( 'blogroll' );
					$title= $form->add( 'text', 'list_title', _t( 'List title: ', 'blogroll' ) );
					$max= $form->add( 'text', 'max_links', _t( 'Max. displayed links: ', 'blogroll') );
					$random= $form->add( 'text', 'sort_by', _t( 'Sort By: ', 'blogroll') );
					$update= $form->add( 'checkbox', 'use_update', _t( 'Use Weblogs.com to get updates? ', 'blogroll') );
					$form->out();
					break;
			}
		}
	}
	
	public function filter_adminhandler_post_loadplugins_main_menu( $menu )
	{
		$menu['manage']['submenu']['blogroll']= array( 'caption' => _t( 'Blogroll', 'blogroll' ), 'url' => URL::get( 'admin', 'page=blogroll_manage' ) );
		$menu['publish']['submenu']['blogroll']= array( 'caption' => _t( 'Blogroll', 'blogroll' ), 'url' => URL::get( 'admin', 'page=blogroll_publish' ) );
		return $menu;
	}
	
	public function action_admin_theme_post_blogroll_manage( $handler, $theme )
	{
		extract( $handler->handler_vars );
		
		if ( isset( $change ) && isset( $blog_ids ) ) {
			$count= count( $blog_ids );
			$blog_ids= (array) $blog_ids;
			
			switch ( $change ) {
				case 'delete':
					foreach ( $blog_ids as $blog_id ) {
						$blog= Blog::get( $blog_id );
						$blog->delete();
					}
					Session::notice( sprintf( _n('Deleted %d blog', 'Deleted %d blogs', $count, 'blogroll'), $count ) );
					break;
				case 'auto_update':
					foreach ( $blog_ids as $blog_id ) {
						$blog= Blog::get( $blog_id );
						if ( $info= Blogs::get_info_from_url( $blog->feed?$blog->feed:$blog->url ) ) {
							foreach ( $info as $key => $value ) {
								$value= trim( $value );
								if ( $value ) {
									$blog->$key= $value;
								}
							}
							$blog->update();
						}
						else {
							Session::error( sprintf( _t('Could not fetch info for %s', 'blogroll'), $blog->name ) );
							$count--;
						}
					}
					Session::notice( sprintf( _n('Automatically updated %d blog', 'Automatically updated %d blogs', $count, 'blogroll'), $count ) );
					break;
			}
		}
		
		if ( !empty( $opml_file ) || ( isset( $_FILES['userfile'] ) && is_uploaded_file( $_FILES['userfile']['tmp_name'] ) ) ) {
			$file= !empty( $opml_file ) ? RemoteRequest::get_contents( $opml_file ) : file_get_contents( $_FILES['userfile']['tmp_name'] );
			try {
				$xml= new SimpleXMLElement( $file );
				$count= $this->import_opml( $xml->body );
				Session::notice( sprintf( _n('Imported %d blog from %s', 'Imported %d blogs from %s', $count, 'blogroll'), $count, (string) $xml->head->title ) );
			}
			catch ( Exception $e ) {
				Session::error( _t('Sorry, could not parse that OPML file. It may be malformed.') );
			}
		}
		
		Utils::redirect( URL::get( 'admin', 'page=blogroll_manage' ) );
		exit;
	}
	
	public function action_admin_theme_post_blogroll_publish( $handler, $theme )
	{
		$params= array_intersect_key( $handler->handler_vars, array_flip( array('name', 'url', 'feed', 'description', 'owner', 'tags') ) );
		extract( $handler->handler_vars );
		
		if ( !empty( $quick_link ) ) {
			$link= $quick_link;
			if ( strpos( $quick_link, 'http://' ) !== 0 ) {
				$quick_link= 'http://' . $quick_link;
			}
			if ( $info= Blogs::get_info_from_url( $quick_link ) ) {
				$params= array_merge( $info, $params );
			}
			else {
				$_POST['url']= $quick_link;
				$_POST['feed']= $quick_link;
				Session::add_to_set( 'last_form_data', $_POST, 'get' );
				Session::error( sprintf( _t('Could not fetch info from %s. Please enter the information manually.', 'blogroll'), $quick_link ) );
			}
		}
		
		if ( ( empty( $params['name'] ) || empty( $params['url'] ) ) ) {
			Session::error( _t('Blog Name and URL are required feilds.', 'blogroll') );
			Session::add_to_set( 'last_form_data', $_POST, 'get' );
		}
		else {
			if ( !empty( $id ) ) {
				$blog= Blog::get( $id );
				foreach ( $params as $key => $value ) {
					$blog->$key= $value;
				}
				$blog->update();
				Session::notice( sprintf( _t('Updated blog %s'), $blog->name ) );
				Session::add_to_set( 'last_form_data', $_POST, 'get' );
			}
			elseif ( $params ) {
				$blog= new Blog( $params );
				if ( $blog->insert() ) {
					Session::notice( sprintf( _t('Successfully added blog %s'), $blog->name ) );
					$_POST['id']= $blog->id;
				}
				else {
					Session::notice( sprintf( _t( 'Could not add blog %s'), $blog->name ) );
				}
				Session::add_to_set( 'last_form_data', $_POST, 'get' );
			}
		}
		
		Utils::redirect( URL::get( 'admin', 'page=blogroll_publish' ) );
		exit;
	}
	
	public function action_admin_theme_get_blogroll_manage( $handler, $theme )
	{
		Stack::add( 'admin_stylesheet', array( $this->get_url() . '/templates/blogroll.css', 'screen' ) );
		$theme->feed_icon= $this->get_url() . '/templates/feed.png';
		
		$theme->display( 'blogroll_manage' );
		exit;
	}
	
	public function action_admin_theme_get_blogroll_publish( $handler, $theme )
	{
		Stack::add( 'admin_stylesheet', array( $this->get_url() . '/templates/blogroll.css', 'screen' ) );
		extract(  $handler->handler_vars );
		
		if ( !empty( $quick_link_bookmarklet ) ) {
			$_GET['quick_link']= $quick_link_bookmarklet;
			unset( $_GET['quick_link_bookmarklet'] );
			Session::add_to_set( 'last_form_data', $_GET, 'post' );
			Utils::redirect( URL::get( 'admin', 'page=blogroll_publish' ) );
			exit;
		}
		
		if ( !empty( $id ) ) {
			$blog= Blog::get( $id );
			$theme->tags= htmlspecialchars( Utils::implode_quoted( ',', $blog->tags ) );
		}
		else {
			$blog= new Blog;
			$theme->tags= '';
		}
		foreach ( $blog->to_array() as $key => $value ) {
			$theme->$key= $value;
		}
		
		$theme->relationships= Plugins::filter( 'blogroll_relationships', array('external'=>'External', 'nofollow'=>'Nofollow', 'bookmark'=>'Bookmark') );
		$controls= array(
			'Extras' => $theme->fetch( 'blogroll_publish_extras' ),
			'Tags' => $theme->fetch( 'publish_tags' ),
		);
		$theme->controls= Plugins::filter( 'blogroll_controls', $controls, $blog );
		$theme->display( 'blogroll_publish' );
		exit;
	}
	
	public function filter_available_templates( $templates, $class ) {
		$templates= array_merge( $templates, array('blogroll_manage','blogroll_publish','blogroll','blogroll_publish_extras') );
		return $templates;
	}
	
	public function filter_include_template_file( $template_path, $template_name, $class )
	{
		if ( ! file_exists( $template_path ) ) {
			switch ( $template_name ) {
				case 'blogroll_manage':
					return dirname( __FILE__ ) . '/templates/blogroll_manage.php';
				case 'blogroll_publish_extras':
					return dirname( __FILE__ ) . '/templates/blogroll_publish_extras.php';
				case 'blogroll_publish':
					return dirname( __FILE__ ) . '/templates/blogroll_publish.php';
				case 'blogroll':
					return dirname( __FILE__ ) . '/templates/blogroll.php';
			}
		}
		return $template_path;
	}
	
	public function theme_show_blogroll( $theme )
	{
		$theme->blogroll_title= Options::get( 'blogroll:list_title' );
		// pass options here as params
		$params= array(
			'limit' => Options::get( 'blogroll:max_links' ),
			'order_by' => Options::get( 'blogroll:sort_by' ),
			);
		$theme->blogs= Blogs::get();
		
		return $theme->fetch( 'blogroll' );
		
	}
	
	public function filter_blogroll_update_cron( $success )
	{
		if ( Options::get( 'blogroll:use_updated' ) ) {
			$request= new RemoteRequest( 'http://www.weblogs.com/rssUpdates/changes.xml', 'GET' );
			$request->add_header( array( 'If-Modified-Since', Options::get('blogroll:last_update') ) );
			if ( $request->execute() ) {
				try {
					$xml= new SimpleXMLElement( $request->get_response_body() );
				}
				catch ( Exception $e ) {
					// log the failure here!
				}
				$atts= $xml->attributes();
				$updated= strtotime( (string) $atts['updated'] );
				foreach ( $xml->weblog as $weblog ) {
					$atts= $weblog->attributes();
					$match= array();
					$match['url']= (string) $atts['url'];
					$match['feed']= (string) $atts['rssUrl'];
					$update= $updated - (int) $atts['when'];
					if ( DB::exists( DB::table( 'blogroll' ), $match ) ) {
						DB::update( DB::table( 'blogroll' ), array( 'updated' => $update ), $match );
					}
				}
				Options::set( 'blogroll:last_update', gmdate( 'D, d M Y G:i:s e' ) );
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	public function filter_habminbar( $menu )
	{
		$menu['blogroll']= array( 'Blogroll', URL::get( 'admin', 'page=blogroll_publish' ) );
		return $menu;
	}
	
	private function import_opml( SimpleXMLElement $xml )
	{
		$count= 0;
		foreach ( $xml->outline as $outline ) {
			$atts= (array) $outline->attributes();
			$params= $this->map_opml_atts( $atts['@attributes'] );
			if ( isset( $params['url'] ) && isset( $params['name'] ) ) {
				$blog= new Blog( $params );
				$blog->insert();
				$count++;
			}
			if ( $outline->children() ) {
				$count+= $this->import_opml( $outline );
			}
		}
		return $count;
	}
	
	private function map_opml_atts( $atts )
	{
		$atts= array_map( 'strval', $atts );
		$valid_atts= array_intersect_key( $atts, array_flip( array('name', 'url', 'feed', 'description', 'owner', 'updated') ) );
		foreach ( $atts as $key => $val ) {
			switch ( $key ) {
				case 'htmlUrl':
					$valid_atts['url']= $atts['htmlUrl'];
					break;
				case 'xmlUrl':
					$valid_atts['url']= $atts['xmlUrl'];
					break;
				case 'text':
					$valid_atts['name']= $atts['text'];
					break;
			}
		}
		return $valid_atts;
	}
}
?>