<div id="footer">
	<p>
		<?php Options::out('title'); _e(' is powered by'); ?> <a
		href="http://www.habariproject.org/" title="Habari">Habari</a> and Charcoal theme
		<?php _e(' - ') ?><a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Atom
		Entries</a> and <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Atom Comments</a>
	</p>
	<?php $theme->footer(); ?>
</div>
	</div>
	<div id="bottom-secondary">
		<div id="tags"><?php if (Plugins::is_loaded('tagcloud')) echo $tag_cloud; ?></div>
	</div>
	<div class="clear"></div>
</div>
</div>
</body>
</html>