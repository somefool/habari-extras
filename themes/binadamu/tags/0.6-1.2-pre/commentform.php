<!-- commentform -->
<?php // Do not delete these lines
	if (!defined('HABARI_PATH')) { die(_t('Please do not load this page directly. Thanks!', 'binadamu')); }
?>
		<div id="respond">
			<h2><?php _e('Leave a Reply', 'binadamu'); ?></h2>
<?php
	if (Session::has_errors()) {
		Session::messages_out();
	}
?>
			<form action="<?php URL::out('submit_feedback', array('id' => $post->id)); ?>" method="post" id="commentform">
				<fieldset>
					<div class="commenter-info">
						<label for="name" class="name"><?php _e('Name', 'binadamu'); ?></label>
						<input name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1" />
						<label for="email" class="email"><?php _e('E-mail', 'binadamu'); ?></label>
						<input name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2" />
						<label for="url" class="url"><?php _e('Website', 'binadamu'); ?></label>
						<input name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3" />
						<label for="message" class="content"><?php _e('Message', 'binadamu'); ?></label>
					</div>
					<div class="response-content">
						<textarea name="content" id="message" cols="100" rows="10" tabindex="4"><?php if (isset($details['content'])) { echo $details['content']; } ?></textarea>
						<button type="submit" id="submit_feedback" tabindex="5" /><?php _e('Submit', 'binadamu'); ?></button>
					</div>
					<p class="moderation-notice"><?php _e('Your comment may not display immediately due to spam filtering. Please wait for moderation.', 'binadamu'); ?></p>
				</fieldset>
			</form>
		</div>
<!-- /commentform -->
