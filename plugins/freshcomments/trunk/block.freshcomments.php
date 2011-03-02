<ul id="recentcomments">
	<?php foreach ( $content->freshcomments as $post ): ?>
	<li>
		<a href="<?php echo $post[ 'post' ]->permalink; ?>" rel="bookmark" class="comment-entry-title"><?php echo $post[ 'post' ]->title_out; ?></a>
		<a href="<?php echo $post[ 'post' ]->permalink; ?>#comments" class="comment-count" title="<?php printf( _n( '%1$d comment', '%1$d comments', $post[ 'post' ]->comments->approved->count, 'freshcomments' ), $post[ 'post' ]->comments->approved->count ); ?>"><?php echo $post[ 'post' ]->comments->approved->comments->count; ?></a>
		<ul class="comment-authors">
			<?php foreach ( $post[ 'comments' ] as $comment ): ?>
			<li><a style="color:<?php echo $comment[ 'color' ]; ?>" href="<?php echo $comment[ 'comment' ]->post->permalink; ?>#comment-<?php echo $comment[ 'comment' ]->id; ?>" title="<?php printf( _t( 'Posted at %1$s', 'freshcomments' ), $comment[ 'comment' ]->date->get( ) ); ?>"><?php echo $comment[ 'comment' ]->name; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</li>
	<?php endforeach; ?>
</ul>