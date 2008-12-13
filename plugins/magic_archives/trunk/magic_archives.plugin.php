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
	}
	
	public function theme_magic_archives($theme) {
		$tags= Tags::get(array('nolimit' => true));
		$theme->tags= $tags;
				
		$theme->display('magicarchives');
		
		return $archives;
	}
	
	public function action_init_theme() {
		Stack::add( 'template_stylesheet', array( URL::get_from_filesystem(__FILE__) . '/magicarchives.css', 'screen' ), 'magicarchives' );

		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/stringranker.js', 'stringranker' );
		Stack::add( 'template_header_javascript', URL::get_from_filesystem(__FILE__) . '/magicarchives.js', 'magicarchives' );
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
	
}

?>