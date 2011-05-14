<?php

	class Export extends Plugin {
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				ACL::create_token( 'export now', 'Export the Habari database', 'Export' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::destroy_token( 'export now' );
				
			}
			
		}
		
		public function filter_plugin_config ( $actions, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
								
				// only users with the proper permission should be allowed to export
				if ( User::identify()->can('export now') ) {
					$actions[] = _t('Export');
					$actions[] = _t('Export as WXR');
				}
				
			}
			
			return $actions;
			
		}
		
		public function action_plugin_ui ( $plugin_id, $action ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				switch ( $action ) {
						
					case _t('Export as BlogML'):
						
						$this->run(true, 'blogml');
						
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
						
					case _t('Export as WXR'):
						
						$this->run(true, 'wxr');
						
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
					
				}
				
			}
			
		}
		
		/**
		 * This is the beginnings of a DOM-based export implementation (instead of SimpleXML, as the real one is).
		 * It got significanlty more difficult than I wanted to deal with, so I didn't worry about it at the time.
		 * 
		 * This was going to be preferred over SimpleXML because SimpleXML doesn't add CDATA blocks.
		 */
		public function run_dom ( ) {
			
			ob_end_clean();
			
			// test the cache
			$cache = Cache::set('export_test', 'test');
			
			if ( $cache == null ) {
				// we can't export!
				EventLog::log( _t( 'Unable to write to the cache, export failed!' ), 'critical', 'export', 'export' );
				
				if ( User::identify() ) {
					Session::error( _t( 'Unable to write to the cache, export failed!' ) );
				}
				
				return false;
			}
			
			$dom = new DOMDocument('1.0', 'utf-8');
			$dom->formatOutput = true;
			
			// create the root blog element
			$blog = $dom->appendChild( new DOMElement( 'blog' ) );
			$blog->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns', 'http://localhost/blogml.xsd' );
			$blog->setAttributeNS( 'http://www.w3.org/2000/xmlns/', 'xmlns:xs', 'http://www.w3.org/2001/XMLSchema' );
			
			$blog->setAttribute( 'root-url', Site::get_url( 'habari' ) );
			$blog->setAttribute( 'date-created', HabariDateTime::date_create()->format('c') );
			
			$title = $dom->createElement( 'title', '' );
			$title->setAttribute( 'type', 'text' );		// type attribute is optional
			$title->appendChild( $dom->createCDATASection( Options::get( 'title' ) ) );
			
			$dom->appendChild( $title );
			
			$sub_title = $dom->createElement( 'sub-title', '' );
			$sub_title->appendChild( $dom->createCDATASection( Options::get( 'tagline' ) ) );
			
			echo $dom->saveXML();
			
			die();
			
		}
		
		public function run ( $download = false, $format = 'blogml' ) {
			
			Plugins::act('export_run_before');
			
			if ( $format == 'blogml' ) {
			
				$export = new SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><blog xmlns="http://schemas.habariproject.org/BlogML.xsd" xmlns:xs="http://www.w3.org/2001/XMLSchema" />' );
				$export->addAttribute( 'root-url', Site::get_path('habari', true) );
				$export->addAttribute( 'date-created', HabariDateTime::date_create()->format( DateTime::W3C ) );
				
				$export->addChild( 'title', Options::get('title') )->addAttribute( 'type', 'text' );
				$export->addChild( 'sub-title', Options::get('tagline') )->addAttribute( 'type', 'text' );
				
				// export all the blog's users
				$this->export_users( $export );
				
				// export all the blog's options
				$this->export_options( $export );
				
				// export all the blog's tags
				$this->export_tags( $export );
				
				// export all the blog's posts and pages
				$this->export_posts( $export, array( 'entry', 'page' ) );
			
			}
			else if ( $format == 'wxr' ) {
				
				$export = new SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.0/" />');
				$channel = $export->addChild( 'channel' );
				
				$channel->title = Options::get('title');
				$channel->link = Site::get_url( 'habari' );
				$channel->description = Options::get( 'tagline' );
				$channel->pubDate = HabariDateTime::date_create()->format( DateTime::RSS );
				$channel->generator = 'Habari/' . Version::get_habariversion() . '-Export/' . $this->info->version;
				$channel->{'wp:wxr_version'} = '1.0';
				$channel->{'wp_base_site_url'} = Site::get_url( 'host' );
				$channel->{'wp_base_blog_url'} = Site::get_url( 'habari' );
				
				// export all the blog's tags
				$this->export_tags_wxr( $export );
				
				// export all the blog's posts and pages
				$this->export_posts_wxr( $export, array( 'entry', 'page' ) );
				
			}
			
			EventLog::log( _t( 'Export completed!' ), 'info', 'export', 'export' );
			
			Plugins::act('export_run_after');
			
			$export = Plugins::filter('export_contents', $export);
			
			// output the xml!
			$xml = $export->asXML();
			
			// filter the xml as well, just for good measure
			$xml = Plugins::filter('export_contents_xml', $xml);
			
			if ( $download ) {
				$this->download( $xml );
			}
			else {
				return $xml;
			}
			
		}
		
		private function download ( $xml ) {
			
			$timestamp = HabariDateTime::date_create('now')->format('YmdHis');
			
			$filename = 'habari_' . $timestamp . '.xml';
			
			// clear out anything that may have been output before us and disable the buffer
			ob_end_clean();
			
			header('Content-Type: text/xml');
			header('Content-disposition: attachment; filename=' . $filename);
			
			echo $xml;
			
			die();
			
		}
		
		/**
		 * Export all the authors / users on a blog to the SimpleXML object.
		 * 
		 * @param SimpleXMLElement $export The SimpleXML element we're using for our export.
		 * @return void
		 */
		private function export_users( $export ) {
			
			$authors = $export->addChild( 'authors' );
			
			$users = Users::get();
			foreach ( $users as $user ) {
				
				$author = $authors->addChild( 'author' );
				$author->addAttribute( 'id', $user->id );
				$author->addAttribute( 'approved', 'true' );
				$author->addAttribute( 'email', $user->email );
				$author->addChild( 'title', $user->displayname )->addAttribute( 'type', 'text' );
				
			}
			
		}
		
		/**
		 * Export all the options stored on the blog to the SimpleXML object.
		 * 
		 * @param SimpleXMLElement $export The SimpleXML element we're using for our export.
		 * @return void
		 */
		private function export_options ( $export ) {
			
			$properties = $export->addChild( 'extended-properties' );
			
			$options = DB::get_results( 'select name, value, type from {options}' );
			foreach ( $options as $option ) {
				
				$property = $properties->addChild( 'property' );
				$property->addAttribute( 'name', $option->name );
				$property->addAttribute( 'value', $option->value );
				
			}
			
		}
		
		/**
		 * Export all the tags that exist on the blog to the SimpleXML object.
		 * 
		 * @param SimpleXMLElement $export The SimpleXML element we're using for our export.
		 * @return void
		 */
		private function export_tags ( $export ) {
			
			$categories = $export->addChild( 'categories' );
			
			$tags = Tags::vocabulary()->get_tree();
			foreach ( $tags as $tag ) {
				
				$category = $categories->addChild( 'category' );
				$category->addAttribute( 'id', $tag->id );
				$category['description'] = $tag->term_display;		// overloading because it needs escaping
				
				$category->title = $tag->term;
				$category->title['type'] = 'text';
				
			}
			
		}
		
		private function export_tags_wxr ( $export ) {
			
			$tags = Tags::vocabulary()->get_tree();
			foreach ( $tags as $tag ) {
				
				$t = $export->addChild( 'wp:tag' );
				$t->{'wp:tag_slug'} = $tag->term;
				$t->{'wp:tag_name'} = $tag->term_display;
				
			}
			
		}
		
		private function format_permalink ( $url ) {
			
			// get the base url to trim off
			$base_url = Site::get_url( 'habari' );
			
			if ( MultiByte::strpos( $url, $base_url ) !== false ) {
				$url = MultiByte::substr( $url, MultiByte::strlen( $base_url ) );
			}
			
			return $url;
			
		}
		
		/**
		 * Export all the posts on the blog, including their tags, comments, and authors.
		 * 
		 * @param SimpleXMLElement $export The SimpleXML element we're using for our export.
		 * @return void
		 */
		private function export_posts ( $export, $content_type = array( 'entry', 'page' ) ) {
			
			$ps = $export->addChild( 'posts' );
			
			$posts = Posts::get( array( 'limit' => null, 'content_type' => $content_type ) );
			foreach ( $posts as $post ) {
				
				// create the post object
				$p = $ps->addChild( 'post' );
				
				// figure out the type of the post
				if ( $post->content_type == Post::type( 'entry' ) ) {
					$type = 'normal';		// blog posts are normal
				}
				else {
					$type = 'article';		// articles are pages, i think
				}
				
				
				// add all the basic post info
				$p->addAttribute( 'id', $post->id );
				$p->addAttribute( 'date-created', $post->pubdate->format('c') );
				$p->addAttribute( 'date-modified', $post->modified->format('c') );
				$p->addAttribute( 'approved', $post->status == Post::status('published') ? 'true' : 'false' );
				$p->addAttribute( 'post-url', $this->format_permalink( $post->permalink ) );
				$p->addAttribute( 'type', $type );
				
				// the post title is already being escaped somewhere, so don't use overloading to escape it again
				$p->addChild( 'title', $post->title );
				
				// we use attribute overloading so they get escaped properly
				$p->content = $post->content;
				$p->{'post-name'} = $post->slug;
				
				// now add the post tags
				$pt = $p->addChild( 'categories' );
				
				$tags = $post->tags;
				foreach ( $tags as $tag ) {
					
					$pt->addChild( 'category' )->addAttribute( 'ref', $tag->id );
					
				}
				
				// now add the post comments
				// @todo add support for trackbacks from the pingback plugin?
				$pc = $p->addChild( 'comments' );
				
				$comments = $post->comments;
				foreach ( $comments as $comment ) {
					
					$c = $pc->addChild( 'comment' );
					$c->addAttribute( 'id', $comment->id );
					$c->addAttribute( 'date-created', $comment->date->format('c') );
					$c->addAttribute( 'date-modified', $comment->date->format('c') );
					$c->addAttribute( 'approved', $comment->status == Comment::STATUS_APPROVED ? 'true' : 'false' );
					$c->addAttribute( 'user-name', $comment->name );
					$c->addAttribute( 'user-url', $comment->url );
					
					$c->addChild( 'title' );
					
					$content = $c->content = $comment->content;
					$content['type'] = 'text';
					
				}
				
				$p->addChild( 'authors' )->addChild( 'author' )->addAttribute( 'ref', $post->author->id );
				
			}
			
		}
		
		private function export_posts_wxr ( $export, $content_type = array( 'entry', 'page' ) ) {
			
			$posts = Posts::get( array( 'limit' => null, 'content_type' => $content_type ) );
			foreach ( $posts as $post ) {
				
				// create the item object
				$item = $export->addChild( 'item' );
				
				// add all the basic post info
				$item->id = $post->id;
				$item->pubDate = $post->pubdate->format( DateTime::RSS );
				$item->{'dc:creator'} = $post->author->username;
				$item->guid = $post->permalink;
				$item->guid['isPermalink'] = 'true';
				$item->description = '';
				$item->{'content:encoded'} = $post->content;
				
				// the post title is already being escaped somewhere, so don't use overloading to escape it again
				$item->addChild( 'title', $post->title );
				
				$item->{'wp:post_id'} = $post->id;
				$item->{'wp:post_date'} = $post->pubdate->format( DateTime::ISO8601 );
				$item->{'wp:post_date_gmt'} = $post->pubdate->set_timezone( 'UTC' )->format( DateTime::ISO8601 );
				$item->{'wp:comment_status'} = $post->info->comments_disabled ? 'closed' : 'open';
				$item->{'wp:ping_status'} = $post->info->comments_disabled ? 'closed' : 'open';
				$item->{'wp:post_name'} = $post->slug;
				$item->{'wp:status'} = $post->status == Post::status('published') ? 'published' : 'draft';
				$item->{'wp:post_parent'} = 0;
				$item->{'wp:menu_order'} = 0;
				$item->{'wp:post_type'} = $post->typename;
				$item->{'wp:post_password'} = '';
				
				$tags = $post->tags;
				foreach ( $tags as $tag ) {
					
					$category = $item->addChild( 'category', $tag->term );
					$category['domain'] = 'tag';
					$category['nicename'] = $tag->term_display;
					
				}
				
				// now add the post comments
				// @todo add support for trackbacks from the pingback plugin?
				foreach ( $post->comments as $comment ) {
					
					$c = $item->addChild( 'wp:comment' );
					$c->{'wp:comment_id'} = $comment->id;
					$c->{'wp:comment_author'} = $comment->name;
					$c->{'wp:comment_author_email'} = $comment->email;
					$c->{'wp:comment_author_url'} = $comment->url;
					$c->{'wp:comment_author_IP'} = $comment->ip;
					$c->{'wp:comment_date'} = $comment->date->format( DateTime::RSS );
					$c->{'wp:comment_date_gmt'} = $comment->date->set_timezone( 'UTC' )->format( DateTime::RSS );
					$c->{'wp:comment_content'} = $comment->content;
					$c->{'wp:comment_approved'} = $comment->status == Comment::STATUS_APPROVED ? 'true' : 'false';
					$c->{'wp:comment_type'} = Comment::type_name( $comment->type );
					$c->{'wp:comment_parent'} = 0;
					
					$user = User::get_by_email( $comment->email );
					
					if ( $user ) {
						$c->{'wp:comment_user_id'} = $user->id;
					}
					
				}
				
			}
			
		}
		
	}

?>