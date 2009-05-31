<div id="secondaryContent">
	<h2 id="tagline">{hi:option:tagline}</h2>
	
	<h2 id="navi">{hi:"Navigation"}</h2>
		<ul id="nav">
			<li><a href="{hi:siteurl:habari}" title="{hi:option:title}">{hi:"Home"}</a></li>
			{hi:pages}
				<li><a href="{hi:permalink}" title="{hi:title}" >{hi:title}</a></li>
			{/hi:pages}
		</ul>
	<h2 id="syndicate">Subscribe</h2>
		<ul id="subscribe">
			<li><a href="{hi:@feed_site}" title="Atom 1.0">Get Updates Via RSS</a></li>
		</ul>
	{hi:@twitter}
		
	{hi:@show_recentcomments}
	{hi:?Plugins::is_loaded( 'monthly_archives' )}
		<h2 id="month_archive">{hi:"Archives"}</h2>
	{/hi:?}
	{hi:@monthly_archives}
	
	
</div>