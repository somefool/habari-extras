<?php if (!empty($blogs)) { ?>
			<li id="widget-blogroll" class="widget">
				<h3><?php echo $blogroll_title; ?></h3>
				<ul>
				<?php
					foreach($blogs as $blog) {
						printf('<li class="vcard"><a href="%1$s" class="url" title="%2$s" rel="%3$s %4$s">%5$s</a></li>', $blog->info->url, $blog->content, $blog->info->relationship, $blog->xfn_relationships, $blog->title);
					}
				?>
				</ul>
			</li>
<?php } ?>
