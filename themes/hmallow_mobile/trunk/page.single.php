<?php // get header ?>
<?php $theme->display ( 'header'); ?>


<body id="single" >

<div id="shadow">

<div id="rap">

<?php // get the nav etc ?>
<?php include "topbanner.php"; ?>




<div id="content">


		

<div class="post">


<div class="meta">
	<span id="thedate"><?php echo date('j', strtotime($post->pubdate_out)); ?> <?php echo date('F', strtotime($post->pubdate_out)); ?>, <?php echo date('Y', strtotime($post->pubdate_out)); ?> <?php if ( $show_author ) { _e( '&middot; By %s', array( $post->author->displayname ) ); } ?></span> &middot; <a href="http://twitter.com/home?status=<?php echo $post->title; ?>%20<?php echo $post->permalink; ?>" title="Tweet this">Tweet this</a> &middot; Tagged: <?php echo $post->tags_out; ?>			
</div>		
<!-- Post title -->
<?php if ( User::identify()->loggedin ) { ?>
			        <span class="editbutton"><a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit &raquo;'); ?></a></span>
			<?php } ?>	<h3 class="storytitle" id="post-<?php echo $post->id; ?>">

			<a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link: <?php echo $post->title; ?>"><?php echo $post->title; ?></a></h3>


		
		<!-- Actual post -->
<div class="storycontent">
    <?php echo $post->content; ?>
</div>

</div>



</div>

<?php if (( ! $post->info->comments_disabled ) and ($post->comments->approved->count!=0)) { ?>


<div id="commentarea">
	
	
<div id="commentcontent">

	<?php  $theme->display ( 'comments' );  ?>
	
	<div class="clear"></div>
	

	
	</div>

		
		</div>
		
		<?php } ?>
	
	

<?php // get footer ?>
<?php $theme->display ( 'footer' ); ?>

</div>
</div>

</body>
</html>