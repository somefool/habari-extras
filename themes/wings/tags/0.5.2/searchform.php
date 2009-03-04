<!-- searchform -->
<?php Plugins::act( 'theme_searchform_before' ); ?>
 	<form method="get" action="<?php URL::out('display_search'); ?>">
		<input type="text" name="criteria" value="To search, type and hit enter" onfocus="this.value = ''" onblur="this.value='To search, type and hit enter'">
  	</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
<!-- /searchform -->
