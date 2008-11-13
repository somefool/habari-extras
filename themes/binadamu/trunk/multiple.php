<?php $theme->display('header'); ?>
<!-- multiple -->
	<div id="content" class="hfeed">
		<?php $theme->mutiple_h1(); ?>
<?php foreach ($posts as $post) { ?>
		<div id="entry-<?php echo $post->slug; ?>" class="hentry entry <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<h2 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h2>
				<ul class="entry-meta">
					<li class="entry-date"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('F j, Y'); ?></abbr></li>
					<li class="entry-time"><abbr class="published" title="<?php echo $post->pubdate->out(HabariDateTime::ISO8601); ?>"><?php echo $post->pubdate->out('g:i a'); ?></abbr></li>
					<li class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post', 'binadamu') ?>"><?php printf(_n('%1$d Comment', '%1$d Comments', $post->comments->approved->count, 'binadamu'), $post->comments->approved->count); ?></a></li>
<?php if (is_array($post->tags)) { ?>
					<li class="entry-tags"><?php echo $post->tags_out; ?></li>
<?php } ?>
<?php if ($user) { ?>
					<li class="entry-edit"><a href="<?php URL::out('admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post', 'binadamu') ?>"><?php _e('Edit', 'binadamu') ?></a></li>
<?php } ?>
				</ul>
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
<!-- /multiple -->
<?php $theme->display('sidebar'); ?>
<?php $theme->display('footer'); ?>
