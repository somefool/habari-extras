<?php if(count($posts) > 0): ?>
	<ol>
	<?php foreach($posts as $post): ?>
		<li>
			<a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?> was published on <?php $post->pubdate->out('F j, Y'); ?>">
				<?php echo $post->title; ?>
			</a>
		</li>
	<?php endforeach; ?>
	</ol>
<?php else: ?>
	<p class="nothing">No posts could be found matching that query.</p>
<?php endif; ?>