<?php Plugins::act( 'theme_searchform_before' ); ?>
<fieldset id="search-fieldset">
	<form method="get" id="searchform" action="<?php URL::out('display_search'); ?>">
		<div id="site-search"><input type="text" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>" /> <input type="submit" id="searchsubmit" value="" /></div>
	</form>
</fieldset>
<?php Plugins::act( 'theme_searchform_after' ); ?>
