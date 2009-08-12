<div style="display:none"<?php echo isset( $class ) ? " class=\"$class\"" : ''; ?><?php echo isset( $id ) ? " id=\"$id\"" : ''; ?>>
	<label<?php if ( isset( $label_title ) ) { ?> title="<?php echo $label_title; ?>"<?php } else { echo ( isset( $title ) ? " title=\"$title\"" : '' ); } ?> for="<?php echo $field; ?>"><?php echo $this->caption; ?></label>
	<input<?php if ( isset( $control_title ) ) { ?> title="<?php echo $control_title; ?>"<?php } else { echo ( isset( $title ) ? " title=\"$title\"" : '' ); } ?> type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars( $value ); ?>">
	<?php $control->errors_out( '<li>%s</li>', '<ul class="error">%s</ul>' ); ?>
</div>
