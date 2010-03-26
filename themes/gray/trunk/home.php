<?php $theme->display('head'); ?>

	<div id="bd">
		<div class="yui-gc">
	    <div class="yui-u first">
				<?php Session::messages_out(); ?>
				<?php $theme->area('primary'); ?>
		  </div>
	    <div class="yui-u">
				<?php $theme->area('recent'); ?>
		  </div>
		</div>
    <div class="yui-gb">
			<div class="yui-u first">
				<?php $theme->area('home_a'); ?>
			</div>
			<div class="yui-u">
				<?php $theme->area('home_b'); ?>
			</div>
			<div class="yui-u">
				<?php $theme->area('home_c'); ?>
			</div>
		</div>
	</div>
			
<?php $theme->display('foot'); ?>