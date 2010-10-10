<?php

	class Export extends Plugin {
		
		public function action_update_check ( ) {
			
			Update::add( 'Export', '41f7ab69-dddd-4308-b31e-92f3d1270123', $this->info->version );
			
		}
		
		public function action_plugin_activation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {

				ACL::create_token( 'export now', 'Export the Habari database.', 'Export' );
				
			}
			
		}
		
		public function action_plugin_deactivation ( $file = '' ) {
			
			if ( Plugins::id_from_file( $file ) == Plugins::id_from_file( __FILE__ ) ) {
				
				ACL::destroy_token( 'export now' );
				
			}
			
		}
		
		public function filter_plugin_config ( $actions, $plugin_id ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$actions[] = _t('Configure');
				
				// only users with the proper permission should be allowed to export
				if ( User::identify()->can('export now') ) {
					$actions[] = _t('Export');
				}
				
			}
			
			return $actions;
			
		}
		
		public function action_plugin_ui ( $plugin_id, $action ) {
			
			if ( $plugin_id == $this->plugin_id() ) {
				
				$frequencies = array(
					'manually' => _t('Manually'),
					'hourly' => _t('Hourly'),
					'daily' => _t('Daily'),
					'weekly' => _t('Weekly'),
					'monthly' => _t('Monthly'),
				);
				
				switch ( $action ) {
					
					case _t('Configure'):
						
						$ui = new FormUI( 'export' );
						//$ui->append( 'text', 'export_path', 'option:export__path', _t('Export path:'));
						//$ui->export_path->add_validator( 'validate_required' );
						
						$ui->append( 'select', 'export_freq', 'option:export__frequency', _t('Auto Export frequency:'), $frequencies );
						
						$ui->append( 'submit', 'save', _t( 'Save' ) );
						$ui->on_success( array( $this, 'updated_config' ) );
						
						$ui->out();
						
						break;
						
					case _t('Export'):
						
						$this->run(true);
						
						Utils::redirect( URL::get( 'admin', 'page=plugins' ) );
						
						break;
					
				}
				
			}
			
		}
		
		public function updated_config ( $ui ) {
			
			$ui->save();
			
			// if they selected an option other than manually, set up the cron
			$frequency = Options::get('export__frequency');
			
			// delete the crontab entry, if there is one
			CronTab::delete_cronjob('export');
			
			switch ( $frequency ) {
				
				case 'manually':
					// do nothing
					break;
					
				case 'hourly':
					CronTab::add_hourly_cron('export', array( $this, 'run' ));
					break;
					
				case 'daily':
					CronTab::add_daily_cron('export', array( $this, 'run' ));
					break;
					
				case 'weekly':
					CronTab::add_weekly_cron('export', array( $this, 'run' ));
					break;
					
				case 'monthly':
					CronTab::add_monthly_cron('export', array( $this, 'run' ));
					break;
				
			}
			
			return false;
			
		}
		
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
		
		public function run ( $download = false ) {
			
			if ( !$this->test_cache() ) {
				return false;
			}
			
			Plugins::act('export_run_before');
			
			$export = new SimpleXMLElement( '<?xml version="1.0" encoding="utf-8"?><blog xmlns="http://schemas.habariproject.org/BlogML.xsd" xmlns:xs="http://www.w3.org/2001/XMLSchema" />' );
			$export->addAttribute( 'root-url', Site::get_url('habari') );
			
			$export->addChild( 'title', Options::get('title') )->addAttribute( 'type', 'text' );
			$export->addChild( 'sub-title', Options::get('tagline') )->addAttribute( 'type', 'text' );
			
			// export all the blog's users
			$this->export_users( $export );
			
			// export all the blog's options
			$this->export_options( $export );
			
			// export all the blog's tags
			$this->export_tags( $export );
			
			// export all the blog's posts
			$this->export_posts( $export );
			
			EventLog::log( _t( 'Export completed!' ), 'info', 'export', 'export' );
			
			Plugins::act('export_run_after');
			
			$export = Plugins::filter('export_contents', $export);
			
			// output the xml!
			$xml = $export->asXML();
			
			// filter the xml as well, just for good measure
			$xml = Plugins::filter('export_contents_xml', $xml);
						
			// save the xml to the cache
			Cache::set('export_xml', $xml);
			
			if ( $download ) {
				$this->download();
			}
			
		}
		
		private function download ( ) {
			
			$timestamp = HabariDateTime::date_create('now')->format('YmdHis');
			
			$filename = 'habari_' . $timestamp . '.xml';
			
			// clear out anything that may have been output before us and disable the buffer
			ob_end_clean();
			
			header('Content-Type: text/xml');
			header('Content-disposition: attachment; filename=' . $filename);
			
			echo Cache::get( 'export_xml' );
			
			die();
			
		}
		
		private function test_cache ( ) {
			
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
			else {
				
				Cache::expire( 'export_test' );
				return true;
				
			}
			
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
			
			$tags = Tags::get();
			foreach ( $tags as $tag ) {
				
				$category = $categories->addChild( 'category' );
				$category->addAttribute( 'id', $tag->id );
				$category->addChild( 'title', $tag->tag_text )->addAttribute( 'type', 'text' );
				
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
		private function export_posts ( $export ) {
			
			$ps = $export->addChild( 'posts' );
			
			$posts = Posts::get( array( 'limit' => null ) );
			foreach ( $posts as $post ) {
				
				// create the post object
				$p = $ps->addChild( 'post' );
				
				// add all the basic post info
				$p->addAttribute( 'id', $post->id );
				$p->addAttribute( 'date-created', $post->pubdate->format('c') );
				$p->addAttribute( 'date-modified', $post->pubdate->format('c') );
				$p->addAttribute( 'approved', $post->status == Post::status('published') ? 'true' : 'false' );
				$p->addAttribute( 'post-url', $this->format_permalink( $post->permalink ) );
				$p->addAttribute( 'type', $post->content_type );
				$p->addChild( 'title', $post->title );
				$p->addChild( 'content', $post->content );
				$p->addChild( 'post-name', $post->slug );
				
				// now add the post tags
				$pt = $p->addChild( 'categories' );
				
				$tags = $post->tags;
				foreach ( $tags as $t_term => $t_slug ) {
					
					$tag = Tag::get( $t_term );
					
					$pt->addChild( 'category' )->addAttribute( 'ref', $tag->id );
					
				}
				
				// now add the post comments
				// @todo manually fetch unapproved comments for each post
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
					$c->addChild( 'content', $comment->content )->addAttribute( 'type', 'text' );
					
				}
				
				$p->addChild( 'authors' )->addChild( 'author' )->addAttribute( 'ref', $post->author->id );
				
			}
			
		}
		
	}

?>