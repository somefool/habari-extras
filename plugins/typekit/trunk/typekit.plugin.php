<?php 

class Typekit extends Plugin {

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Typekit', '8cbb17db-dc5f-4a28-ba75-c407e83b9303', $this->info->version );
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

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}
	
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$class_name = strtolower( get_class( $this ) );
					$ui = new FormUI( $class_name );

					$embed = $ui->append( 'text', 'embed', 'typekit__embed', _t( 'Embed Code (required)' ) );
					$embed->add_validator( 'validate_required' );
					$ui->append( 'static', 'clarification', _t( '<p>Typically this will look like <i>http://use.typekit.com/abcdef.js</i>, and can be found if you click <u>Embed Code</u> in the Kit Editor.</p>' ) );

					$ui->on_success( array($this, 'update_config' ) );
					$ui->append( 'submit', 'save', 'save' );
					$ui->out();
					break;
			}
		}
	}
	/**
	 * Give the user a session message to confirm options were saved.
	 **/
	public function update_config( $ui )
	{
		Session::notice( _t( 'Typekit Embed Code stored.', 'typekit' ) );
		$ui->save();
	}

	/**
	 * Add tags to headers.
	 * @return array
	 **/
	public function theme_header( $theme )
	{
		return $this->get_tags();
	}

	/**
	 * Generate tags for adding to headers.
	 * @return string Tags to add to headers.
	 **/
	private function get_tags()
	{
		$embed = Options::get( 'typekit__embed' );

		return "<script type=\"text/javascript\" src=\"$embed\"></script>" .
			"\n<script type=\"text/javascript\">try{Typekit.load();}catch(e){}</script>";
	}
}
?>
