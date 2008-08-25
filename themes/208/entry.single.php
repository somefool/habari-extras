<?php $theme->display ('header'); ?>
		<div class="entry span-24">
			<div class="left column span-6 first">
				<h2></h2>
			</div>
			<div class="center column span-13">
				<h2><?php echo $post->title_out; ?></h2>
					<?php echo $post->content_out; ?>
					<span class="tags"><p><strong>Tagged:</strong> <?php echo $post->tags_out; ?></p></span>
			</div>
			<div class="right column span-5 last">
				<h2><a href="<?php echo $post->permalink; ?>" rel="bookmark" title='<?php echo $post->title; ?>'><?php echo $post->pubdate_out; ?></a></h2>
				<span class="comments">
					<a href="#comments">Skip to Comments</a>
				</span>
			</div>
		</div>
<?php $theme->display ('comments'); ?>
<?php $theme->display ('footer'); ?>
