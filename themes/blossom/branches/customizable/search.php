<?php include 'header.php'; ?>

<div id="primary" class="single-post">
	<div class="inside">
		<div class="primary">

			<?php if ( $posts ): ?>

			<h1>Search Results</h1>

			<ul class="dates">

				<div>
					<b class="spiffy">
					<b class="spiffy1"><b></b></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy3"></b>
					<b class="spiffy4"></b>
					<b class="spiffy5"></b>
					</b>

					<div class="spiffy_content">
						<?php foreach ( $posts as $post ): ?>
						<li>
							<span class="date"><?php echo Format::nice_date($post->pubdate, 'Y.j.n') ?></span>
							<a href="<?php echo $post->permalink ?>"><?php echo $post->title; ?></a>
							<?php
									if ( is_array($post->tags) ) {
										echo " posted in {$post->tags_out}";
									}
							?>
						</li>
						<?php endforeach; ?>
					</div>
					<b class="spiffy">
					<b class="spiffy5"></b>
					<b class="spiffy4"></b>
					<b class="spiffy3"></b>
					<b class="spiffy2"><b></b></b>
					<b class="spiffy1"><b></b></b>
					</b>
				</div>

			</ul>
			<div class="navigation">
				<!-- Todo: Need to add with navigation -->
				<div class="left"><?php //next_posts_link('&laquo; Previous Entries') ?></div>
				<div class="right"><?php //previous_posts_link('Next Entries &raquo;') ?></div>
			</div>

		<?php else: ?>
			<h1>No posts found. Try a different search?</h1>
		<?php endif; ?>

		</div>

		<div class="secondary">
			<h2>Search</h2>
			<div class="featured">
				<p>You searched for &ldquo;<?php echo htmlspecialchars( $criteria ); ?>&rdquo; at <?php Options::out('title'); ?>. There were
				<?php
					if (!$posts) echo "no results, better luck next time.";
					elseif (1 == count($posts)) echo "one result found. It must be your lucky day.";
					else echo count($posts) . " results found.";
				?>
				</p>

			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>

<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
