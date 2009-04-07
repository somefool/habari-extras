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
	 * Plugin information
	 * 
	 * @return array Plugin info array
	 */
	function info()
	{
		return array (
			'name' => 'Spam HoneyPot',
			'url' => 'http://seancoates.com/habari',
			'author' => 'Sean Coates',
			'authorurl' => 'http://seancoates.com/',
			'version' => '1.0.1',
			'description' => 'Entraps spammers with a honeypot comment field',
			'license' => 'Apache License 2.0',
		);
	}
	
	public function filter_final_output ( $out ) {
		// this sucks, fwiw, but there's no way to properly capture a comment form, currently
		$tokenizer = new HTMLTokenizer( $out, false );
		$tokens = $tokenizer->parse();
		$slices = $tokens->slice( 'textarea', array( 'id' => 'content' ) );
		// no comment form...
		if (!$slices) {
			return $out;
		}
		// should only be one:
		$slice = $slices[0];
		$sliceValue = trim( (string)$slice );
		$sliceValue .= '<div style="display: none;" id="honeypot">Hello fine '
						.'sir, please enter your '
						.'good content here (unless you are evil.. in which '
						.'case, do not):<textarea name="morecontent" '
						.'id="morecontent"></textarea></div>';
		$slice->tokenize_replace( $sliceValue );
		$tokens->replace_slice( $slice );
		return (string) $tokens;
	}
	
	/**
	 * Check submitted form for honeypot and qualify as spam accordingly
	 * 
	 * @param Comment The comment that will be processed before storing it in the database.
	 * @return Comment The comment result to store.
	 **/
	function action_comment_insert_before ( Comment $comment )
	{
		// This plugin ignores non-comments
		if($comment->type != Comment::COMMENT) {
			return $comment;
		}

		if (isset($_POST['morecontent']) &&  $_POST['morecontent'] != '') {
			$comment->status = Comment::STATUS_SPAM;
			$spamcheck[] = _t('Caught by the honeypot');
		}
	}

}

?>
