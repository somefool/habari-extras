<?php $theme->display ('header'); ?>
<div id="container">
<div id="content">
<?php if ( isset($criteria) ) {
	$theme->mutiple_h1(htmlspecialchars($criteria));
} else {
	$theme->mutiple_h1("");
} ?>
<?php if ( sizeof( $posts ) ) { ?>
<?php foreach ( $posts as $post ) { ?>
	<div id="post-<?php echo $post->id; ?>" class="hentry entry <?php echo $post->statusname , ' ' ,$post->tags_class; ?>">
	<h2 class="entry-title">
		<?php
		$date1 = strtotime( $post->pubdate );
		$date2 = $post->pubdate;
		$date = Utils::getdate( strtotime( $post->pubdate ) );
		?>
		<span class="entry-date"><span class="entry-ym"><?php if ( $date1 == "" ) {
			echo $post->pubdate->out("m.y");
		} else {
			echo $date['mon0'].".".substr($date['year'], -2, 2);
		} ?></span><span class="entry-day"><?php if ( $date1 == "" ) {
			echo $post->pubdate->out("d");
		} else {
			echo $date['mday0'];
		} ?></span></span><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a>
	</h2>
	<div class="entry-meta">
		<?php if ( $show_author ) { ?><span class="author"><?php _e('by'); ?> <?php echo $post->author->displayname; ?></span>
		<span class="meta-sep">|</span><?php } ?>
		<?php if ( $post->get_access()->edit ) { ?>
			<span class="edit-link"><a href="<?php URL::out( 'admin', 'page=publish&slug=' . $post->slug); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit'); ?></a></span>
			<span class="meta-sep">|</span>
		<?php } ?>
		<?php if ( count( $post->tags ) ) { ?>
			<span class="tag-links"><?php _e('Tagged:'); ?> <?php echo $post->tags_out; ?></span>
			<span class="meta-sep">|</span>
		<?php } ?>
		<span class="comments-link"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post'); ?>"><?php echo $post->comments->approved->count; ?>
		<?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span>
	</div>
	<div class="entry-content">
		<?php echo $post->content_out; ?>
	</div>
	</div>
<?php } ?>
<div class="pagebar">
	<?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 4, 'rightSide' => 4 ) ); ?> <?php $theme->next_page_link(); ?>
</div>
<?php } else { ?>
<div class="hentry post error404">
	<div class="entry-content">
		<p><?php _e('The requested post was not found.'); ?></p>
	</div>
</div>
<?php } ?>
</div><!-- #content -->
</div><!-- #container -->
<?php $theme->display ('sidebar'); ?>
<?php $theme->display ('footer'); ?>
