<div class="container plugins downloadableplugins" id="downloadableplugins">

	<h2><?php _e('Downloadable Plugins'); ?></h2>
	
	<?php
	foreach ( $loadable as $plugin) {
		$theme->plugin = $plugin;
		$theme->display('plugin');
	}
	?>

</div>
