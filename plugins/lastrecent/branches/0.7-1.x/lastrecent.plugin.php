<?php
class LastRecent extends Plugin
{
	private $api_key= 'd4a06492b226a89f358cf9dbd687001b';
	private $cache_expiry= '7200';
	private $uuid= 'eeaf6421-abca-4999-95d0-512d328d2462';
	private $user= null;
	private $limit= 3;
	private $images= 1;

	public function action_init()
	{
		$this->add_template( 'lastfm', dirname( __FILE__ ) . '/lastfm.php' );
		$this->user= Options::get( 'lastrecent__user' );
		$this->limit= Options::get( 'lastrecent__limit' );
		$size= Options::get( 'lastrecent__images' );
		switch( $size ) {
			case 'none': $this->images= null; break;
			case 'small': $this->images= 0; break;
			case 'medium': $this->images= 1; break;
			case 'large': $this->images= 2; break;
		}
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			// a cronjob to fetch the data from last.fm
			CronTab::add_hourly_cron( 'lastrecent', 'lastrecent', 'Fetch recent tracks from last.fm' );
		}
	}

	function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
			CronTab::delete_cronjob( 'lastrecent' );
			Cache::expire( 'lastrecent' );
		}
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
					$user= $ui->append( 'text', 'user', 'lastrecent__user',  _t('Last.fm username: ' ) );
					$limit= $ui->append( 'select', 'limit', 'lastrecent__limit', _t('Number of tracks to display: ') );
					$options= array();
					for ( $i= 1; $i<= 10; $i++ ) {
						$options[$i]= $i;
					}
					$limit->options= $options;
					$images= $ui->append( 'select', 'images', 'lastrecent__images', _t('Show images: ') );
					$images->options= array( 'none' => 'none', 'small' => 'small', 'medium' => 'medium', 'large' => 'large' );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					break;
				}
			}
	}

	public function update_config( $ui )
	{
		return true;
	}

	public function filter_lastrecent( $result )
	{
		$lastrecent= $this->get_lastrecent();
		Cache::set( 'lastrecent', $lastrecent, $this->cache_expiry );
		return $result;
	}

	public function theme_lastrecent( $theme )
	{
		if ( Cache::has( 'lastrecent' ) ) {
			$theme->lastfm_tracks= Cache::get( 'lastrecent' );
		} else {
			$theme->lastfm_tracks= $this->get_lastrecent();
			Cache::set( 'lastrecent', $theme->lastfm_tracks, $this->cache_expiry );
		}
		return $theme->fetch( 'lastfm' );
	}

	private function get_lastrecent()
	{
		$recent= '';
		try {
			$last= new RemoteRequest( 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&api_key=' . $this->api_key . '&user=' . urlencode( $this->user ) . '&limit=' . $this->limit );
			$last->set_timeout( 5 );
			$result= $last->execute();
			if ( Error::is_error( $result ) ) {
				throw $result;
			}
			$response= $last->get_response_body();
			$xml= new SimpleXMLElement( $response );
			$recent= '<ul id="lastrecent">';
			foreach( $xml->recenttracks->track as $track ) {
				$recent.= '<li><a href="' . $track->url . '">';
				if ( null !== $this->images ) {
					$recent.= '<img src="' . $track->image[$this->images] . '" /><br>';
				}
				$recent.= $track->name . '</a> by ' . $track->artist . '</li>';
			}
			$recent.= '</ul>';
		} catch( Exception $e ) {
			$recent['error']= $e->getMessage();
		}
		return $recent;
	}

	public function action_update_check()
	{
		Update::add( 'RecentLast', $this->uuid, $this->info->version );
	}

}
?>
