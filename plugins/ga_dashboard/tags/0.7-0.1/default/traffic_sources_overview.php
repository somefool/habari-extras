<?php 
/**
 * traffic_sources_overview.php
 * Template for Traffic Sources Overview dashboard module
 * 
 * If you need to override the options, do it here.
 **/
?>
<div class="pct90" id="div_<?php echo $slug; ?>"></div>
<script type="text/javascript">
	var opts = { <?php echo $js_opts; ?> };
	<?php echo $js_data; ?>
	<?php echo $js_draw; ?>
</script>
