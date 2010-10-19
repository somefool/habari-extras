<?php $theme->display('head'); ?>
	<div id="bd">
		<div id="yui-main">
	    <div id="primary" class="yui-b">
				<?php Session::messages_out(); ?>
				<?php $theme->area('primary'); ?>
		  </div>
		</div>
    <div id="sidebar" class="yui-b">
			<?php $theme->area('sidebar'); ?>
	  </div>
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