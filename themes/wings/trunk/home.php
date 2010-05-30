<?php $theme->display( 'header'); ?>
<!-- home -->
				<div id="left-col">
					<?php foreach ( $posts as $post ) { ?>
					<div class="post">
						<h2><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
						<p class="details"><?php echo $post->pubdate_out; ?> &bull; Posted by <?php echo $post->author->displayname; ?> &bull; <a href="<?php echo $post->permalink; ?>#comments"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></p>
						<?php echo $post->content_excerpt; ?>
						<p class="bottom">
							<span><a href="<?php echo $post->permalink; ?>#comments">&rarr; <?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span>
							<?php if ( is_array( $post->tags ) ) { ?>
							<strong>Tags:</strong> <?php echo $post->tags_out; ?>
							<?php } ?>
						</p>
					</div>
					<?php } ?>
					<div class="clear"></div>
					<div id="page-selector">
						<?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>
					</div>
				</div>
				<div id="right-col">
					<?php $theme->display ( 'sidebar' ); ?>
				</div>
<!-- /home -->
<?php $theme->display ('footer'); ?>
