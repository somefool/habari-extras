<?php // get header ?>
<?php $theme->display ( 'header'); ?>


<body id="single" >

<div id="shadow">

<div id="rap">

<?php // get the nav etc ?>
<?php include "topbanner.php"; ?>




<div id="content">



				
	

	<div class="navigation">
		<?php if ( $previous= $post->descend() ): ?>
		<div class="alignleft"> &#x2190; <a href="<?php echo $previous->permalink ?>" title="<?php echo $previous->slug ?>"><?php echo $previous->title ?></a></div>
		<?php endif; ?>
		<?php if ( $next= $post->ascend() ): ?>
		<div class="alignright"><a href="<?php echo $next->permalink ?>" title="<?php echo $next->slug ?>"><?php echo $next->title ?></a> &#x2192;</div>
		<?php endif; ?>
		
		<div class="clear"></div>
		</div>

		

<div class="post">


<div class="meta">
	<?php echo date('j', strtotime($post->pubdate_out)); ?> <?php echo date('F', strtotime($post->pubdate_out)); ?>, <?php echo date('Y', strtotime($post->pubdate_out)); ?> <?php if ( $show_author ) { _e( '&middot; By %s', array( $post->author->displayname ) ); } ?> &middot; Tagged: <?php echo $post->tags_out; ?>			
</div>		
<!-- Post title -->
<?php if ( User::identify()->loggedin ) { ?>
			        <span class="editbutton"><a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit &raquo;'); ?></a></span>
			<?php } ?>	<h3 class="storytitle" id="post-<?php echo $post->id; ?>">

			<a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link: <?php echo $post->title; ?>"><?php echo $post->title; ?></a></h3>


		
		<!-- Actual post -->
<div class="storycontent">
    <?php echo $post->content_out; ?>
</div>

<div class="navigation">
		<?php if ( $previous= $post->descend() ): ?>
		<div class="alignleft"> &#x2190; <a href="<?php echo $previous->permalink ?>" title="<?php echo $previous->slug ?>"><?php echo $previous->title ?></a></div>
		<?php endif; ?>
		<?php if ( $next= $post->ascend() ): ?>
		<div class="alignright"><a href="<?php echo $next->permalink ?>" title="<?php echo $next->slug ?>"><?php echo $next->title ?></a> &#x2192;</div>
		<?php endif; ?>

		<div class="clear"></div>
	</div>

</div>



</div>

<?php if (( ! $post->info->comments_disabled ) or ($post->comments->approved->count!=0)) { ?>


<div id="commentarea">
<div id="commentcontent">

	<?php $theme->display ( 'comments' );  ?>
	
	<div class="clear"></div>
	
	<div class="navigation">
			<?php if ( $previous= $post->descend() ): ?>
			<div class="alignleft"> &#x2190; <a href="<?php echo $previous->permalink ?>" title="<?php echo $previous->slug ?>"><?php echo $previous->title ?></a></div>
			<?php endif; ?>
			<?php if ( $next= $post->ascend() ): ?>
			<div class="alignright"><a href="<?php echo $next->permalink ?>" title="<?php echo $next->slug ?>"><?php echo $next->title ?></a> &#x2192;</div>
			<?php endif; ?>

			<div class="clear"></div>
		</div>

		

	
	</div>
		
		</div>
	
	
		<?php }?>

<?php // get footer ?>
<?php $theme->display ( 'footer' ); ?>

</div>
</div>

</body>
</html>