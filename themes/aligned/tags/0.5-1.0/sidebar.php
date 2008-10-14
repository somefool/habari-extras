	</div>
	<div class="right_content" id="sidebar">
		<?php Plugins::act( 'theme_sidebar_top' ); ?>
		<div class="block" id="recent_comments">
			<h3><span>Recent comments</span></h3>
			<ul>
				<?php foreach($recent_comments as $recent_comment): ?>
				<li><a href="<?php echo $recent_comment->post->permalink; ?>"><?php echo $recent_comment->name; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="block" id="flickr">
			<h3><a href="http://www.flickr.com/photos/theundersigned/">Flickr photostream</a></h3>
			<div class="images clearfix">
				<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=9&amp;display=latest&amp;size=s&amp;layout=x&amp;source=user&amp;user=35634769@N00"></script>
			</div>
		</div>
		<?php $theme->switcher(); ?>
		<?php $theme->show_blogroll(); ?>
		<?php $theme->twitter(); ?>
		<div class="block" id="recent_posts">
			<h3><span>Recent posts</span></h3>
			<ul>
				<?php foreach($recent_posts as $recent_post): ?>
				<li><a href="<?php echo $recent_post->permalink; ?>"><?php echo $recent_post->title; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="block" id="login">
			<h3><span>User</span></h3>
			<?php include 'loginform.php'; ?>
		</div>
		<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
	</div>
</div>