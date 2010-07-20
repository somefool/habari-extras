<!-- Sidebar menu div -->
<div id="menu">


	<?php Plugins::act( 'theme_sidebar_top' ); ?>

<ul>
	<?php if ($this->request->display_search) { ; ?>
	<li id="search">
<?php _e('Search results for \'%s', array( htmlspecialchars( $criteria ) ) ); ?>'</li>
	<?php } ?>
	
<div id="sidebar_area">
<?php $theme->area('sidebar'); ?>
</div>

<li class="user"><?php _e('User'); ?><ul> 
	<?php $theme->display ( 'loginform' ); ?>
</ul></li>






</ul>



<?php Plugins::act( 'theme_sidebar_bottom' ); ?>



</div>
