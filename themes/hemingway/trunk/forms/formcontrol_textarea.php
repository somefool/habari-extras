<div>
<label class="hidden" for="<?php echo $field; ?>"><?php echo $this->caption; ?></label>
<textarea id="<?php echo $field; ?>" class="commentbox" <?php echo "rows=\"" . ( isset( $rows ) ? $rows : 10 ) . "\" cols=\"" . ( isset( $cols ) ? $cols : 100 ). "\""; if ( isset( $tabindex ) ) { ?> tabindex="<?php echo $tabindex; ?>"<?php } ?>>
<?php echo htmlspecialchars( $value ); ?></textarea>
<?php if ( $message != '' ) : ?>
<p class="error"><?php echo $message; ?></p>
<?php endif; ?>
</div>







