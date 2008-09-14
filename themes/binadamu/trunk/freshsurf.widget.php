			<li id="widget-freshsurf" class="widget">
				<h3><?php _e('Notepad', 'binadamu') ?></h3>
				<ul>
				<?php
					foreach ($delicious->post as $item) {
						printf('<li><a href="%1$s" title="%2$s" rel="%3$s">%4$s</a></li>', $item['href'], $item['extended'], $item['tag'], $item['description']);
					}
				?>
				</ul>
			</li>
