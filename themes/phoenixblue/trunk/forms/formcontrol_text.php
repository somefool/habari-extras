		<p>
			<input<?php if ( isset( $control_title ) ) { ?> title="<?php echo $control_title; ?>"<?php } else { echo ( isset( $title ) ? " title=\"$title\"" : '' ); } ?> class="textfield" tabindex="<?php echo $tabindex; ?>" size="22" type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars( $value ); ?>" />
			<label<?php if ( isset( $label_title ) ) { ?> title="<?php echo $label_title; ?>"<?php } else { echo ( isset( $title ) ? " title=\"$title\"" : '' ); } ?> for="<?php echo $field; ?>"><small><?php echo $caption; ?></small></label>
			<?php $control->errors_out( '<li>%s</li>', '<ul class="error">%s</ul>' ); ?>
</p>
