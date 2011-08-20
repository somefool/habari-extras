<?php 

class Fontdeck extends Plugin {

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Fontdeck', '483f2c57-3e38-48cc-87c1-f38c28aec691', $this->info->version );
	}

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
	 * Simple plugin configuration 
	 * @return FormUI The configuration form 
	 **/ 
	public function configure() 
	{
		$ui = new FormUI( 'fontdeck' );
		$code = $ui->append( 'textarea', 'code', 'fontdeck__code', _t( 'Header code.<p>From the textarea "Paste this code into the <code>&lt;head&gt;</code> of your web page:"</p>', 'fontdeck' ) );
		$code->rows = 4; $code->cols = 50;
		$code->raw = true;
		$code->add_validator( 'validate_required' );

		$embed = $ui->append( 'textarea', 'stack', 'fontdeck__stack', _t( 'Selectors and font stacks.<p>From the textarea "Paste these CSS rules into your stylesheet and adapt the selectors as you need to:"</p>', 'fontdeck' ) );
		$embed->rows = 4; $embed->cols = 50;
		$embed->raw = true;
		$embed->add_validator( 'validate_required' );

		$ui->on_success( array( $this, 'updated_config' ) );

		$ui->append( 'submit', 'save', 'save' );

		return $ui; // should probably do this in Twitter??
	}
	/**
	 * Give the user a session message to confirm options were saved.
	 **/
	public function updated_config( $ui )
	{
		Session::notice( _t( 'Fontdeck CSS saved.', 'fontdeck' ) );
		$ui->save();
	}

	/**
	 * Add tags to headers.
	 * @return array
	 **/
	public function theme_header( $theme )
	{
		return $this->get_fonts() ."\n". $this->get_css();
	}

	/**
	 * Generate tags for adding to headers.
	 * @return string Tags to add to headers.
	 **/
	private function get_fonts()
	{
		return Options::get( 'fontdeck__code' );
	}

	/**
	 * Generate header style.
	 * @return string text to add to headers.
	 **/
	private function get_css()
	{
		$stack = Options::get( 'fontdeck__stack' );

		return "<style type=\"text/css\">\n$stack</style>\n";
	}


}
?>
