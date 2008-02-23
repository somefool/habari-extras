<?php include 'header.php'; ?>
<?php if (!isset($post)) Utils::redirect("404"); ?>
<div id="main-posts"><?php $post=reset($posts); ?>
<h2>Search results for <?php echo htmlspecialchars( $criteria ); ?></h2>
<?php $post_class='post'; ?>
<?php if (!$show_entry_paperclip) $post_class.='-alt'; ?>
<div class="<?php echo $post_class?>">
<?php if (!isset($post)) Utils::redirect("htt://www.google.com"); ?>
<?php if ( is_array( $post->tags ) ) {;?>
<div class="post-tags">
<?php echo $post->tags_out;?>
</div>
<?php } ?>
<div class="post-title">
<h2><a href="<?php echo $post->permalink; ?>"
	title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h2>
</div>
<div class="post-sup"><span class="post-date"><?php echo $post->pubdate_out; ?></span>
<span class="post-comments-link"><a
	href="<?php echo $post->permalink.'#comment-form'; ?>" title="Comments on this post"><?php $theme->post_comments_link( $post, 'No Comments', '%s Comment', '%s Comments' ); ?>
</a></span>
<span class="clear"></span></div>
<div class="post-entry"><?php echo $post->content_out; ?></div>
<div class="post-footer"><?php if ( $user ) { ?> <span class="post-edit"><a
	href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>"
	title="Edit post">Edit</a></span> <?php } ?></div>
</div>
</div>
</div>
<div id="top-secondary"><?php include'sidebar.php' ?></div>
<div class="clear"></div>
</div>
</div>
<div id="page-bottom">
<div id="wrapper-bottom">
<div id="bottom-primary">
<div id="prev-posts"><?php while($post=next($posts)) { ?>
<div class="prev-post">
<div class="prev-post-title">
<h3><a href="<?php echo $post->permalink; ?>"
	title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
</div>
<div class="prev-post-excerpt">
<p><?php echo $post->content_excerpt; ?><a href="<?php echo $post->permalink; ?>" title="Continue reading <?php echo $post->title; ?>"><img src="<?php Site::out_url( 'theme' ); ?>/images/arrow.png" alt="more"></a></p>
</div>
</div>
<?php } ?>
<div id="prev-posts-footer"><?php $theme->prevnext($page, Utils::archive_pages($posts->count_all())); ?></div>
</div>

<div id="archives"><?php if (Plugins::is_loaded("extendedmonthlyarchives")) echo $extended_monthly_archives;?></div>
<?php include 'footer.php'; ?></div>
<div id="bottom-secondary">
<div id="tags"><?php if (Plugins::is_loaded('tagcloud')) echo $tag_cloud; ?></div>
</div>
<div class="clear"></div>
</div>
</div>
</body>
</html>
