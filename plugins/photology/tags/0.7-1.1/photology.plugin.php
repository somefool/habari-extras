<?php
class Photology extends Plugin
{
	private	$uuid = '3e343f83-75cd-4648-91a6-22c4da399209';	

	public function action_init()
	{
	}

	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			if ( Options::get( 'photology__maxdim' ) == null ) {
				// Set a reasonable default
				Options::set( 'photology__maxdim', 100 );
			}
		}
	}

	function action_plugin_deactivation( $file )
	{
		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
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
				case _t('Configure') :
					$ui = new FormUI( strtolower( get_class( $this ) ) );

					$max_dimension = $ui->append( 'text', 'max_dimension', 'photology__maxdim', _t( 'Maximum size of thumbnail (length and width)' ) );
					$max_dimension->add_validator( array ( $this, 'validate_numeric' ) );
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->on_success( array( $this, 'update_config' ) );
					$ui->out();
					break;
				}
			}
	}

	public function validate_numeric( $value )
	{
		if( !is_numeric( $value ) ) {
			return array( _t( 'This field must be a number.', 'photology' ) );
		}
		return array();
	}

	/**
	 * Give the user a session message to confirm options were saved.
	**/
	public function update_config( $ui )
	{
		Session::notice( _t( 'Maximum Thumbnail Dimension set.', 'photology' ) );
		$ui->save();
	}

	/**
	 * function action_post_insert_after
	 * Invokes our thumbnail generating function when a new post is saved
	 * @param Post the post being added
	**/
	public function action_post_insert_after( $post )
	{
		$this->save_thumbnail( $post );
	}

	/**
	 * function action_post_update_after
	 * invokes our thumbnail generating function when a post is updated
	 * @param Post the post being updated
	**/
	public function action_post_update_after( $post )
	{
		$this->save_thumbnail( $post );
	}

	/**
	 * function save_thumbnail
	 * Determines whether a thumbnail needs to be created for this post, and adds it to the postinfo for this post
	 * @param Post the post for which the thumb should be generated
	**/
	public function save_thumbnail( $post )
	{
		// set up a temporary variable to capture the image tag(s)
		$matches= array();
		if ( preg_match( '/<img [^>]+>/', $post->content, $matches) ) {
			// we got one! Now tease out the src element
			$html= new HTMLTokenizer( $matches[0] );
			$tokens= $html->parse();
			foreach ($tokens as $node ) {
				if ( 'img' == $node['name'] ) {
					$elements= $node['attrs'];
				}
			}
		}
		if ( ! isset( $elements['src'] ) ) {
			// no src= found, so don't try to do anything else
			return;
		}

		$thumb= $post->info->photology_thumb;

		if ( ! isset( $thumb ) ) {
			// no thumbnail exists for this post yet, so make one
			$post->info->photology_thumb= $this->make_thumbnail( $elements['src'] );
			$post->info->photology_md5= md5_file( $this->get_image_file( $elements['src'] ) );
			$post->info->commit();
		} else {
			// a thumbnail exists; we should check whether we need to update it
			if (true) { // ( md5_file( $this->get_image_file( $elements['src'] ) ) != $post->info->photology_md5 ) {
				// the image has a different MD5 sum than the
				// one we previously calculated for it, so
				// generate a new thumbnail
				$post->info->photology_thumb= $this->make_thumbnail( $elements['src'] );
				$post->info->photology_md5= md5_file( $this->get_image_file( $elements['src'] ) );
				$post->info->commit();
			}
		}
	}

	/**
	 * function get_image_file
	 * Given a URL to an image, obtain the filesystem path to the image
	 * @param String the image URL
	 * @return String the filesystem path to the image
	**/
	public function get_image_file( $image )
	{
		return substr_replace( $image, Site::get_dir( 'user' ), 0, strlen( Site::get_url( 'user' ) ) );
	}

	/**
	 * function get_image_url
	 * Given the filesystem path to the image to an image, obtain a URL 
	 * @param String the filesystem path to the image
	 * @return String the image URL
	**/
	public function get_image_url( $image )
	{
		return substr_replace( $image, Site::get_url( 'user' ), 0, strlen( Site::get_dir( 'user' ) ) );
	}

	/**
	 * function make_thumbnail
	 * Create a thumbnail from an image URL
	 * @param String The image defined in the <img> tag
	 * @return String the URL of the generated thumbnail
	**/
	public function make_thumbnail( $image )
	{
		// Get maximum size from stored options
		$max_dimension= Options::get( 'photology__maxdim' ); 

		// Get the image from the filesystem
		$img= $this->get_image_file( $image );

		// Does derivative directory not exist?
		$thumbdir = dirname( $img ) . '/' . HabariSilo::DERIV_DIR . '';
		if( ! is_dir( $thumbdir ) ) {
			// Create the derivative directory
			if( ! mkdir( $thumbdir, 0755 ) ){
				// Couldn't make derivative directory
				return false;
			}
		}

		// Get information about the image
		list( $src_width, $src_height, $type, $attr )= getimagesize( $img );

		// Load the image based on filetype
		switch( $type ) {
			case IMAGETYPE_JPEG:
			$src_img = imagecreatefromjpeg( $img );
			break;
			
			case IMAGETYPE_PNG:
			$src_img = imagecreatefrompng( $img );
			break;

			case IMAGETYPE_GIF:
			$src_img = imagecreatefromgif( $img );
			break;
			
			default:
			return false;
		}

		// Did the image fail to load?
		if ( !$src_img ) {
			return false;
		}

		// Calculate the output size based on the original's aspect ratio
		if ( $src_width > $src_height ) 
		{
			$thumb_w = $max_dimension;
			$thumb_h = $src_height * $max_dimension / $src_width;
		} 
		else { // it's either portrait, or square
			$thumb_h = $max_dimension;
			$thumb_w = $src_width * $max_dimension / $src_height;
		}

		// Create the output image and copy to source to it
		$dst_img = ImageCreateTrueColor( $thumb_w, $thumb_h );
		imagecopyresampled( $dst_img, $src_img, 0,0,0,0, $thumb_w, $thumb_h, $src_width, $src_height );

		// Define the thumbnail filename
		$dst_filename = $thumbdir . '/' . basename( $img ) . ".photology_tb.jpg";

		// Save the thumbnail as a JPEG
		imagejpeg( $dst_img, $dst_filename );

		// Clean up memory
		imagedestroy( $dst_img );
		imagedestroy( $src_img );

		// Get back a URL - probably should just store the filename...
		$dst_url= $this->get_image_url( $dst_filename );

		return $dst_url;
	}

	/**
	 * post_filter_content_excerpt_out
	 * filters the post's excerpt to display only the thumbnail
	**/
	public function filter_post_content_excerpt_out ( $excerpt, $post )
	{
		if (isset( $post->info->photology_thumb ) ) {
			return '<img src="' . $post->info->photology_thumb . '">';
		} else {
			return $excerpt;
		}
	}	
}
?>

