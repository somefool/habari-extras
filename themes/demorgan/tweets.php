			<li id="widget-twitter" class="widget">
				<h3><?php _e('Soliloquy', 'demorgan'); ?></h3>
				<p>
				<?php
					printf('%1$s <br /><a href="http://twitter.com/%2$s">' . _t('Read more…', 'demorgan') . '</a>',
							$tweet_text,
							urlencode(Options::get('twitter__username')),
							$tweet_time);
				?>
				</p>
			</li>
