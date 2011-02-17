<?php 

class HTML5Shiv extends Plugin {

	/**
	 * Set priority to move inserted tags nearer to the end
	 * @return array
	 **/
	public function set_priorities()
	{
		return array(
			'theme_header' => 11,
		);
	}

	/**
	 * Add tags to headers.
	 * @return array
	 **/
	public function theme_header( $theme )
	{
		return '<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->';
	}


}
?>
