<?php $theme->display ('header'); ?>
<div id="container">
	<div id="content">	
    <div id="post-<?php echo $post->id; ?>" class="hentry entry <?php echo $post->statusname , ' ' , $post->tags_class; ?>">
		<?php $thistype = $post->tags_type; ?>
		<h2 class="entry-title">
			<?php
			$date1 = strtotime( $post->pubdate );
			$date2 = $post->pubdate;
			$date= Utils::getdate( strtotime( $post->pubdate ) );
			?>
			<span class="entry-date"><span class="entry-ym"><?php if($date1==""){
				echo $post->pubdate->out("m.y");
			}else{
				echo $date['mon0'].".".substr($date['year'], -2, 2);
			} ?></span><span class="entry-day"><?php if($date1==""){
				echo $post->pubdate->out("d");
			}else{
				echo $date['mday0'];
			} ?></span></span><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a>
		</h2>
		<div class="entry-meta">
			<?php if ( $show_author ) { ?><span class="author"><?php _e('by'); ?> <?php echo $post->author->displayname; ?></span><span class="meta-sep">|</span><?php } ?>
			<?php if ( $user ) { ?>
			        <span class="edit-link"><a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a></span>
					<span class="meta-sep">|</span>
			<?php } ?>
			<?php if ( is_array( $post->tags ) ) { ?>
			        <span class="tag-links"><?php _e('Tagged:'); ?> <?php echo $post->tags_out; ?></span>
					<span class="meta-sep">|</span>
			<?php } ?>
			<span class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post'); ?>"><?php echo $post->comments->approved->count; ?>
			<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span>
		</div>
		<div class="entry-content">
			<?php echo $post->content_out; ?>
		</div>
		<?php if ( Plugins::is_loaded('RelatedPosts') || Plugins::is_loaded('RelatedTags') ){ ?>
		<div class="entry-related">
			<?php if ( Plugins::is_loaded('RelatedPosts') ){ ?>
			<div id="related-post" class="related-box">
				<h3>Related Posts</h3>
				<?php echo $related_posts; ?>
			</div>
			<?php } if ( Plugins::is_loaded('RelatedTags') ){ ?>
			<div id="related-tags" class="related-box">
				<h3>Related Tags</h3>
				<?php echo $related_tags; ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
		<div id="nav-below" class="navigation">
			<?php if ( $previous= $post->descend() ): ?>
			<div class="nav-previous"> <span class="meta-nav">&laquo;</span> <a href="<?php echo $previous->permalink ?>" title="<?php echo $previous->slug ?>"><?php echo $previous->title ?></a></div>
			<?php endif; ?>
			<?php if ( $next= $post->ascend() ): ?>
			<div class="nav-next"><a href="<?php echo $next->permalink ?>" title="<?php echo $next->slug ?>"><?php echo $next->title ?></a> <span class="meta-nav">&raquo;</span></div>
			<?php endif; ?>
		</div>
    </div>
<?php $theme->display ('comments'); ?>

	</div><!-- #content -->
</div><!-- #container -->
<?php $theme->display ('sidebar'); ?>
<?php $theme->display ('footer'); ?>
