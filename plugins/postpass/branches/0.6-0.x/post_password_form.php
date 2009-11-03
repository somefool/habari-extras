<p>
<?php _e('This post is password protected. Please enter the password to view the post.', 'postpass'); ?>
</p>

<?php Session::messages_out() ?>

<form method="post" action="">

<label>
	Password:
	<input type="text" name="post_password" />
</label>
<input type="hidden" name="post_password_id" value="<?php echo $post->id; ?>" />
<input type="submit" value="submit" />

</form>