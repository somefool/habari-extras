<div class="<?php echo $field; ?> <?php echo $required ? 'required' : ''; ?>">
	<label for="<?php echo $field; ?>"><?php echo $caption; ?> <?php echo $required ? '' : _t('<span>(optional)</span>'); ?></label>
	<input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo Utils::htmlspecialchars( $value ); ?>" placeholder="<?php echo $placeholder; ?>" tabindex="<?php echo $tabindex; ?>" <?php echo $required ? 'required' : ''; ?> />
</div>