<?php include 'header.php'; ?>
	<div class="post">
		<h2 class="post_title"><span>Please log in</span></h2>
		<div class="post_content">
			<?php include 'loginform.php'; ?>
			<?php Plugins::act( 'theme_login' ); ?>
		</div>
	</div>
<?php include 'sidebar.php'; ?>
<?php include 'footer.php'; ?>
