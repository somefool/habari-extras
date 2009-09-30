        <h2>Today's GetCliky Stats</h2><div class="handle">&nbsp;</div>
        <ul class="items">
        	<li class="item clear">
            	<span class="pct90">Site Rank</span>
                <span class="comments pct10"><?php print $site_rank; ?></span>
            </li>
        	<li class="item clear">
            	<span class="pct90">Current Visitors (Online Now)</span>
                <span class="comments pct10"><?php print $current_visitors; ?></span>
            </li>
			<li class="item clear">
                <span class="pct90">Unique Visitors</span>
                <span class="comments pct10"><?php print $unique_visitors; ?></span>
            </li>
			<li class="item clear">
                <span class="pct90">No of Actions (sum of page views, downloads, and outbound links)</span>
                <span class="comments pct10"><?php print $todays_actions; ?></span>
            </li>
            <li class="item clear">
                <span class="pct90">Average Actions</span>
                <span class="comments pct10"><?php print $actions_average; ?></span>
            </li>
            <li class="item clear">
                <span class="pct90">Total Time Spent On Site</span>
                <span class="comments pct10"><?php print $time_total; ?></span>
            </li>
            <li class="item clear">
                <span class="pct90">Average Time</span>
                <span class="comments pct10"><?php print $time_average; ?></span>
            </li>
        </ul>
        <p>
        	For more statistics for your site visit <a href="http://getclicky.com/stats/home?site_id=<?php print $siteid;?>">GetClicky</a>
        </p>
