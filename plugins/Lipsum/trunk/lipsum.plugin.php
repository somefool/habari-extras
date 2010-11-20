<?php

/**
 * Lipsum Plugin Class
 *
 **/

class Lipsum extends Plugin
{
	private $thumbs = array();

	/**
	 * Adds a Configure action to the plugin
	 * 
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The id of a plugin
	 * @return array The array of actions
	 */
	public function filter_plugin_config ( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= _t('Configure');		
		}
		return $actions;
	}
	
	/**
	 * Creates a UI form to handle the plugin configuration
	 *
	 * @param string $plugin_id The id of a plugin
	 * @param array $actions An array of actions that apply to this plugin
	 */
	public function action_plugin_ui ( $plugin_id, $action )
	{
		if ( $this->plugin_id() == $plugin_id ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$form = new FormUI( strtolower(get_class( $this ) ) );
					
					$form->append( 'text', 'num_posts', 'option:lipsum__num_posts', _t('Number of posts to have present:', 'Lipsum'));
					$form->num_posts->add_validator( 'validate_lipsum_posts' );
					
					$form->append( 'submit', 'save', _t( 'Save' ) );
					
					$form->on_success( array( $this, 'updated_config' ) );
					$form->out();
				
					break;
			}
		}
	}
	
	public function filter_validate_lipsum_posts ( $valid, $value, $form, $container )
	{
		if(!is_numeric($value) || intval($value) != $value || $value < 0 ) {
			$valid[] = _t('This value must be a non-negative integer.', 'Lipsum');
		}
		return $valid;
	}

	public function action_plugin_activation ( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			// create the default option
			Options::set( 'lipsum__num_posts', 20 );
			
			// create initial posts
			$this->update_num_posts( 20 );
		}
	}

	public function action_plugin_deactivation ( $file )
	{
		if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
			// remove all the posts
			$this->update_num_posts( 0 );
			
			// delete the option
			Options::delete( 'lipsum__num_posts' );
		}
	}
	
	public function updated_config ( $form ) {
		$form->save();
		
		$this->update_num_posts( $form->num_posts->value );
		
		return $form->get( null, false );
	}
	
	public function update_num_posts ( $num_posts )
	{
		// get the current number of posts created by lipsum
		$current_count = intval( Posts::get( array( 'info' => array( 'lipsum' => true ), 'count' => true ) ) );
		
		// if we've already got the right number, do nothing
		if ( $num_posts == $current_count ) {
			Session::notice( _t( 'Did not change Lipsum post count from %d.  Nothing to do.', array( $current_count ), 'Lipsum' ) );
		}
		
		// if we've got too many posts, we need to remove some
		if ( $current_count > $num_posts ) {
			// how many do we need to dump?
			$limit = $current_count - $num_posts;
			
			// get the posts we're going to delete
			$posts = Posts::get( array( 'info' => array( 'lipsum' => true ), 'limit' => $limit ) );
			
			$count = 0;
			foreach ( $posts as $post ) {
				$post->delete();
				$count++;
			}
			
			Session::notice( _t( 'Removed %d sample posts and their comments.', array( $count ), 'Lipsum' ) );
		}
		
		// if we don't have enough posts, we need to create some
		if ( $current_count < $num_posts ) {
			// how many do we need to create?
			$limit = $num_posts - $current_count;
			
			// check for and get the user, creating it if needed
			$user = $this->get_user();
			
			// the initial time we'll start from
			$time = time() - 160;
			
			$count = 0;
			for ( $i = 0; $i < $limit; $i++ ) {
				// calculate a random time in the past
				$time = $time - mt_rand( 3600, 3600 * 36 );
				
				$this->make_post( $user, $time );
				
				$count++;
			}
			
			Session::notice( _t( 'Created %d sample posts with random comments.', array( $count ), 'Lipsum' ) );
		}
	}
	
	private function get_user ( ) {
		$user = User::get_by_name( 'lipsum' );
		
		// if it doesn't exist, create it
		if ( !$user ) {
			$user = User::create( array(
				'username' => 'lipsum',
				'email' => 'lipsum@example.com',
				'password' => md5( mt_rand() ),
			) );
		}
		
		// return the user object
		return $user;
	}
	
	/**
	 * make_post
	 * Makes a single post and adds a random number of comments (between 0 and 6)
	 * to the post
	 * @param object $user The Lipsum user
	 * @param timestamp $time The published timestamp of the new posts
	 */
	private function make_post ( $user, $time )
	{
		
		// start a transaction, so if we die due to a timeout (like in meller's php-cgi environment) we don't end up with broken posts and post info
		DB::begin_transaction();
		
		$post = Post::create( array(
			'title' => $this->get_title(),
			'content' => $this->get_content( 1, 3, 'some', array( 'thumb' => 1, 'ol' => 1, 'ul' => 1 ), 'cat' ),
			'user_id' => $user->id,
			'status' => Post::status('published'),
			'content_type' => Post::type('entry'),
			'tags' => 'lipsum',
			'pubdate' => HabariDateTime::date_create( $time ),
		) );
		
		$post->info->lipsum = true;
		$post->info->commit();
		
		// how many comments to create
		$comments = mt_rand( 0, 6 );
		
		// the initial time we'll start with
		$comment_time = time() - 160;
		
		for ( $i = 0; $i < $comments; $i++ ) {
			
			$comment_time = $comment_time + rand( 3600, 3600 * 24 );
			
			$comment = Comment::create( array(
				'post_id' => $post->id,
				'name' => $this->num2word( rand( 1, 9999 ) ),
				'url' => 'http://example.com/',
				'content' => $this->get_content( 1, 2, 'none', array(), 'cat' ),
				'status' => Comment::STATUS_APPROVED,
				'type' => Comment::COMMENT,
				'date' => HabariDateTime::date_create( $comment_time ),
			) );
			
			$comment->info->lipsum = true;
			$comment->info->commit();
		}
		
		// commit the transaction, we're done!
		DB::commit();
	}

	private function get_pgraph()
	{
		$start = array("Nam quis nulla", "Integer malesuada", "In an enim", "Sed vel lectus", "Donec odio urna,", "Phasellus rhoncus", "Aenean id ", "Vestibulum fermentum", "Pellentesque ipsum",  "Nulla non", "Proin in tellus", "Vivamus luctus", "Maecenas sollicitudin", "Etiam egestas", "Lorem ipsum dolor sit amet,", "Nullam feugiat,", "Aliquam erat volutpat", "Mauris pretium",);
		$mid = array(" a arcu imperdiet", " tempus molestie,", " porttitor ut,", " iaculis quis,", " metus id velit", " lacinia neque", " sed nisl molestie", " sit amet nibh", " consectetuer adipiscing", " turpis at pulvinar vulputate,", " erat libero tristique tellus,", " nec bibendum odio risus"," pretium quam", " ullamcorper nec,", " rutrum non,", " nonummy ac,", " augue id magna",);
		$end = array(" nulla.  "," malesuada.  "," lectus.  "," sem.  "," pulvinar.  "," faucibus fringilla.  "," dignissim sagittis.  "," egestas leo.  "," metus.  "," erat.  "," elit.  "," sit amet ante.  "," volutpat.  "," urna.  "," rutrum.  ",);

		$ipsum_text = '';
		$lines = rand(1,6);
		for($l = 0; $l < $lines; $l++) {
			$line = $start[rand(0,count($start)-1)];
			$mids = rand(1,3);
			for($z = 0; $z < $mids; $z++) $line .= $mid[rand(0,count($mid)-1)];
			$line .= $end[rand(0,count($end)-1)];
			$ipsum_text .= $line;
		}
		$ipsum_text .= "\n\n";
		return $ipsum_text;
	}

	private function num2word($i)
	{
		$word = '';
		$phon = array('do', 're', 'mi', 'fa', 'so', 'la', 'ti', 'ko', 'fu', 'jan');
		do {
			$word = $phon[$i % 10] . $word;
			$i = floor($i/10);
		} while($i >0);
		return $word;
	}

	private function get_title()
	{
		$text = $this->get_pgraph(1);
		$text = strtolower($text);
		$text = preg_replace('/[^a-z\s]/', '', $text);
		$text = explode(' ', $text);
		$words = rand(2, 8);
		$title = '';
		for($i = 0; $i < $words; $i++) {
			$title .= $text[rand(0, count($text)-1)] . ' ';
		}
		$title = ucwords(trim($title));
		return $title;
	}

	private function get_thumb_tag($tags)
	{
		if(count($this->thumbs) == 0) {
			$searchurl = 'http://www.flickr.com/services/rest/?method=flickr.photos.search&api_key=420fb7714e08dbcc97ac8228df21d985&license=4,2&per_page=10&tags=' . urlencode(implode(',', $tags));
			$results = RemoteRequest::get_contents($searchurl);
			preg_match_all('/<photo.*id="([0-9]+)".*owner="([^"]+)".*secret="([0-9a-f]+)".*server="([0-9]+)".*title="([^"]+)".*\/>/', $results, $matches, PREG_SET_ORDER);
			foreach($matches as $match) {
				list($fulltag, $id, $owner, $secret, $server, $title) = $match;
				$imgurl = "http://static.flickr.com/{$server}/{$id}_{$secret}_m.jpg";
				$flickrurl = "http://flickr.com/photos/{$owner}/{$id}";
				$styles = array (
					' style="float:left;"',
					' style="float:right;"',
					' style="display:block;"',
				);
				$style = $styles[rand(0,count($styles)-1)];
				$this->thumbs[] = "<a href=\"{$flickrurl}\"{$style}><img src=\"{$imgurl}\" alt=\"{$title}\"></a>";
			}
		}
		return (count($this->thumbs) > 0) ? $this->thumbs[rand(0,count($this->thumbs)-1)] : ''; 
	}

	private function get_content($min, $max, $more, $features, $imgtags)
	{
		$lipsum_text = '';
		$howmany = rand($min, $max);
		for($i = 0; $i < $howmany; $i++) {
			if(isset($features['thumb'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$lipsum_text .= $this->get_thumb_tag(explode(' ',$imgtags));
					unset($features['thumb']);
				}
			}
			$lipsum_text .= $this->get_pgraph();
			if(isset($features['ol'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ol>\n";
					for($z = 0; $z < $listitems; $z++) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ol>\n";
					unset($features['ol']);
				}
			}
			if(isset($features['ul'])) {
				if(rand(1, $max - $i + 1) == 1) {
					$listitems = rand(3,10);
					$lipsum_text .= "<ul>\n";
					for($z = 0; $z < $listitems; $z++) {
						$lipsum_text .= "\t<li>" . $this->get_title() . "</li>\n";
					}
					$lipsum_text .= "</ul>\n";
					unset($features['ul']);
				}
			}

			switch($more) {
			case 'none':
				break;
			case 'some':
				if(rand(1,2) == 1) break;
			case 'all':
				if($i==0 && $howmany > 1) {
					$lipsum_text .= '<!--more-->';
				}
			}
		}
		return $lipsum_text;
	}



}

?>

