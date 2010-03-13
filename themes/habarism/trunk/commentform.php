      <h3 id="respond" class="comment_title">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
$post->comment_form()->out(); ?>
