<!-- sidebar -->
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
<?php if (strlen(Options::get('about')) > 0) { ?>
			<li id="widget-about" class="widget">
				<h3><?php _e('About', 'binadamu'); ?></h3>
				<p><?php Options::out('about'); ?></p>
			</li>
<?php } ?>
			<?php
				$theme->display('recententries.widget');
				if (Plugins::is_loaded('FreshComments')) $theme->freshcomments();
				if (Plugins::is_loaded('RecentComments')) $theme->show_recentcomments();
				if (Plugins::is_loaded('TagCloud', '1.3')) $theme->display('tagcloud.widget');
			?>
		</ul>
		<ul id="sidebar-2" class="xoxo">
			<?php
				if (Plugins::is_loaded('Jaiku')) $theme->jaiku();
				if (Plugins::is_loaded('Twitter')) $theme->twitter();
				if (Plugins::is_loaded('AudioScrobbler')) $theme->audioscrobbler();
				if (Plugins::is_loaded('FlickrFeed')) $theme->flickrfeed();
				if (Plugins::is_loaded('FlickrRSS', '1.3')) $theme->display('flickrrss.widget');
				if (Plugins::is_loaded('DeliciousFeed')) $theme->deliciousfeed();
				if (Plugins::is_loaded('FreshSurf')) $theme->display('freshsurf.widget');
				if (Plugins::is_loaded('Blogroll')) $theme->show_blogroll();
				$theme->display('feedlink.widget');
				$theme->display('admin.widget');
			?>
		</ul>
	</div>
	<hr />
<!-- /sidebar -->
