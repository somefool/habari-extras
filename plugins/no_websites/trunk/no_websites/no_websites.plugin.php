<?php

class NoWebsites extends Plugin
{
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
		if ( isset( $comment->url ) && ! empty( $comment->url ) ) {
			// mark as spam.
			$comment->status = Comment::STATUS_SPAM;
			$spamcheck[] = _t( 'Website field filled.', 'nowebsites' );
		}

		// store spamcheck reason
		if ( isset( $comment->info->spamcheck ) && is_array( $comment->info->spamcheck ) ) {
			$comment->info->spamcheck = array_unique( array_merge( $comment->info->spamcheck, $spamcheck ) );
		}
		else {
			$comment->info->spamcheck = $spamcheck;
		}
		// note this just passes along the same spam rating after changing the status.
		return $spam_rating;
	}
}

?>
