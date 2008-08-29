<div id="fireeagle">
  <?php if (!empty($fireeagle_location)): ?>
    <h2><?php echo _t('Location', 'fireeagle'); ?></h2>
    <?php echo sprintf(_t('I\'m at %s.', 'fireeagle'), $fireeagle_location); ?>

    <?php if (Plugins::is_loaded('14c8414f-6cdf-11dd-b14a-001b210f913f')): ?>
    <a href="http://maps.google.com/?ll=<?php echo $fireeagle_latitude . ',' . $fireeagle_longitude; ?>&amp;z=<?php echo $zoom; ?>&amp;_markers=<?php echo $fireeagle_latitude . ',' . $fireeagle_longitude; ?>&amp;_size=180x180&amp;_controls=none"><?php echo $fireeagle_location; ?></a>
    <?php endif; ?>
  <?php endif; ?>
</div>