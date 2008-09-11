<!-- footer -->
	<br class="clear" />
	<div id="footer">
		<p>
		<?php
			printf(
				_t('%1$s is powered by %2$s and %3$s theme.', 'demorgan'),
				Options::get('title'),
				'<a href="http://www.habariproject.org/" title="Habari">Habari</a>',
				'<a href="http://blog.bcse.info/de-morgan/">De Morgan</a>'
			);
		?>
		</p>
	</div>
<?php $theme->footer(); ?>
</div>
</body>
</html>
<!-- /footer -->
