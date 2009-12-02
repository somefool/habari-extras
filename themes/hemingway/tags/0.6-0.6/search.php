<!-- home -->
<?php include 'header.php'; ?>

	<div id="primary" class="single-post">
	<div class="inside">
		<div class="primary">

	<?php if ( count($posts) != 0 ): ?>

		<h1>Search Results</h1>
		
		<ul class="dates">
		 	<?php foreach ( $posts as $post ): ?>
			<li>
				<span class="date"><?php echo $post->pubdate->out(); ?></span>
				<a href="<?php echo $post->permalink; ?>"><?php echo $post->title; ?></a> posted in <?php echo $post->tags_out; ?>
			</li>
			<?php endforeach; ?>
		</ul>
		
		<div class="navigation">
			<div class="left"><?php $theme->prev_page_link(); ?><?php //next_posts_link('&laquo; Previous Entries') ?></div>
			<div class="right"><?php $theme->next_page_link(); ?><?php //previous_posts_link('Next Entries &raquo;') ?></div>
		</div>
	
	<?php else : ?>

		<h1>No posts found. Try a different search?</h1>

	<?php endif; ?>
		
	</div>
	
	<div class="secondary">
		<h2>Search</h2>
		<div class="featured">
			<p>You searched for &ldquo;<?php echo htmlspecialchars( $criteria ); ?>&rdquo; at <?php //bloginfo('name'); ?>. There were
			<?php
				if (!count($posts)) echo "no results, better luck next time.";
				elseif (1 == count($posts)) echo "one result found. It must be your lucky day.";
				else echo count($posts) . " results found.";
			?>
			</p>
			
		</div>
	</div>
	<div class="clear"></div>
	</div>
	</div>
  <!-- end primary -->

<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
<!-- end home -->