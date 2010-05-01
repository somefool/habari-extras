		<div id="sidebar">
			<div class="block">
				<form method="get" id="search" action="<?php URL::out('display_search'); ?>">
					<p><input type="text" id="searchfield" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>"> <input type="submit" id="searchsubmit" value="<?php _e('Search'); ?>"></p>
				</form>
			</div>
			<div class="block">
				<h2><?php _e('Tags'); ?></h2>
				<?php $theme->tag_cloud(5); ?>
				<a href="<?php Site::out_url( 'habari' ); ?>/tag"><?php _e('more'); ?>...</a>
			</div>
			<br>
			<div class="block">
				<h2><?php _e('Archive'); ?></h2>
				<?php $theme->monthly_archives(0, 'N'); ?>
			</div>

		</div>
		<div id="footer">
			<p><?php printf( _t('%1$s is powered by %2$s'), Options::get('title'),' <a
			href="http://www.habariproject.org/" title="Habari">Habari ' . Version::HABARI_VERSION  . '</a>' ); ?> - 
			<a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>"><?php _e( 'Atom Entries' ); ?></a><?php _e( ' and ' ); ?>
			<a href="<?php URL::out( 'atom_feed_comments' ); ?>"><?php _e( 'Atom Comments' ); ?></a></p>
		</div>

		<?php $theme->footer(); ?>

	</body>
</html>
