<?php if ( ! empty( $blogs ) ) { ?>
	<li id="widget-blogroll" class="widget widget_comments">
		<h3><?php _e('Blogroll'); ?></h3>
		<ul>
		<?php
			foreach ( $blogs as $blog ) {
				printf('<li><a href="%1$s" title="%2$s" rel="%3$s">%4$s</a></li>', $blog->url, $blog->description, $blog->rel, $blog->name);
			}
		?>
		</ul>
	</li>
<?php } ?>
