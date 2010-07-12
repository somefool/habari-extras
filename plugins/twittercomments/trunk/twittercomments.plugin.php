<?php

class TwitterComments extends Plugin
{
	/**
	 * Remove fields, replace a single one for twitter username
	 * ...
	 * @return the form
	 **/
	public function action_form_comment( $form, $context = 'public' ) {

		$form->append( 'hidden', 'twitter_comment' )->value = true;

		// add the twitter username
		$form->append(
			'text',
			'twitter_username',
			'null:null',
			_t( 'Twitter Username', 'twittercomments' )
		)->add_validator(
			'validate_required',
			_t( 'Twitter username is required', 'twittercomments' )
		)->tabindex = 1;
		$form->move_before( $form->twitter_username, $form->cf_commenter );

		// remove the existing fields

		$form->cf_commenter->remove();
		$form->cf_email->remove();
		$form->cf_url->remove();

		return $form;
	}

	/**
	 * Populate the fields based on the twitter username
	 * ...
	 * @return the form
	 **/
	public function action_comment_form_submit ( $form ) {

		if( $form->twitter_comment->value ) {
			$form->append( 'hidden', 'cf_commenter' )->value = $form->twitter_username->value;
			$form->append( 'hidden', 'cf_email' )->value = '@' . $form->twitter_username->value;
			$form->append( 'hidden', 'cf_url' )->value = 'http://twitter.com/' . $form->twitter_username->value;
		}

		return $form;
	}

	/**
	 * Add commentinfo for only these twitter comments
	 *
	 * @param Comment The comment that will be processed before storing it in the database.
	 * @return Comment The comment result to store.
	 **/
	function action_comment_insert_before ( Comment $comment )
	{
		if ( isset( $_POST[ sprintf( '%x', crc32( 'twitter_comment' ) )] ) ) {
			$comment->info->twitter_comment = true;
		}
		return $comment;
	}


	/**
	 * Change priority to run after other plugins modifying the form,
	 * under the assumption they retain the default priority of 8
	 **/
	function set_priorities()
	{
		return array( 'action_form_comment' => 10, );
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( $this->info->name, $this->info->guid, $this->info->version );
	}

}

?>
