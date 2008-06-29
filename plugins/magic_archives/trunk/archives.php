<div id="magicArchives">
	<div id="archiveControls">
		<div class="search">
			<input type="text" name="archiveSearch" value="" id="archiveSearch">
		</div>
		<div class="control month">
			<h4><?php echo _t('Month'); ?></h4>
			<ol>
				<li class="allofthestuff active">All (<span>12</span>)</li>
				<li class="january">January</li>
				<li class="february">February</li>
				<li class="march">March</li>
				<li class="april">April</li>
				<li class="may">May</li>
				<li class="june">June</li>
				<li class="july">July</li>
				<li class="august">August</li>
				<li class="september">September</li>
				<li class="october">October</li>
				<li class="november">November</li>
				<li class="december">December</li>
			</ol>
		</div>
		<div class="control year">
			<h4><?php echo _t('Year'); ?></h4>
			<ol>
				<li class="allofthestuff active">All (<span><?php echo date('Y', time()) - 1970; ?></span>)</li>
			<?php
				$year = 1970;
				while($year <= date('Y', time())) { ?>
					<li class="y<?php echo $year;?>"><?php echo $year; ?></li>
					<?php
					$year++;
				}
			?>
			</ol>
		</div>
		<div class="control tags">
			<h4><?php echo _t('Tag'); ?></h4>
			<ol>
				<?php $tags = Tags::get(); ?>
				<li class="allofthestuff active">All (<span><?php echo count($tags); ?></span>)</li>
				<?php foreach($tags as $tag) { ?>
				<li class="<?php echo $tag->tag; ?>"><?php echo $tag->tag; ?></li>
				<?php } ?>
			</ol>
		</div>
		<div class="control type">
			<h4><?php echo _t('Type'); ?></h4>
			<ol>
				<?php $types = Post::list_active_post_types(); ?>
				<li class="allofthestuff active">All (<span><?php echo count($types); ?></span>)</li>
				<?php foreach($types as $type => $id) { if($type != 'any') { ?>
				<li class="<?php echo $type; ?>"><?php echo $type; ?></li>
				<?php } } ?>
			</ol>
		</div>
	</div>
	
	<ol id="archiveItems">
		<li class="headings"><div class="container">
			<span class="comments section"><?php echo _t('Comments'); ?></span>
			<strong class="title section"><?php echo _t('Title'); ?></strong>
			<span class="date section"><?php echo _t('Date'); ?></span>
			<span class="tags section"><?php echo _t('Tags'); ?></span>
		</div></li>
	<?php foreach(MagicArchives::get_magic_archives() as $post) { ?>
		<li id="post-<?php echo $post->id; ?>" class="<?php echo Post::type_name($post->content_type); ?> <?php echo Post::status_name($post->status); ?>">
			<a href="<?php echo $post->permalink; ?>" title="<?php echo sprintf( _t('Read %s'), $post->title ); ?>">
				<span class="comments section"><strong><?php echo $post->comments->approved->count; ?></strong> <span class="unit"><?php echo _n( 'comment', 'comments', $post->comments->approved->count ); ?></span></span>
				<strong class="title section"><?php echo $post->title; ?></strong>
				<span class="date section">
					<span class="month"><?php echo date('F', strtotime($post->pubdate_out)); ?></span>
					<span class="day"><?php echo date('j', strtotime($post->pubdate_out)); ?></span><span class="sep">,</span>
					<span class="year"><?php echo date('Y', strtotime($post->pubdate_out)); ?></span>
				</span>
				<span class="tags section"><?php foreach($post->tags as $key => $tag) { ?>
					<span class="tag"><?php echo $tag; ?></span><?php } ?>
				</span>
				<span class="type section"><?php echo Post::type_name($post->content_type); ?></span>
			</a>
		</li>
	<?php } ?>
	</ol>
</div>