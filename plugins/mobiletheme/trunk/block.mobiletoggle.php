<?php if($content->mobile): ?>
<a href="<?php echo $content->mobile_off; ?>">Use the standard site.</a>
<?php else: ?>
<a href="<?php echo $content->mobile_on; ?>">Use the mobile site.</a>
<?php endif; ?>
<?php // Utils::debug($_template_list, $_template); ?>