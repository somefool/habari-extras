		<div id="bottom">
			<div class="column">
				<h3>Recent Comments</h3>
				<?php $theme->show_recentcomments(); ?>
			</div>
			<div class="column">
				<h3>Monthly Archives</h3>
				<?php $theme->monthly_archives(5, 'N'); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/archives">More...</a>
			</div>
			<div class="column">
				<h3>Tags</h3>
				<?php $theme->tag_cloud(5); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/tag">More...</a>
			</div>
			<br>
			<div id="footer">
				<p>&copy; Copyright 2002 - <?php echo date('Y');?>. All Rights Reserved. Subscribe to <a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries</a> or <a href="<?php URL::out( 'atom_feed_comments', array( 'index' => '1' ) ); ?>">Comments</a></p>
			</div>
		</div>
	</body>
<?php $theme->footer(); ?>
</html>