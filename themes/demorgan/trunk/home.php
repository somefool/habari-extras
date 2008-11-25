<?php $theme->display('header.home'); ?>
<!-- home -->
	<div id="content" class="hfeed">
<?php foreach ($posts as $post) { ?>
		<div id="entry-<?php echo $post->slug; ?>" class="hentry entry <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<div class="entry-date"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('Y • m • d'); ?></abbr></div>
				<br class="clear" />
				<h2 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h2>
				<br class="clear" />
				<span class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post', 'demorgan'); ?>"><?php echo $post->comments->approved->count; ?> <?php _ne('Comment', 'Comments', $post->comments->approved->count, 'demorgan'); ?></a></span>
<?php if (is_array($post->tags)) { ?>
				<span class="entry-tags"><?php echo $post->tags_out; ?></span>
<?php } ?>
<?php if ($user->loggedin) { ?>
				<span class="entry-edit"><a href="<?php echo $post->editlink; ?>" title="<?php _e('Edit post', 'demorgan'); ?>"><?php _e('Edit', 'demorgan'); ?></a></span>
<?php } ?>
			</div>
			<div class="entry-content">
				<?php echo $post->content_out; ?>
			</div>
		</div>
<?php } ?>
		<div id="page-selector">
			<?php $theme->prev_page_link(); ?> <?php $theme->page_selector(null, array('leftSide' => 2, 'rightSide' => 2)); ?> <?php $theme->next_page_link(); ?>
		</div>
	</div>
	<hr />
<!-- /home -->
<?php $theme->display('sidebar'); ?>
<?php $theme->display('footer'); ?>
