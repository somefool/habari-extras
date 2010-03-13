<!-- searchform -->
<?php Plugins::act( 'theme_searchform_before' ); ?>
     <form method="get" id="searchform" action="<?php URL::out('display_search'); ?>">
      <p>Search <input type="text" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>" /></p>
     </form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
<!-- /searchform -->
