<?php
 
  /**
   * Render_Type Plugin Class
   * 
   * This plugin adds a formatter called 'render_type' which templates can use
   * to render text, such as titles, as images in a chosen font using the data:
   * URI scheme. It requires ImageMagick and the PHP Imagick library. (It will
   * not work with GD.)
   *
   * Here is a minimal example of how you might use Render Type in
   * action_init_theme in your theme.php (see the documentation for 
   * filter_render_type below for more details):
   *
   * Format::apply( 'render_type', 'post_title_out', '/path/to/font.ttf', 28, 'black' );
   *
   * data: URIs work in all modern browsers except Internet Explorer
   * (which does not support them at all as of version 7). This plugin uses
   * conditional comments to hide the data: images from all versions of IE
   * and to show it the text instead, which of course you may style as
   * desired.
   *
   * The image data for a unique combination of input string, font, size, color,
   * background color, and output format (e.g. PNG, JPEG, etc.) is cached
   * after the first time it is requested, so performance should be reasonable,
   * though I've performed no load testing. This cache is currently never
   * expired -- not even if you deactivate the plugin. This may be
   * addressed in the future.
   *
   * (If you are dead set on providing rendered text to IE users, it would
   * probably not be too difficult to modify this plugin to write the images
   * out to disk and return an img element with the appropriate http: URI;
   * since I find the data: URI approach much cleaner, and since it degrades
   * gracefully, such modification is not a priority for me.)
   *
   * Nota bene: This plugin is not suitable for hiding email addresses from
   * harvesters -- the original text appears in the formatter's output in the
   * IE conditional comments and in the alt and title attributes of the img element.
   *
   * @package render_type
   **/

require_once 'render-type-formatter.php';
 
class RenderTypePlugin extends Plugin
{
  const VERSION='0.5';

  /**
   * function info
   * Returns information about this plugin
   * @return array Plugin info array
   **/
  public function info()
  {
    return array (
		  'name' => 'Render Type',
		  'url' => 'http://svn.habariproject.org/habari-extras/plugins/render-type/',
		  'author' => 'Eli Naeher',
		  'authorurl' => 'http://flyoverblues.com',
		  'version' => self::VERSION,
		  'description' => 'A plugin for rendering text inline as data: PNGs.',
		  'license' => 'This plugin is in the public domain.',
		  );
  }

  /**
   * function action_update_check
   * Check for more recent versions of this plugin.
   **/

  public function action_update_check ( )
  {
    Update::add( 'RenderType', 'FEFD6638-838E-11DD-8AFF-17DB55D89593', self::VERSION );
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

  public function filter_render_type ( $content,
				       $font_file, 
				       $font_size = 28,
				       $font_color = 'black',
				       $background_color = 'transparent',
				       $output_format = 'png' )
  {
    
    $cache_group = strtolower( get_class( $this ) );
    $cache_key = $font_file
      . $font_size
      . $font_color
      . $background_color
      . $output_format 
      . $content;

    if ( Cache::has( array ( $cache_group, $cache_key ) ) ) {
      $html_out = Cache::get( array ( $cache_group, $cache_key ) );
    } else {     
      $draw = new ImagickDraw();
      $draw->setFont ($font_file);
      $draw->setFontSize ($font_size);
      $draw->setFillColor($font_color);
      $draw->annotation (0, 50, $content);
      $canvas = new Imagick();
      $canvas->newImage (1000, $font_size * 2, $background_color, $output_format);
      $canvas->drawImage ($draw);
      $canvas->trimImage(0);
      
      $html_out = '
<!--[if IE]>' . $content . '<![endif]-->
<!--[if !IE]>--><img src="data:image/png;base64,' . base64_encode ($canvas) . '" title="' . $content . '" alt="' . $content . '"><!--<![endif]-->';

      Cache::set( array ( $cache_group, $cache_key ), $html_out );
    }

    return $html_out;

  }

} 

?>