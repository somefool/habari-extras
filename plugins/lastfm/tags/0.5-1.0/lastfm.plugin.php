<?php
class lastfmAPI
{
	function __construct()
	{
		$this->key = 'd38cf8df461b96a3a6ef4cb8acc1b9cc';
		$this->secret = 'b8b8288e69ea9b7bae5be0fb0449f522';
		$this->endpoint = 'http://ws.audioscrobbler.com/2.0/';
		$this->conntimeout = 20;
	}
	
	function fetch($method, $params = array(), $tokenize = false, $debug = false) {
		$url = $this->endpoint;
		$url.= '?method=' . strtolower($method);
				
		foreach($params as $key => $val) {
			$url.= '&' . $key . '=' . $val;
		}
		
		$url.= '&api_key=' . $this->key;
		
		if($debug) {
			print_r($url);	
		}
		
		$contents = RemoteRequest::get_contents($url);
				
		$data = new SimpleXMLElement($contents);
		
		if($data['status'] == 'ok') {
			return $data;
		} else {
			return FALSE;
		}
	}
	
	function tags() {
		return $this->fetch('user.getTopTags', array('user' => User::identify()->info->lastfm__username));
	}
	
	/* this SHOULD fetch all tracks from a user's library. except it is recursive and insanely processor intensive. use at your own risk */
	
	function tracks($page) {
			
		$tracks = $this->fetch('library.getTracks', array('user' => User::identify()->info->lastfm__username, 'page' => $page));
		
		$results = array();
		
		foreach($tracks->tracks->track as $track) {
			$props = array();
			$props['title'] = (string)$track->name;
			$props['url'] = (string)$track->url;
			$props['icon'] = (string)$track->image[1];
			$props['thumbnail_url'] = (string)$track->image[1];
			$props['image_url'] = (string)$track->image[2];
			$props['filetype'] = 'lastfm';

			$results[] = new MediaAsset(
				lastfmSilo::SILO_NAME . '/songs/'. (string)$track->title,
				false,
				$props
			);						
		}
		
		if($tracks->tracks['page'] != $tracks->tracks['totalPages']) {
			$results = array_merge($results, $this->tracks($page + 1));
		}
		
		return $results;
	}
	
}
/**
* last.fm Silo
*/

class lastfmSilo extends Plugin implements MediaSilo
{
	const SILO_NAME = 'Last.fm';

	static $cache = array();
	

	
	/**
	* Provide plugin info to the system
	*/
	public function info()
	{
		return array('name' => 'Last.fm Media Silo',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Allows data from last.fm to be inserted into posts',
			'copyright' => '2008',
			);
	}

	public function action_admin_footer() {
		echo '<script type="text/javascript">';
		require('lastfm.js');
		echo '</script>';		
	}

	/**
	* Initialize some internal values when plugin initializes
	*/
	public function action_init() {
		$this->api = new lastfmAPI();
	}

	/**
	* Return basic information about this silo
	*     name- The name of the silo, used as the root directory for media in this silo
	*	  icon- An icon to represent the silo
	*/
	public function silo_info()
	{
		if(User::identify()->info->lastfm__username != '') {
			return array('name' => self::SILO_NAME, 'icon' => URL::get_from_filesystem(__FILE__) . '/icon.png');
		}
		else {
			return array();
		}
	}
	
	/**
	* Add actions to the plugin page for this plugin
	* The authorization should probably be done per-user.
	*
	* @param array $actions An array of actions that apply to this plugin
	* @param string $plugin_id The string id of a plugin, generated by the system
	* @return array The array of actions to attach to the specified $plugin_id
	*/
	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()){
			$actions[] = 'Configure';
		}

		return $actions;
	}
		
	/**
	* Respond to the user selecting an action on the plugin page
	*
	* @param string $plugin_id The string id of the acted-upon plugin
	* @param string $action The action string supplied via the filter_plugin_config hook
	*/
	public function action_plugin_ui($plugin_id, $action)
	{
		if ($plugin_id == $this->plugin_id()){
			switch ($action){
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );
					$ui->append( 'text', 'username', 'user:lastfm__username', _t('User:') );
					$ui->append('submit', 'save', _t( 'Save' ) );
					$ui->set_option('success_message', _t('Options saved'));
					$ui->out();
					break;
			}
		}
	}
	
	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_dir($path)
	{
		$results = array();
		$user = User::identify()->info->lastfm_username;

		$section = strtok($path, '/');
		switch($section) {
			case 'tags':
				$selected = strtok('/');
				if($selected) {
					if($artist = strtok('/')) {
						if($level = strtok('/')) {
							if($level == 'albums') {
								$albums = $this->api->fetch('artist.getTopAlbums', array('artist' => $artist));

								foreach($albums->topalbums->album as $album) {
									$props = array();
									$props['title'] = (string)$album->name;
									$props['url'] = (string)$album->url;
									$props['icon'] = (string)$album->image[1];
									$props['thumbnail_url'] = (string)$album->image[1];
									$props['image_url'] = (string)$album->image[2];
									$props['filetype'] = 'lastfm';

									$results[] = new MediaAsset(
										self::SILO_NAME . '/tags/' . $selected . '/' . $artist . '/' . (string)$album->mbid,
										false,
										$props
									);						
								}
							} elseif($level == 'songs') {
								$tracks = $this->api->fetch('artist.getTopTracks', array('artist' => $artist));
																
								foreach($tracks->toptracks->track as $track) {
									$props = array();
									$props['title'] = (string)$track->name;
									$props['url'] = (string)$track->url;
									$props['icon'] = (string)$track->image[1];
									$props['thumbnail_url'] = (string)$track->image[1];
									$props['image_url'] = (string)$track->image[2];
									$props['filetype'] = 'lastfm';

									$results[] = new MediaAsset(
										self::SILO_NAME . '/tags/' . $selected . '/' . $artist . '/' . (string)$track->name,
										false,
										$props
									);						
								}
							}
						} else {
							$results[] = new MediaAsset(
								self::SILO_NAME . '/tags/' . $selected . '/' . $artist . '/albums',
								true,
								array('title' => 'Albums')
							);
							$results[] = new MediaAsset(
								self::SILO_NAME . '/tags/' . $selected . '/' . $artist . '/songs',
								true,
								array('title' => 'Songs')
							);
						}
					} else {
						$artists = $this->api->fetch('tag.getTopArtists', array('tag' => $selected));

						foreach($artists->topartists->artist as $artist) {
							$results[] = new MediaAsset(
								self::SILO_NAME . '/tags/' . $selected . '/' . (string)$artist->name,
								true,
								array('title' => (string)$artist->name)
							);
						}			
					}
				} else {
					$tags = $this->api->tags();

					foreach($tags->toptags->tag as $tag) {					
						$results[] = new MediaAsset(
							self::SILO_NAME . '/tags/' . (string)$tag->name,
							true,
							array('title' => (string)$tag->name)
						);
					}		
				}

				break;
				
			case 'artists':
				if($artist = strtok('/')) {
					if($level = strtok('/')) {
						if($level == 'albums') {
							$albums = $this->api->fetch('artist.getTopAlbums', array('artist' => $artist));

							foreach($albums->topalbums->album as $album) {
								$props = array();
								$props['title'] = (string)$album->name;
								$props['url'] = (string)$album->url;
								$props['icon'] = (string)$album->image[1];
								$props['thumbnail_url'] = (string)$album->image[1];
								$props['image_url'] = (string)$album->image[2];
								$props['filetype'] = 'lastfm';

								$results[] = new MediaAsset(
									self::SILO_NAME . '/artists/' . $artist . '/' . (string)$album->mbid,
									false,
									$props
								);						
							}
						} elseif($level == 'songs') {
							$tracks = $this->api->fetch('artist.getTopTracks', array('artist' => $artist));
															
							foreach($tracks->toptracks->track as $track) {
								$props = array();
								$props['title'] = (string)$track->name;
								$props['url'] = (string)$track->url;
								$props['icon'] = (string)$track->image[1];
								$props['thumbnail_url'] = (string)$track->image[1];
								$props['image_url'] = (string)$track->image[2];
								$props['filetype'] = 'lastfm';

								$results[] = new MediaAsset(
									self::SILO_NAME . '/artists/' . $artist . '/' . (string)$track->name,
									false,
									$props
								);						
							}
						}
					} else {
						$results[] = new MediaAsset(
							self::SILO_NAME . '/artists/' . $artist . '/albums',
							true,
							array('title' => 'Albums')
						);
						$results[] = new MediaAsset(
							self::SILO_NAME . '/artists/' . $artist . '/songs',
							true,
							array('title' => 'Songs')
						);
					}
				} else {
					$artists = $this->api->fetch('user.getTopArtists', array('user' => User::identify()->info->lastfm__username));

					foreach($artists->topartists->artist as $artist) {
						$results[] = new MediaAsset(
							self::SILO_NAME . '/artists/' . (string)$artist->name,
							true,
							array('title' => (string)$artist->name)
						);
					}			
				}
				break;
				
			case 'albums':
				$albums = $this->api->fetch('library.getAlbums', array('user' => User::identify()->info->lastfm__username));
								
				foreach($albums->albums->album as $album) {
					$props = array();
					$props['title'] = (string)$album->name;
					$props['url'] = (string)$album->url;
					$props['icon'] = (string)$album->image[1];
					$props['thumbnail_url'] = (string)$album->image[1];
					$props['image_url'] = (string)$album->image[2];
					$props['filetype'] = 'lastfm';

					$results[] = new MediaAsset(
						self::SILO_NAME . '/albums/'. (string)$album->mbid,
						false,
						$props
					);						
				}
				
				break;
				
			case 'songs':
				$tracks = $this->api->fetch('user.getTopTracks', array('user' => User::identify()->info->lastfm__username));
									
				foreach($tracks->toptracks->track as $track) {
					$props = array();
					$props['title'] = (string)$track->name;
					$props['url'] = (string)$track->url;
					$props['icon'] = (string)$track->image[1];
					$props['thumbnail_url'] = (string)$track->image[1];
					$props['image_url'] = (string)$track->image[2];
					$props['filetype'] = 'lastfm';

					$results[] = new MediaAsset(
						self::SILO_NAME . '/songs/'. (string)$track->url,
						false,
						$props
					);						
				}
				
				break;
				
			case 'recent':
				$tracks = $this->api->fetch('user.getRecentTracks', array('user' => User::identify()->info->lastfm__username));

				foreach($tracks->recenttracks->track as $track) {
					$props = array();
					$props['title'] = (string)$track->name;
					$props['url'] = (string)$track->url;
					$props['icon'] = (string)$track->image[1];
					$props['thumbnail_url'] = (string)$track->image[1];
					$props['image_url'] = (string)$track->image[2];
					$props['filetype'] = 'lastfm';

					$results[] = new MediaAsset(
						self::SILO_NAME . '/recent/'. (string)$track->url,
						false,
						$props
					);						
				}

				break;
				
			case 'favorites':
				$tracks = $this->api->fetch('user.getLovedTracks', array('user' => User::identify()->info->lastfm__username));

				foreach($tracks->lovedtracks->track as $track) {
					$props = array();
					$props['title'] = (string)$track->name;
					$props['url'] = (string)$track->url;
					$props['icon'] = (string)$track->image[1];
					$props['thumbnail_url'] = (string)$track->image[1];
					$props['image_url'] = (string)$track->image[2];
					$props['filetype'] = 'lastfm';

					$results[] = new MediaAsset(
						self::SILO_NAME . '/favorites/'. (string)$track->url,
						false,
						$props
					);						
				}

				break;
				
			case '':
				$results[] = new MediaAsset(
					self::SILO_NAME . '/tags',
					true,
					array('title' => 'Tags')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/artists',
					true,
					array('title' => 'Artists')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/albums',
					true,
					array('title' => 'Albums')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/songs',
					true,
					array('title' => 'Songs')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/recent',
					true,
					array('title' => 'Recent Songs')
				);
				$results[] = new MediaAsset(
					self::SILO_NAME . '/favorites',
					true,
					array('title' => 'Favorites')
				);
				break;
		}
		return $results;
	}

	/**
	 * Provide controls for the media control bar
	 *
	 * @param array $controls Incoming controls from other plugins
	 * @param MediaSilo $silo An instance of a MediaSilo
	 * @param string $path The path to get controls for
	 * @param string $panelname The name of the requested panel, if none then emptystring
	 * @return array The altered $controls array with new (or removed) controls
	 *
	 * @todo This should really use FormUI, but FormUI needs a way to submit forms via ajax
	 */
	public function filter_media_controls( $controls, $silo, $path, $panelname )
	{
		$controls = array();
		return $controls;
	}

	public function silo_upload_form() {
	}

	/**
	* Get the file from the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return MediaAsset The requested asset
	*/
	public function silo_get($path, $qualities = null)
	{
	}

	/**
	* Get the direct URL of the file of the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param array $qualities Qualities that specify the version of the file to retrieve.
	* @return string The requested url
	*/
	public function silo_url($path, $qualities = null)
	{
	}

	/**
	* Create a new asset instance for the specified path
	*
	* @param string $path The path of the new file to create
	* @return MediaAsset The requested asset
	*/
	public function silo_new($path)
	{
	}

	/**
	* Store the specified media at the specified path
	*
	* @param string $path The path of the file to retrieve
	* @param MediaAsset $ The asset to store
	*/
	public function silo_put($path, $filedata)
	{
	}

	/**
	* Delete the file at the specified path
	*
	* @param string $path The path of the file to retrieve
	*/
	public function silo_delete($path)
	{
	}

	/**
	* Retrieve a set of highlights from this silo
	* This would include things like recently uploaded assets, or top downloads
	*
	* @return array An array of MediaAssets to highlihgt from this silo
	*/
	public function silo_highlights()
	{
	}

	/**
	* Retrieve the permissions for the current user to access the specified path
	*
	* @param string $path The path to retrieve permissions for
	* @return array An array of permissions constants (MediaSilo::PERM_READ, MediaSilo::PERM_WRITE)
	*/
	public function silo_permissions($path)
	{
	}

	/**
	* Return directory contents for the silo path
	*
	* @param string $path The path to retrieve the contents of
	* @return array An array of MediaAssets describing the contents of the directory
	*/
	public function silo_contents()
	{
	}
}

?>
