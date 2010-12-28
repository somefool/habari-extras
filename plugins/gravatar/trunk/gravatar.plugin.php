<?php
/**
 * @package Habari
 * @subpackage Gravatar
 *
 * To use, add <img src="<? echo $comment->gravatar ?>">
 * 
 */

/**
 * All plugins must extend the Plugin class to be recognized.
 */
class Gravatar extends Plugin {


	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Gravatar', 'c33ba010-3b9a-11dd-ae16-0800200c9a66', $this->info->version );
	}
	
	/**
	 * Return a URL to the author's Gravatar based on his e-mail address.
	 *
	 * @param object $comment The Comment object to build a Gravatar URL from.
	 * @return string URL to the author's Gravatar.
	 */
	public function filter_comment_gravatar( $out, $comment ) { 
		// The Gravar ID is an hexadecimal md5 hash of the author's e-mail address.
		$query_arguments= array( 'gravatar_id' => md5( strtolower( $comment->email ) ) );
		// Retrieve the Gravatar options.
		$options= Options::get( array( 'gravatar__default', 'gravatar__size', 'gravatar__rating' ) );
		foreach ( $options as $key => $value ) {
			if ( $value != '' ) {
				// We only want "default, size, rating".
				list( $junk, $key )= explode( '__', $key );
				$query_arguments[$key]= $value;
			}
		}
		// Ampersands need to be encoded to &amp; for HTML to validate.
		$query= http_build_query( $query_arguments, '', '&amp;' );
		$url= "http://www.gravatar.com/avatar.php?" . $query;
		
		return $url;
	}
	
	/**
	 * Handle calls from FormUI actions.
	 * Show the form to manage the plugin's options.
	 *
	 */
	public function configure() {
        $ui= new FormUI( 'gravatar' );
		$g_s_d= $ui->append( 'text', 'default', 'gravatar__default', '<dl><dt>Default Gravatar</dt><dd>An optional "default" parameter may follow that specifies the full, URL encoded URl, protocol included of a GIF, JPEG or PNG image that should be returned if either the request email address has no associated gravatar, or that gravatar has a rating higher than is allowed by the "rating" parameter.</dd></dl>' );
		$g_s_s= $ui->append( 'text', 'size', 'gravatar__size', '<dl><dt>Size</dt><dd>An optional "size" parameter may follow that specifies the desired width and height of the gravatar. Valid vaues are from 1 to 80 inclusive. Any size other than 80 will cause the original gravatar image to be downsampled using bicubic resampling before output.</dd></dl>' );
		//mark size as required
		$g_s_s->add_validator( 'validate_required' );

		$g_s_r= $ui->append( 'select', 'rating', 'gravatar__rating', '<dl><dt>Rating</dt><dd>An optional "rating" parameter may follow with a value of [ G | PG | R | X ] that determines the highest rating (inclusive) that will be returned.</dd></dl>', array( 'G' => 'G', 'PG' => 'PG', 'R' => 'R', 'X' => 'X' ) );
		$ui->append( 'submit', 'save', _t('Save') );
		$ui->out();
	}
	
}
?>
