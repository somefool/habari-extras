<!-- home -->
<?php include 'header.php'; ?>

	<div id="primary" class="single-post">
	<div class="inside">
		<div class="primary">

		<h1>Archive for the '<?php echo $tag ?>' tag</h1>
		
		<ul class="dates">
		 	<?php foreach ( $posts as $post ): ?>
			<li>
				<span class="date"><?php echo $post->pubdate->out(); ?></span>
				<a href="<?php echo $post->permalink; ?>"><?php echo $post->title; ?></a> posted in <?php echo $post->tags_out; ?>
			</li>
			<?php endforeach; ?>
		</ul>
		
		<div class="navigation">
			<div class="left"><?php $theme->prev_page_link(); ?></div>
			<div class="right"><?php $theme->next_page_link(); ?></div>
		</div>
	
	</div>
	
	<div class="secondary">
		<h2>About the archives</h2>
		<div class="featured">
			<p>Welcome to the archives here at stanbar.jp. Have a look around.</p>
			
		</div>
	</div>
	<div class="clear"></div>
	</div>
	</div>
  <!-- end primary -->

<?php include 'sidebar.php'; ?>

<?php include 'footer.php'; ?>
<!-- end home -->