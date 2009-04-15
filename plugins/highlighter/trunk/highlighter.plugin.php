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

class HighlightPlugin extends Plugin {

	public function info() {
		return array (
			'name' => 'Highlighter',
			'url' => 'http://seancoates.com/habari',
			'author' => 'Sean Coates',
			'authorurl' => 'http://seancoates.com/',
			'version' => '0.1.1',
			'description' => 'Highlighter',
			'license' => 'Apache License 2.0',
		);
	}

	public function action_init() {
		spl_autoload_register( array( __CLASS__, '_autoload') );
		Format::apply( 'do_highlight', 'post_content_out' );
		Format::apply( 'do_highlight', 'comment_content_out' );
	}

	public static function _autoload( $class_name ) {
		if ( strtolower( $class_name ) == 'geshi' ) {
			require HABARI_PATH . "/3rdparty/geshi/geshi.php";
		}
	}
}

class GeshiHighlighterFormatPlugin extends Format
{

	public static function do_highlight( $in )
	{
		// Look, ma! No Regex!
		
		$tokenizer = new HTMLTokenizer( $in, false );
		$tokens = $tokenizer->parse();
		$slices = $tokens->slice( array('div','pre','code') , array( 'class' => 'highlight' ) );
		foreach ($slices as $slice) {
			$classes = array_filter( explode( ' ', trim( str_replace( 'highlight', '', $slice[0]['attrs']['class'] ) ) ) ); // ugly, refactor
			$slice->trim_container(); // trims off the div
			$sliceValue = trim( (string)$slice );
			
			$sliceCacheName = 'plugin.highlight.' . md5($sliceValue);
			
			if ( false && Cache::has( $sliceCacheName ) ) {
				$geshiOutput = Cache::get( $sliceCacheName );
			} else {
				// capture the first class (not "highlight")
				if ( substr( $sliceValue, 0, 9 ) == '<![CDATA[' && substr( $sliceValue, -3 ) == ']]>' ) {
					// trim off CDATA wrapper:
					$sliceValue = substr( $sliceValue, 9, -3 );
				}
				$geshi = new Geshi( trim( $sliceValue ), isset( $classes[0] ) ? $classes[0] : 'php' );
				$geshi->set_header_type( GESHI_HEADER_PRE );
				$geshi->set_overall_class( 'geshicode' );
				$geshiOutput = @$geshi->parse_code(); // @ is slow, but geshi is full of E_NOTICE
				Cache::set( $sliceCacheName, '$geshiOutput' );
			}
			$slice->tokenize_replace( $geshiOutput );
			$tokens->replace_slice( $slice );
		}
		return (string) $tokens;
	}
}

?>