		<div id="bottom">

			<div class="column">
				<h2>Recent Comments</h2>
				<?php $theme->show_recentcomments(); ?>
			</div>

			<div class="column">
				<h2>Monthly Archives</h2>
				<?php $theme->monthly_archives(5, 'N'); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/archives">More...</a>
			</div>

			<div class="column">
				<h2>Tags</h2>
				<?php $theme->tag_cloud(5); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/tag">More...</a>
			</div>

			<br>

			<div id="footer">
				<p><?php echo $copyright; ?> Subscribe to <a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries</a> or <a href="<?php URL::out( 'atom_feed_comments', array( 'index' => '1' ) ); ?>">Comments</a></p>
			</div>
		</div>

		<?php $theme->footer(); ?>

	</body>
</html>