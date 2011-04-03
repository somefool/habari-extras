<?php 

/**********************************
 *
 * Random Posts plugin for Habari
 * 
 *
 *********************************/

class RandomPosts extends Plugin
{
	private $config = array();
	private $random_posts = '';
 
	public function help() 
	{
		$help = _t( 'To use, add <code>&lt;?php $theme->random_posts(); ?&gt;</code> to your theme where you want the list output.' );
		return $help;
	}

	public function action_init()
	{
		$class_name = strtolower( get_class( $this ) );
		$this->config['num_posts'] = Options::get( $class_name . '__num_posts', 5 );
		$this->config['tags_exclude'] = Options::get( $class_name . '__tags_exclude' );
		$this->config['tags_include'] = Options::get( $class_name . '__tags_include' );
		$this->config['before_title'] = Options::get( $class_name . '__before_title' );
		$this->config['after_title'] = Options::get( $class_name . '__after_title' );
	}

	public function configure()
	{
		$ui = new FormUI( 'randomposts' );
		$num_posts = $ui->append( 'text', 'num_posts', 'randomposts__num_posts', _t( 'Default number of links to show', 'randomposts' ) );
		$tags_include = $ui->append( 'text','tags_include', 'randomposts__tags_include', _t( 'Comma-delimited list of tags to include', 'randomposts' ) );
		$tags_exclude = $ui->append( 'text','tags_exclude', 'randomposts__tags_exclude', _t( 'Comma-delimited list of tags to exclude', 'randomposts' ) );
		$before_title = $ui->append( 'text','before_title', 'randomposts__before_title', _t( 'HTML before title', 'randomposts' ) );
		$after_title = $ui->append( 'text','after_title', 'randomposts__after_title', _t( 'HTML after title', 'randomposts' ) );
 
		$ui->append( 'submit', 'save', 'save' );
		return $ui;
	}

	public function theme_random_posts()
	{
		static $random_posts = null; if( $random_posts != null ) { return $random_posts; }

		$params = array(
			'content_type' => Post::type( 'entry' ),
			'status' => Post::status( 'published' ),
		);

		$params[ 'limit' ] = $this->config[ 'num_posts' ];

		if ($this->config[ 'tags_include' ] != '' ) {
			$params[ 'tag' ] = explode( ',', $this->config[ 'tags_include' ] );
		}
		if ($this->config[ 'tags_exclude' ] != '' ) {
			$params[ 'not_tag' ] = explode( ',', $this->config[ 'tags_exclude' ] );
		}

		$params[ 'orderby' ] = DB::get_driver_name() == 'mysql' ? 'RAND()' : 'Random()';
 
		$posts = Posts::get( $params );
 
		$prefix = ( empty( $this->config[ 'before_title' ] ) ? '' : $this->config[ 'before_title' ] );
		$suffix = ( empty( $this->config[ 'after_title' ] )  ? '' : $this->config[ 'after_title' ] );
 
		foreach ( $posts as $post ) {
			$the_link = "<a href='{$post->permalink}'>{$post->title}</a>";
			$this->random_posts .= "$prefix$the_link$suffix\n";
		}

		return $this->random_posts;
	}
 
}
?>
