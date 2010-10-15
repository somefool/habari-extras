	<ul class="items">

		<?php foreach((array)$recent_posts as $post): ?>
		<li class="item clear">
			<span class="date pct20 minor"><?php echo DraftDashModule::nice_time( $post->pubdate ); ?></span>
			<span class="title pct80"><a href="<?php URL::out('admin', 'page=publish&id=' . $post->id); ?>" title="<?php printf( _t('Edit \'%s\''), $post->title ); ?>"><?php echo $post->title; ?></a></span>
		</li>
		<?php endforeach; ?>

	</ul>
