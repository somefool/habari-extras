<?php
class Lilliputian extends Plugin
{

/*
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			// establish our max id counter, but don't clobber
			// any existing value
			if ( ! Options::get( 'lilliputian__max' ) ){
				Options::set( 'lilliputian__max', 0 );
			}
		}
	}
*/

	/* public function action_init() */
	public function filter_rewrite_rules( $rules )
	{
		if ( Options::get( 'lilliputian__service' ) == 'internal' ) {
			$redirect= RewriteRule::create_url_rule( '"r"/url', 'UserThemeHandler', 'redirect' );
			$shorten= RewriteRule::create_url_rule( '"s"/url', 'UserThemeHandler', 'shorten' );
			$rules[]= $redirect;
			$rules[]= $shorten;
		}
		return $rules;
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					// present the user with a list of
					// URL shortening services
					$service= $ui->append( 'select', 'service', 'lilliputian__service', _t('The URL shortening service to use: ') );
					$services= array();
					$services['internal']= 'internal';
					$list= Utils::glob( dirname(__FILE__) . '/*.helper.php' );
					if ( count( $list ) > 0 ) {
						foreach ( $list as $item ) {
							$item= basename( $item, '.helper.php' );
							$services[$item]= $item;
						}
					}
					$service->options= $services;

					if ( Options::get( 'lilliputian__service' ) == 'internal' ) {
						$secret= $ui->append( 'text', 'secret', 'lilliputian__secret', _t('The secret word that must be passed when generating short URLs.  May be blank to disable this security feature.' ) );
					}

					$ui->append( 'submit', 'save', _t('Save' ) );

					$ui->out();
					break;
			}
		}
	}

	public function action_post_update_content( $post, $old, $new )
	{
		$service= Options::get('lilliputian__service');
		if ( 'internal' != $service ) {
			include_once( dirname( __FILE__ ) . '/' . $service . '.helper.php' );
		}
		$count= preg_match_all( '/href=[\'"]([^\'"]+)[\'"]/', $new, $urls );
		if ( $count ) {
			$update= false;
			foreach ( $urls[1] as $url ) {
				// see if this URL is already stored
				if ( isset( $post->info->$url ) ) {
					continue;
				}

				if ( 'internal' == $service ) {
					$tiny= $this->internal_check( $url );
					if ( ! $tiny ) {
						$tiny= $this->internal_shrink( $url );
					}
				} else {
					$tiny= shrink( $url );
				}
				$post->info->$url= $tiny;
				$update= true;
		}
			// update the list of links as needed
			if ( $update ) { 
				$post->info->url_list= $urls[1];
				$post->info->commit();
			}
		}
	}

	public function filter_post_content_out( $content, $post )
	{
		$urls= $post->info->url_list;
		if ( $urls ) {
			foreach( $urls as $url ) {
				$content= str_replace( $url, $post->info->$url, $content );
			}
		}
		return $content;
	}

	/**
	 * given a short URL, redirect to the corresponding long URL
	**/
	public function action_handler_redirect( $vars )
	{
		
		$shorturl= Site::get_url('habari') . '/r/' . substr( $vars['entire_match'], 2 );
		$url= DB::get_value( 'SELECT name FROM {postinfo} WHERE value = ?', array( $shorturl ) );
		if ( $url ) {
			Utils::redirect( $url );
		} else {
			die ('Error');
		}
		exit;
	}

	/**
	 * given a long URL in a query string or HTTP POST, generate and
	 * return a short URL
	**/
	public function action_handler_shorten( $vars )
	{
		$secret= Options::get( 'lilliputian__secret' );
		list( $junk, $pass, $url )= explode( '/', $vars['entire_match'], 3 );
		if ( ( $secret ) && ( $pass == $secret ) ) {
			$tiny= $this->internal_check( $url );
			if ( ! $tiny ) {
				$tiny= $this->internal_shrink( $url );
				$result= DB::query( 'INSERT INTO {postinfo} (post_id, name, value) VALUES (?, ?, ?)', array( 0, $url, $tiny ) );
			}
			echo $tiny;
			die;
		}
		echo 'Go away';
		die;
	}

	public function internal_check( $url )
	{
		$check= DB::get_value( 'SELECT value FROM {postinfo} WHERE name=?', array( $url ) );
		if ( $check ) { return $check; }
	}

	public function internal_shrink( $url )
	{
		$max= Options::get( 'lilliputian__max' ) + 1;
		$shorturl = Site::get_url( 'habari' ) . '/r/' . base_convert($max, 10, 36);
		Options::set( 'lilliputian__max', $max );
		return $shorturl;
	}
}
?>

