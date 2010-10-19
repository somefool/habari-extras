<?php 
 
class IEAdmin extends Plugin {
 
	const JS_VERSION = '2.1(beta4)'; // to be updated as this changes.
 
	public function info() {
		return array(
			'name' => 'IE_Admin',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'The Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'Adds the <a href="http://code.google.com/p/ie7-js/">ie7-js</a> stylesheet to the Habari Admin.',
		);
	}
 
 
	public function action_update_check()
	{
	 	Update::add( $this->info->name, $this->info->guid, $this->info->version );
	}
 
	public function configure()
	{
		$ui = new FormUI( 'ieadmin' );
		$ver = $ui->append( 'text', 'ieversion', 'ieadmin__ieversion', _t( 'IE version to be compatible with (e.g. 8)', 'ieadmin' ) );
		$ver->add_validator( 'validate_regex', '/^[7-9]*$/', _t( 'Please enter a valid version number between 7-9.', 'ieadmin' ) );
		$ui->append( 'text', 'jsversion','ieadmin__jsversion', _t( 'Script version, if not using "' . self::JS_VERSION . '"', 'isadmin' ) );
 
		$ui->append( 'submit', 'save', 'save' );
		return $ui;
	}
 
	public function action_admin_header_after( $theme )
	{
		$i_v = ( Options::get( 'ieadmin__ieversion' ) ? Options::get( 'ieadmin__ieversion' ) : 7 );
		$j_v = ( Options::get( 'ieadmin__jsversion' ) ? Options::get( 'ieadmin__jsversion' ) : self::JS_VERSION );

		echo "<!--[if lt IE $i_v]>\n\t<script src=\"http://ie7-js.googlecode.com/svn/version/$j_v/IE$i_v.js\"></script>\n\t<![endif]-->";
	}
 
}
?>

