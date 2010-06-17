<?php Plugins::act( 'theme_searchform_before' ); ?>

<form method="get" id="searchform" action="<?php URL::out('display_search'); ?>">
<div><label for="s">Search</label></div>
<div><input type="text" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>" /></div>
</form>

<?php Plugins::act( 'theme_searchform_after' ); ?>


