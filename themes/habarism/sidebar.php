<!-- sidebar -->
<?php Plugins::act( 'theme_sidebar_top' ); ?>
		<div class="block" id="search">
			<h3>Search</h3>
			<?php include 'searchform.php'; ?>
		</div>
		<div class="block" id="flickr">
			<div class="images clearfix">
				<?php echo $flickr_images; ?>
			</div>
		</div>
		<div class="block" id="recent_comments">
			<h3>Recent comments</h3>
			<ul>
				<?php foreach($recent_comments as $recent_comment): ?>
				<li><span class="user"><a href="<?php echo $recent_comment->url; ?>"><?php echo $recent_comment->name; ?></a></span> on <a href="<?php echo $recent_comment->post->permalink; ?>"><?php echo $recent_comment->post->title; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="block">
			<h3>Blogroll</h3>
			<p>Here is a little collection of links to the websites I enjoy reading. Friends, family and fellow bloggers.</p>
			<ul>
				<li><a href="http://www.habariproject.org/en/">Habari</a></li>
				<li><a href="http://theundersigned.net">The undersigned</a></li>
				<li><a href="http://femalecliche.net">Female Cliché</a></li>
			</ul>
			<p>Please get in touch if you feel betrayed.</p>
		</div>
		<div class="block" id="recent_posts">
			<h3>Recent posts</h3>
			<ul>
				<?php foreach($recent_posts as $recent_post): ?>
				<li><a href="<?php echo $recent_post->permalink; ?>"><?php echo $recent_post->title; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="block" id="login">
			<h3>User</h3>
			<?php include 'loginform.php'; ?>
		</div>
		<div class="block" id="footer">
			<p><?php Options::out('title'); _e(' is powered by'); ?> <a href="http://www.habariproject.org/" title="Habari">Habari</a> and <a href="#">Habarism</a><br>
			<a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Atom Entries</a> and <a href="<?php URL::out( 'atom_feed_comments' ); ?>">Atom Comments</a></p>
			<?php $theme->footer(); ?>
		</div>
	</div>    
<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
<!-- /sidebar -->