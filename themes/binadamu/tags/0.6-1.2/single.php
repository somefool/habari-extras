<?php $theme->display('header'); ?>
<!-- single -->
	<div id="content" class="hfeed">
		<div id="entry-<?php echo $post->slug; ?>" class="hentry entry <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<h1 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h1>
				<ul class="entry-meta">
					<li class="entry-date"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('F j, Y'); ?></abbr></li>
					<li class="entry-time"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('g:i a'); ?></abbr></li>
					<li class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post', 'binadamu') ?>"><?php printf(_n('%1$d Comment', '%1$d Comments', $post->comments->approved->count, 'binadamu'), $post->comments->approved->count); ?></a></li>
<?php if (is_array($post->tags)) { ?>
					<li class="entry-tags"><?php echo $post->tags_out; ?></li>
<?php } ?>
<?php if ($loggedin) { ?>
					<li class="entry-edit"><a href="<?php echo $post->editlink; ?>" title="<?php _e('Edit post', 'binadamu') ?>"><?php _e('Edit', 'binadamu') ?></a></li>
<?php } ?>
				</ul>
			</div>
			<div class="entry-content">
				<?php echo $post->content_out; ?>
			</div>
		</div>

		<div id="pager">
			<?php if ($previous = $post->descend()): ?>
			<a class="previous" href="<?php echo $previous->permalink ?>" title="<?php echo $previous->title; ?>"><?php echo $previous->title; ?></a>
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
