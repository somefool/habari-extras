
<div class="container">
	<p>
		<label for="<?php echo $id; ?>" class="<?php echo $class; ?>"><?php echo $caption; ?></label>
		<select class="styleformelement select <?php echo $class; ?>" id="<?php echo $field; ?>" name="<?php echo $field . ( $multiple ? '[]' : '' ); ?>"<?php echo ( $multiple ? ' multiple="multiple" size="' . intval($size) . '"' : '' ) ?>>
		<?php foreach($options as $opts_key => $opts_val) : ?>
			<?php if (is_array($opts_val)) : ?>
				<optgroup label="<?php echo $opts_key; ?>">
				<?php foreach($opts_val as $opt_key => $opt_val) : ?>
					<option value="<?php echo $opt_key; ?>"<?php echo ( in_array( $opt_key, (array) $value ) ? ' selected' : '' ); ?>><?php echo Utils::htmlspecialchars($opt_val); ?></option>
				<?php endforeach; ?>
				</optgroup>
			<?php else : ?>
				<option value="<?php echo $opts_key; ?>"<?php echo ( in_array( $opts_key, (array) $value ) ? ' selected' : '' ); ?>><?php echo Utils::htmlspecialchars($opts_val); ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
		</select>
	</p>
<?php $control->errors_out('<li>%s</li>', '<ul class="error">%s</ul>'); ?>
</div>
