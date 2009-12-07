			<li id="widget-twitterlitte" class="widget">
				<h3><?php _e('Soliloquy', 'binadamu'); ?></h3>
				<ul>
				<?php
					if (is_array($tweets)) { // Multiple tweets
						foreach ($tweets as $tweet) {
							printf('<li class="twitter-message">%1$s <a href="%2$s"><abbr title="%3$s">#</abbr></a></li>', $tweet->message_out, $tweet->url, $tweet->created_at);
						}
						printf('<li class="twitter-more"><a href="%s">' . _t('Read more…', 'binadamu') . '</a></li>', $tweets[0]->user->profile_url);
					}
					else { // Exceptions
						echo '<li class="twitter-error">' . $tweets . '</li>';
					}
				?>
				</ul>
			</li>