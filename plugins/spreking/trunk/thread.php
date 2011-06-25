<?php if ( !defined( 'HABARI_PATH' ) ) { die('No direct access'); } ?>
<?php $theme->display ('header'); ?>
<?php
if( $post->anonymous )
{
	$author = $post->anonymous;
}
else
{
	$author = $post->author;
}
?>
<!-- entry.multiple -->
	<div class="content">
	<div id="primary">
		
		<div id="primarycontent">
					<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->statusname; ?>">

						<div class="entry-head">
							<h3 class="entry-title"><a href="<?php echo $post->permalink; ?>" title="<?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
							<small class="entry-meta">
							<span class="chronodata"><abbr class="published"><?php $post->pubdate->out(); ?></abbr></span>
							<span class="authordata">Published by <a href="<?php echo $author->permalink; ?>"><?php echo $author->displayname; ?></a></span>
							</small>
						</div>

						<div class="entry-content">
							<?php echo $post->content_out; ?>
						</div>

						<?php
						if( $post->closed && Spreking::has_permission('open_thread', $thread))
						{
							echo '<p><a href="' . URL::get('forum_open_thread', array( 'thread' => $thread->id, 'forum' => $forum->slug )) . '">' . _t('Open Thread') . '</a></p>';
						}
						elseif( Spreking::has_permission('close_thread', $thread) )
						{
							echo '<p><a href="' . URL::get('forum_close_thread', array( 'thread' => $thread->id, 'forum' => $forum->slug )) . '">' . _t('Close Thread') . '</a></p>';
						}
						?>
						
						<?php if( Spreking::has_permission('edit_thread', $thread) ): ?>
							<p><a href="<?php echo $thread->editlink; ?>">Edit Thread</a></p>
						<?php endif; ?>
					</div>
					
					<div id="replies">
						<h2>Replies</h2>
						<ol>
						<?php
						foreach( $replies as $reply ):
							if( $reply->anonymous )
							{
								$replier = $reply->anonymous;
							}
							else
							{
								$replier = $reply->author;
							}
						?>
							<li id="reply-<?php echo $reply->id; ?>">
								<a href="<?php echo $reply->permalink; ?>">Reply</a> by <a href="<?php echo $replier->permalink; ?>"><?php echo $replier->displayname; ?></a>:
								<p><?php echo $reply->content_out; ?></p>
								<?php if( Spreking::has_permission('edit_reply', $reply) ): ?>
									<p><a href="<?php echo $reply->editlink; ?>">Edit Reply</a></p>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
						</ol>
					</div>
					
					<div id="reply">
						<?php if( $post->closed ): ?>
							<h2>Thread is Closed</h2>
						<?php else: ?>
						<h2>Respond</h2>
						<?php $theme->reply_form( $thread, $forum ); ?>
						<?php endif; ?>
					</div>
					
		</div>

	</div>

	<hr>

	<div class="secondary">

<?php $theme->display ('sidebar'); ?>

	</div>

	<div class="clear"></div>
	</div>
<!-- /entry.multiple -->
<?php $theme->display ( 'footer'); ?>
