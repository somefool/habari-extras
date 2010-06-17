<div id="sidebar">
<?php Plugins::act( 'theme_sidebar_top' ); ?>
<?php $theme->display ( 'searchform' ); ?>

<div class="separator"></div>

<div id="navigation">
<ul>
<?php foreach ( $pages as $page ) {
// list pages and remove link the current page ?>
<?php if (isset($post) && $post->slug == $page->slug) : ?>
<li><span class="nav-current"><?php echo $page->title; ?></span></li>
<?php else : ?>
<li><a href="<?php echo $page->permalink; ?>"><?php echo $page->title; ?></a></li>
<?php endif; ?>
<?php } ?>
</ul>
</div>

<?php if ( $loggedin ) { ?>
<br />
<p><?php _e('You are logged in as'); ?> <a href="<?php URL::out( 'admin', 'page=user&user=' . $user->username ) ?>"><?php echo $user->username; ?></a>.</p>
<p><?php _e('Want to'); ?> <a href="<?php Site::out_url( 'habari' ); ?>/auth/logout"><?php _e('log out'); ?>?</a></p>
<?php } ?>

<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
</div>
<div class="clear"></div>