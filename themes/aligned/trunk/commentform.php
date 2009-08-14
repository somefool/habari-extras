<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
	<h3 id="respond">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}

$post->comment_form()->out(); ?>

</div>
