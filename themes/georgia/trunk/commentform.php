	<h3 id="respond">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}

$post->comment_form()->out(); ?>
