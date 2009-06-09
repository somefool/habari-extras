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
		$this->add_template('page.archives', dirname(__FILE__) . '/page.archives.php');
		$this->add_template('magicarchives', dirname(__FILE__) . '/archives.php');
		$this->add_template('archive_posts', dirname(__FILE__) . '/posts.php');
	}
	
	public function theme_magic_archives($theme) {
		$tags= Tags::get(array('nolimit' => true));
		$theme->tags= $tags;
		
		$theme->posts= self::get_posts();
		
		$theme->display('magicarchives');
		
		return $archives;
	}
	
	public function action_ajax_archive_posts($handler) {
		$theme= Themes::create();
				
		$theme->posts= self::get_posts($handler->handler_vars['search']);
		
		$theme->display('archive_posts');
	}
	
	/**
	 * Weights a tag for the tag cloud
	 *
	 **/
	public static function tag_weight($count, $max)
	{
		return round( 10 * log($count + 1) / log($max + 1) );
	}
	
	public function action_init_theme() {
		Stack::add( 'template_stylesheet', array( URL::get_from_filesystem(__FILE__) . '/magicarchives.css', 'screen' ), 'magicarchives' );

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/magicarchives.js', 'magicarchives', array('jquery', 'ajax_manager') );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/ajax_manager.js', 'ajax_manager', 'jquery' );
		Stack::add( 'template_header_javascript', 'magicArchives.endpoint=\'' . URL::get('ajax', array('context' => 'archive_posts')) . '\'', 'magicurl', 'magicarchives');
	}
	
	
	public function action_ajax_getdates($param=array()) {
	$count = 0;
	$param = json_decode($param);
	$posts = Posts::get($param);
	?>	
	<div id="results">
	<?php
		if(isset($posts[0])) {
			foreach($posts as $post):
				$count++
					?>
					<div id="post-<?php echo $count; ?>" class="entry">
						<span id="name"><?php echo"$post->title"?></span>
						<span id="date"><?php echo "$post->pubdate_out" ?></span>

					</div>
					<?php
			endforeach;
		} else {
			?>
			<div id="none" class="entry">
				<span id="name">no posts where found</span>
			</div>
		<?php
		}
	?>
	</div>

	<?php
	}
	
	/**
	 * Fetch posts
	 *
	 * @param string Search query
	 * @param array Tags to filter by
	 * @param int The timestamp to use as the right-bound (lastest) border of fetched posts
	 * @param int The number of posts to fetch
	 * @return obj Posts object
	 **/
	static public function get_posts($search = null, $tags = array(), $right_bound = null, $limit = 20)
	{
		$params= array(
			'content_type' => Post::type('entry'),
			'limit' => $limit
		);
		
		if($search != null && $search != '') {
			$params['criteria']= $search;
		}
		
		if($tags != null && count($tags) > 0) {
			$params['all:tag']= $tags;
		}
		
		if($right_bound != null) {
			$params['before']= $right_bound;
		}
		
		$posts= Posts::get($params);
		return $posts;
	}
	
}

?>