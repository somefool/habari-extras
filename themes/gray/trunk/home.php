<?php $theme->display('head'); ?>
<?php

$primary = implode( '', ( array ) $theme->area_return('primary') );
$sidebar = implode( '', ( array ) $theme->area_return('sidebar') );

$primary_class = 'yui-b';
$show_sidebar = true;
if($sidebar == '') {
	$show_sidebar = false;
	$primary_class = '';
}

?>
	<div id="bd">
		<div id="yui-main">
			<div id="primary" class="<?php echo $primary_class; ?>">
				<?php Session::messages_out(); ?>
				<?php echo $primary ?>
		  </div>
		</div>
		<?php if($show_sidebar): ?>
		<div id="sidebar" class="yui-b">
			<?php echo $sidebar; ?>
	  </div>
	  <?php endif; ?>
    <div id="columns" class="yui-gb">
			<div id="home_a" class="yui-u first">
				<?php $theme->area('home_a'); ?>
			</div>
			<div id="home_b" class="yui-u">
				<?php $theme->area('home_b'); ?>
			</div>
			<div id="home_c" class="yui-u">
				<?php $theme->area('home_c'); ?>
			</div>
		</div>
	</div>
			
<?php $theme->display('foot'); ?>