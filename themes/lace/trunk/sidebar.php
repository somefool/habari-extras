<!-- sidebar -->

<div id="navigation" class="span-4 last">
	<p>Navigation</p>
	<ul>
	<li><a href="<?php Site::out_url( 'habari' ); ?>" title="<?php Options::out( 'title' ); ?>"><?php echo $home_tab; ?></a></li>
<?php foreach ( $pages as $tab ) {
?>
	<li><a href="<?php echo $tab->permalink; ?>" title="<?php echo $tab->title; ?>"><?php echo $tab->title; ?></a></li>
<?php
} ?>
	</ul>
	<p>Meta</p>
	<ul>
<?php if ( $user ) { ?>
		<li><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Admin</a></li>
<?php } else { ?>
		<li><a href="<?php Site::out_url( 'admin' ); ?>" title="Login">Login</a></li>
<?php } ?>
		<li><a href="<?php URL::out( 'atom_feed_comments' ); ?>">Comments Feed</a></li>
		<li><a href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Entries Feed</a></li>
	</ul>
</div>

<!-- /sidebar -->