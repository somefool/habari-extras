<!-- commentsform -->
	<h3 id="respond">Leave a Reply</h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
?>
	<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">

	<p>
	<input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1" />
	<label for="name"><small>Name (required)</small></label>
	</p>

	<p>
	<input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2" />
	<label for="email"><small>Mail (will not be published) (required)</small></label>
	</p>

	<p>
	<input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3" />
	<label for="url"><small>Website</small></label>
	</p>

	<p>
	<textarea name="content" id="comment" cols="100%" rows="10" tabindex="4"></textarea>
	</p>

	<p>
	<input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" />
	</p>

	</form>

<!-- commentsform -->
