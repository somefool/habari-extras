<!-- footer.php -->
<hr class="hide" />
	<div id="footer">
		<div class="inside">
			<p class="copyright">Powered by <a href="http://warpspire.com/hemingway">Hemingway</a> and <a href="http://www.habariproject.com/" >Habari</a>.</p>
			<p class="attributes"><a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries RSS</a> <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Comments RSS</a> <a href="http://validator.w3.org/check?uri=referer">XHTML</a> <?php if ( $loggedin ): ?><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Site Admin</a><?php else: ?><a href="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" title="login">Login</a><?php endif; ?></p
		</div>
	</div>
	
	<?php $theme->footer() ?>
</body>
</html>
<!-- end footer.php -->
