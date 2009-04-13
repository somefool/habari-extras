<?php Plugins::act( 'theme_searchform_before' ); ?>
	<form method="get" id="search" action="<?php URL::out('display_search'); ?>">
		<p class="clearfix">
			<input type="text" id="s" name="criteria" value="<?php if ( isset( $criteria ) ) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>"> 
			<input type="submit" id="searchsubmit" value="Search">
		</p>
	</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
