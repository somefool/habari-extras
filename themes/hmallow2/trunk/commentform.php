<!-- commentsform -->

<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }

$cookie= 'comment_' . Options::get( 'GUID' );

if ( $user ) {
	if ( $user->displayname != '' ) {
		$commenter_name = $user->displayname;
	}
	else {
		$commenter_name = $user->username;
	}
	$commenter_email= $user->email;
	$commenter_url= Site::get_url('habari');
}
elseif ( isset( $_COOKIE[$cookie] ) ) {
	list( $commenter_name, $commenter_email, $commenter_url )= explode( '#', $_COOKIE[$cookie] );
}
else {
	$commenter_name= '';
	$commenter_email= '';
	$commenter_url= '';
}  
?>

<div class="commentform">
<h4 id="respond" class="reply"><?php _e('Leave a Reply'); ?></h4>
<?php
if ( Session::has_messages() ) {
	Session::messages_out();
}
?>

<?php	$post->comment_form()->out(); ?>
    
</div>

<!-- /commentsform -->