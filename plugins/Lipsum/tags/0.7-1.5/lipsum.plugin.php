<?php

	class Lipsum extends Plugin {
		
		// ringmaster registered this API key, if you have any questions
		const FLICKR_API_KEY = '420fb7714e08dbcc97ac8228df21d985';
		
		public function action_plugin_activation ( $file ) {
			
			// set the default options
			Options::set( 'lipsum__num_posts', 20 );
			Options::set( 'lipsum__num_comments', 6 );
			Options::set( 'lipsum__num_tags', 10 );
			
			// and create the default number of posts
			$this->update_num_posts( 20 );
			
		}
		
		public function action_plugin_deactivation ( $file ) {
			
			// remove all the posts
			$this->update_num_posts( 0 );
			
			// delete the option
			Options::delete( 'lipsum__num_posts' );
			
		}
		
		public function configure ( ) {
			
			$ui = new FormUI( 'lipsum' );
			
			$ui->append( 'text', 'num_posts', 'option:lipsum__num_posts', _t( 'Number of posts to have present:', 'Lipsum' ) );
			$ui->num_posts->add_validator( 'validate_lipsum_numbers' );
			
			$ui->append( 'text', 'num_comments', 'option:lipsum__num_comments', _t( 'Max number of comments for each post:', 'Lipsum' ) );
			$ui->num_comments->add_validator( 'validate_lipsum_numbers' );
			
			$ui->append( 'text', 'num_tags', 'option:lipsum__num_tags', _t( 'Max number of tags for each post:', 'Lipsum' ) );
			$ui->num_tags->add_validator( 'validate_lipsum_numbers' );
			
			$ui->append( 'submit', 'save', _t( 'Save' ) );
			
			$ui->on_success( array( $this, 'updated_config' ) );
			
			$ui->out();
			
		}
		
		public function filter_validate_lipsum_numbers ( $valid, $value, $form, $container ) {
			
			if ( !is_numeric( $value ) || intval( $value ) != $value || $value < 0 ) {
				$valid[] = _t('This value must be a non-negative integer.', 'Lipsum');
			}
			
			return $valid;
			
		}
		
		public function updated_config ( $form ) {
			
			// make sure to save the values of the config form to the db
			$form->save();
			
			// update the number of posts
			$this->update_num_posts( $form->num_posts->value );
			
			return $form->get( null, false );
			
		}
		
		private function update_num_posts ( $num_posts ) {
			
			// get the current number of posts
			$current_count = intval( Posts::get( array( 'info' => array( 'lipsum' => true ), 'count' => true ) ) );
			
			if ( $num_posts == $current_count ) {
				
				// if we've already got the right number, do nothing
				Session::notice( _t( '%1$d posts already exist, nothing to do!', array( $current_count ), 'Lipsum' ) );
				
			}
			else if ( $current_count > $num_posts ) {
				
				// we need to delete posts, find out how many!
				$limit = $current_count - $num_posts;
				
				// get the posts we're going to delete
				$posts = Posts::get( array( 'info' => array( 'lipsum' => true ), 'limit' => $limit ) );
				
				foreach ( $posts as $post ) {
					$post->delete();
				}
				
				Session::notice( _t( 'Removed %1$d sample posts and their comments.', array( $limit ), 'Lipsum' ) );
				
			}
			else if ( $current_count < $num_posts ) {
				
				// we need to create posts. yippie! find out how many
				$limit = $num_posts - $current_count;
				
				// make sure the Lipsum user exists and get it
				$user = $this->get_user();
				
				// the initial time we'll start from - a little in the past
				$time = time() - 160;
				
				for ( $i = 0; $i < $limit; $i++ ) {
					
					// calculate a random time in the past for this post
					$time = $time - mt_rand( 3600, 3600 * 36 );		// between 1 hour and 36 hours before the last post
					
					$this->make_post( $user, $time );
					
				}
				
				Session::notice( _t( 'Created %1$d sample posts with random comments.', array( $limit ), 'Lipsum' ) );
				
			}
			
		}
		
		private function get_user ( ) {
			
			$user = User::get_by_name( 'lipsum' );
			
			// if it doesn't exist, create it instead
			if ( $user == false ) {
				
				$user = User::create( array(
					'username' => 'lipsum',
					'email' => 'lipsum@example.com',
					'password' => Utils::random_password( 16 ),
				) );
				
			}
			
			// return the user, however we got it
			return $user;
			
		}
		
		private function make_post ( $user, $time ) {
			
			// start a transaction, so if we die due to a timeout (like in meller's php-cgi environment) we don't end up with broken posts and post info
			DB::begin_transaction();
			
			$post = Post::create( array(
				'title' => $this->get_post_title(),
				'content' => $this->get_post_content(),
				'user_id' => $user->id,
				'status' => $this->get_post_status(),
				'content_type' => Post::type('entry'),
				'tags' => $this->get_post_tags(),
				'pubdate' => HabariDateTime::date_create( $time ),
			) );
			
			$post->info->lipsum = true;
			$post->info->commit();
			
			// how many comments should we create?
			$comments = mt_rand( 0, Options::get( 'lipsum__num_comments' ) );
			
			// the initial comment time we'll start from - a little after the post was created
			$comment_time = $time + 160;
			
			for ( $i = 0; $i < $comments; $i++ ) {
				
				// figure out the comment time for this one
				$comment_time = $comment_time + mt_rand( 3600, 3600 * 24 );		// between 1 hour and 24 hours after the last comment
				
				$this->make_comment( $post, $comment_time );
				
			}
			
			// commit the transaction, we're done!
			DB::commit();
			
		}
		
		private function make_comment ( $post, $time ) {
			
			$comment = Comment::create( array(
				'post_id' => $post->id,
				'name' => $this->get_comment_name(),
				'url' => 'http://example.com',
				'content' => $this->get_comment_content(),
				'status' => $this->get_comment_status(),
				'type' => Comment::COMMENT,
				'date' => HabariDateTime::date_create( $time ),
			) );
			
			$comment->info->lipsum = true;
			$comment->info->commit();
			
		}
		
		private function get_comment_name ( ) {
			
			return $this->generate_title( 1, 2 );
			
		}
		
		private function get_comment_status ( ) {
			
			$rand = mt_rand( 1, 10 );

			if ( $rand > 0 && $rand <= 5 ) {
				// give approved the highest probability
				return Comment::STATUS_APPROVED;
			}
			else if ( $rand > 5 && $rand <= 6 ) {
				// next up is spam
				return Comment::STATUS_SPAM;
			}
			else if ( $rand > 6 && $rand <= 8 ) {
				// unapproved
				return Comment::STATUS_UNAPPROVED;
			}
			else {
				// finally, deleted
				return Comment::STATUS_DELETED;
			}
			
		}
		
		private function get_post_content ( ) {
			
			// get between 1 and 3 paragraphs of content, some of them should have 'more' links
			// they should randomly include thumbnails, ordered lists, and unordered lists
			// and we should use flickr images with the 'cat' tag
			return $this->generate_content( 1, 3, 'some', array( 'thumb', 'ol', 'ul' ), 'cat' );
			
		}
		
		private function get_comment_content ( ) {
			
			// get between 1 and 2 paragraphs of content, none of them should have 'more' links
			// they shouldn't use any special features (including tagged images)
			return $this->generate_content( 1, 2 );
			
		}
		
		private function get_post_title ( ) {
			
			return $this->generate_title( 5, 8 );
			
		}
		
		private function get_post_tags ( ) {
			
			// how many are we configured to create?
			$num = Options::get( 'lipsum__num_tags' );
			
			// snag a title - try to keep it the right length
			$title = $this->generate_title( $num, $num );
			
			$title = MultiByte::strtolower( $title );
			$words = explode( ' ', $title );
			
			// always include lipsum
			$words[] = 'lipsum';
			
			return $words;
			
		}
		
		/**
		 * Generate random content for a post or comment.
		 * 
		 * @param int $min Minimum number of paragraphs to generate.
		 * @param int $max Maximum number of paragraphs to generate.
		 * @param string $more Include a <!--more--> tag in: 'none' of the posts, 'some' of the posts, or 'all' of the posts.
		 * @param string|array $features Single feature or array of features to include: thumb = thumbnails from flickr, ol = ordered lists, ul = unordered lists
		 * @param string|array $tags Single tag or array of tags to search flickr for. Only used if $features includes 'thumb'.
		 * @return string The generated HTML.
		 */
		private function generate_content ( $min = 1, $max = 5, $more = 'none', $features = array(), $tags = array() ) {
			
			if ( !is_array( $features ) ) {
				$features = array( $features );
			}
			
			// make features an array of feature => feature for easy access
			if ( !empty( $features ) ) {
				$features = array_combine( $features, $features );
			}
			
			if ( !is_array( $tags ) ) {
				$tags = array( $tags );
			}
			
			// figure out how many random paragraphs we should create between the min and max
			$how_many = mt_rand( $min, $max );
			
			$text = '';
			for ( $i = 0; $i < $how_many; $i++ ) {
				
				// should we include thumbnails?
				if ( isset( $features['thumb'] ) ) {
					
					// 50% probability we'll include one
					if ( mt_rand( 1, 2 ) == 1 ) {
						$text .= $this->generate_thumb( $tags );
					}
					
					// we've included a thumbnail, don't include another in this content
					unset( $features['thumb'] );
					
				}
				
				
				// generate a paragraph of text
				$text .= $this->generate_paragraph();
				
				
				// should we include an ordered list?
				if ( isset( $features['ol'] ) ) {
					
					// 50% probability we'll include one
					if ( mt_rand( 1, 2 ) == 1 ) {
						$text .= $this->generate_ol();
					}
					
					// we've included an ordered list, don't include another in this content
					unset( $features['ol'] );
					
				}
				
				
				// should we include an unordered list?
				if ( isset( $features['ul'] ) ) {
					
					// 50% probability we'll include one
					if ( mt_rand( 1, 2 ) == 1 ) {
						$text .= $this->generate_ul();
					}
					
					// we've included an unordered list, don't include another in this content
					unset( $features['ul'] );
					
				}
				
				
				// only add more tags if we just finished the first paragraph and we have more to go
				if ( $i == 0 && $i < $how_many ) {
					
					// if we're supposed to include more tags
					if ( $more == 'none' ) {
						// no tag, keep generating more content
						continue;
					}
					else if ( $more == 'some' ) {
						
						// 50% probability we'll include one
						if ( mt_rand( 1, 2 ) == 1 ) {
							$text .= '<!--more-->';
						}
						
					}
					else if ( $more == 'all' ) {
						// always include one
						$text .= '<!--more-->';
					}
					
				}
				
			}
			
			return $text;
			
		}
		
		/**
		 * Fetch a CC-licensed thumbnail from Flickr tagged with any of the given tags.
		 * 
		 * @param array $tags Tags to search for.
		 */
		private function generate_thumb ( $tags = array() ) {
			
			// cache thumbnails so we only make one http request
			static $thumbs;
			
			// if we haven't fetched thumbnails yet, do it
			if ( !isset( $thumbs ) ) {

				$licenses = '4,2,7';	// 4 = CC-Attribution, 2 = CC-Attribution-NonCommerical, 7 = no known copyright restrictions
				
				$url = 'http://www.flickr.com/services/rest?method=flickr.photos.search&api_key=' . self::FLICKR_API_KEY . '&license=' . $licenses . '&per_page=25&tags=' . urlencode( implode(',', $tags ) );
				
				// make the api request
				$results = RemoteRequest::get_contents( $url );
								
				// parse the xml
				$xml = new SimpleXMLElement( $results );
				
				$photos = array();
				foreach ( $xml->photos->photo as $photo ) {
					
					$photos[] = array(
						'id' => (string)$photo['id'],
						'owner' => (string)$photo['owner'],
						'secret' => (string)$photo['secret'],
						'server' => (string)$photo['server'],
						'title' => (string)$photo['title'],
						'img_url' => 'http://static.flickr.com/' . (string)$photo['server'] . '/' . (string)$photo['id'] . '_' . (string)$photo['secret'] . '_m.jpg',
						'url' => 'http://flickr.com/photos/' . (string)$photo['owner'] . '/' . (string)$photo['id'],
					);
					
				}
				
				// save the photos in our static var
				$thumbs = $photos;
				
			}

			// now pick out a thumbnail and return the html
			$thumb = $thumbs[ array_rand( $thumbs ) ];
			
			// the various styles we might apply
			$styles = array(
				'left' => 'style="float: left;"',
				'right' => 'style="float: right;"',
				'block' => 'style="display: block;"',
			);
			
			// pick out the style we'll use for this one
			$style = $styles[ array_rand( $styles ) ];
			
			$thumb_html = '<a href="' . $thumb['url'] . '" title="' . Utils::htmlspecialchars( $thumb['title'] ) . '" ' . $style . '><img src="' . $thumb['img_url'] . '"></a>';
			
			return $thumb_html;
			
		}
		
		/**
		 * Generate a paragraph of "random" lipsum copy.
		 * 
		 * @todo make $features work
		 * 
		 * @param int $min Minimum number of lines to generate.
		 * @param int $max Maximum number of lines to generate.
		 * @param array $features Features to include in the content: links, headings, blockquotes, abbr, acronym
		 * @return string The generated text.
		 */
		private function generate_paragraph ( $min = 1, $max = 6, $features = array() ) {
			
			// remember what your english teacher said, a paragraph has a beginning, middle, and end!
			$start = array( 'Nam quis nulla', 'Integer malesuada', 'In an enim', 'Sed vel lectus', 'Donec odio urna,', 'Phasellus rhoncus', 'Aenean id', 'Vestibulum fermentum', 'Pellentesque ipsum', 'Nulla non', 'Proin in tellus', 'Vivamus luctus', 'Maecenas sollicitudin', 'Etiam egestas', 'Lorem ipsum dolor sit amet,', 'Nullam feugiat,', 'Aliquam erat volutpat', 'Mauris pretium' );
			$mid = array( 'a arcu imperdiet', 'tempus molestie,', 'porttitor ut,', 'iaculis quis,', 'metus id velit', 'lacinia neque', 'sed nisl molestie', 'sit amet nibh', 'consectetuer adipiscing', 'turpis at pulvinar vulputate,', 'erat libero tristique tellus,', 'nec bibendum odio risus', 'pretium quam', 'ullamcorper nec,', 'rutrum non,', 'nonummy ac,', 'augue id magna' );
			$end = array( 'nulla.', 'malesuada.', 'lectus.', 'sem.', 'pulvinar.', 'faucibus fringilla.', 'dignissim sagittis.', 'egestas leo.', 'metus.', 'erat.', 'elit.', 'sit amet ante.', 'volutpat.', 'urna.', 'rutrum.' );		
			
			// how many lines do we want for this paragraph?
			$how_many_lines = mt_rand( $min, $max );
			
			$lines = array();
			for ( $i = 0; $i < $how_many_lines; $i++ ) {
				
				$s = $start[ array_rand( $start ) ];
				
				// how many middle blocks do we want in this line?
				$how_many_mids = mt_rand( 1, 3 );
				
				$mids = array();
				for ( $z = 0; $z < $how_many_mids; $z++ ) {
					$mids[] = $mid[ array_rand( $mid ) ];
				}
				
				// put it together
				$m = implode( ' ', $mids );
				
				$e = $end[ array_rand( $end ) ];
				
				// assemble the line
				$line = implode( ' ', array( $s, $m, $e ) );
				
				$lines[] = $line;
				
			}
			
			$paragraph = implode( ' ', $lines );
			
			return $paragraph;
			
		}
		
		private function generate_ol ( $min = 3, $max = 10 ) {
			
			$how_many = mt_rand( $min, $max );
			
			$lis = array();
			for ( $i = 0; $i < $how_many; $i++ ) {
				$lis[] = $this->generate_title();
			}
			
			$html = '<ol><li>' . implode( '</li><li>', $lis ) . '</li></ol>';
			
			return $html;
			
		}
		
		private function generate_ul ( $min = 3, $max = 10 ) {
			
			$how_many = mt_rand( $min, $max );
			
			$lis = array();
			for ( $i = 0; $i < $how_many; $i++ ) {
				$lis[] = $this->generate_title();
			}
			
			$html = '<ul><li>' . implode( '</li><li>', $lis ) . '</li></ul>';
			
			return $html;
			
		}
		
		private function generate_title ( $min = 2, $max = 8 ) {
			
			// get a fake paragraph of text that's 1 line long
			$text = $this->generate_paragraph( 1, 1 );
			
			$text = MultiByte::strtolower( $text );
			
			// remove commas and periods
			$text = MultiByte::str_replace( array( '.', ',' ) , '', $text );
			
			$words = explode( ' ', $text );
			
			// randomize the words list
			shuffle( $words );
			
			// we can only get the max number of words the paragraph generated
			if ( $min > count( $words ) ) {
				$min = count( $words );
			}
			
			if ( $max > count( $words ) ) {
				$max = count( $words );
			}
			
			// decide how many words we want
			$how_many_words = mt_rand( $min, $max );
			
			$title = array();
			for ( $i = 0; $i < $how_many_words; $i++ ) {
				
				// snag a random word
				$title[] = array_pop( $words );
				
			}
			
			$title = implode( ' ', $title );
			
			// capitalize the first letter of each word
			$title = MultiByte::ucwords( $title );
			
			return $title;
			
			
		}
		
		private function get_post_status ( ) {
			
			// @todo allow all the post statuses to be selected on the config page and pick one randomly here
			return Post::status('published');
			
		}
		
	}

?>