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
		<?php $post->comment_form()->out(); ?>
	</div>
</div>
