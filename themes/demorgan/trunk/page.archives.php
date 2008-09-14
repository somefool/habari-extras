<?php $theme->display('header'); ?>
<!-- page.archive -->
	<div id="content" class="hfeed">
		<div id="page-<?php echo $post->slug; ?>" class="hentry page <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<h1 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h1>
				<br class="clear" />
				<span class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="Comments to this post"> <?php echo $post->comments->approved->count; ?> <?php _ne('Comment', 'Comments', $post->comments->approved->count); ?></a></span>
<?php if ($user) { ?>
				<span class="entry-edit"><a href="<?php URL::out('admin', 'page=publish&slug=' . $post->slug); ?>" title="Edit post">Edit</a></span>
<?php } ?>
			</div>
			<div class="entry-content">
				<?php
					echo $post->content_out;
					if (Plugins::is_loaded('BreezyArchives')) {
						$theme->breezyarchives();
					}
					else
					if (Plugins::is_loaded('MonthlyArchives') || Plugins::is_loaded('Monthly_Archives')) {
						$theme->monthly_archives();
					}
					else {
						echo '<p>' . _t('Not implemented yet.', 'demorgan') . '</p>';
					}
				?>
			</div>
		</div>
<?php $theme->display('comments'); ?>
	</div>
	<hr />
<!-- /page.archive -->
<?php $theme->display('sidebar'); ?>
<?php $theme->display('footer'); ?>
