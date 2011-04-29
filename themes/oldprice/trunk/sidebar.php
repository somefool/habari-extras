	<div id="primary" class="sidebar">
		<ul class="xoxo">
		<?php Plugins::act( 'theme_sidebar_top' ); ?> 
			<?php if ( Plugins::is_loaded('Twitter') ) $theme->twitter(); ?>
			<?php $theme->display('recentcomments.widget'); ?>
			<?php if ( $this->request->display_home || $this->request->display_404 ) { ?>
			<?php if ( Plugins::is_loaded('Blogroll') ) $theme->show_blogroll(); ?>
			<?php } ?>
			<?php if ( Plugins::is_loaded('TagCloud') ) $theme->display('tagcloud.widget'); ?>
		<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
		</ul>
	</div>	