<?php
 /*
  * Unfortunately Google's GeoMap doesn't correctly fit to the width of the containing DIV.
  * To get around this, we need to add the options for the map here in the template so that
  * we can get the current div width and add it to our options.
  */
?>
<div id="div_<?php echo $slug; ?>"></div>
<script type="text/javascript">
// Get the actual width of the div
var divWidth = document.getElementById("div_<?php echo $slug; ?>").offsetWidth;
// Load up our GeoMap options
var opts = { 
				height: 200,
				width: divWidth,
				showLegend: false
			  };
<?php echo $js_data; ?>
<?php echo $js_draw; ?>
</script>
