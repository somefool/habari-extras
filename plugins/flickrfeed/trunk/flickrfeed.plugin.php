<?php
/**
 * FlickrFeed Plugin: Show the images from Flickr feed
 */

class FlickrFeed extends Plugin
{
	private static function default_options( )
	{
		return array (
			'feed_type' => 'user',
			'user_id' => '',
			'image_count' => '6',
			'image_size' => 'square',
			'image_tags' => '',
			'cache_expiry' => '1800',
		);
	}

	public function validate_uint( $value )
	{
		if ( !ctype_digit( $value ) || strstr( $value, '.' ) || $value < 0 ) {
			return array( _t( 'This field must be positive integer.', 'flickrfeed' ) );
		}
		return array( );
	}

	public function validate_flickr_id( $value )
	{
		if ( empty( $value ) && in_array( $this->config[ 'type' ], array( 'user', 'group' ) ) ) {
			return array( _t( 'A value for this field is required while type is not ‘Public’.', 'flickrfeed' ) );
		}
		return array( );
	}

	public function filter_block_list( $block_list )
	{
		$block_list[ 'flickrfeed' ] = _t( 'FlickrFeed', 'flickrfeed' );
		return $block_list;
	}

	private static function build_api_url( $block )
	{
		switch ( $block->feed_type ) {
			case 'user':
				return 'http://api.flickr.com/services/feeds/photos_public.gne?id=' . $block->user_id . '&tags=' . $block->image_tags . '&format=php_serial';
				break;
			case 'friends':
				return 'http://api.flickr.com/services/feeds/photos_friends.gne?user_id=' . $block->user_id . '&format=php_serial';
				break;
			case 'faves':
				return 'http://api.flickr.com/services/feeds/photos_faves.gne?id=' . $block->user_id . '&format=php_serial';
				break;
			case 'group':
				return 'http://api.flickr.com/services/feeds/groups_pool.gne?id=' . $block->user_id. '&format=php_serial';
				break;
			default:
				return 'http://api.flickr.com/services/feeds/photos_public.gne?tags=' . $block->image_tags . '&format=php_serial';
				break;
		}
	} 

	private static function get_external_content( $url )
	{
		// Get PHP serialized object from Flickr
		$call = new RemoteRequest( $url );
		$call->set_timeout( 5 );
		$result = $call->execute( );
		if ( Error::is_error( $result ) ) {
			throw Error::raise( _t( 'Unable to contact Flickr.', 'flickrfeed' ) );
		}
		return $call->get_response_body( );
	}

	private static function parse_data( $block, $data )
	{
		// Unserialize and manipulate the data
		$flickrfeed = unserialize( $data );
		$flickrfeed = array_slice( $flickrfeed[ 'items' ], 0, $block->image_count );

		// Photo size
		foreach ( $flickrfeed as &$image ) {
			$image[ 'image_sizes' ] = array(
				'thumbnail' => str_replace( '_m.jpg', '_t.jpg', $image[ 'm_url' ] ),
				'small' => $image[ 'm_url' ],
				'medium' => $image[ 'l_url' ],
				'medium_z' => str_replace( '_m.jpg', '_z.jpg', $image[ 'm_url' ] ),
				'large' => str_replace( '_m.jpg', '_b.jpg', $image[ 'm_url' ] ),
				'original' => $image[ 'photo_url' ],
				'default' => $image[ 't_url' ],
			);
			if( isset( $image[ 'image_sizes' ][ $block->image_size ] ) ) {
				$image[ 'image_url' ] = $image[ 'image_sizes' ][ $block->image_size ];
			}
			else {
				$image[ 'image_url' ] = $image[ 'image_sizes' ][ 'default' ];
			}
		}

		return $flickrfeed;
	}

	public function action_block_content_flickrfeed( $block, $theme )
	{
		// Load defaults
		foreach ( self::default_options( ) as $k => $v ) {
			if ( !isset( $block->$k ) )
				$block->$k = $v;
		}
		
		$cache_name = 'flickrfeed_' . md5( serialize( array(
			$block->type,
			$block->user_id,
			$block->image_count,
			$block->image_size,
			$block->image_tags
			) ) );

		if ( $block->user_id != '' ) {
			if ( Cache::has( $cache_name ) ) {
				$block->images = Cache::get( $cache_name );
			}
			else {
				try {
					$url = self::build_api_url( $block );
					$data = self::get_external_content( $url );
					$images = self::parse_data( $block, $data );
					$block->images = $images;
					// Do cache
					Cache::set( $cache_name, $block->images, $block->cache );
				}
				catch ( Exception $e ) {
					$block->error = $e->getMessage( );
				}
			}
		}
		else {
			$block->error = _t( 'FlickrFeed Plugin is not configured properly.', 'flickrfeed' );
		}
	}

	public function action_block_form_flickrfeed( $form, $block )
	{
		// Load defaults
		foreach ( self::default_options( ) as $k => $v ) {
			if ( !isset( $block->$k ) )
				$block->$k = $v;
		}		

		$form->append( 'select', 'feed_type', $block, _t( 'Photostream Type', 'flickrfeed' ) );
		$form->feed_type->options = array(
			'public' => _t( 'Public photos & video', 'flickrfeed' ),
			'user' => _t( 'Public photos & video from you', 'flickrfeed' ),
			'friends' => _t( 'Your friends’ photostream', 'flickrfeed' ),
			'faves' => _t( 'Public favorites from you', 'flickrfeed' ),
			'group' => _t( 'Group pool', 'flickrfeed' )
		);
		$form->feed_type->add_validator( 'validate_required' );

		$form->append( 'text', 'user_id', $block, _t( 'Flickr ID ( You can get it from <a target="_blank" href="http://idgettr.com">idGettr</a>)', 'flickrfeed' ) );
		$form->user_id->add_validator( 'validate_flickr_id' );

		$form->append( 'text', 'image_count', $block, _t( '&#8470; of Photos', 'flickrfeed' ) );
		$form->image_count->add_validator( 'validate_uint' );
		$form->image_count->add_validator( 'validate_required' );

		$form->append( 'select', 'image_size', $block, _t( 'Photo Size', 'flickrfeed' ) );
		$form->image_size->options = array(
			'square' => _t( 'Square', 'flickrfeed' ),
			'thumbnail' => _t( 'Thumbnail', 'flickrfeed' ),
			'small' => _t( 'Small', 'flickrfeed' ),
			'medium' => _t( 'Medium ( 500 )', 'flickrfeed' ),
			'medium_z' => _t( 'Medium ( 640 )', 'flickrfeed' ),
			'large' => _t( 'Large', 'flickrfeed' ),
			'original' => _t( 'Original', 'flickrfeed' )
		);
		$form->image_size->add_validator( 'validate_required' );

		$form->append( 'text', 'image_tags', $block, _t( 'Tags ( comma separated, no space )', 'flickrfeed' ) );

		$form->append( 'text', 'cache_expiry', $block, _t( 'Cache Expiry ( in seconds )', 'flickrfeed' ) );
		$form->cache_expiry->add_validator( 'validate_uint' );
		$form->cache_expiry->add_validator( 'validate_required' );
	}

	/**
	 * On plugin init, add the template included with this plugin to the available templates in the theme
	 */
	public function action_init( )
	{
		$this->load_text_domain( 'flickrfeed' );
		$this->add_template( 'block.flickrfeed', dirname( __FILE__ ) . '/block.flickrfeed.php' );
	}
}
?>
