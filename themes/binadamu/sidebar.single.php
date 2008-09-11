<!-- sidebar.single -->
	<div id="sidebar">
		<div id="search-form">
			<form action="<?php URL::out('display_search'); ?>" method="get">
				<fieldset>
					<h3><label for="criteria"><?php _e('Search', 'binadamu'); ?></label></h3>
					<input type="text" id="criteria" name="criteria" value="<?php if (isset($criteria)) { echo htmlentities($criteria, ENT_COMPAT, 'UTF-8'); } ?>" />
					<input id="search-submit" type="submit" value="Search" />
				</fieldset>
			</form>
		</div>
		<ul id="sidebar-1" class="xoxo">
			<?php
				if (Plugins::is_loaded('RelatedPosts')) $theme->display('relatedposts.widget');
				if (Plugins::is_loaded('RelatedTags')) $theme->display('relatedtags.widget');
			?>
		</ul>
		<ul id="sidebar-2" class="xoxo">
			<?php
				$theme->display('recententries.widget');
				$theme->display('feedlink.widget');
				$theme->display('admin.widget');
			?>
		</ul>
	</div>
	<hr />
<!-- /sidebar.single -->
