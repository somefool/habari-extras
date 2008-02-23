
<div id="search">
<?php Plugins::act( 'theme_searchform_before' ); ?>
<form method="get" id="search-form" action="<?php URL::out('display_search'); ?>">
<input type="text" name="criteria" id="search-box" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8');} else {echo "Search ".Options::get( 'title' );}  ?>" onfocus="if (this.value == 'Search <?php Options::out( 'title' ) ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search <?php Options::out( 'title' ) ?>';}" >
<input type="submit" id="search-btn" value="" title="Go">
</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
</div>
<div id="feeds">
<div class="feedlink"><a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>"><span><img src="<?php Site::out_url( 'theme' ); ?>/images/rss.png" alt="Atom">{blog entries}</span></a></div>
<div class="feedlink"><a href="<?php URL::out( 'atom_feed_comments' ); ?>"><span><img src="<?php Site::out_url( 'theme' ); ?>/images/rss.png" alt="Atom">{comments}</span></a></div>
</div>
<div id="habari-link"><?php if ($show_powered) { ?><a href="http://www.habariproject.org" title="Powered by Habari"><img
	src="<?php Site::out_url('theme'); ?>/images/pwrd_habari.png" alt="Powered by Habari"></a><?php } ?></div>
<?php
if (isset($post)) $post_type= $post->content_type;
else $post_type=0;
?>	
<div id="sidebar">
	<!-- Related Posts module -->
	<?php if (Plugins::is_loaded ('relatedposts') && $post_type==1 && $request->display_entry )  {?>
	<div class="module">
	<h3>Related Posts</h3>
	<?php echo $related_posts;?>
	</div>
	<?php } ?>
	
	<!-- Recent Comments module -->
	<?php if (Plugins::is_loaded('recentcomments')) { ?>
	<div class="module">
	<h3>Recent Comments</h3>
	<?php echo $recent_comments;?>
	</div>
	<?php } ?>
	<?php $theme->sidebar(); ?>
</div>
