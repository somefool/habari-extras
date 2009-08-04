<?php

/**
 * SpamHoneyPot Class
 *
 * This plugin entraps the spammer by supplying a hidden, second textarea that
 * if filled, auto-qualifies the comment as spam.
 */
class SpamHoneyPot extends Plugin
{
/*	public function filter_final_output ( $out ) {
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
	}*/

	public function action_form_comment( $form, $context = 'public' ) {
		
		$form->append( 'text','morecontent','null:null', _t( 'stuff here' ) );
		return $form;
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

// 		if (isset($_POST['morecontent']) &&  $_POST['morecontent'] != '') {
		if (isset($_POST['morecontent']) &&  $_POST['morecontent'] != '') {
			$comment->status = Comment::STATUS_SPAM;
			$spamcheck[] = _t('Caught by the honeypot');
		}
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Spam Honeypot', '7dc5c83b-d4ec-4cc8-b65a-bd4139685bb4', $this->info->version );
	}

}

?>
