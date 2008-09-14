			<li id="widget-feeds" class="widget">
				<h3><?php _e('Subscribe', 'binadamu'); ?></h3>
				<ul>
					<li><a href="<?php URL::out('atom_feed', array('index' => '1')); ?>"><?php _e('All posts', 'binadamu'); ?></a></li>
					<li><a href="<?php URL::out('atom_feed_comments'); ?>"><?php _e('All comments', 'binadamu'); ?></a></li>
				</ul>
			</li>
