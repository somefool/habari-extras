<?php Plugins::act( 'theme_searchform_before' ); ?>
<form method="get" id="searchform" action="<?php URL::out( 'display_search' ); ?>">
	<div>
		<input type="search" id="s" name="criteria" value="<?php echo isset( $theme->criteria ) ? htmlentities( $theme->criteria, ENT_COMPAT, 'UTF-8' ) : ''; ?>" placeholder="<?php echo _t( 'Search' ); ?>" />
		<input type="submit" id="searchsubmit" value="<?php echo _t( 'Search' ); ?>" />
	</div>
</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
