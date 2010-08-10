<div class="block" id="blogroll">
	<h3><?php echo $blogroll_title; ?></h3>
	<ul>
	<?php if ( ! empty( $blogs ) ) { foreach( $blogs as $blog ) { ?>
		<li><a href="<?php echo $blog->info->url; ?>"><?php echo $blog->title; ?></a></li>
	<?php } } ?>
	</ul>
</div>