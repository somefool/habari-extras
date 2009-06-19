<?php
class AutoSave extends Plugin {
	
	public function info() {
		return array(
			'name' => 'AutoSave',
			'version' => '0.1.1',
			'author' =>	'Andrew da Silva',
			'authorurl' => 'http://andrewdasilva.com/',
			'license' => 'Apache License 2.0',
			'description' => 'Saves post every minute if changes were made to the title or content. <strong>Do not use with the Revision plugin.</strong>',
			'copyright' => '2008'
		);
	}
	
	public function action_admin_theme_get_publish() {
		Stack::add('admin_header_javascript', array($this->get_url() . '/autosave.plugin.js'), 'autosave-plugin-js');
	}
	
	/**
	 * Altered copy of AdminHandler::post_publish():
	 * - Throws exceptions rather than Session notices so we can return errors to AJAX calls;
	 * - Does not redirect but echo a JSON object with the post's ID and slug
	 *
	 * @see AdminHandler::post_publish()
	 *
	 * @param AdminHandler $that The AdminHandler instance
	 * @param Theme $theme The Theme instance
	 */
	public function action_admin_theme_post_autosave($that, $theme) {
		$response = array();

		try {
			$form = $that->form_publish( new Post(), false );

			// check to see if we are updating or creating a new post
			if ( $form->post_id->value != 0 ) {
				$post = Post::get( array( 'id' => $form->post_id->value, 'status' => Post::status( 'any' ) ) );
				$post->title = $form->title->value;
				if ( $form->newslug->value == '' ) {
					throw new Exception(_t('A post slug cannot be empty. Keeping old slug.'));
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

				$post->status = $form->status->value;
			}
			else {
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

				$post = Post::create( $postdata );
			}

			if( $post->pubdate->int > HabariDateTime::date_create()->int && $post->status == Post::status( 'published' ) ) {
				$post->status = Post::status( 'scheduled' );
			}

			$post->info->comments_disabled = !$form->comments_enabled->value;

			Plugins::act('publish_post', $post, $form);

			$post->update( $form->minor_edit->value );
			$response['post_id'] = $post->id;
			$response['post_slug'] = $post->slug;
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