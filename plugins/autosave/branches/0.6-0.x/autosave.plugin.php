<?php
class AutoSave extends Plugin {
	
	public function action_admin_theme_get_publish() {
		Stack::add('admin_header_javascript', array($this->get_url() . '/autosave.plugin.js'), 'autosave');
		Stack::add('admin_header_javascript', 'autoSave.url=\'' . URL::get('ajax', array('context' => 'autosave')) . '\'', 'autosave-url');
	}
		
	/**
	 * Altered copy of AdminHandler::post_publish():
	 * - Throws exceptions rather than Session notices so we can return errors to AJAX calls;
	 * - Does not redirect but echo a JSON object with the post's ID and slug
	 *
	 * @see AdminHandler::post_publish()
	 *
	 * @param AjaxHandler $that The AjaxHandler instance
	 */
	public function action_ajax_autosave($handler) {
		$response = array();

		try {
			$post_id = 0;
			if ( isset($handler->handler_vars['id']) ) {
				$post_id = intval($handler->handler_vars['id']);
			}

			// If an id has been passed in, we're updating an existing post, otherwise we're creating one
			if ( 0 !== $post_id ) {
				$post = Post::get( array( 'id' => $post_id, 'status' => Post::status( 'any' ) ) );

				$this->theme->admin_page = sprintf(_t('Publish %s'), Plugins::filter('post_type_display', Post::type_name($post->content_type), 'singular')); 
				$form = $post->get_form( 'ajax' );

				$post->title = $form->title->value;
				if ( $form->newslug->value == '' ) {
					Session::notice( _t('A post slug cannot be empty. Keeping old slug.') );
				}
				elseif ( $form->newslug->value != $form->slug->value ) {
					$post->slug = $form->newslug->value;
				}
				$post->tags = $form->tags->value;

				$post->content = $form->content->value;
				$post->content_type = $form->content_type->value;
				// if not previously published and the user wants to publish now, change the pubdate to the current date/time
				// if the post pubdate is <= the current date/time.
				if ( ( $post->status != Post::status( 'published' ) )
					&& ( $form->status->value == Post::status( 'published' ) )
					&& ( HabariDateTime::date_create( $form->pubdate->value )->int <= HabariDateTime::date_create()->int )
					) {
					$post->pubdate = HabariDateTime::date_create();
				}
				// else let the user change the publication date.
				//  If previously published and the new date is in the future, the post will be unpublished and scheduled. Any other status, and the post will just get the new pubdate.
				// This will result in the post being scheduled for future publication if the date/time is in the future and the new status is published.
				else {
					$post->pubdate = HabariDateTime::date_create( $form->pubdate->value );
				}
				$minor = $form->minor_edit->value && ($post->status != Post::status('draft'));
				$post->status = $form->status->value;
			}
			else {
				$post = new Post();
				$form = $post->get_form( 'ajax' );
				$form->set_option( 'form_action', URL::get('admin', 'page=publish' ) );

				$postdata = array(
					'slug' => $form->newslug->value,
					'title' => $form->title->value,
					'tags' => $form->tags->value,
					'content' => $form->content->value,
					'user_id' => User::identify()->id,
					'pubdate' => HabariDateTime::date_create($form->pubdate->value),
					'status' => $form->status->value,
					'content_type' => $form->content_type->value,
				);
				$minor = false;

				$post = Post::create( $postdata );
			}

			if ( $post->pubdate->int > HabariDateTime::date_create()->int && $post->status == Post::status( 'published' ) ) {
				$post->status = Post::status( 'scheduled' );
			}

			$post->info->comments_disabled = !$form->comments_enabled->value;

			Plugins::act('publish_post', $post, $form);

			$post->update( $minor );

			$permalink = ( $post->status != Post::status( 'published' ) ) ? $post->permalink . '?preview=1' : $post->permalink;
			Session::notice( sprintf( _t( 'The post %1$s has been saved as %2$s.' ), sprintf('<a href="%1$s">\'%2$s\'</a>', $permalink, htmlspecialchars( $post->title ) ), Post::status_name( $post->status ) ) );
			if ( $post->slug != Utils::slugify( $post->title ) ) {
				Session::notice( sprintf( _t( 'The content address is \'%1$s\'.'), $post->slug ));
			}
			
			
			$response['post_id'] = $post->id;
			$response['post_slug'] = $post->slug;
			$response['messages'] = Session::messages_get( true, 'array' );
			
			ob_end_clean();
						
			echo json_encode($response);
						
			// Prevent rest of adminhandler to run, we only wanted to save!
			exit;
		} catch(Exception $e) {
			$response['error'] = $e->getMessage();
			ob_end_clean();
			echo json_encode($response);
			// Prevent rest of adminhandler to run, we only wanted to save!
			exit;
		}
	}

}
?>