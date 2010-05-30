<?php $theme->display( 'header'); ?>
<!-- single -->
<div class="entry">
				<div id="left-col">
						<h2><?php echo $post->title_out; ?></h2>
						<p class="details"><?php echo $post->pubdate_out; ?> &bull; Posted by <?php echo $post->author->displayname; ?> &bull; <a href="<?php echo $post->permalink; ?>#comments"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></p>
						<?php echo $post->content_out; ?>
						<p class="bottom">
							<?php if ( is_array( $post->tags ) ) { ?>
							<strong>Tags:</strong> <?php echo $post->tags_out; ?>
							<?php } ?>
						</p>
					<?php $theme->display ('comments'); ?>
				</div>
				<div id="right-col">
					<?php $theme->display ( 'sidebar' ); ?>
				</div>
</div>
<!-- /single -->
<?php $theme->display ('footer'); ?>
