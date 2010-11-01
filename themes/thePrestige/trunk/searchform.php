<!-- searchform -->
<?php Plugins::act( 'theme_searchform_before' ); ?>
	<form method="get" id="searchform" action="{hi:url:display_search}">
	<p><input type="text" id="s" name="criteria" value="{hi:?isset(criteria)}{hi:criteria}{/hi:?}"> <input type="submit" id="searchsubmit" value="{hi:"Go!"}"></p>
	</form>
<?php Plugins::act( 'theme_searchform_after' ); ?>
<!-- searchform -->
