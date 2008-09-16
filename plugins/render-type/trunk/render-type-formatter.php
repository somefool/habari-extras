<?php

class RenderTypeFormat extends Format
{
  public function render_type( $content,
			       $font_file, 
			       $font_size = 28,
			       $font_color = 'black',
			       $background_color = 'transparent',
			       $output_format = 'png' )
  {
    return Plugins::filter( 'render_type',
			    $content,
			    $font_file,
			    $font_size,
			    $font_color,
			    $background_color,
			    $output_format );
  }
}

?>