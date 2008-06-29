<?php

class MagicArchives extends Plugin
{
	function info()
	{
		return array(
			'name' => 'Magic Archives',
			'version' => '1.0',
			'url' => 'http://habariproject.org/',
			'author' => 'Habari Community',
			'authorurl' => 'http://habariproject.org/',
			'license' => 'Apache License 2.0',
			'description' => 'A little magician which reads your mind and... *presto* ...finds the posts you were thinking of.',
		);
	}

	public function action_init()
	{
		$this->add_template('magicarchives', dirname(__FILE__) . '/archives.php');
	}
	
	public function get_magic_archives() {
		$cache = 'magicarchives__posts';
		
		if(Cache::has($cache)) {
			$archives = Cache::get($cache);

		} else {
			$archives = Posts::get(array('nolimit' => true));
			Cache::set( $cache, $archives, 4000);
	
		}
		
		return $archives;
	}
	
	public function action_init_theme() {
		Stack::add( 'template_stylesheet', array( URL::get_from_filesystem(__FILE__) . '/magicarchives.css', 'screen' ), 'magicarchives' );

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/stringranker.js', 'stringranker' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/magicarchives.js', 'magicarchives' );
	}
}

?>