<!-- commentsform -->
<?php // Do not delete these lines
if ( ! defined('HABARI_PATH' ) ) { die( _t('Please do not load this page directly. Thanks!') ); }
?>
     <div id="respond">
      <h3 class="reply"><?php _e('Leave a Reply'); ?></h3>
<?php
if ( Session::has_errors() ) {
	Session::messages_out();
}
?>
	<div class="formcontainer">
	 	<form action="<?php URL::out( 'submit_feedback', array( 'id' => $post->id ) ); ?>" method="post" id="commentform">
		<p id="comment-notes"><?php _e('Mail'); ?> <?php _e('will not be published'); ?></p>
			<div class="form-label"><label for="name"><?php _e('Name'); ?><span class="required"><?php if (Options::get('comments_require_id') == 1) : ?> *<?php endif; ?></span></label></div>
			<div class="form-input"><input type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1"></div>
			<div class="form-label"><label for="email"><?php _e('Mail'); ?><span class="required"><?php if (Options::get('comments_require_id') == 1) : ?> *<?php endif; ?></span></label></div>
			<div class="form-input"><input type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2"></div>
			<div class="form-label"><label for="url"><?php _e('Website'); ?></label></div>
			<div class="form-input"><input type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3"></div>
	       <div class="form-label"><label for="comment"><?php _e('Comment'); ?></label></div>
			<div class="form-textarea"><textarea name="content" id="comment" cols="100" rows="10" tabindex="4"><?php echo $commenter_content; ?></textarea></div>
	        <div class="form-submit"><input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit'); ?>"></div>
	   </form>
	</div>
   </div>
