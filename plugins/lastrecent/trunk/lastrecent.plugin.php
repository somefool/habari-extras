<?php
class LastRecent extends Plugin
{
	private $api_key = 'd4a06492b226a89f358cf9dbd687001b';
	private $cache_expiry = '7200';
	private $user = null;
	private $limit = 3;
	private $images = 1;

	public function action_init()
	{
		$this->add_template( 'lastfm', dirname( __FILE__ ) . '/lastfm.php' );
		$this->add_template( "block.lastfm_recent", dirname( __FILE__ ) . "/block.lastfm_recent.php" );
		$this->user = Options::get( 'lastrecent__user' );
		$this->limit = Options::get( 'lastrecent__limit' );
		$size = Options::get( 'lastrecent__images' );
		switch( $size ) {
			case 'none': $this->images = null; break;
			case 'small': $this->images = 0; break;
			case 'medium': $this->images = 1; break;
			case 'large': $this->images = 2; break;
			case 'extralarge': $this->images = 3; break;
		}
	}

	public function action_plugin_activation()
	{
		// a cronjob to fetch the data from last.fm
		CronTab::add_hourly_cron( 'lastrecent', 'lastrecent', _t( 'Fetch recent tracks from last.fm', 'lastrecent' ) );
	}

	function action_plugin_deactivation( $file )
	{
		CronTab::delete_cronjob( 'lastrecent' );
		Cache::expire( array( 'lastrecent' ) );
	}

	public function filter_plugin_config( $actions )
	{
		$actions['configure']= _t( 'Configure' );
		return $actions;
	}

	public function action_plugin_ui_configure()
	{
		$ui = new FormUI( strtolower( get_class( $this ) ) );
		$ui->append( 'static', 'null:null', _t( 'These settings are only effective for themes using the legacy implementation method (See Help). If you plan to use blocks, you can must set these settings on a per-block basis.', 'lastrecent') );
		$user= $ui->append( 'text', 'user', 'lastrecent__user',  _t( 'Last.fm username: ', 'lastrecent' ) );
		$limit= $ui->append( 'select', 'limit', 'lastrecent__limit', _t( 'Number of tracks to display: ', 'lastrecent' ) );
		$options= array();
		for ( $i= 1; $i<= 10; $i++ ) {
			$options[$i]= $i;
		}
		$limit->options= $options;
		$images= $ui->append( 'select', 'images', 'lastrecent__images', _t( 'Show images: ', 'lastrecent' ) );
		$images->options= array( 'none' => _t( 'None', 'lastrecent' ), 'small' => _t( 'Small', 'lastrecent' ), 'medium' => _t( 'Medium', 'lastrecent' ), 'large' => _t( 'Large', 'lastrecent' ), 'extralarge' => _t( 'Extralarge', 'lastrecent' ) );
		$ui->append( 'submit', 'save', _t( 'Save' ) );
		$ui->on_success( array( $this, 'saveOptions' ) );
		$ui->out();
	}

	// Save the form and clear the cache
	public static function saveOptions ( $ui )
	{
		$ui->save();
		Cache::expire( 'lastrecent__legacy' );
	}
	
	public function filter_block_list( $block_list )
	{
		$block_list['lastfm_recent'] = _t( 'Last.fm Recent Tracks', 'lastrecent' );
		return $block_list;
	}
	
	public function action_block_content_lastfm_recent( $block, $theme )
	{
		$block->lastfm_recent = $this->get_lastrecent( $block );
	}
	
	public function action_block_form_lastfm_recent( $form, $block )
	{
		$form->append( 'text', 'user', $block,  _t( 'Last.fm username: ', 'lastrecent' ) );
		$form->append( 'text', 'user', $block,  _t( 'Last.fm username: ', 'lastrecent' ) );
		$form->append( 'select', 'limit', $block, _t( 'Number of tracks to display: ', 'lastrecent' ) );
		$options = array();
		for ( $i = 1; $i<= 10; $i++ ) {
			$options[$i] = $i;
		}
		$form->limit->options = $options;
		$form->append( 'select', 'size', $block, _t( 'Show images: ', 'lastrecent' ) );
		$form->size->options= array( null => _t( 'None', 'lastrecent' ), 0 => _t( 'Small', 'lastrecent' ), 1 => _t( 'Medium', 'lastrecent' ), 2 => _t( 'Large', 'lastrecent' ), 3 => _t( 'Extralarge', 'lastrecent' ) );
	}
	
	public function action_block_update_after( $block )
	{
		if ( $block->type == 'lastfm_recent' ) {
			Cache::expire( 'lastrecent__' . $block->title );
		}
	}
	
	public function theme_lastrecent( $theme )
	{
		$theme->lastfm_tracks= $this->get_lastrecent();
		return $theme->fetch( 'lastfm' );
	}

	private function get_lastrecent( $block = null )
	{
		$name = ( $block ) ? $block->title : 'legacy';
		$user = ( $block ) ? $block->user : $this->user;
		$limit = ( $block ) ? $block->limit : $this->limit;
		$size = ( $block ) ? (int) $block->size : $this->images;
		
		if ( Cache::has( 'lastrecent__' . $name ) ) {
			$recent = Cache::get( 'lastrecent__' . $name );
		} else {
			try {
				$last = new RemoteRequest( 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&api_key=' . $this->api_key . '&user=' . urlencode( $user ) . '&limit=' . $limit );
				$last->set_timeout( 5 );
				$result = $last->execute();
				if ( Error::is_error( $result ) ) {
					throw $result;
				}
				$response = $last->get_response_body();
				$xml = new SimpleXMLElement( $response );
				$recent = array();
				foreach ( $xml->recenttracks->track as $track ) {
					$recent[] = array( 'name' => (string) $track->name,
									   'url' => (string) $track->url, 
									   'image' => (string) $track->image[$size],
									   'artist' => (string) $track->artist,
									   'album' => (string) $track->album,
									   'date' => (string) $track->date,
								);
				}
				Cache::set( 'lastrecent__' . $name, $recent, $this->cache_expiry );
			} catch( Exception $e ) {
				$recent['error'] = $e->getMessage();
			}
		}
		return $recent;
	}
}
?>
