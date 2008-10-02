<?php $theme->display('header'); ?>
<!-- single -->
	<div id="content" class="hfeed">
		<div id="entry-<?php echo $post->slug; ?>" class="hentry entry <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<div class="entry-date"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('Y • m • d'); ?></abbr></div>
				<br class="clear" />
				<h1 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h1>
				<br class="clear" />
				<span class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post', 'demorgan'); ?>"> <?php echo $post->comments->approved->count; ?> <?php _ne('Comment', 'Comments', $post->comments->approved->count, 'demorgan'); ?></a></span>
<?php if (is_array($post->tags)) { ?>
				<span class="entry-tags"><?php echo $post->tags_out; ?></span>
<?php } ?>
<?php if ($user) { ?>
				<span class="entry-edit"><a href="<?php URL::out('admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post', 'demorgan'); ?>"><?php _e('Edit', 'demorgan'); ?></a></span>
<?php } ?>
			</div>
			<div class="entry-content">
				<?php echo $post->content_out; ?>
			</div>
		</div>

		<div id="pager">
			<?php if ($previous = $post->descend()): ?>
			<a class="prev" href="<?php echo $previous->permalink ?>" title="<?php echo $previous->title; ?>"><?php echo $previous->title; ?></a>
			<?php endif; ?>
			<?php if ($next = $post->ascend()): ?>
			<a class="next" href="<?php echo $next->permalink ?>" title="<?php echo $next->title; ?>"><?php echo $next->title; ?></a>
			<?php endif; ?>
		</div>

<?php $theme->display('comments'); ?>
	</div>
	<hr />
<!-- /single -->
<?php $theme->display('sidebar.single'); ?>
<?php $theme->display('footer'); ?>
