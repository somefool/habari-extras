<?php
/**
 * Highlighter Plugin
 *
 * Using this plugin is simple. To highlight inline source using GeSHi,
 * just surround your code in <div class="highlight php"> ... </div>
 *
 * Where "php" could be any language supported by GeSHi.
 *
 * I also recommend you use CDATA blocks (which the plugin will automatically
 * strip), just in case the plugin is ever disabled:
 *
 * <div class="highlight php">
 * <![CDATA[
 *   <?php
 *   $foo = 'bar';
 *   ?>
 * ]]>
 * </div>
 *
 * Note, you'll also need to grab GeSHi from http://qbnz.com/highlighter/ and
 * unpack the archive to: [habari directory]/3rdparty/geshi
 *
 * THIS PLUGIN REQUIRES HABARI 0.7-dev! IT WILL NOT WORK ON 0.6 (needs at least r3475)
 */

class HighlightPlugin extends Plugin
{
	
	public static $geshi_path = null;

	public function action_init() {
		spl_autoload_register( array( __CLASS__, '_autoload') );
		Format::apply( 'do_highlight', 'post_content_out' );
		Format::apply( 'do_highlight', 'comment_content_out' );
	}

	public static function _autoload( $class_name ) {
		
		if ( strtolower( $class_name ) == 'geshi' ) {
			
			$geshi_paths = array(
				dirname( __FILE__ ) . '/geshi',		// is there a geshi directory in our plugin?
				HABARI_PATH . '/3rdparty/geshi',		// check the old 3rdparty path first
				Site::get_dir('vendor') . '/geshi'	// fallback to the new vendor
			);
			
			foreach ( $geshi_paths as $gp ) {
				if ( file_exists( $gp . '/geshi.php' ) ) {
					self::$geshi_path = $gp;
					require( $gp . '/geshi.php' );
					return;
				}
			}
			
		}
	}
	
}

class HighlighterFormatPlugin extends Format
{

	public static function do_highlight( $in )
	{
		// Look, ma! No Regex!
		
		$tokenizer = new HTMLTokenizer( $in, false );
		$tokens = $tokenizer->parse();
		
		// fetch div, pre, code slices that have a class="highlight"
		$slices = $tokens->slice( array('div','pre','code') , array( 'class' => 'highlight' ) );
		
		// iterate the found slices
		foreach ($slices as $slice) {
			// store the class to use once we've stripped the container
			$classAttr = $slice[0]['attrs']['class'];
			
			// unique name to use in the cache for this slice/markup
			$sliceCacheName = 'plugin.highlight.' . md5( (string)$slice ) . filemtime( __FILE__ );
			
			// trim off the div, and determine the value
			$slice->trim_container();
			$sliceValue = trim( (string)$slice );
			
			// see if it's already been cached
			if ( Cache::has( $sliceCacheName ) ) {
				$output = Cache::get( $sliceCacheName );
			} else {
				// trim off the CDATA wrapper, if applicable
				if ( substr( $sliceValue, 0, 9 ) == '<![CDATA[' && substr( $sliceValue, -3 ) == ']]>' ) {
					$sliceValue = substr( $sliceValue, 9, -3 );
				}
				
				$classes = array_filter( explode( ' ', trim( str_replace( 'highlight', '', $classAttr ) ) ) ); // ugly, refactor
				
				$geshi = new Geshi( trim( $sliceValue ), isset( $classes[0] ) ? $classes[0] : 'php', HighlightPlugin::$geshi_path . '/geshi/' );
				$geshi->set_header_type( GESHI_HEADER_PRE );
				$geshi->set_overall_class( 'geshicode' );
				$output = @$geshi->parse_code(); // @ is slow, but geshi is full of E_NOTICE
				Cache::set( $sliceCacheName, $output );
			}
			$slice->tokenize_replace( $output );
			$tokens->replace_slice( $slice );
		}
		return (string) $tokens;
	}
}

?>
