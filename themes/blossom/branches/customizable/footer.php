<hr class="hide" />
	<div id="footer">
		<div class="inside">
			<p class="attributes">
				<a title="Atom Feed for Posts" href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries Feed</a>
				<a title="Atom Feed for Comments" href="<?php URL::out( 'atom_feed_comments', array( 'page' => '1' ) ); ?>">Comments Feed</a>
			</p>
		</div>
	</div>
	<!-- / #footer -->
		<div id="live-search">
			<div class="inside">
				<div id="search">
					<form id="sform" method="get" action="<?php URL::out( 'display_search' ); ?>">
						<img src="<?php Site::out_url('theme'); ?>/images/search.gif" alt="Search:" />
						<input type="text" id="q" value="<?php _e('Search'); ?>" name="criteria" size="15" />
					</form>
				</div>
			</div>
		</div>
	<?php $theme->footer(); ?>
	</body>
</html>
