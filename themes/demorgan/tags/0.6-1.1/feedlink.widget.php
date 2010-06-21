			<li id="widget-feeds" class="widget">
				<h3><?php _e('Subscribe', 'demorgan'); ?></h3>
				<ul>
					<li><a href="<?php URL::out('atom_feed', array('index' => '1')); ?>"><?php _e('All posts', 'demorgan'); ?></a></li>
					<li><a href="<?php URL::out('atom_feed_comments'); ?>"><?php _e('All comments', 'demorgan'); ?></a></li>
				</ul>
			</li>
