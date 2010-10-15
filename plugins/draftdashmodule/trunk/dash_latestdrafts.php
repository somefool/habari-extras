	<ul class="items">

		<?php foreach((array)$recent_posts as $post): ?>
		<li class="item clear">
			<span class="date pct20 minor"><?php echo DraftDashModule::nice_time( $post->pubdate ); ?></span>
			<span class="title pct80"><a href="<?php URL::out('admin', 'page=publish&id=' . $post->id); ?>" title="<?php
				$post_title = ( $post->title ? $post->title : _t( "[untitled post id: %d]" , array( $post->id ),'draftdashmodule' ) );
			printf( _t('Edit \'%s\''), $post_title ); ?>"><?php echo $post_title; ?></a></span>
		</li>
		<?php endforeach; ?>

	</ul>
