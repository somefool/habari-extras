<?php 

/**
 * http://portal.solvemedia.com/portal/help/pub/php/
 **/
class SolveMedia extends Plugin {

	public function configure()
	{
		$ui = new FormUI( 'solvemedia' );
	
		$ckey = $ui->append( 'text', 'ckey', 'solvemedia__ckey', _t( 'Challenge Key (C-key)', 'solvemedia' ) );
		$ckey->add_validator( 'validate_required' );
		$vkey = $ui->append( 'text', 'vkey', 'solvemedia__vkey', _t( 'Verification Key (V-key)', 'solvemedia' ) );
		$vkey->add_validator( 'validate_required' );
		$hkey = $ui->append( 'text', 'hkey', 'solvemedia__hkey', _t( 'Authentication Hash Key (H-key)', 'solvemedia' ) );
		$hkey->add_validator( 'validate_required' );
		$themes = array( 
			'red' => _t( 'Red', 'solvemedia' ),
			'white' => _t( 'White', 'solvemedia' ),
			'black' => _t( 'Black', 'solvemedia' ),
			'purple' => _t( 'Purple', 'solvemedia' ),
		);
		$sizes = array( 
			'300x100' => _t( '300px by 100px', 'solvemedia' ),
			'300x150' => _t( '300px by 150px', 'solvemedia' ),
			'300x250' => _t( '300px by 250px', 'solvemedia' ),
			'336x2800' => _t( '336px by 280px', 'solvemedia' ),
		);
		$languages = array(
			'en' => _t( 'English', 'solvemedia' ),
			'de' => _t( 'German', 'solvemedia' ),
			'fr' => _t( 'French', 'solvemedia' ),
			'es' => _t( 'Spanish', 'solvemedia' ),
			'it' => _t( 'Italian', 'solvemedia' ),
			'yi' => _t( 'Yiddish', 'solvemedia' ),
		);
		$ui->append( 'select', 'theme', 'solvemedia__theme', _t( 'Theme', 'solvemedia' ), $themes );
		$ui->append( 'select', 'dimensions', 'solvemedia__dims', _t( 'Size', 'solvemedia' ), $sizes );
		$ui->append( 'select', 'language', 'solvemedia__lang', _t( 'Language', 'solvemedia' ), $languages );

		$ui->append( 'submit', 'save', _t( 'Save', 'solvemedia' ) );
		return $ui;
	}

	/**
	 * Add captcha
	 * ...
	 * @return the form
	 */
	public function action_form_comment( $form, $context = 'public' ) {
		$solvemedia_options = "<script type='text/javascript'>var ACPuzzleOptions = {
			tabindex: {$form->cf_submit->tabindex},
			theme: '" . Options::get( 'solvemedia__theme', 'white' ) . "',
			lang: '" . Options::get( 'solvemedia__lang', _t( 'en', 'solvemedia' ) ) /* use ENglish as a default */ . "',
			size: '" . Options::get( 'solvemedia__size', '300x100' )  . "' };</script> ";
		
		$form->append( 'static','solvemedia_captcha', $solvemedia_options . solvemedia_get_html( Options::get( 'solvemedia__ckey' ) ) );
		$form->move_before( $form->solvemedia_captcha, $form->cf_submit);
		$form->cf_submit->tabindex = $form->cf_submit->tabindex + 1; // ideally we get the captcha tabindex between content and this.
		return $form;
	}

	/**
	 * Assess allowability of comment based on captcha success 
	 * ...
	 * @return boolean $allow Whether the comment should be allowed or not.
	 **/
	public function filter_comment_insert_allow( $allow, $comment ) {

		if ( $allow ) {

			$solvemedia_response = solvemedia_check_answer( 
						Options::get( 'solvemedia__vkey' ), 
						$_SERVER[ "REMOTE_ADDR" ],
						$_POST[ "adcopy_challenge" ],
						$_POST[ "adcopy_response" ],
						Options::get( 'solvemedia__hkey' ) ); 
			if ( $solvemedia_response->is_valid ) {
				$comment->status = Comment::STATUS_APPROVED;
				EventLog::log( _t( 'Comment by %s approved by SolveMedia captcha', array( $comment->name ), 'solvemedia' ), 'info', 'comment', 'SolveMedia' );
			} else {
				Session::add_to_set( 'comment', $comment->name, 'name' );
				Session::add_to_set( 'comment', $comment->email, 'email' );
				Session::add_to_set( 'comment', $comment->url, 'url' );
				Session::add_to_set( 'comment', $comment->content, 'content' );
				Session::error( _t( 'Your CAPTCHA attempt did not succeed: %s', array( $solvemedia_response->error ), 'solvemedia' ) );
			}
		}
		return $allow;
	}

}
require_once( "solvemedialib.php" ); //include the Solve Media library

?>
