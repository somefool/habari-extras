<div class="container">
	<p>
		<label for="<?php echo $id; ?>" class="<?php echo $class; ?>"><?php echo $caption; ?></label>
		<input type="file" name="<?php echo $field; ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>" <?php echo isset($tabindex) ? ' tabindex="' . $tabindex . '"' : ''?>>
	</p>
<?php $control->errors_out('<li>%s</li>', '<ul class="error">%s</ul>'); ?>
</div>
