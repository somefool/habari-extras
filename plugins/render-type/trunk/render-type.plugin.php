<?php

/**
 * RenderType Plugin Class
 *
 * This plugin adds a formatter called 'render_type' which templates can use
 * to render text, such as titles, as images in a chosen font. It requires
 * ImageMagick and the PHP Imagick library.(It will not work with GD.)
 *
 * Here is a minimal example of how you might use Render Type in
 * action_init_theme in your theme.php(see the documentation for
 * filter_render_type below for more details):
 *
 * Format::apply( 'render_type', 'post_title_out', '/path/to/font.ttf' );
 *
 * The image data for a unique combination of input string, font, size, color,
 * background color, and output format(e.g. PNG, JPEG, etc.) is cached
 * after the first time it is requested, so performance should be reasonable,
 * though I've performed no load testing. This cache is currently never
 * expired -- not even if you deactivate the plugin. This may be
 * addressed in the future.
 *
 * Nota bene: This plugin is not suitable for hiding email addresses from
 * harvesters -- the original text appears in the formatter's output in the
 * alt and title attributes of the img element.
 *
 * @package render_type
 **/

require_once 'render-type-formatter.php';

class RenderTypePlugin extends Plugin
{
	const VERSION = '0.5';

	/**
	 * function info
	 * Returns information about this plugin
	 * @return array Plugin info array
	 **/
	public function info()
	{
		return array(
			'name' => 'Render Type',
			'url' => 'http://svn.habariproject.org/habari-extras/plugins/render-type/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org',
			'version' => self::VERSION,
			'description' => 'A plugin for rendering text as images.',
			'license' => 'Public Domain.',
			);
	}

	/**
	 * function action_update_check
	 * Check for more recent versions of this plugin.
	 **/

	public function action_update_check()
	{
		Update::add( 'RenderType', 'FEFD6638-838E-11DD-8AFF-17DB55D89593', self::VERSION );
	}

	/**
	 * function theme_header
	 * Add CSS to our theme header.
	 **/

	public function theme_header( $theme )
	{
		Stack::add ( 'template_stylesheet', array( $this->get_url() . '/render-type.css', 'screen' ), 'render-type' );
	}

	/**
	 * function filter_render_type
	 * Returns HTML markup containing an image with a data: URI
	 * @param string $content The text to be rendered
	 * @param string $font_file The path to a font file in a format ImageMagick can handle
	 * @param integer $font_size The font size, in pixels (defaults to 28)
	 * @param string $font_color The font color (defaults to 'black')
	 * @param string $background_color The background color (defaults to 'transparent')
	 * @param string $output_format The image format to use (defaults to 'png')
	 * @return string HTML markup containing the rendered image
	 **/

	public function filter_render_type( $content, $font_file, $font_size, $font_color, $background_color, $output_format)
	{
		// Preprocessing $content
		// 1. Strip HTML tags. It would be better to support them, but we just strip them for now.
		// 2. Decode HTML entities to UTF-8 charaaters.
		$content = html_entity_decode( strip_tags( $content ), ENT_QUOTES, 'UTF-8' );

		$cache_group = strtolower( get_class( $this ) );
		$cache_key =
			$font_file .
			$font_size .
			$font_color .
			$background_color .
			$output_format .
			$content;

		if ( ! Cache::has( array( $cache_group, md5( $cache_key ) ) ) ) {
			$font_color = new ImagickPixel( $font_color );
			$background_color = new ImagickPixel( $background_color );
			$draw = new ImagickDraw();
			$draw->setFont( $font_file );
			$draw->setFontSize( $font_size );
			$draw->setFillColor( $font_color );
			$draw->setTextEncoding( 'UTF-8' );
			$draw->annotation(0, $font_size * 2, $content);
			$canvas = new Imagick();
			$canvas->newImage( 1000, $font_size * 5, $background_color );
			$canvas->setImageFormat( $output_format );
			$canvas->drawImage( $draw );
			// The following line ensures that the background color is set in the PNG
			// metadata, when using that format. This allows you, by specifying an RGBa
			// background color (e.g. #ffffff00) to create PNGs with a transparent background
			// for browsers that support it but with a "fallback" background color (the RGB
			// part of the RGBa color) for IE6, which does not support alpha in PNGs.
			$canvas->setImageBackgroundColor( $background_color );
			$canvas->trimImage( 0 );
			Cache::set( array( $cache_group, md5( $cache_key ) ), $canvas->getImageBlob() );
		}

		return '<span class="rendered-type" style="background-image: url(\''
				. URL::get( 'display_rendertype', array( 'hash' => md5( $cache_key ), 'format' => $output_format ) )
				. '\');">' . $content . '</span>';
	}

	public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule(array(
			'name' => 'display_rendertype',
			'parse_regex' => '%^rendertype/(?P<hash>[0-9a-f]{32}).(?P<format>png)$%i',
			'build_str' => 'rendertype/{$hash}.{$format}',
			'handler' => 'UserThemeHandler',
			'action' => 'display_rendertype',
			'rule_class' => RewriteRule::RULE_PLUGIN,
			'is_active' => 1,
			'description' => 'display_rendertype'
		));
		return $rules;
	}

	public function action_handler_display_rendertype( $handler_vars )
	{
		$cache_group = strtolower( get_class( $this ) );
		if( Cache::has( array( $cache_group, $handler_vars['hash'] ) ) ) {
			header( 'Content-type: image/' . $handler_vars['format'] );
			header( 'Pragma: ');
			header( 'Cache-Control: public' );
			header( 'Expires: ' . gmdate("D, d M Y H:i:s", strtotime("+10 years")) . ' GMT' );
			echo Cache::get( array( $cache_group, $handler_vars['hash'] ) );
		} else {
			echo 'Cache not found';
		}
	}
}

?>