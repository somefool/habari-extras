<?php $theme->display( 'header'); ?>
	<?php foreach( $posts as $post ) { ?>
		<div class="entry span-24">
			<div class="left column span-6 first">
				&nbsp;
			</div>
			<div class="center column span-13">
				<?php if( !in_array( 'quote', $post->tags ) ) { ?>
				<h2><?php echo $post->title_out; ?></h2>
				<?php } ?>
					<?php echo $post->content_out; ?>
			</div>
			<div class="right column span-5 last">
					<h2><a href="<?php echo $post->permalink; ?>" rel="bookmark" title='<?php echo $post->title; ?>'><img src="<?php Site::out_url( 'theme' ); ?>/images/types/entry.png" alt="entry"><?php echo $post->pubdate_out; ?></a></h2>
					<span class="comments">
						<a href="<?php echo $post->permalink; ?>#comments" title="Comments on this post" ><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a>
					</span>
			</div>
		</div>
	<?php } ?>
<?php $theme->display( 'footer'); ?>