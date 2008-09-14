<!-- commentform -->
<?php // Do not delete these lines
	if (!defined('HABARI_PATH')) { die(_t('Please do not load this page directly. Thanks!', 'demorgan')); }
?>
		<div id="respond">
			<h2><span></span><?php _e('Leave a Reply', 'demorgan'); ?></h2>
<?php
	if (Session::has_errors()) {
		Session::messages_out();
	}
?>
			<form action="<?php URL::out('submit_feedback', array('id' => $post->id)); ?>" method="post" id="commentform">
				<fieldset>
					<div>
						<label for="name" class="name"><?php _e('Name', 'demorgan'); ?></label>
						<input class="text" type="text" name="name" id="name" value="<?php echo $commenter_name; ?>" size="22" tabindex="1" />
					</div>
					<div>
						<label for="email" class="email"><?php _e('E-mail', 'demorgan'); ?></label>
						<input class="text" type="text" name="email" id="email" value="<?php echo $commenter_email; ?>" size="22" tabindex="2" />
					</div>
					<div>
						<label for="url" class="url"><?php _e('Website', 'demorgan'); ?></label>
						<input class="text" type="text" name="url" id="url" value="<?php echo $commenter_url; ?>" size="22" tabindex="3" />
					</div>
					<div>
						<label for="message" class="content"><?php _e('Message', 'demorgan'); ?></label>
						<textarea name="content" id="message" cols="100" rows="10" tabindex="4"><?php if (isset($details['content'])) { echo $details['content']; } ?></textarea>
					</div>
					<div>
						<input name="submit" type="submit" id="submit" tabindex="5" value="Submit" />
					</div>
					<p class="moderation-notice"><?php _e('Your comment may not display immediately due to spam filtering. Please wait for moderation.', 'demorgan'); ?></p>
				</fieldset>
			</form>
		</div>
<!-- /commentform -->
