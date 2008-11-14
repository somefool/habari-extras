<?php


// tell Habari which class to use
define( 'THEME_CLASS', 'FunWithPhotoblogs' );

/**
 * A custom class for the Fun With Photoblogs theme
 */
class FunWithPhotoblogs extends Theme
{
	
	public $post_content_more = '';
	
	/**
	 * Execute on theme init to apply these filters to output
	 */
	public function action_init_theme()
	{
		Format::apply( 'nice_date', 'post_pubdate_out', 'l jS F Y' );
	}
	
	
	/**
	 * 
	 * @return 
	 */
	public function add_template_vars()
	{
		//the title to display on the manu bar
		$this->assign('menu_title',$this->calculate_menu_title()); 
				
		parent::add_template_vars();
	}
	
	/**
	 * 
	 * @return 
	 */
	public function act_display_home( $user_filters = array() ){
		//limit the home page to on post
		parent::act_display_entries( array('limit'=>1 ) );
	}	 
	
	public function action_template_header( )
	{		
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', Site::get_url('user') . '/themes/fun-with-photoblogs/js/script.js', 'fwpb-script' );
		
	}
	
	/**
	 * Return the title for the page
	 * @return String the title.
	 */
	public function the_title( $head = false ){
		$title = '';
		
		//check against the matched rule
		switch( $this->matched_rule->name ){
			case 'display_404':
				$title = 'Error 404';
			break;
			case 'display_entry':
				$title .= $this->post->title;
			break;
			case 'display_page':
				$title .= $this->post->title;
			break;
			case 'display_search':
				$title .= 'Search for ' . ucfirst( $this->criteria );
			break;
			case 'display_entries_by_tag':
				$title .= ucfirst( $this->tag ) . ' Tag';
			break;
			case 'display_entries_by_date':
				$title .= 'Archive for ';
				$archive_date = new HabariDateTime();
				if ( empty($date_array['day']) ){
					if ( empty($date_array['month']) ){
						//Year only
						$archive_date->set_date( $this->year , 1 , 1 );
						$title .= $archive_date->format( 'Y' );
						break;
					}
					//year and month only
					$archive_date->set_date( $this->year , $this->month , 1 );
					$title .= $archive_date->format( 'F Y' );
					break;
				}
				$archive_date->set_date( $this->year , $this->month , $this->day );
				$title .= $archive_date->format( 'F jS, Y' );
			break;
			case 'display_home':
				return Options::get( 'title' );
			break;
		}
		
		return $title;
	}
	
	/**
	 * Post content filter to find the photo information and formatit on the comments page. 
	 */
	public function filter_post_content_out( $content , $post ){
  		
		//if the plugin is being used
		if ( isset($post->info->photo) && !empty($post->info->photo) ){
			
			$this->post_content_more = $content;
			$content = sprintf('<img src="%s" alt="%s" />' , $post->info->photo , $post->title  );
			return $content;
			
		}
		
		//see if there is anything to extract
		if ( preg_match('/<!--details-->(.*)$/s' , $content , $matches) ) { 
			//keep the content for later
			$this->post_content_more = $matches[1];
			//remove the extras from the post here 
			$content = str_replace($matches[0] , '' , $content);
			}
		
		return $content;
  	}
	
	/**
	 * 
	 * @return 
	 */
	public function calculate_menu_title(){
		
		if (isset($this->post)){
			//if home page use the most recent post title and the date
			if ( $this->request->display_home ){
				if ( $this->posts instanceof Posts ){
				return $this->posts[0]->title_out . ' <span>' . $this->posts[0]->pubdate_out . '</span>';
				} else {
					//if there is a static home page then no title is needed.
					return '';
				}
			}
			if ( $this->request->display_page  ){
				return 'page';
			}
			if ( $this->request->display_entry  ){
				return $this->post->title_out . ' <span>' . $this->posts->pubdate_out . '</span>';
			}
	
		} 
		
		
	}

	/**
	 * Returns a link to the next post based on the post object passed to it.
	 * I am sure there should be a built in method of doing this but I can't find it, so...
	 * @return 
	 * @param $post Object A post obkect
	 */
	public function next_post_link( $post ){
		//get the next post
		if ( $next_post = $post->ascend() ) {
			return '<a href="'.$next_post->permalink.'" title="'.$next_post->title_out.'" class=next-post>'.$next_post->title_out.'</a>';	
		} else {
			return '';
		}
	}	
	
	/**
	 * Returns a link to the previous post based on the post object passed to it.
	 * I am sure there should be a built in method of doing this but I can't find it, so...
	 * @return 
	 * @param $post Object A post obkect
	 */
	public function prev_post_link( $post ){
		//get the next post
		if ( $prev_post = $post->descend() ) {
			return '<a href="'.$prev_post->permalink.'" title="'.$prev_post->title_out.'" class=prev-post>'.$prev_post->title_out.'</a>';	
		} else {
			return '';
		}
	}		


	/**
	 * Generate an HTML menu containing the site's pages.
	 * @return 
	 */
	public function pages_menu(){
		
		//get a list of pages
		$pages =  Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) );
				
		//start the html
		$pages_menu_html = '<ul id="pages_menu">' . PHP_EOL;
		
		//add the home page
		$pages_menu_html .= $this->make_link( 'Home' , Site::get_url( 'habari' ) ) . PHP_EOL;
				
		//get the titles and links for other pages		
		foreach( $pages as $page ){
			$pages_menu_html .= $this->make_link( $page->title , $page->permalink ) . PHP_EOL;
		}
		
		//add the feedurl
		//???? $theme->feed_alternate();
		//$pages_menu_html .= $this->make_link( 'Home' , Site::get_url( 'habari' ) );
				
		//end the html
		$pages_menu_html .= '</ul>' . PHP_EOL;
		
		//send it back
		return $pages_menu_html;
		
	}
	
	/**
	 * Generate and return the given title and url as an html link.
	 * @return String html link
	 * @param $title The title and display text
	 * @param $url The url to link to
	 */
	private function make_link( $title , $url){
		return '<a href="'.$url.'" title="'.$title.'">'.$title.'</a>';
	}
	
	public function fwpb_comment_class( $comment, $post )
	{
		$class= 'class="comment';
		if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
			$class.= '-unapproved';
		}
		// check to see if the comment is by a registered user
		if ( $u= User::get( $comment->email ) ) {
			$class.= ' byuser comment-author-' . Utils::slugify( $u->displayname );
		}
		if( $comment->email == $post->author->email ) {
			$class.= ' bypostauthor';
		}

		$class.= '"';
		return $class;
	}
	
}	
	
?>