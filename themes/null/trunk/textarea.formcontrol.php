<div class="<?php echo $field; ?> <?php echo $required ? 'required' : ''; ?>">
	<label for="<?php echo $field; ?>" class="required"><?php echo $caption; ?></label>
	<textarea id="<?php echo $field; ?>" name="<?php echo $field; ?>" placeholder="<?php echo $placeholder; ?>" rows="10" cols="60" tabindex="<?php echo $tabindex; ?>" <?php echo $required ? 'required' : ''; ?>><?php echo Utils::htmlspecialchars( $value ); ?></textarea>
</div>
