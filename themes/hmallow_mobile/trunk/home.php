<?php $theme->display( 'header'); ?>

<body id="home">


<div id="shadow">


<div id="rap">

<?php // get the nav etc ?>
<?php include "topbanner.php"; ?>

<?php // get sidebar ?>
<?php $theme->display ( 'sidebar' ); ?>


<div id="content">



<!-- home -->

<?php foreach ( $posts as $post ) { ?>
	
				<div class="post" id="post-<?php echo $post->id; ?>">
	<!-- Post title -->
	<?php if ( User::identify()->loggedin ) { ?>
				        <span class="editbutton"><a href="<?php URL::out( 'admin', 'page=publish&id=' . $post->id); ?>" title="<?php _e('Edit post'); ?>"><?php _e('Edit &raquo;'); ?></a></span>
				<?php } ?>	<h3 class="storytitle" id="post-<?php echo $post->id; ?>">
	
				<a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link: <?php echo $post->title; ?>"><?php echo $post->title; ?></a>&nbsp;&nbsp;<span class="commentcount"><a href="<?php echo $post->permalink; ?>#comments" title="<?php _e('Comments to this post'); ?>"><?php echo $post->comments->approved->count; ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a></span></h3>
	<!-- Post meta -->
	<div class="meta">
	<?php echo date('j', strtotime($post->pubdate_out)); ?> <?php echo date('F', strtotime($post->pubdate_out)); ?>, <?php echo date('Y', strtotime($post->pubdate_out)); ?> <?php if ( $show_author ) { _e( '&middot; By %s', array( $post->author->displayname ) ); } ?> &middot; Tagged: <?php echo $post->tags_out; ?></div>

	<!-- Actual post -->
	<div class="storycontent">
       <?php echo $post->content_out; ?>
	</div>			
				
				</div>
				<?php } ?>
				
				
				<div style="clear: both;"></div>
	
	
			 <div id="page-selector">

			     <?php $theme->prev_page_link(); ?> <?php $theme->page_selector( null, array( 'leftSide' => 2, 'rightSide' => 2 ) ); ?> <?php $theme->next_page_link(); ?>

			    </div>
		    </div>
	
	
				<?php // get footer ?>

				<?php $theme->display ('footer'); ?>

				</div>


				</div>

				</body>

				</html>