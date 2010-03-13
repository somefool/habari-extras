<input type="submit"<?php if(isset($disabled) && $disabled) { ?>disabled <?php } if ( isset( $tabindex ) ) { ?> tabindex="<?php echo $tabindex; ?>"<?php } ?> name="<?php echo $field; ?>" id="<?php echo $id; ?>" value="<?php echo htmlspecialchars($caption); ?>">
<?php if($message != '') : ?>
<p class="error"><?php echo $message; ?></p>
<?php endif; ?>
