<?php

/**
 * SpamHoneyPot Class
 *
 * This plugin entraps the spammer by supplying a hidden, second textarea that
 * if filled, auto-qualifies the comment as spam.
 */
class SpamHoneyPot extends Plugin
{
	/**
	 * Register the new textarea template
	 *
	 */
	public function action_init() {
		$this->add_template( 'honeypot_text', dirname(__FILE__) . '/templates/honeypot_text.php' );
	}

	/**
	 * Create additional textarea
	 * ...
	 * @return the form
	 */
	public function action_form_comment( $form, $context = 'public' ) {

		$second_textarea = $form->append( 'text','more_content','null:null', _t( 'stuff here' ) );
		$second_textarea->template = 'honeypot_text';

		return $form;
	}

	/**
	 * Check comment for honeypot field and qualify as spam accordingly
	 *
	 * @param float $spam_rating The spamminess of the comment as detected by other plugins
	 * @param Comment $comment The submitted comment object
	 * @param array $handlervars An array of handlervars passed in via the comment submission URL
	 * @param array $extra An array of all fields passed to the comment form
	 * @return float The original spam rating
	 */
	function filter_spam_filter( $spam_rating, $comment, $handlervars, $extra )
	{
		// This plugin ignores non-comments
		if( $comment->type != Comment::COMMENT ) {
			return $spam_rating;
		}

		if( !empty( $extra[ 'more_content' ]) ) {
			$comment->status = Comment::STATUS_SPAM;
			$spamcheck[] = _t('Caught by the honeypot');
		}

		// store spamcheck reason
		if ( isset( $comment->info->spamcheck ) && is_array( $comment->info->spamcheck ) ) {
			$comment->info->spamcheck = array_unique( array_merge( $comment->info->spamcheck, $spamcheck ) );
		}
		else {
			$comment->info->spamcheck = $spamcheck;
		}

		return $spam_rating;
	}
}

?>
