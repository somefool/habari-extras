<!-- searchform -->
<?php Plugins::act( 'theme_searchform_before' ); ?>
<form method="get" id="searchform" action="{hi:url:display_search}" accept-charset="UTF-8" >
	<input type="text" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>"> <input type="submit" id="searchsubmit" value="{hi:"Go!"}">
</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
<!-- /searchform -->
