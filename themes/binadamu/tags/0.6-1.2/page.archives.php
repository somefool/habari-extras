<?php $theme->display('header'); ?>
<!-- page.archives -->
	<div id="content" class="hfeed">
		<div id="page-<?php echo $post->slug; ?>" class="hentry page <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
			<div class="entry-head">
				<h1 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo strip_tags($post->title); ?>" rel="bookmark"><?php echo $post->title_out; ?></a></h1>
				<ul class="entry-meta">
					<li class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post', 'binadamu') ?>"><?php printf(_n('%1$d Comment', '%1$d Comments', $post->comments->approved->count, 'binadamu'), $post->comments->approved->count); ?></a></li>
<?php if ($loggedin) { ?>
					<li class="entry-edit"><a href="<?php echo $post->editlink; ?>" title="<?php _e('Edit post', 'binadamu') ?>"><?php _e('Edit', 'binadamu') ?></a></li>
<?php } ?>
				</ul>
			</div>
			<div class="entry-content">
				<?php
					echo $post->content_out;
					$theme->breezyarchives();
					$theme->monthly_archives();
				?>
			</div>
		</div>
<?php $theme->display('comments'); ?>
	</div>
	<hr />
<!-- /page.archives -->
<?php $theme->display('sidebar'); ?>
<?php $theme->display('footer'); ?>
