			<li id="widget-audioscrobbler" class="widget">
				<h3><?php _e('Listening', 'binadamu'); ?></h3>
				<p>
				<?php
					if ($track instanceof SimpleXMLElement) {
						printf('“<a href="%1$s">%2$s</a>” performed by %3$s', $track->url, $track->name, $track->artist);
					}
					else {
						echo $track;
					}
				?>
				</p>
			</li>
