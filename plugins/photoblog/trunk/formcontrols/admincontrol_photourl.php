
<div class="container">
	<p>
		<label for="<?php echo $id; ?>" class="incontent <?php echo $class; ?>"><?php echo $caption; ?></label>
		<input type="text" name="<?php echo $field; ?>" id="<?php echo $id; ?>" class="styledformelement text <?php echo $class; ?>" size="100%" value="<?php echo htmlspecialchars($value); ?>" <?php echo isset($tabindex) ? ' tabindex="' . $tabindex . '"' : ''?>>
		<input type="button" id="pb_loadURL" name="pb_loadURL" value="Thumbnail">
	</p>
	<?php if($message != '') : ?>
	<p class="error"><?php echo $message; ?></p>
	<?php endif; ?>
</div>

