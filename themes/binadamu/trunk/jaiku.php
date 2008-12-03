			<li id="widget-jaiku" class="widget">
				<h3><?php _e('Soliloquy', 'binadamu'); ?></h3>
				<ul>
				<?php
					if (is_array($presences)) {
						foreach ($presences as $presence) {
							printf('<li class="jaiku-message">%1$s <a href="%2$s"><abbr title="%3$s">%4$s</abbr></a> (<a href="%2$s#comments">%5$s</a>)</li>', $presence->message_out, $presence->url, $presence->created_at, $presence->created_at_relative, count($presence->comments));
						}
						printf('<li class="jaiku-more"><a href="%s">' . _t('Read more…', 'binadamu') . '</a></li>', $presences[0]->user->url);
					}
					else {
						echo '<li class="jaiku-error">' . $presences . '</li>';
					}
				?>
				</ul>
			</li>