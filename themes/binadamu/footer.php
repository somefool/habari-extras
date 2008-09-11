<!-- footer -->
	<div id="footer">
		<p>
		<?php
			printf(
				_t('%1$s is powered by %2$s and %3$s theme.', 'binadamu'),
				Options::get('title'),
				'<a href="http://www.habariproject.org/" title="Habari">Habari</a>',
				'<a href="http://blog.bcse.info/binadamu/">Binadamu</a>'
			);
		?>
		</p>
	</div>
<?php $theme->footer(); ?>
<?php //$theme->display('db_profiling'); ?>
</div>
</body>
</html>
<!-- /footer -->